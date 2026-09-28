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
            if (!Schema::hasColumn('orders', 'zoho_books_id')) {
                $table->string('zoho_books_id')->nullable()->after('qikink_sync_error');
                $table->integer('zoho_sync_attempts')->default(0)->after('zoho_books_id');
                $table->text('zoho_sync_error')->nullable()->after('zoho_sync_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'zoho_books_id')) {
                $table->dropColumn(['zoho_books_id', 'zoho_sync_attempts', 'zoho_sync_error']);
            }
        });
    }
};
