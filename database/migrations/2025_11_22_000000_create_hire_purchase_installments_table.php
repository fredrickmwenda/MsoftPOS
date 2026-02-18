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
        Schema::create('hire_purchase_installments', function (Blueprint $table) {
            $table->id();
            $table->integer('sale_id');
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('amount', 15, 6);
            $table->decimal('paid_amount', 15, 6)->default(0);
            $table->string('payment_status')->default('pending'); // pending, partial, paid
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable(); // cash, card, check, transfer, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key
            //$table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');

            // Indexes for better query performance
            $table->index('sale_id');
            $table->index('payment_status');
            $table->index('due_date');
            $table->index(['sale_id', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_purchase_installments');
    }
};
