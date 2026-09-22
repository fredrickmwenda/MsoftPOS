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
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('title', 191)->nullable();
            $table->string('mode', 50)->default('structured'); // structured, provider, mixed
            $table->string('provider', 191)->nullable();
            $table->timestamps();

            // Establish the foreign key following SalePro convention
            
            // Composite index for time-based conversation history lookups by tenant and user
            $table->index([ 'user_id', 'created_at'], 'ai_conv_tenant_user_time_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
