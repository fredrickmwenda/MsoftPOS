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
        Schema::create('sms_messages', function (Blueprint $table) {
                    $table->id();
                    $table->integer('sms_gateway_id');      // keep history even if gateway deleted
                    $table->text('recipients');     // comma-separated phone list
                    $table->text('message');
                    $table->string('status')->default('sent'); // sent | failed | pending
                    $table->text('response')->nullable();      // raw gateway response / error
                    $table->string('message_id')->nullable();  // Arkesel message id (if returned)
                    $table->integer('sent_by')->nullable();
                    $table->timestamps();

                    $table->index('status');
                    $table->index('created_at');
                });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
