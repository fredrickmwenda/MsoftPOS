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
        Schema::create('black_list_customers', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->integer('total_delivery_missed')->default(0);
            $table->string('last_delivery_miss_date')->nullable();
            $table->integer('delivery_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('black_list_customer');
    }
};
