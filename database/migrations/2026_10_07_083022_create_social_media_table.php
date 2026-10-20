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
            Schema::create('social_media', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');                       // e.g. Facebook
                $table->string('url', 500);                   // https://facebook.com/yourpage
                $table->string('icon', 100);                  // icon key — REQUIRED (e.g. 'facebook')
                $table->boolean('is_active')->default(true);  // only active show on storefront
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

    public function down(): void
    {
        Schema::dropIfExists('social_media');
    }
};
