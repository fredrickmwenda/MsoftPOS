<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Renames sale-specific filter columns to generic names for shared use across sales and purchases.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            // Rename sale_percentage_filter to percentage_filter
            if (Schema::hasColumn('general_settings', 'sale_percentage_filter') && 
                !Schema::hasColumn('general_settings', 'percentage_filter')) {
                $table->renameColumn('sale_percentage_filter', 'percentage_filter');
            }
            
            // Rename sale_cumulative_total_target to cumulative_total_target
            if (Schema::hasColumn('general_settings', 'sale_cumulative_total_target') && 
                !Schema::hasColumn('general_settings', 'cumulative_total_target')) {
                $table->renameColumn('sale_cumulative_total_target', 'cumulative_total_target');
            }
            
            // Rename sale_cumulative_total_operator to cumulative_total_operator
            if (Schema::hasColumn('general_settings', 'sale_cumulative_total_operator') && 
                !Schema::hasColumn('general_settings', 'cumulative_total_operator')) {
                $table->renameColumn('sale_cumulative_total_operator', 'cumulative_total_operator');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            // Revert percentage_filter to sale_percentage_filter
            if (Schema::hasColumn('general_settings', 'percentage_filter') && 
                !Schema::hasColumn('general_settings', 'sale_percentage_filter')) {
                $table->renameColumn('percentage_filter', 'sale_percentage_filter');
            }
            
            // Revert cumulative_total_target to sale_cumulative_total_target
            if (Schema::hasColumn('general_settings', 'cumulative_total_target') && 
                !Schema::hasColumn('general_settings', 'sale_cumulative_total_target')) {
                $table->renameColumn('cumulative_total_target', 'sale_cumulative_total_target');
            }
            
            // Revert cumulative_total_operator to sale_cumulative_total_operator
            if (Schema::hasColumn('general_settings', 'cumulative_total_operator') && 
                !Schema::hasColumn('general_settings', 'sale_cumulative_total_operator')) {
                $table->renameColumn('cumulative_total_operator', 'sale_cumulative_total_operator');
            }
        });
    }
};
