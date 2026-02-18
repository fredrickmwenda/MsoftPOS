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
        Schema::table('payments', function (Blueprint $table) {
            //     $payment->authorized_by = Auth::id();
        //$payment->authorized_at = now();
           $table->integer('authorized_by')->nullable()->before('approved_by');
           $table->timestamp('authorized_at')->nullable()->after('authorized_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('authorized_by');
            $table->dropColumn('authorized_at');
        });
    }
};
