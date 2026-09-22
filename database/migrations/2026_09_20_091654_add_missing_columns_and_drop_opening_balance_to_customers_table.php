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
        Schema::table('customers', function (Blueprint $table) {
            // ── ADD missing columns (after updated_at to match target schema) ──
            $table->string('region', 255)->nullable()->after('updated_at');
            $table->string('location', 255)->nullable()->after('region');
            $table->string('community', 255)->nullable()->after('location');
            $table->string('status', 255)->nullable()->after('community');

            // ── DROP opening_balance ──
            if (Schema::hasColumn('customers', 'opening_balance')) {
                $table->dropColumn('opening_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Restore opening_balance
            $table->double('opening_balance')->default(0)->after('country');

            // Drop the added columns
            $table->dropColumn(['region', 'location', 'community', 'status']);
        });
    }
};
