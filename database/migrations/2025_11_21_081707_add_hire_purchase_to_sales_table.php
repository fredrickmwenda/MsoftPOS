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
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('is_hire_purchase')->default(false)->after('sale_status');
            $table->decimal('hire_purchase_down_payment', 15, 6)->default(0)->after('is_hire_purchase');
            $table->integer('hire_purchase_terms')->nullable()->after('hire_purchase_down_payment');
            $table->decimal('hire_purchase_interest_rate', 5, 2)->default(0)->after('hire_purchase_terms');
            $table->string('hire_purchase_status')->default('active')->nullable()->after('hire_purchase_interest_rate');
            $table->date('hire_purchase_start_date')->nullable()->after('hire_purchase_status');
            $table->date('hire_purchase_end_date')->nullable()->after('hire_purchase_start_date');
            
            // Indexes for better query performance
            $table->index('is_hire_purchase');
            $table->index('hire_purchase_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            //
        });
    }
};
