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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('tracking_consent_timestamp')->nullable()->after('tracking_consent');
            $table->string('tracking_consent_ip_address', 45)->nullable()->after('tracking_consent_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tracking_consent_timestamp', 'tracking_consent_ip_address']);
        });
    }
};
