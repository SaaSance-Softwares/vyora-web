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
            $response = $zohoBooksService->pushOrder($this->order);
            Log::info('Successfully synced order to Zoho Books.', ['response' => $response]);

        } catch (\Exception $e) {
            Log::error('Failed to sync order to Zoho Books: ' . $e->getMessage(), [
                'order_id' => $this->order->id
            ]);
            throw $e;
        }
    }
}
