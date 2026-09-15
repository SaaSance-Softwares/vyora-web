<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('order_statuses')->updateOrInsert(
            ['name' => 'POS'],
            [
                'fulfillment_type' => 'POS',
                'is_system' => true,
                'color' => '#8B5CF6', // Purple
                'sort_order' => 7, // Place it after Refunded (6)
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('order_statuses')->where('name', 'POS')->delete();
    }
};
