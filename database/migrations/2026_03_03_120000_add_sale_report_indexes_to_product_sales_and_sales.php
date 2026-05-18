<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds indexes to speed up the sale report (sale-first query on product_sales + sales).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_sales', function (Blueprint $table) {
            $table->index('sale_id');
            $table->index(['product_id', 'variant_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('warehouse_id');
            $table->index('biller_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_sales', function (Blueprint $table) {
            $table->dropIndex(['sale_id']);
            $table->dropIndex(['product_id', 'variant_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['warehouse_id']);
            $table->dropIndex(['biller_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
