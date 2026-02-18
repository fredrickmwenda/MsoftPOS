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
        Schema::table('quotations', function (Blueprint $table) {
            // Add columns for multiple order taxes
            $table->string('order_tax_ids')->nullable()->after('order_tax_rate');
            $table->string('order_tax_names')->nullable()->after('order_tax_ids');
        });

        Schema::table('product_quotation', function (Blueprint $table) {
            // Add column for product tax names
            $table->string('tax_names')->nullable()->after('tax_rate');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn(['order_tax_ids', 'order_tax_names']);
        });

        Schema::table('product_quotation', function (Blueprint $table) {
            $table->dropColumn('tax_names');
        });
    }
};
