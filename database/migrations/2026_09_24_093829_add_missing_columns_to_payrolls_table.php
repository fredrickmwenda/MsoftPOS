<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        $table = 'payrolls';

        // ── Identifiers & references ──────────────────────────
        if (! Schema::hasColumn($table, 'reference_no')) {
            Schema::table($table, fn (Blueprint $t) => $t->string('reference_no')->nullable()->after('id'));
        }
        if (! Schema::hasColumn($table, 'employee_id')) {
            Schema::table($table, fn (Blueprint $t) => $t->unsignedBigInteger('employee_id')->nullable()->after('reference_no'));
        }
        if (! Schema::hasColumn($table, 'payroll_template_id')) {
            Schema::table($table, fn (Blueprint $t) => $t->unsignedBigInteger('payroll_template_id')->nullable()->after('employee_id'));
        }
        if (! Schema::hasColumn($table, 'account_id')) {
            Schema::table($table, fn (Blueprint $t) => $t->unsignedBigInteger('account_id')->nullable()->after('payroll_template_id'));
        }
        if (! Schema::hasColumn($table, 'user_id')) {
            Schema::table($table, fn (Blueprint $t) => $t->unsignedBigInteger('user_id')->nullable()->after('account_id'));
        }

        // ── Employee bank details ─────────────────────────────
        if (! Schema::hasColumn($table, 'employee_name')) {
            Schema::table($table, fn (Blueprint $t) => $t->string('employee_name')->nullable()->after('user_id'));
        }
        if (! Schema::hasColumn($table, 'bank_name')) {
            Schema::table($table, fn (Blueprint $t) => $t->string('bank_name')->nullable()->after('employee_name'));
        }
        if (! Schema::hasColumn($table, 'account_number')) {
            Schema::table($table, fn (Blueprint $t) => $t->string('account_number')->nullable()->after('bank_name'));
        }

        // ── Descriptions ──────────────────────────────────────
        if (! Schema::hasColumn($table, 'description')) {
            Schema::table($table, fn (Blueprint $t) => $t->text('description')->nullable()->after('account_number'));
        }
        if (! Schema::hasColumn($table, 'comments')) {
            Schema::table($table, fn (Blueprint $t) => $t->text('comments')->nullable()->after('description'));
        }

        // ── Work duration & amounts ──────────────────────────
        if (! Schema::hasColumn($table, 'work_duration')) {
            Schema::table($table, fn (Blueprint $t) => $t->decimal('work_duration', 10, 2)->default(0)->after('comments'));
        }
        if (! Schema::hasColumn($table, 'duration_unit')) {
            Schema::table($table, fn (Blueprint $t) => $t->string('duration_unit', 20)->default('day')->after('work_duration'));
        }
        if (! Schema::hasColumn($table, 'amount_per_duration')) {
            Schema::table($table, fn (Blueprint $t) => $t->decimal('amount_per_duration', 10, 2)->default(0)->after('duration_unit'));
        }
        if (! Schema::hasColumn($table, 'total_duration_amount')) {
            Schema::table($table, fn (Blueprint $t) => $t->decimal('total_duration_amount', 10, 2)->default(0)->after('amount_per_duration'));
        }

        // ── Payroll amounts ───────────────────────────────────
        if (! Schema::hasColumn($table, 'amount')) {
            Schema::table($table, fn (Blueprint $t) => $t->decimal('amount', 10, 2)->default(0)->after('total_duration_amount'));
        }
        if (! Schema::hasColumn($table, 'gross_amount')) {
            Schema::table($table, fn (Blueprint $t) => $t->decimal('gross_amount', 10, 2)->default(0)->after('amount'));
        }
        if (! Schema::hasColumn($table, 'total_allowances')) {
            Schema::table($table, fn (Blueprint $t) => $t->decimal('total_allowances', 10, 2)->default(0)->after('gross_amount'));
        }
        if (! Schema::hasColumn($table, 'total_deductions')) {
            Schema::table($table, fn (Blueprint $t) => $t->decimal('total_deductions', 10, 2)->default(0)->after('total_allowances'));
        }

        // ── Date / period ─────────────────────────────────────
        if (! Schema::hasColumn($table, 'date')) {
            Schema::table($table, fn (Blueprint $t) => $t->date('date')->nullable()->after('total_deductions'));
        }
        if (! Schema::hasColumn($table, 'year')) {
            Schema::table($table, fn (Blueprint $t) => $t->integer('year')->nullable()->after('date'));
        }
        if (! Schema::hasColumn($table, 'month')) {
            Schema::table($table, fn (Blueprint $t) => $t->integer('month')->nullable()->after('year'));
        }

        // ── Recurring payroll ─────────────────────────────────
        if (! Schema::hasColumn($table, 'recurring')) {
            Schema::table($table, fn (Blueprint $t) => $t->boolean('recurring')->default(false)->after('month'));
        }
        if (! Schema::hasColumn($table, 'recur_frequency')) {
            Schema::table($table, fn (Blueprint $t) => $t->string('recur_frequency', 20)->nullable()->after('recurring'));
        }
        if (! Schema::hasColumn($table, 'recur_start_date')) {
            Schema::table($table, fn (Blueprint $t) => $t->date('recur_start_date')->nullable()->after('recur_frequency'));
        }
        if (! Schema::hasColumn($table, 'recur_end_date')) {
            Schema::table($table, fn (Blueprint $t) => $t->date('recur_end_date')->nullable()->after('recur_start_date'));
        }
        if (! Schema::hasColumn($table, 'recur_next_date')) {
            Schema::table($table, fn (Blueprint $t) => $t->date('recur_next_date')->nullable()->after('recur_end_date'));
        }
        if (! Schema::hasColumn($table, 'recur_type')) {
            Schema::table($table, fn (Blueprint $t) => $t->string('recur_type', 30)->nullable()->after('recur_next_date'));
        }

        // ── Payment ───────────────────────────────────────────
        if (! Schema::hasColumn($table, 'paying_method')) {
            Schema::table($table, fn (Blueprint $t) => $t->string('paying_method', 50)->nullable()->after('recur_type'));
        }
        if (! Schema::hasColumn($table, 'note')) {
            Schema::table($table, fn (Blueprint $t) => $t->text('note')->nullable()->after('paying_method'));
        }

        // ── Timestamps (if missing) ───────────────────────────
        if (! Schema::hasColumn($table, 'created_at')) {
            Schema::table($table, fn (Blueprint $t) => $t->timestamps());
        }
    }

    public function down(): void
    {
        $table = 'payrolls';

        $columns = [
            'reference_no', 'employee_id', 'payroll_template_id', 'account_id', 'user_id',
            'employee_name', 'bank_name', 'account_number',
            'description', 'comments',
            'work_duration', 'duration_unit', 'amount_per_duration', 'total_duration_amount',
            'amount', 'gross_amount', 'total_allowances', 'total_deductions',
            'date', 'year', 'month',
            'recurring', 'recur_frequency', 'recur_start_date', 'recur_end_date', 'recur_next_date', 'recur_type',
            'paying_method', 'note',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
    }

};
