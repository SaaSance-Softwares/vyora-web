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
            // Optional: small delay to avoid hitting the 30 requests/min rate limit if processing many orders
            usleep(500000); // 0.5 seconds
        }

        $this->info('Qikink order sync complete.');
    }

    private function syncOrder(Order $order, string $accessToken, string $clientId, string $baseUrl)
    {
        try {
            $response = Http::withHeaders([
                'Accesstoken' => $accessToken,
                'ClientId' => $clientId,
            ])->get("$baseUrl/api/order", [
                'id' => $order->qikink_order_id
            ]);

            if ($response->successful()) {
                $qikinkOrder = $response->json();
                
                if (!$qikinkOrder || isset($qikinkOrder['error'])) {
                    $this->error("Error fetching order {$order->order_number}: " . ($qikinkOrder['error'] ?? 'Unknown error'));
                    return;
                }

                $updateData = [];

                // 1. Update Status (Only Shipping & Tracking Statuses)
                $oldStatusId = $order->order_status_id;
                $newStatusId = null;

                if (!empty($qikinkOrder['status'])) {
                    $qStatus = strtolower(trim($qikinkOrder['status']));
                    $mappedName = null;

                    // Shipping/Tracking mapped to OMS Status Names
                    if (str_contains($qStatus, 'manifested') || str_contains($qStatus, 'ready to ship')) {
                        $mappedName = 'Manifested';
                    } elseif (str_contains($qStatus, 'transit') || str_contains($qStatus, 'shipped')) {
                        $mappedName = 'Shipped';
                    } elseif (str_contains($qStatus, 'out for delivery')) {
                        $mappedName = 'Out for Delivery';
                    } elseif (str_contains($qStatus, 'delivered')) {
                        $mappedName = 'Delivered';
                    } elseif (str_contains($qStatus, 'exception') || str_contains($qStatus, 'undelivered')) {
                        $mappedName = 'Delivery Failed';
                    } elseif (str_contains($qStatus, 'rto') || str_contains($qStatus, 'returned')) {
                        $mappedName = 'Returned';
                    } elseif (str_contains($qStatus, 'cancel')) {
                        $mappedName = 'Cancelled';
                    }

                    if ($mappedName) {
                        $statusModel = \App\Models\OrderStatus::where('name', $mappedName)->first();
                        
                        if ($statusModel) {
                            $updateData['order_status_id'] = $statusModel->id;
                            $updateData['status'] = strtolower($statusModel->name);
                            $newStatusId = $statusModel->id;
                        } else {
                            $updateData['status'] = strtolower($mappedName); // Fallback string
                        }

                        if ($mappedName === 'Shipped' && !$order->shipped_at) {
                            $updateData['shipped_at'] = now();
                        }
                        if ($mappedName === 'Delivered' && !$order->delivered_at) {
                            $updateData['delivered_at'] = now();
                        }
                    }
                }

                // 2. Update Shipping Details
                if (!empty($qikinkOrder['shipping'])) {
                    $shipping = $qikinkOrder['shipping'];
                    if (!empty($shipping['awb']) && !$order->tracking_number) {
                        $updateData['tracking_number'] = $shipping['awb'];
                    }
                    if (!empty($shipping['tracking_link']) && !$order->tracking_url) {
                        $updateData['tracking_url'] = $shipping['tracking_link'];
                    }
                }

                if (!empty($qikinkOrder['shipping_type']) && !$order->courier_partner) {
                    $updateData['courier_partner'] = $qikinkOrder['shipping_type'];
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
                $this->error("Failed to fetch order {$order->order_number}. HTTP " . $response->status());
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
