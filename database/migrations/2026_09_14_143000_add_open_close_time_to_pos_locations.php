<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('pos_locations', function (Blueprint $table) {
            $table->time('open_time')->nullable()->after('end_date');
            $table->time('close_time')->nullable()->after('open_time');
        });
    }
    public function down() {
        Schema::table('pos_locations', function (Blueprint $table) {
            $table->dropColumn(['open_time', 'close_time']);
        });
    }
};
