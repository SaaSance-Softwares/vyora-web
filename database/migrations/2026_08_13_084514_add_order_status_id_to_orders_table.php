<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('order_status_id')->nullable()->constrained('order_statuses')->nullOnDelete();
        });

        // Insert default statuses
        $defaultStatuses = [
            ['name' => 'Pending', 'color' => '#f59e0b', 'is_system' => true, 'sort_order' => 1],
            ['name' => 'Processing', 'color' => '#3b82f6', 'is_system' => true, 'sort_order' => 2],
            ['name' => 'Shipped', 'color' => '#8b5cf6', 'is_system' => true, 'sort_order' => 3],
            ['name' => 'Delivered', 'color' => '#10b981', 'is_system' => true, 'sort_order' => 4],
            ['name' => 'Cancelled', 'color' => '#ef4444', 'is_system' => true, 'sort_order' => 5],
            ['name' => 'Refunded', 'color' => '#6b7280', 'is_system' => true, 'sort_order' => 6],
        ];
        
        foreach ($defaultStatuses as $status) {
            DB::table('order_statuses')->insert(array_merge($status, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Map existing orders
        DB::statement("UPDATE orders o JOIN order_statuses os ON LOWER(o.status) = LOWER(os.name) SET o.order_status_id = os.id");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['order_status_id']);
            $table->dropColumn('order_status_id');
        });
    }
};
