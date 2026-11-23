<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingHistoryTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_venue_booking_history_does_not_show_the_study_space_label(): void
    {
        $room = Room::create([
            'name' => 'Ruang Meeting Danuraga',
            'type' => 'Meeting Room',
            'capacity' => 10,
            'price_per_session' => 25000,
            'facilities' => [],
            'status' => 'active',
        ]);
        $booking = Booking::create([
            'booking_code' => 'VENUE-HISTORY-001',
            'room_id' => $room->id,
            'room_name' => $room->name,
            'customer_name' => 'Pelanggan Venue',
            'customer_phone' => '08123456789',
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'duration' => 1,
            'day_pass_price' => 125000,
            'product_total' => 0,
            'total_price' => 131250,
            'status' => 'confirmed',
        ]);

        $this->get(route('booking.check_status', ['keyword' => $booking->booking_code]))
            ->assertOk()
            ->assertSee('Sewa Venue')
            ->assertSee('Ruang Meeting Danuraga')
            ->assertDontSee('Study Space Area (All-Access)');
    }

    public function test_open_space_booking_history_keeps_the_study_space_label(): void
    {
        $booking = Booking::create([
            'booking_code' => 'SPACE-HISTORY-001',
            'customer_name' => 'Pelanggan Study Space',
            'customer_phone' => '08123456789',
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '22:00:00',
            'duration' => 1,
            'day_pass_price' => 35000,
            'product_total' => 0,
            'total_price' => 35000,
            'status' => 'confirmed',
        ]);

        $this->get(route('booking.check_status', ['keyword' => $booking->booking_code]))
            ->assertOk()
            ->assertSee('Study Space Area (All-Access)')
            ->assertDontSee('Sewa Venue');
    }
}
