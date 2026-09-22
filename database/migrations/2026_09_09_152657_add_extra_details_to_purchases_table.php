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
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('purchase_type',191)->nullable()->after('note'); 
            $table->enum('accounting_status', ['pending', 'posted', 'failed', 'reversed'])->default('pending')->after('payment_status');
            $table->date('due_date')->nullable();
            $table->integer('pay_term_no')->nullable()->after('due_date');
            $table->string('pay_term_period')->nullable()->after('pay_term_no');
            $table->integer('deleted_by')->nullable()->after('updated_at');
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'pay_term_no', 'pay_term_period', 'accounting_status', 'purchase_type', 'deleted_by']);
            $table->dropSoftDeletes();
        });
    }
};
