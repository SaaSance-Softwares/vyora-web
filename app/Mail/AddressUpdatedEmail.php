<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\ThemeSetting;

class AddressUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $action; // 'added', 'updated', or 'deleted'
    public $storeName;

    public function __construct(User $user, string $action = 'updated')
    {
        $this->user = $user;
        $this->action = $action;
        $this->storeName = ThemeSetting::where('key', 'store_name')->first()?->value ?? config('app.name', 'Store');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Delivery Address ' . ucfirst($this->action) . ' - ' . $this->storeName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.address-updated',
        );
    }
}
