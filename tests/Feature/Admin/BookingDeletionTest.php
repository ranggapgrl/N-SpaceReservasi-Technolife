<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_booking_redirects_to_the_booking_list(): void
    {
        $this->actingAs(User::factory()->create());

        $room = Room::create([
            'name' => 'Open Space',
            'type' => 'Regular',
            'capacity' => 20,
            'price_per_hour' => 10000,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'booking_code' => 'DELETE-BOOKING',
            'room_id' => $room->id,
            'customer_name' => 'Test User',
            'customer_phone' => '08123456789',
            'booking_date' => '2026-11-02',
            'start_time' => '08:00:00',
            'end_time' => '22:00:00',
            'duration' => 1,
            'room_price' => 50000,
            'product_total' => 0,
            'total_price' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->delete(route('admin.bookings.destroy', $booking));

        $response->assertRedirect(route('admin.bookings.index'))
            ->assertSessionHas('success', 'Data booking berhasil dihapus.');
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        $this->get(route('admin.bookings.show', $booking))->assertNotFound();
    }
}
