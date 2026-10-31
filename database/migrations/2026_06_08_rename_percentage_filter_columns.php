<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename each column in its own schema operation. SQLite does not support
     * multiple rename/drop commands in one table modification.
     */
    public function up()
    {
        if (Schema::hasColumn('general_settings', 'sale_percentage_filter')
            && ! Schema::hasColumn('general_settings', 'percentage_filter')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->renameColumn('sale_percentage_filter', 'percentage_filter');
            });
        }

        if (Schema::hasColumn('general_settings', 'sale_cumulative_total_target')
            && ! Schema::hasColumn('general_settings', 'cumulative_total_target')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->renameColumn('sale_cumulative_total_target', 'cumulative_total_target');
            });
        }

        if (Schema::hasColumn('general_settings', 'sale_cumulative_total_operator')
            && ! Schema::hasColumn('general_settings', 'cumulative_total_operator')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->renameColumn('sale_cumulative_total_operator', 'cumulative_total_operator');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('general_settings', 'percentage_filter')
            && ! Schema::hasColumn('general_settings', 'sale_percentage_filter')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->renameColumn('percentage_filter', 'sale_percentage_filter');
            });
        }

        if (Schema::hasColumn('general_settings', 'cumulative_total_target')
            && ! Schema::hasColumn('general_settings', 'sale_cumulative_total_target')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->renameColumn('cumulative_total_target', 'sale_cumulative_total_target');
            });
        }

        if (Schema::hasColumn('general_settings', 'cumulative_total_operator')
            && ! Schema::hasColumn('general_settings', 'sale_cumulative_total_operator')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->renameColumn('cumulative_total_operator', 'sale_cumulative_total_operator');
            });
        }
    }
};
