<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');                                // Salary Grade / Hourly Name
            $table->enum('type', ['monthly', 'hourly'])->index();  // template type
            $table->decimal('basic_salary', 12, 2)->default(0);    // monthly only
            $table->decimal('overtime_rate', 12, 2)->default(0);   // per hour — monthly only
            $table->decimal('hourly_rate', 12, 2)->default(0);     // hourly only
            $table->decimal('hours_per_day', 6, 2)->default(8);    // hourly only
            $table->boolean('is_default')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('salary_template_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_template_id')->index(); // ✅ integer reference — no FK
            $table->string('name');
            $table->enum('type', ['allowance', 'deduction'])->default('allowance');
            $table->decimal('amount', 12, 2)->default(0);              // ✅ FIXED value — no percentage
            $table->timestamps();
        });

        // Employees are assigned a template (salary OR hourly)
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('salary_template_id')
                  ->nullable()
                  ->index()
                  ->after('sales_target'); // ✅ integer selection — no foreign constraint
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('salary_template_id');
        });
        Schema::dropIfExists('salary_template_items');
        Schema::dropIfExists('salary_templates');
    }
};
