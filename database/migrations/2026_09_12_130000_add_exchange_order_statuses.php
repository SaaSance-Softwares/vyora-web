<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderStatus;

return new class extends Migration
{
    public function up()
    {
        // 1. Insert new statuses if they don't exist
        $statuses = [
            [
                'name' => 'Partially Returned',
                'fulfillment_type' => 'POS',
                'color' => '#f59e0b', // Amber
                'sort_order' => 54,
                'is_system' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Exchanged',
                'fulfillment_type' => 'POS',
                'color' => '#8b5cf6', // Purple
                'sort_order' => 55,
                'is_system' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($statuses as $status) {
            if (!DB::table('order_statuses')->where('name', $status['name'])->exists()) {
                DB::table('order_statuses')->insert($status);
            }
        }

        // 2. Fetch the newly inserted IDs
        $partiallyReturnedId = DB::table('order_statuses')->where('name', 'Partially Returned')->value('id');
        $exchangedId = DB::table('order_statuses')->where('name', 'Exchanged')->value('id');
        $returnedId = DB::table('order_statuses')->where('name', 'Returned')->value('id');

        // 3. Auto-Healer: Retroactively fix all orders that were returned/exchanged before this migration
        $orders = Order::all();
        foreach($orders as $order) {
            $totalQty = DB::table('order_items')->where('order_id', $order->id)->sum('quantity');
            $totalReturned = DB::table('order_items')->where('order_id', $order->id)->sum('returned_quantity');
            
            if($totalReturned > 0 && $totalQty > 0) {
                $newStatusId = null;
                if($totalReturned == $totalQty) {
                    // Check if there's an exchange order
                    $hasExchange = Order::where('notes', 'LIKE', '%Exchange against Order #' . $order->order_number . '%')->exists();
                    $newStatusId = $hasExchange ? $exchangedId : $returnedId;
                } else {
                    $newStatusId = $partiallyReturnedId;
                }
                
                if($newStatusId && $order->order_status_id != $newStatusId) {
                    $statusModel = OrderStatus::find($newStatusId);
                    if($statusModel) {
                        DB::table('orders')->where('id', $order->id)->update([
                            'order_status_id' => $newStatusId,
                            'status' => strtolower($statusModel->name)
                        ]);
                    }
                }
            }
        }
    }

    public function down()
    {
        DB::table('order_statuses')->whereIn('name', ['Partially Returned', 'Exchanged'])->delete();
    }
};
