<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'sale_cumulative_total_target')) {
                $table->decimal('sale_cumulative_total_target', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('general_settings', 'sale_cumulative_total_operator')) {
                $table->string('sale_cumulative_total_operator', 20)->default('equal');
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
            if (Schema::hasColumn('general_settings', 'sale_cumulative_total_target')) {
                $table->dropColumn('sale_cumulative_total_target');
            }
            if (Schema::hasColumn('general_settings', 'sale_cumulative_total_operator')) {
                $table->dropColumn('sale_cumulative_total_operator');
            }
        });
    }
};
