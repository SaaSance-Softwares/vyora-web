<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartReminder;
use App\Models\Cart;
use App\Models\ThemeSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:abandoned-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic emails for abandoned carts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $smtpEnabled = ThemeSetting::where('group', 'integration.smtp')->where('key', 'enabled')->value('value') === '1';
        $abandonedEnabled = ThemeSetting::where('group', 'integration.smtp')->where('key', 'enable_abandoned_cart_emails')->value('value') === '1';

        // Check if WhatsApp is configured for abandoned carts
        $waTemplateName = ThemeSetting::where('group', 'auth')->where('key', 'whatsapp_abandoned_cart_template')->value('value') ?? 'abandoned_cart';
        $waTemplate = \App\Models\WhatsappTemplate::where('name', $waTemplateName)->first();

        if ((!$smtpEnabled || !$abandonedEnabled) && !$waTemplate) {
            $this->info('Both Abandoned cart emails and WhatsApp are disabled or not configured.');
            return;
        }

                        $carts = Cart::with(['items.sku.product', 'user'])
            ->where('status', 'active')
            ->has('items')
            ->where('updated_at', '<', now()->subHours(2))
            ->whereNull('abandoned_email_sent_at')
            ->where(function ($query) {
                $query->whereNotNull('guest_email')
                      ->orWhereNotNull('user_id');
            })
            ->get();
            
                $count = 0;

        // Fetch WhatsApp template if enabled (defaulting to 'abandoned_cart' if it exists)
        $waTemplateName = ThemeSetting::where('group', 'auth')->where('key', 'whatsapp_abandoned_cart_template')->value('value') ?? 'abandoned_cart';
        $waTemplate = \App\Models\WhatsappTemplate::where('name', $waTemplateName)->first();

        foreach ($carts as $cart) {
            if ($cart->items->isEmpty()) {
                // If they emptied their cart, mark it as abandoned to prevent future retries
                $cart->update(['status' => 'abandoned', 'abandoned_email_sent_at' => now()]);
                continue;
            }
            $email = $cart->user ? $cart->user->email : $cart->guest_email;
            $sentEmail = false;

            if ($email && $cart->items->count() > 0 && $smtpEnabled && $abandonedEnabled) {
                try {
                    Mail::to($email)->send(new AbandonedCartReminder($cart));
                    $sentEmail = true;
                    Log::info("Sent abandoned cart email to {$email}");
                } catch (\Exception $e) {
                    Log::error("Failed to send abandoned cart email to {$email}: " . $e->getMessage());
                }
            }

            // Also send WhatsApp if template exists and phone exists
            $waStatus = 'pending';
            if ($waTemplate) {
                try {
                    $success = app(\App\Services\WhatsAppService::class)->sendAbandonedCartWhatsApp($cart, $waTemplate);
                    $waStatus = $success ? 'sent' : 'failed';
                } catch (\Exception $e) {
                    $waStatus = 'failed';
                    Log::error("Failed to send abandoned cart WhatsApp for cart {$cart->id}: " . $e->getMessage());
                }
            } else {
                $waStatus = 'not_configured'; // No template
            }
            if (!$cart->user || !$cart->user->phone) {
                $waStatus = 'no_phone';
            }
            
            // Mark as sent if either email was attempted or WhatsApp was attempted
            if ($sentEmail || $waTemplate) {
                $cart->update([
                    'status' => 'abandoned',
                    'abandoned_email_sent_at' => now(),
                    'whatsapp_status' => $waStatus,
                ]);
                $count++;
            }
        }

        $this->info("Sent {$count} abandoned cart emails.");
    }
}
