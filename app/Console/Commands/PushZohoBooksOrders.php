<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ThemeSetting;
use App\Services\ZohoBooksService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PushZohoBooksOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho-books:push-orders {--retry-failed : Reset and retry orders that previously failed after 3 attempts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push un-synced orders to Zoho Books';

    /**
     * Execute the console command.
     */
    public function handle(ZohoBooksService $zohoBooksService)
    {
        $this->info('Starting Zoho Books order push...');

        // Check if Zoho Books is enabled
        $enabledSetting = ThemeSetting::where('group', 'integration.zoho-books')->where('key', 'enabled')->first();
        if (!$enabledSetting || $enabledSetting->value !== '1') {
            $this->info('Zoho Books integration is disabled. Skipping.');
            return;
        }

        if (!$zohoBooksService->isConfigured()) {
            $this->error('Zoho Books is not fully configured.');
            return;
        }

        // Automatically recover orders that failed due to item/contact collision bugs or when --retry-failed is passed
        $failedQuery = Order::whereNull('zoho_books_id')->where('zoho_sync_attempts', '>=', 3);
        if (!$this->option('retry-failed')) {
            $failedQuery->where(function ($q) {
                $q->where('zoho_sync_error', 'like', '%1001%')
                  ->orWhere('zoho_sync_error', 'like', '%3032%')
                  ->orWhere('zoho_sync_error', 'like', '%interstate%')
                  ->orWhere('zoho_sync_error', 'like', '%already exists%')
                  ->orWhere('zoho_sync_error', 'like', '%contact_name%')
                  ->orWhere('zoho_sync_error', 'like', '%null%');
            });
        }
        $resetCount = $failedQuery->update([
            'zoho_sync_attempts' => 0,
            'zoho_sync_error' => null,
        ]);
        if ($resetCount > 0) {
            $this->info("Reset {$resetCount} previously failed order(s) for retry.");
        }

        // Find orders that:
        // 1. Don't have a Zoho Books ID
        // 2. Have attempts < 3
        // 3. Status is active/paid (not pending, cancelled, or failed)
        $orders = Order::whereNull('zoho_books_id')
            ->where('zoho_sync_attempts', '<', 3)
            ->whereNotIn('status', ['pending', 'cancelled', 'failed'])
            ->orderBy('id', 'desc')
            ->take(20) // Limit to avoid rate limits
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No pending Zoho Books orders to push.');
            return;
        }

        $this->info('Found ' . $orders->count() . ' orders to push to Zoho Books.');

        foreach ($orders as $order) {
            $this->info("Pushing order {$order->uuid}...");
            
            $result = $zohoBooksService->pushOrder($order);

            if (isset($result['success']) && $result['success']) {
                $this->info("Successfully pushed order {$order->uuid}.");
                $order->zoho_books_id = $result['id'] ?? 'synced';
                $order->zoho_sync_error = null;
                $order->zoho_sync_attempts = 0;
                $order->save();
            } else {
                $errorMsg = $result['error'] ?? 'Unknown API Error';
                $this->error("Failed to push order {$order->uuid}: " . $errorMsg);
                
                $order->increment('zoho_sync_attempts');
                
                if ($order->zoho_sync_attempts >= 3) {
                    $order->zoho_sync_error = $errorMsg;
                    $order->save();
                    Log::error("Order {$order->uuid} completely failed Zoho Books sync after 3 attempts.", [
                        'error' => $errorMsg
                    ]);
                }
            }
        }

        $this->info('Zoho Books order push complete.');
    }
}
