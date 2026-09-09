<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\QikinkOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PushQikinkOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qikink:push-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push new orders to Qikink API in batches to respect rate limits';

    /**
     * Execute the console command.
     */
    public function handle(QikinkOrderService $qikinkService)
    {
        $this->info('Starting Qikink order push...');

        // Find orders that:
        // 1. Don't have a Qikink Order ID (haven't been successfully pushed)
        // 2. Have attempts < 3
        // 3. Status is processing (or pending, but usually we push when it's placed. Let's just rely on null qikink_order_id)
        // We need to make sure the order actually has Qikink items. We can filter that after loading,
        // or join. Given typical volume, we can just load them and filter.
        
        $orders = Order::whereNull('qikink_order_id')
            ->where('qikink_sync_attempts', '<', 3)
            ->whereNotIn('status', ['cancelled', 'refunded', 'delivered'])
            // Only look at reasonably recent orders (e.g. last 30 days) to avoid scanning ancient ones
            ->where('created_at', '>=', now()->subDays(30))
            ->with(['items.product'])
            ->get();

        // Filter orders that actually contain Qikink items
        $qikinkOrders = $orders->filter(function ($order) {
            return $order->items->contains(function ($item) {
                return $item->product && $item->product->use_qikink;
            });
        });

        // Limit to 25 orders per run to respect Qikink's 30 req/min rate limit
        $ordersToProcess = $qikinkOrders->take(25);

        if ($ordersToProcess->isEmpty()) {
            $this->info('No pending Qikink orders to push.');
            return;
        }

        $this->info('Found ' . $ordersToProcess->count() . ' orders to push to Qikink.');

        foreach ($ordersToProcess as $order) {
            $this->info("Pushing order {$order->order_number}...");
            
            $result = $qikinkService->processOrder($order);

            if ($result['success']) {
                $this->info("Successfully pushed order {$order->order_number}.");
                // Reset errors if any
                $order->qikink_sync_error = null;
                $order->qikink_sync_attempts = 0;
                $order->save();
            } else {
                $this->error("Failed to push order {$order->order_number}: " . $result['error']);
                $order->increment('qikink_sync_attempts');
                
                // If it reached 3 attempts, save the error for the admin
                if ($order->qikink_sync_attempts >= 3) {
                    $order->qikink_sync_error = $result['error'] ?? 'Unknown Qikink API Error';
                    $order->save();
                    Log::error("Order {$order->order_number} completely failed Qikink sync after 3 attempts.", [
                        'error' => $order->qikink_sync_error
                    ]);
                }
            }
        }

        $this->info('Qikink order push complete.');
    }
}
