<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ⚠️ Destructive — old structure removed
        Schema::dropIfExists('payroll_item_meta');
        Schema::dropIfExists('payroll_template_items');
        Schema::dropIfExists('payroll_template_tax');
        Schema::dropIfExists('payroll_templates');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_payments');
        Schema::dropIfExists('payrolls');

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->nullable()->unique();
            $table->unsignedBigInteger('employee_id')->index();          // ✅ integer
            $table->unsignedBigInteger('salary_template_id')->nullable(); // ✅ integer
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_name')->nullable();
            $table->enum('template_type', ['monthly', 'hourly'])->default('monthly');

            // Monthly snapshot
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_rate', 12, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);

            // Hourly snapshot (paid on daily basis)
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('hours_per_day', 6, 2)->default(0);
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->integer('days_worked')->default(0);

            // Totals
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('total_allowance', 12, 2)->default(0);
            $table->decimal('total_deduction', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);   // net payable
            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->date('date')->nullable();
            $table->integer('year')->index();
            $table->integer('month')->index();
            $table->tinyInteger('paying_method')->default(0);
            $table->string('payment_status')->default('due'); // due | partial | paid
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'month'], 'payroll_employee_period_unique');
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_id')->index(); // ✅ integer — no FK
            $table->string('name');
            $table->enum('type', ['allowance', 'deduction', 'overtime'])->default('allowance');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('source')->default('template'); // template | overtime | manual
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_id')->index(); // ✅ integer — no FK
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->tinyInteger('paying_method')->default(0);
            $table->date('payment_date')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('overtimes');
        Schema::dropIfExists('payroll_payments');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
    }
};
