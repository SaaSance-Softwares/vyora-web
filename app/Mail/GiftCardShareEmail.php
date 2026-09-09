<?php

namespace App\Mail;

use App\Models\GiftCard;
use App\Models\ThemeSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftCardShareEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $card;
    public $shareUrl;
    public $storeName;
    public $logo;

    /**
     * Create a new message instance.
     */
    public function __construct(GiftCard $card, string $shareUrl)
    {
        $this->card = $card;
        $this->shareUrl = $shareUrl;
        $this->storeName = ThemeSetting::where('key', 'store_name')->value('value') ?: config('app.name');
        $this->logo = ThemeSetting::where('key', 'main_logo')->value('value');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have received a ₹' . number_format($this->card->amount, 0) . ' Gift Card from ' . $this->storeName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.gift-card-share',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
