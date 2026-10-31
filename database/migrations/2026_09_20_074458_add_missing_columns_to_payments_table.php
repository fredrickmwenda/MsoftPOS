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

        // 2. Update the MySQL enum. SQLite stores this as a string and does
        // not support MySQL's MODIFY/ENUM syntax.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `payments` MODIFY `approval_status` ENUM('pending','waiting_approval','waiting_authorization','approved','rejected') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the MySQL enum; SQLite has no enum alteration to reverse.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `payments` MODIFY `approval_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
        }

        foreach (['change', 'status', 'mobile_money_operator', 'mobile_number'] as $column) {
            if (Schema::hasColumn('payments', $column)) {
                Schema::table('payments', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
