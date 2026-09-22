<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
  // 1. Add missing columns
        Schema::table('payments', function (Blueprint $table) {
            // 'change' goes right after 'used_points' (matches first schema)
            $table->double('change')->after('used_points');

            // 'status' goes right after 'paying_method'
            $table->tinyInteger('status')->nullable()->after('paying_method');

            // mobile money fields go after 'updated_at'
            $table->text('mobile_money_operator')->nullable()->after('updated_at');
            $table->text('mobile_number')->nullable()->after('mobile_money_operator');
        });

        // 2. Update approval_status enum to include all 5 values
        //    (raw DB statement because Blueprint enum changes are DB-specific)
        DB::statement("ALTER TABLE `payments` MODIFY `approval_status` ENUM('pending','waiting_approval','waiting_authorization','approved','rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back to 3 values
        DB::statement("ALTER TABLE `payments` MODIFY `approval_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'change',
                'status',
                'mobile_money_operator',
                'mobile_number',
            ]);
        });
    }
};
