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
        Schema::table('coupons', function (Blueprint $table) {
            $table->integer('bogo_buy_qty')->nullable()->after('type');
            $table->integer('bogo_get_qty')->nullable()->after('bogo_buy_qty');
            $table->decimal('bogo_max_discount', 10, 2)->nullable()->after('bogo_get_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['bogo_buy_qty', 'bogo_get_qty', 'bogo_max_discount']);
        });
    }
};
