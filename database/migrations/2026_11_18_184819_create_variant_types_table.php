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
        // If the table doesn't exist, create it with all required columns
        if (!Schema::hasTable('variant_types')) {
            Schema::create('variant_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->integer('order')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });

            return;
        }

        // Table exists — check for each column individually and add only the missing ones
        Schema::table('variant_types', function (Blueprint $table) {
            if (!Schema::hasColumn('variant_types', 'id')) {
                $table->bigIncrements('id')->first();
            }

            if (!Schema::hasColumn('variant_types', 'name')) {
                $table->string('name')->after('id');
            }

            if (!Schema::hasColumn('variant_types', 'order')) {
                $table->integer('order')->default(0)->after('name');
            }

            if (!Schema::hasColumn('variant_types', 'active')) {
                $table->boolean('active')->default(true)->after('order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty — this migration is additive/safe.
        // Dropping columns here could destroy data added via the original migration.
        // If you need a hard rollback, uncomment the block below.
        //
        // Schema::dropIfExists('variant_types');
    }
};
