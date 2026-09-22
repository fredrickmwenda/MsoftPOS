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
        Schema::create('leaves', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id');
            $table->integer('leave_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('days');
            $table->enum('status', ['approved', 'pending', 'canceled'])->default('pending');
            $table->integer('approver_id');
        //             'leave_types',
        // 'start_date',
        // 'end_date',
        // 'days',
        // 'status',
        // 'approver_id'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
