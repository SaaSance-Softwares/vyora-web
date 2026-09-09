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
            $table->decimal('coupon_discount_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('prepaid_discount_amount', 10, 2)->default(0)->after('coupon_discount_amount');
            $table->decimal('gift_card_discount_amount', 10, 2)->default(0)->after('prepaid_discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_discount_amount', 'prepaid_discount_amount', 'gift_card_discount_amount']);
        });
    }
};
