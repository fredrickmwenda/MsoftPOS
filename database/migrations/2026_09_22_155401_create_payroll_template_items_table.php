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

        Schema::table('payroll_template_items', function (Blueprint $table) {
            $table->string('name')->after('payroll_template_id');
            $table->string('type')->default('allowance')->after('name'); // allowance or deduction
            $table->string('amount_type')->default('fixed')->after('type'); // fixed or percentage
            $table->decimal('amount', 15, 2)->default(0)->after('amount_type');
            $table->boolean('taxable')->default(false)->after('amount');
            $table->string('description')->nullable()->after('taxable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::table('payroll_template_items', function (Blueprint $table) {
            $table->dropColumn('name', 'type', 'amount', 'amount_type', 'taxable', 'description');
        });
    }
};
