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
        Schema::create('accounting_sync_queue', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->enum('status', ['pending', 'posted', 'failed', 'reversed'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('posted_at')->nullable();

            $table->timestamps();

            $table->unique(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_sync_queues');
    }
};
