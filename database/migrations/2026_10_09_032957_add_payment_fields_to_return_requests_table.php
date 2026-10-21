<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('return_requests')) {
            Schema::table('return_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('return_requests', 'payment_status')) {
                    $table->string('payment_status')->nullable();
                }
                if (!Schema::hasColumn('return_requests', 'payment_id')) {
                    $table->string('payment_id')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_id']);
        });
    }
};
