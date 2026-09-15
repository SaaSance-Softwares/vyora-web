<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'amount_refunded')) {
                $table->decimal('amount_refunded', 10, 2)->default(0)->after('amount_paid');
            }
        });
        
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'returned_quantity')) {
                $table->integer('returned_quantity')->default(0)->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('amount_refunded');
        });
        
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('returned_quantity');
        });
    }
};
