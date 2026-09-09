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

class PasswordUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $storeName;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->storeName = ThemeSetting::where('key', 'store_name')->first()?->value ?? config('app.name', 'Store');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Security Alert: Password Changed - ' . $this->storeName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-updated',
        );
    }
}
