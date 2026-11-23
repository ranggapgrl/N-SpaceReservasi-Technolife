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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 100);
            $table->text('description')->nullable();
            $table->unsignedInteger('capacity');
            $table->decimal('price_per_session', 10, 2);
            $table->string('image', 2048)->nullable();
            $table->string('image_2', 2048)->nullable();
            $table->string('image_3', 2048)->nullable();
            $table->json('facilities')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
