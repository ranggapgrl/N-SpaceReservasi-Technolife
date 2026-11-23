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
            $table->string('venue_package', 20)->nullable()->after('room_name');
            $table->unsignedTinyInteger('venue_duration_hours')->nullable()->after('venue_package');
            $table->decimal('venue_price_per_person', 10, 2)->nullable()->after('venue_duration_hours');
            $table->decimal('service_charge_rate', 5, 2)->default(0)->after('venue_price_per_person');
            $table->decimal('service_charge', 10, 2)->default(0)->after('service_charge_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'venue_package',
                'venue_duration_hours',
                'venue_price_per_person',
                'service_charge_rate',
                'service_charge',
            ]);
        });
    }
};
