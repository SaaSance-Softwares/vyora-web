<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_number', 20)->unique(); // Display ID e.g. GC-XXXX-XXXX
            $table->text('code');                        // AES-encrypted redemption code
            $table->decimal('amount', 10, 2);
            $table->decimal('used_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->enum('status', ['created', 'assigned', 'active', 'partially_used', 'used', 'withdrawn'])->default('created');
            $table->enum('type', ['direct', 'purchasable'])->default('direct');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('purchased_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('purchase_order_id')->nullable(); // FK to orders (no FK constraint to avoid circular)
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
