<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pos_market_pricing')) {
            // Table doesn't exist yet — will be created by the slug migration, skip here
            return;
        }

        if (!Schema::hasColumn('pos_market_pricing', 'stock')) {
            Schema::table('pos_market_pricing', function (Blueprint $table) {
                $table->integer('stock')->default(0)->after('override_price');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pos_market_pricing') && Schema::hasColumn('pos_market_pricing', 'stock')) {
            Schema::table('pos_market_pricing', function (Blueprint $table) {
                $table->dropColumn('stock');
            });
        }
    }
};
