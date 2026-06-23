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
        // Check if SMTP is configured and abandoned cart emails are enabled
        $smtpEnabled = ThemeSetting::where('group', 'integration.smtp')->where('key', 'enabled')->value('value') === '1';
        $abandonedEnabled = ThemeSetting::where('group', 'integration.smtp')->where('key', 'enable_abandoned_cart_emails')->value('value') === '1';

        if (!$smtpEnabled || !$abandonedEnabled) {
            $this->info('Abandoned cart emails are disabled or SMTP is not configured.');
            return;
        }

        // Get carts abandoned > 2 hours ago that haven't received an email yet
        $carts = Cart::with(['items.sku.product', 'user'])
            ->where('status', 'active')
            ->where('updated_at', '<', now()->subHours(2))
            ->whereNull('abandoned_email_sent_at')
            ->where(function ($query) {
                $query->whereNotNull('guest_email')
                      ->orWhereNotNull('user_id');
            })
            ->get();

        $count = 0;

        foreach ($carts as $cart) {
            $email = $cart->user ? $cart->user->email : $cart->guest_email;

            if ($email && $cart->items->count() > 0) {
                try {
                    Mail::to($email)->send(new AbandonedCartReminder($cart));
                    
                    $cart->update([
                        'status' => 'abandoned',
                        'abandoned_email_sent_at' => now(),
                    ]);
                    
                    $count++;
                    Log::info("Sent abandoned cart email to {$email}");
                } catch (\Exception $e) {
                    Log::error("Failed to send abandoned cart email to {$email}: " . $e->getMessage());
                }
            }
        }

        $this->info("Sent {$count} abandoned cart emails.");
    }
}
