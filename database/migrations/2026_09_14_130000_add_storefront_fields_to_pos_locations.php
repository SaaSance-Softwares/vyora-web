<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            $table->string('store_image')->nullable()->after('end_date');
            $table->text('map_link')->nullable()->after('store_image');
            $table->boolean('show_in_store')->default(1)->after('map_link');
        });
    }

    public function down()
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            $table->dropColumn(['store_image', 'map_link', 'show_in_store']);
        });
    }
};
