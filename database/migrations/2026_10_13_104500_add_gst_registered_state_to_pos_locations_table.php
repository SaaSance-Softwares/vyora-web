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
        if (Schema::hasTable('pos_locations') && !Schema::hasColumn('pos_locations', 'gst_registered_state')) {
            Schema::table('pos_locations', function (Blueprint $table) {
                $table->string('gst_registered_state', 100)->nullable()->after('gst_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pos_locations') && Schema::hasColumn('pos_locations', 'gst_registered_state')) {
            Schema::table('pos_locations', function (Blueprint $table) {
                $table->dropColumn('gst_registered_state');
            });
        }
    }
};
