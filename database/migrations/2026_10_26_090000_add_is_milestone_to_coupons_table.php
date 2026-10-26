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
        if (! Schema::hasColumn('coupons', 'is_milestone')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->boolean('is_milestone')->default(false)->after('show_on_checkout_page');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'is_milestone')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('is_milestone');
            });
        }
    }
};
