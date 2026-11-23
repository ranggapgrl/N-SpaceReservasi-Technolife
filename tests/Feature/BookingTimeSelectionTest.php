<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingDate;
use App\Services\Payment\PaymentServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
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

    public function test_booking_page_requires_manual_visitor_count(): void
    {
        $response = $this->get(route('booking.index'));

        $response->assertOk()
            ->assertSee('name="visitor_count"', false)
            ->assertSee('x-model.number="form.visitor_count"', false)
            ->assertDontSee('name="visitor_count" value="1"', false);
    }

    public function test_booking_persists_selected_arrival_and_departure_times(): void
    {
        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldReceive('createTransaction')
            ->withArgs(fn (Booking $booking): bool => $booking->payment !== null && $booking->payment->exists)
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
            'visitor_count' => 2,
            'customer_name' => 'Test Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'customer@example.com',
        ]);

        $response->assertRedirect('/checkout/test');
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Test Customer',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'visitor_count' => 2,
        ]);
    }

    public function test_booking_saves_multiple_selected_dates_and_facility_notes(): void
    {
        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldReceive('createTransaction')
            ->once()
            ->andReturn('/checkout/test');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        $dates = [
            now()->addDays(2)->toDateString(),
            now()->addDays(5)->toDateString(),
        ];

        $response = $this->post(route('booking.store'), [
            'visitor_category' => 'umum',
            'dates' => $dates,
            'date' => $dates[0],
            'duration' => 2,
            'arrival_time' => '09:00',
            'departure_time' => '17:00',
            'visitor_count' => 3,
            'facility_notes' => 'Meja dekat stopkontak',
            'customer_name' => 'Multi Date Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'multi@example.com',
        ]);

        $response->assertRedirect('/checkout/test');
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Multi Date Customer',
            'booking_date' => $dates[0],
            'duration' => 2,
            'visitor_count' => 3,
            'day_pass_price' => 330000,
            'total_price' => 330000,
            'facility_notes' => 'Meja dekat stopkontak',
        ]);
        $this->assertDatabaseHas('payments', ['amount' => 330000]);
        $this->assertSame(2, BookingDate::count());
        $this->assertTrue(BookingDate::whereDate('date', $dates[0])->exists());
        $this->assertTrue(BookingDate::whereDate('date', $dates[1])->exists());
    }

    public function test_booking_rejects_duplicate_selected_dates(): void
    {
        $date = now()->addDays(2)->toDateString();

        $response = $this->from(route('booking.index'))
            ->withHeader('referer', route('booking.index'))
            ->post(route('booking.store'), [
                'visitor_category' => 'umum',
                'dates' => [$date, $date],
                'date' => $date,
                'duration' => 2,
                'arrival_time' => '09:00',
                'departure_time' => '17:00',
                'visitor_count' => 1,
                'customer_name' => 'Test Customer',
                'customer_phone' => '08123456789',
                'customer_email' => 'customer@example.com',
            ]);

        $response->assertRedirect(route('booking.index'))
            ->assertSessionHasErrors('dates.1');
        $this->assertSame(0, Booking::count());
    }

    public function test_booking_is_rejected_when_one_selected_date_exceeds_capacity(): void
    {
        Config::set('booking.open_space_capacity', 5);
        $date = now()->addDays(2)->toDateString();

        $existingBooking = Booking::create([
            'booking_code' => 'NSS-CAPACITY-TEST',
            'customer_name' => 'Existing Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'existing@example.com',
            'booking_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'duration' => 1,
            'visitor_count' => 4,
            'day_pass_price' => 55000,
            'product_total' => 0,
            'total_price' => 55000,
            'status' => 'confirmed',
            'visitor_category' => 'umum',
        ]);
        $existingBooking->bookingDates()->create(['date' => $date]);

        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldNotReceive('createTransaction');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        $response = $this->from(route('booking.index'))->post(route('booking.store'), [
            'visitor_category' => 'umum',
            'dates' => [$date, now()->addDays(4)->toDateString()],
            'date' => $date,
            'duration' => 2,
            'arrival_time' => '09:00',
            'departure_time' => '17:00',
            'visitor_count' => 2,
            'customer_name' => 'New Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('booking.index'))
            ->assertSessionHas('error', 'Kapasitas open space tidak mencukupi pada jadwal yang dipilih. Kurangi jumlah pengunjung atau pilih jadwal lain.');
        $this->assertSame(1, Booking::count());
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
