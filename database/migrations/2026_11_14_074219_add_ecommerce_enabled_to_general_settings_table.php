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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('ecommerce_enabled')->default(true)->after('is_zatca');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
     {
         Schema::table('general_settings', function (Blueprint $table) {
             $table->dropColumn('ecommerce_enabled');
         });
     }
};
