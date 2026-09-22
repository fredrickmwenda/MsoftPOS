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
        Schema::create('ai_provider_settings', function (Blueprint $table) {
            $table->increments('id');
                       // The AI provider (e.g., openai, gemini, claude).
            $table->string('provider', 50);
            
            // Use 'text' instead of 'string' to safely accommodate encrypted payloads (ciphertext + IV + MAC),
            // which can exceed standard varchar(255) limits depending on the cipher and key length.
            $table->text('api_key')->nullable();
            
            $table->string('base_url', 191)->nullable();
            $table->string('model', 191)->nullable();
            
            // Secure default: disabled by default
            $table->boolean('is_enabled')->default(false);
            
            $table->json('settings')->nullable();
            
            $table->timestamps();

            // Unique constraint prevents duplicate active configurations (e.g., multiple NULL-tenant 'openai' rows).
            // This replaces the non-unique index and efficiently serves tenant/provider lookups.
            $table->unique(['provider'], 'ai_prov_set_tenant_prov_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_provider_settings');
    }
};
