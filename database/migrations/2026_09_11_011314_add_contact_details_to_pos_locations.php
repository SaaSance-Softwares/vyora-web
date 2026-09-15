<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_locations', 'gst_number')) {
                $table->string('gst_number')->nullable()->after('pincode');
            }
            if (!Schema::hasColumn('pos_locations', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('gst_number');
            }
            if (!Schema::hasColumn('pos_locations', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('contact_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('pos_locations', 'contact_phone')) $columns[] = 'contact_phone';
            if (Schema::hasColumn('pos_locations', 'contact_email')) $columns[] = 'contact_email';
            // Not dropping gst_number as it might have existed before
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
