<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ZohoBooksService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncZohoBooksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(ZohoBooksService $zohoBooksService): void
    {
        try {
            if (!$zohoBooksService->isConfigured()) {
                Log::info('Zoho Books is not configured. Skipping sync for order: ' . $this->order->id);
                return;
            }

            Log::info('Starting Zoho Books sync for order: ' . $this->order->id);
            $result = $zohoBooksService->pushOrder($this->order);

            if (!empty($result['success'])) {
                $this->order->update([
                    'zoho_books_id' => $result['id'] ?? 'synced',
                    'zoho_sync_error' => null,
                    'zoho_sync_attempts' => 0,
                ]);
                Log::info('Successfully synced order to Zoho Books.', ['order_id' => $this->order->id, 'zoho_id' => $result['id'] ?? null]);
            } else {
                Log::warning('Zoho Books job sync returned error (will retry via cron): ' . ($result['error'] ?? 'Unknown'), [
                    'order_id' => $this->order->id,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync order to Zoho Books: ' . $e->getMessage(), [
                'order_id' => $this->order->id
            ]);
            throw $e;
        }
    }
}
