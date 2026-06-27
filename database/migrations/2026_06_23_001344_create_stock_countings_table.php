<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();

            $table->string('reference_no')->unique();

            $table->integer('warehouse_id');
            $table->integer('user_id');

            $table->enum('status', [
                'pending',
                'approved',
                'denied'
            ])->default('pending');

            $table->text('note')->nullable();

            $table->timestamps();

        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_counts');
    }
};