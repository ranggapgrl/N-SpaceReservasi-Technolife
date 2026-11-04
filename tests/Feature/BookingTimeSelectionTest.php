<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Services\Payment\PaymentServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BookingTimeSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_page_shows_arrival_and_departure_time_fields(): void
    {
        $response = $this->get(route('booking.index'));

        $response->assertOk()
            ->assertSee('Jam kedatangan')
            ->assertSee('Jam kepulangan')
            ->assertSee('name="departure_time"', false);
    }

    public function test_booking_persists_selected_arrival_and_departure_times(): void
    {
        Room::create([
            'name' => 'Open Space',
            'type' => 'Coworking Space',
            'capacity' => 20,
            'price_per_hour' => 0,
            'status' => 'active',
        ]);

        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldReceive('createTransaction')
            ->once()
            ->andReturn('/checkout/test');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        $response = $this->post(route('booking.store'), [
            'room_type' => 'Coworking Space',
            'visitor_category' => 'umum',
            'date' => now()->addDay()->toDateString(),
            'arrival_time' => '09:00',
            'departure_time' => '17:00',
            'duration' => 1,
            'customer_name' => 'Test Customer',
            'customer_phone' => '08123456789',
        ]);

        $response->assertRedirect('/checkout/test');
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Test Customer',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);
    }

    public function test_departure_time_must_be_after_arrival_time(): void
    {
        $response = $this->from(route('booking.index'))->post(route('booking.store'), [
            'room_type' => 'Coworking Space',
            'visitor_category' => 'umum',
            'date' => now()->addDay()->toDateString(),
            'arrival_time' => '09:00',
            'departure_time' => '09:00',
            'duration' => 1,
            'customer_name' => 'Test Customer',
            'customer_phone' => '08123456789',
        ]);

        $response->assertRedirect(route('booking.index'))
            ->assertSessionHasErrors('departure_time');
        $this->assertSame(0, Booking::count());
    }
}
