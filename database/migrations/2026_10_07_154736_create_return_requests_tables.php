<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('return_requests')) {
            Schema::create('return_requests', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('type', ['return', 'exchange']);
                $table->string('status')->default('pending');
                $table->decimal('fee_deducted', 10, 2)->default(0);
                $table->decimal('refund_amount', 10, 2)->default(0);
                $table->text('reason')->nullable();
                $table->text('comments')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('return_request_items')) {
            Schema::create('return_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('return_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity');
                $table->foreignId('exchange_variant_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_items');
        Schema::dropIfExists('return_requests');
    }
};
