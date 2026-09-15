<?php
namespace App\Traits;

use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

trait OrderStatusNotificationTrait
{
    public function fireOrderStatusNotifications(Order $order, OrderStatus $newStatus)
    {
        $statusName = $newStatus->name;
        $statusNameLower = strtolower($statusName);

        // Handle dynamic Email Template
        if ($newStatus->emailTemplate) {
            try {
                $email = $order->user?->email ?? $order->shippingAddress?->email;
                if ($email) {
                    Mail::to($email)->send(new \App\Mail\DynamicOrderStatusEmail($order, $newStatus->emailTemplate));
                }
            } catch (\Exception $e) {
                Log::error("Failed to send Dynamic Order Status Email ({$statusName}): ".$e->getMessage());
            }
        } else {
            if (in_array($statusNameLower, ['shipped', 'cancelled', 'delivered', 'returned'])) {
                 try {
                    $email = $order->user?->email ?? $order->shippingAddress?->email;
                    if ($email) {
                        Mail::to($email)->send(new \App\Mail\OrderStatusEmail($order, $statusNameLower));
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send Legacy Order Status Email ({$statusNameLower}): ".$e->getMessage());
                }
            }
        }

        // Handle dynamic SMS Template
        if ($newStatus->smsTemplate) {
            try {
                app(\App\Services\TwilioSmsService::class)->sendDynamicSms($order, $newStatus->smsTemplate);
            } catch (\Exception $e) {
                Log::error("Failed to send Dynamic Order SMS ({$statusName}): ".$e->getMessage());
            }
        }

        // Handle dynamic WhatsApp Template
        if ($newStatus->whatsappTemplate) {
            try {
                app(\App\Services\WhatsAppService::class)->sendDynamicWhatsApp($order, $newStatus->whatsappTemplate);
            } catch (\Exception $e) {
                Log::error("Failed to send Dynamic Order WhatsApp ({$statusName}): ".$e->getMessage());
            }
        }
    }
}
