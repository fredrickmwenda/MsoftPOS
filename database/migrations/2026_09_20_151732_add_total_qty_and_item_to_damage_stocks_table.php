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
        Schema::table('damage_stocks', function (Blueprint $table) {
            // Only add the columns if they don't already exist
            if (!Schema::hasColumn('damage_stocks', 'total_qty')) {
                $table->integer('total_qty')->default(0)->after('damaged_at');
            }
            
            if (!Schema::hasColumn('damage_stocks', 'item')) {
                $table->integer('item')->default(0)->after('total_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damage_stocks', function (Blueprint $table) {
            $table->dropColumn(['total_qty', 'item']);
        });
    }
};
