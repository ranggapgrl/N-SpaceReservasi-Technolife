<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration');
            $table->decimal('day_pass_price', 10, 2); // Menggantikan room_price
            $table->decimal('product_total', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('pending');

            // Kolom pendukung open space & kategori mahasiswa
            $table->string('visitor_category')->default('mahasiswa');
            $table->string('ktm_path')->nullable();
            $table->string('ktm_status')->nullable();
            $table->text('ktm_reject_reason')->nullable();
            $table->integer('visitor_count')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
