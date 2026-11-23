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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('booking_code')->constrained('rooms')->nullOnDelete();
            $table->string('room_name')->nullable()->after('room_id');
            $table->decimal('room_price_per_session', 10, 2)->nullable()->after('room_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
            $table->dropColumn(['room_name', 'room_price_per_session']);
        });
    }
};
