<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\ThemeSetting;

class DynamicOrderStatusEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $emailTemplate;
    public $storeName;
    public $parsedSubject;
    public $parsedBody;

    public function __construct(Order $order, EmailTemplate $emailTemplate)
    {
        $this->order = $order;
        $this->emailTemplate = $emailTemplate;
        $this->storeName = ThemeSetting::where('key', 'store_name')->first()?->value ?? config('app.name', 'Store');
        
        $this->parseTemplate();
    }

    private function parseTemplate()
    {
        $replacements = [
            '{customer_name}' => $this->order->user?->name ?? $this->order->shippingAddress?->name ?? 'Customer',
            '{order_number}' => $this->order->order_number,
            '{total_amount}' => '₹' . number_format($this->order->total_amount, 2),
            '{store_name}' => $this->storeName,
            '{tracking_url}' => $this->order->tracking_url ?? '',
            '{tracking_number}' => $this->order->tracking_number ?? '',
            '{courier_partner}' => $this->order->courier_partner ?? '',
        ];

        $this->parsedSubject = str_replace(array_keys($replacements), array_values($replacements), $this->emailTemplate->subject);
        $this->parsedBody = str_replace(array_keys($replacements), array_values($replacements), $this->emailTemplate->body);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->parsedSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dynamic-order-status',
        );
    }
}
