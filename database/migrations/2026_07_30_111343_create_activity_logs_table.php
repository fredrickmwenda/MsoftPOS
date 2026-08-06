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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();          // e.g., 'warehouse', 'default'
            $table->string('description');                  // e.g., 'created', 'updated', 'deleted'
            $table->nullableMorphs('subject');              // the model being acted upon
            $table->nullableMorphs('causer');               // the user who performed the action
            $table->json('properties')->nullable();         // old, new, attributes, etc.
            $table->timestamps();

            $table->index('log_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
