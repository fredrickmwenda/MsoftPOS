<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
       {
           // 1) Clear duplicate names first — otherwise the unique index will fail.
           //    Keeps the OLDEST record per name.
           DB::statement('
               DELETE FROM salary_templates
               WHERE id NOT IN (
                   SELECT MIN(id) FROM salary_templates GROUP BY name
               )
           ');

           // 2) Add the unique constraint
           Schema::table('salary_templates', function (Blueprint $table) {
               $table->unique('name');
           });
       }

       public function down(): void
       {
           Schema::table('salary_templates', function (Blueprint $table) {
               $table->dropUnique(['name']);
           });
       }
};
