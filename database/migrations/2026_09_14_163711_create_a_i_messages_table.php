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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->increments('id');
            // The referenced ai_conversations table uses id() which generates an unsignedBigInteger.
            // So conversation_id must be unsignedBigInteger.
            $table->unsignedBigInteger('conversation_id');
            
            // Following repository convention, we avoid DB-level ENUMs and use strings 
            // for maximum compatibility, relying on app-level validation.
            $table->string('role', 50); // user, assistant, system
            $table->longText('content');
            $table->string('response_type', 50)->default('text'); // text, card, table, chart, error
            $table->json('metadata')->nullable();
            
            $table->timestamps();
                  
            // Composite index for fast ordered retrieval of a conversation's messages.
            $table->index(['conversation_id', 'created_at'], 'ai_messages_conv_time_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
