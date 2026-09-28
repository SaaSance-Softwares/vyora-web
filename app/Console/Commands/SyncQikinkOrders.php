<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ThemeSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncQikinkOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qikink:sync-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync order statuses and tracking details from Qikink API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Qikink order sync...');

        // Get Qikink settings
        $settings = ThemeSetting::where('group', 'integration.qikink')->get()->keyBy('key');
        if (($settings->get('enabled')->value ?? '0') !== '1') {
            $this->warn('Qikink integration is disabled. Aborting sync.');
            return;
        }

        $clientId = $this->maybeDecrypt($settings->get('client_id')?->value);
        $clientSecret = $this->maybeDecrypt($settings->get('client_secret')?->value);
        $mode = $settings->get('mode')?->value ?? 'test';

        if (! $clientId || ! $clientSecret) {
            $this->error('Qikink credentials missing.');
            return;
        }

        $baseUrl = $mode === 'live' ? 'https://api.qikink.com' : 'https://sandbox.qikink.com';

        // 1. Get Token
        $tokenResponse = Http::asForm()->post("$baseUrl/api/token", [
            'ClientId' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (! $tokenResponse->successful() || ! $tokenResponse->json('Accesstoken')) {
            $this->error('Failed to authenticate with Qikink.');
            return;
        }

        $accessToken = $tokenResponse->json('Accesstoken');

        // 2. Fetch orders that need syncing
        // (Orders with a Qikink ID, not yet delivered or cancelled)
        $orders = Order::whereNotNull('qikink_order_id')
            ->whereNotIn('status', ['delivered', 'cancelled', 'refunded'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No active Qikink orders to sync.');
            return;
        }

        $this->info('Found ' . $orders->count() . ' orders to sync.');

        foreach ($orders as $order) {
            $this->syncOrder($order, $accessToken, $clientId, $baseUrl);
            // Sleep for 2 seconds to ensure we NEVER exceed QikInk's 30 requests/minute rate limit
            usleep(2000000); // 2.0 seconds
        }

        $this->info('Qikink order sync complete.');
    }

    private function syncOrder(Order $order, string $accessToken, string $clientId, string $baseUrl)
    {
        try {
            // Using raw cURL due to Qikink's strict case-sensitive header requirements 
            // and occasional hanging connections if normalized by Laravel/Guzzle
            $ch = curl_init("$baseUrl/api/order?id=" . urlencode($order->qikink_order_id));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 seconds timeout
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "ClientId: $clientId",
                "Accesstoken: $accessToken"
            ]);

            $responseBody = curl_exec($ch);
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \Exception("cURL Error: " . $curlError);
            }

            if ($httpStatus >= 200 && $httpStatus < 300) {
                $qikinkOrder = json_decode($responseBody, true);
                
                if (!$qikinkOrder || isset($qikinkOrder['error'])) {
                    $this->error("Error fetching order {$order->order_number}: " . ($qikinkOrder['error'] ?? 'Unknown error'));
                    return;
                }

                $updateData = [];

                // 1. Update Status (Only Shipping & Tracking Statuses)
                $oldStatusId = $order->order_status_id;
                $newStatusId = null;

                $qikinkData = $qikinkOrder['order'] ?? $qikinkOrder; // Handle potential nested 'order' wrapper
                if (is_array($qikinkData) && array_key_exists(0, $qikinkData)) {
                    $qikinkData = $qikinkData[0]; // QikInk sometimes returns an array of objects
                }
                $statusString = $qikinkData['status'] ?? '';
                if (!empty($qikinkData['shipping']['status'])) {
                    $statusString .= ' ' . $qikinkData['shipping']['status'];
                }
                
                if (!empty($statusString)) {
                    $qStatus = strtolower(trim($statusString));
                    $mappedName = null;

                    // Shipping/Tracking mapped to OMS Status Names
                    if (str_contains($qStatus, 'manifested') || str_contains($qStatus, 'ready to ship')) {
                        $mappedName = 'Manifested';
                    } elseif (str_contains($qStatus, 'transit') || str_contains($qStatus, 'shipped')) {
                        $mappedName = 'Shipped';
                    } elseif (str_contains($qStatus, 'out for delivery')) {
                        $mappedName = 'Out for Delivery';
                    } elseif (str_contains($qStatus, 'delivered') || str_contains($qStatus, 'delivery successful')) {
                        $mappedName = 'Delivered';
                    } elseif (str_contains($qStatus, 'exception') || str_contains($qStatus, 'undelivered')) {
                        $mappedName = 'Delivery Failed';
                    } elseif (str_contains($qStatus, 'rescheduled')) {
                        $mappedName = 'Delivery Rescheduled';
                    } elseif (str_contains($qStatus, 'rto') || str_contains($qStatus, 'returned')) {
                        $mappedName = 'Returned';
                    } elseif (str_contains($qStatus, 'cancel')) {
                        $mappedName = 'Cancelled';
                    }

                    if ($mappedName) {
                        $statusModel = \App\Models\OrderStatus::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($mappedName)])->first();
                        
                        if ($statusModel) {
                            // Only update if it actually changed!
                            if ($order->order_status_id !== $statusModel->id) {
                                $updateData['order_status_id'] = $statusModel->id;
                                $updateData['status'] = strtolower($statusModel->name);
                                $newStatusId = $statusModel->id;
                            }
                        } else {
                            if (strtolower($order->status) !== strtolower($mappedName)) {
                                $updateData['status'] = strtolower($mappedName); // Fallback string
                            }
                        }

                        if ($mappedName === 'Shipped' && !$order->shipped_at) {
                            $updateData['shipped_at'] = now();
                        }
                        if ($mappedName === 'Delivered' && !$order->delivered_at) {
                            $updateData['delivered_at'] = now();
                        }
                    } else {
                        \Log::warning("Qikink Sync: Could not map status for {$order->order_number}", [
                            'raw_status_string' => $statusString,
                            'qikink_order' => $qikinkOrder
                        ]);
                    }
                }

                // 2. Update Shipping Details
                if (!empty($qikinkData['shipping'])) {
                    $shipping = $qikinkData['shipping'];
                    if (!empty($shipping['awb']) && !$order->tracking_number) {
                        $updateData['tracking_number'] = $shipping['awb'];
                    }
                    if (!empty($shipping['tracking_link']) && !$order->tracking_url) {
                        $updateData['tracking_url'] = $shipping['tracking_link'];
                    }
                }

                if (!empty($qikinkData['shipping_type']) && !$order->courier_partner) {
                    $updateData['courier_partner'] = $qikinkData['shipping_type'];
                }

                if (!empty($updateData)) {
                    $order->update($updateData);
                    $this->info("Updated order {$order->order_number} successfully.");
                    
                    // Trigger Emails and WhatsApp if status changed!
                    if ($newStatusId && $newStatusId !== $oldStatusId) {
                        $this->fireNotifications($order, $newStatusId);
                    }
                } else {
                    $this->line("No updates for order {$order->order_number}.");
                }
            } else {
                $this->error("Failed to fetch order {$order->order_number}. HTTP " . $httpStatus . " Response: " . $responseBody);
            }
        } catch (\Exception $e) {
            $this->error("Exception while syncing order {$order->order_number}: " . $e->getMessage());
            Log::error('Qikink Sync Exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function maybeDecrypt(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return $value;
        }
    }

    private function fireNotifications(Order $order, $newStatusId)
    {
        $newStatus = \App\Models\OrderStatus::with(['smsTemplate', 'emailTemplate', 'whatsappTemplate'])->find($newStatusId);
        if (!$newStatus) return;

        $email = $order->user?->email ?? $order->shippingAddress?->email;

        // Email
        if ($newStatus->emailTemplate) {
            try {
                if ($email) {
                    \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\DynamicOrderStatusEmail($order, $newStatus->emailTemplate));
                }
            } catch (\Exception $e) {
                Log::error("Qikink Sync Email Failed: ".$e->getMessage());
            }
        } else {
            // Legacy hardcoded fallback for Shipped / Delivered / Cancelled
            $statusNameLower = strtolower($newStatus->name);
            if (in_array($statusNameLower, ['shipped', 'cancelled', 'delivered', 'returned'])) {
                try {
                    if ($email) {
                        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\OrderStatusEmail($order, $statusNameLower));
                    }
                } catch (\Exception $e) {
                    Log::error("Qikink Legacy Email Failed: ".$e->getMessage());
                }
            }
        }

        // WhatsApp
        if ($newStatus->whatsappTemplate) {
            try {
                app(\App\Services\WhatsAppService::class)->sendDynamicWhatsApp($order, $newStatus->whatsappTemplate);
            } catch (\Exception $e) {
                Log::error("Qikink Sync WhatsApp Failed: ".$e->getMessage());
            }
        }
    }
}
