<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replaces sale amount filter with percentage filter: when set (e.g. 50),
     * only the top X% of sales by value are shown.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'sale_percentage_filter')) {
                $table->unsignedTinyInteger('sale_percentage_filter')->nullable()->comment('0-100; only sales making up this % of total are shown');
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
            if (Schema::hasColumn('general_settings', 'sale_percentage_filter')) {
                $table->dropColumn('sale_percentage_filter');
            }
        });
    }
};
