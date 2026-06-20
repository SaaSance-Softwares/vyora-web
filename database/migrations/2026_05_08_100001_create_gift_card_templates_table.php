<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_card_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();              // Optional display name e.g. "Classic ₹500"
            $table->decimal('amount', 10, 2);                     // The denomination value
            $table->text('description')->nullable();              // Short storefront description
            $table->boolean('is_active')->default(true);          // Toggle storefront visibility
            $table->integer('validity_days')->nullable();         // If set, purchased cards expire after N days
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_templates');
    }
};
