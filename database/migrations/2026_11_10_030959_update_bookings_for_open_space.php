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
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropForeign(['room_id']);
            $table->unsignedBigInteger('room_id')->nullable()->change();
            $table->renameColumn('room_price', 'day_pass_price');
            $table->unsignedSmallInteger('visitor_count')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('bookings')->whereNull('room_id')->exists()) {
            throw new \RuntimeException(
                'Cannot restore the required room_id column while open-space bookings have no legacy room assignment.'
            );
        }

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn('visitor_count');
            $table->renameColumn('day_pass_price', 'room_price');
            $table->unsignedBigInteger('room_id')->nullable(false)->change();
            $table->foreign('room_id')->references('id')->on('rooms')->cascadeOnDelete();
        });
    }
};
