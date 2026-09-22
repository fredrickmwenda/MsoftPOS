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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('payroll_template_id')->unsigned()->nullable()->after('id');
            // total earnings
            $table->decimal('total_earnings', 10, 2)->default(0)->after('payroll_template_id');
            $table->decimal('total_deductions', 10, 2)->default(0)->after('total_earnings');
            
            $table->decimal('net_pay', 10, 2)->default(0)->after('total_deductions');
            $table->string('status')->default('pending')->after('net_pay');
            //gross pay aint there what there is amount , employee_id, payroll_template_id, total_earnings, total_deductions, net_pay, status
            $table->decimal('total_allowances', 10, 2)->default(0)->after('net_pay');
            $table->decimal('gross_pay', 10, 2)->default(0)->after('total_allowances');
            //add recurrency columns
            $table->string('recurrency_type')->default('none')->after('gross_pay');
            $table->string('recurring_frequency')->nullable()->after('recurrency_type');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['payroll_template_id', 'total_earnings', 'total_deductions', 'net_pay', 'status', 'total_allowances']);
        });
    }
};
