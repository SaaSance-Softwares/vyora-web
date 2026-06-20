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
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('customer_name')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('whatsapp_conversations')->onDelete('cascade');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('type')->default('text'); // text, template, image, document, etc.
            $table->text('body')->nullable();
            $table->string('message_id')->nullable()->unique(); // WAMID from Meta
            $table->string('status')->default('sent'); // sent, delivered, read, failed
            $table->timestamps();
        });

        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('language')->default('en');
            $table->string('status')->default('APPROVED'); // APPROVED, REJECTED, PENDING
            $table->string('category')->nullable();
            $table->json('components')->nullable(); // The template content structure
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_conversations');
    }
};
