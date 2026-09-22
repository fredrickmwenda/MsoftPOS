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
        Schema::create('category_departments', function (Blueprint $table) {
            $table->increments('id'); // Creates an auto-incrementing UNSIGNED BIGINT (or INT) 'id'
            $table->string('name'); // Creates a VARCHAR 'name' column
            $table->string('image')->nullable(); // Creates a nullable VARCHAR 'image' column
            $table->boolean('is_active')->default(1); // Creates a TINYINT 'is_active' with default 1
            $table->timestamps(); // Creates nullable 'created_at' and 'updated_at' TIMESTAMP columns
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_departments');
    }
};
