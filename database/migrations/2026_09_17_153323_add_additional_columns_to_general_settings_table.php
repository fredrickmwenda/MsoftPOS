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
            
            // Previous column from your error
            if (!Schema::hasColumn('general_settings', 'whatsapp_number')) {
                $table->string('whatsapp_number')->nullable();
            }

            // New requested columns
            if (!Schema::hasColumn('general_settings', 'support_email')) {
                $table->string('support_email')->nullable();
            }

            if (!Schema::hasColumn('general_settings', 'is_packing_slip')) {
                // Defaults to 1 (true), change to ->default(0) if it should be off by default
                $table->boolean('is_packing_slip')->default(1); 
            }

            if (!Schema::hasColumn('general_settings', 'cumulative_total_target')) {
                // Using decimal for monetary/numeric values. Adjust precision (15,2) if needed.
                $table->decimal('cumulative_total_target', 15, 2)->nullable();
            }

            if (!Schema::hasColumn('general_settings', 'cumulative_total_operator')) {
                // Stores symbols like '>', '<', '=', '+', '-' etc.
                $table->string('cumulative_total_operator')->nullable();
            }

            if (!Schema::hasColumn('general_settings', 'percentage_filter')) {
                // Using decimal for percentages (e.g., 10.50)
                $table->decimal('percentage_filter', 5, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $columns = [
                'whatsapp_number',
                'support_email',
                'is_packing_slip',
                'cumulative_total_target',
                'cumulative_total_operator',
                'percentage_filter'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('general_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};