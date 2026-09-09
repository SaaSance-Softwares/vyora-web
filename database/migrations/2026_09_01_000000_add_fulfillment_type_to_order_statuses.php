<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\OrderStatus;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order_statuses', 'fulfillment_type')) {
            Schema::table('order_statuses', function (Blueprint $table) {
                $table->string('fulfillment_type')->default('System')->after('name');
            });
        }
        
        $qikinkStatuses = [
            ['name' => 'Manifested', 'color' => '#3b82f6'],
            ['name' => 'Shipped', 'color' => '#8b5cf6'],
            ['name' => 'Out for Delivery', 'color' => '#14b8a6'],
            ['name' => 'Delivered', 'color' => '#10b981'],
            ['name' => 'Delivery Failed', 'color' => '#ef4444'],
            ['name' => 'Returned', 'color' => '#f97316'],
            ['name' => 'Cancelled', 'color' => '#6b7280'],
        ];

        $sort = 50;
        foreach ($qikinkStatuses as $s) {
            $existing = OrderStatus::where('name', $s['name'])->first();
            if ($existing) {
                $existing->update(['fulfillment_type' => 'QikInk', 'is_system' => true]);
            } else {
                OrderStatus::create([
                    'name' => $s['name'],
                    'color' => $s['color'],
                    'sort_order' => $sort++,
                    'fulfillment_type' => 'QikInk',
                    'is_system' => true
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->dropColumn('fulfillment_type');
        });
    }
};
