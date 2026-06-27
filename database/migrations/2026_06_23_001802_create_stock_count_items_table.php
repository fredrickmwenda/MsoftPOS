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
        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();

            $table->integer('stock_count_id');
            $table->integer('product_id');

            $table->decimal('system_qty', 15, 4)
                ->default(0);

            $table->decimal('physical_qty', 15, 4)
                ->default(0);

            $table->decimal('variance', 15, 4)
                ->default(0);

            $table->text('reason')
                ->nullable();

            $table->timestamps();


            $table->index([
                'stock_count_id',
                'product_id'
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_count_items');
    }
};
