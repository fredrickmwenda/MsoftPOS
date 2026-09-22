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
        Schema::create('payroll_item_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payroll_id')->unsigned();
            $table->integer('payroll_item_id')->unsigned();
            $table->decimal('percentage', 8, 2)->default(0);
            $table->string('name');
            $table->string('type');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('amount_type')->default('fixed');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_item_metas');
    }
};
