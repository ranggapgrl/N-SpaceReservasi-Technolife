<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("bookings", function (Blueprint $table) {
            $table->id();
            $table->string("booking_code")->unique();
            $table->foreignId("room_id")->constrained()->onDelete("cascade");
            $table->string("customer_name");
            $table->string("customer_phone");
            $table->date("booking_date");
            $table->time("start_time");
            $table->time("end_time");
            $table->integer("duration");
            $table->decimal("room_price", 10, 2);
            $table->decimal("product_total", 10, 2)->default(0);
            $table->decimal("total_price", 10, 2);
            $table->string("status")->default("pending");
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists("bookings");
    }
};