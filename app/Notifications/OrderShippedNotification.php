<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrderShippedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order   = $this->order;
        $address = $order->shippingAddress;
        $name    = $address?->name ?? $notifiable->name ?? 'Customer';

        $mail = (new MailMessage)
            ->subject("Your Order #{$order->order_number} Has Been Shipped! 🚚")
            ->greeting("Hello {$name}!")
            ->line("Great news! Your order **#{$order->order_number}** has been shipped and is on its way to you.")
            ->line("**Courier Partner:** " . ($order->courier_partner ?? 'Our Delivery Partner'))
            ->line("**Tracking Number:** " . ($order->tracking_number ?? 'Will be updated shortly'));

        if ($order->tracking_url) {
            $mail->action('Track Your Order', $order->tracking_url);
        }

        $mail->line("**Order Total:** ₹" . number_format($order->total_amount))
             ->line("**Shipping to:** " . ($address ? "{$address->address_line1}, {$address->city}, {$address->state} - {$address->zip_code}" : 'Your address'))
             ->line("If you have any questions about your order, please contact our support team.")
             ->salutation("Thank you for shopping with us!");

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'order_shipped',
            'order_id'        => $this->order->id,
            'order_number'    => $this->order->order_number,
            'tracking_url'    => $this->order->tracking_url,
            'courier_partner' => $this->order->courier_partner,
            'tracking_number' => $this->order->tracking_number,
            'message'         => "Your order #{$this->order->order_number} has been shipped!",
        ];
    }

    /**
     * SMS notification stub — wire to MSG91 / Twilio later.
     * Call this manually after firing the notification if SMS is configured.
     */
    public static function sendSmsStub(Order $order): void
    {
        $address = $order->shippingAddress;
        $phone   = $address?->phone ?? '';
        $msg     = "Hi! Your order #{$order->order_number} has been shipped.";

        if ($order->tracking_url) {
            $msg .= " Track here: {$order->tracking_url}";
        }

        // TODO: Replace this with MSG91 / Twilio / any SMS provider
        Log::channel('stack')->info('[SMS STUB] Would send SMS', [
            'to'      => $phone,
            'message' => $msg,
        ]);
    }

    /**
     * WhatsApp notification stub — wire to WhatsApp Business API / Twilio later.
     */
    public static function sendWhatsAppStub(Order $order): void
    {
        $address = $order->shippingAddress;
        $phone   = $address?->phone ?? '';
        $name    = $address?->name ?? 'Customer';

        $msg = "Hello {$name}! 🎉 Your order #{$order->order_number} is on the way!\n\n"
             . "Courier: " . ($order->courier_partner ?? 'Our Partner') . "\n"
             . "Tracking #: " . ($order->tracking_number ?? 'N/A');

        if ($order->tracking_url) {
            $msg .= "\n\nTrack your order: {$order->tracking_url}";
        }

        // TODO: Replace with WhatsApp Business API / Twilio WhatsApp sandbox
        Log::channel('stack')->info('[WHATSAPP STUB] Would send WhatsApp message', [
            'to'      => "+91{$phone}",
            'message' => $msg,
        ]);
    }
}
