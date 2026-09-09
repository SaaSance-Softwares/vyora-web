<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\ThemeSetting;

class OrderStatusEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $status;
    public $storeName;

    public function __construct(Order $order, string $status)
    {
        $this->order = $order;
        $this->status = $status;
        $this->storeName = ThemeSetting::where('key', 'store_name')->first()?->value ?? config('app.name', 'Store');
    }

    public function envelope(): Envelope
    {
        $subjectMap = [
            'confirmed' => 'Order Confirmed - ' . $this->order->order_number,
            'shipped' => 'Your Order Has Been Shipped! - ' . $this->order->order_number,
            'delivered' => 'Your Order Has Been Delivered - ' . $this->order->order_number,
            'cancelled' => 'Order Cancelled - ' . $this->order->order_number,
            'returned' => 'Order Returned - ' . $this->order->order_number,
        ];

        return new Envelope(
            subject: $subjectMap[$this->status] ?? 'Order Update: ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status',
        );
    }
}
