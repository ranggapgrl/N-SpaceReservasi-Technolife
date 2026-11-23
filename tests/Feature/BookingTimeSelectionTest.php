<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingDate;
use App\Models\Room;
use App\Services\Payment\PaymentServiceInterface;
use Database\Seeders\RoomCatalogSeeder;
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
            ->assertSee('Jam mulai')
            ->assertSee('Jam selesai')
            ->assertSee('departure_times[', false)
            ->assertSee('Perjam')
            ->assertSee('Rp25.000 / orang')
            ->assertSee('Halfday')
            ->assertSee('Rp40.000 / orang')
            ->assertSee('Fullday')
            ->assertSee('Rp55.000 / orang')
            ->assertSee('Service Charge 5%');
    }

    public function test_booking_page_lists_individual_rooms_from_the_catalog(): void
    {
        $this->seed(RoomCatalogSeeder::class);

        $response = $this->get(route('booking.index'));

        $response->assertOk()
            ->assertSee('Ruang Bhaswara')
            ->assertSee('Ruang Bhaskara')
            ->assertSee('Ruang Bimantara')
            ->assertSee('Ruang Biwara')
            ->assertSee('Ruang Meeting Sadewa')
            ->assertSee('Ruang Meeting Danuraga')
            ->assertSee('Ruang Meeting Diwangkara')
            ->assertSee('Ruang Meeting Abimanyu')
            ->assertSee('Ruang Meeting Bhimasena VVIP');
    }

    public function test_room_catalog_seeder_removes_legacy_options_without_losing_booking_history(): void
    {
        $legacyMeetingRoom = Room::create([
            'name' => 'Ruang Meeting',
            'type' => 'Meeting Room',
            'capacity' => 6,
            'price_per_session' => 25000,
            'facilities' => [],
            'status' => 'active',
        ]);
        $legacyTrainingRoom = Room::create([
            'name' => 'Ruangan Training',
            'type' => 'Training',
            'capacity' => 20,
            'price_per_session' => 25000,
            'facilities' => [],
            'status' => 'active',
        ]);
        $booking = Booking::create([
            'booking_code' => 'NSS-LEGACY-ROOM-HISTORY',
            'room_id' => $legacyMeetingRoom->id,
            'room_name' => $legacyMeetingRoom->name,
            'room_price_per_session' => $legacyMeetingRoom->price_per_session,
            'customer_name' => 'Legacy Room Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'legacy-room@example.com',
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration' => 1,
            'visitor_count' => 5,
            'day_pass_price' => 125000,
            'product_total' => 0,
            'total_price' => 125000,
            'status' => 'confirmed',
            'visitor_category' => 'venue',
        ]);

        $this->seed(RoomCatalogSeeder::class);

        $this->assertDatabaseHas('rooms', [
            'id' => $legacyMeetingRoom->id,
            'status' => 'inactive',
        ]);
        $this->assertDatabaseMissing('rooms', ['id' => $legacyTrainingRoom->id]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'room_id' => $legacyMeetingRoom->id,
        ]);
    }

    public function test_booking_page_requires_manual_visitor_count(): void
    {
        $response = $this->get(route('booking.index'));

        $response->assertOk()
            ->assertSee('name="visitor_count"', false)
            ->assertSee('x-model.number="form.visitor_count"', false)
            ->assertDontSee('name="visitor_count" value="1"', false);
    }

    public function test_booking_page_disables_a_venue_whose_capacity_is_below_its_minimum(): void
    {
        Room::create([
            'name' => 'Meeting Room Capacity Check',
            'type' => 'Meeting Room',
            'capacity' => 1,
            'price_per_session' => 25000,
            'facilities' => [],
            'status' => 'active',
        ]);

        $response = $this->get(route('booking.index'));

        $response->assertOk()
            ->assertSee(':disabled="true"', false)
            ->assertSee('Belum bisa dipesan: kapasitas di bawah minimum pemesanan. Hubungi admin.')
            ->assertDontSee('<footer class="nf">', false);
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

    public function test_room_booking_uses_a_fixed_session_price_for_each_date_and_saves_each_schedule(): void
    {
        $room = Room::create([
            'name' => 'Meeting Room A',
            'type' => 'Meeting Room',
            'description' => 'Ruang rapat',
            'capacity' => 12,
            'price_per_session' => 150000,
            'facilities' => ['Proyektor'],
            'status' => 'active',
        ]);
        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldReceive('createTransaction')
            ->once()
            ->withArgs(fn (Booking $booking): bool => (float) $booking->total_price === 672000.0)
            ->andReturn('/checkout/room');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        $dates = [
            now()->addDays(2)->toDateString(),
            now()->addDays(5)->toDateString(),
        ];

        $response = $this->post(route('booking.store'), [
            'room_id' => $room->id,
            'venue_package' => 'halfday',
            'dates' => $dates,
            'date' => $dates[0],
            'duration' => 2,
            'arrival_times' => [$dates[0] => '09:00', $dates[1] => '13:00'],
            'departure_times' => [$dates[0] => '10:00', $dates[1] => '14:00'],
            'visitor_count' => 8,
            'customer_name' => 'Room Customer',
            'institution' => 'PT Contoh Nusantara',
            'customer_phone' => '08123456789',
            'customer_email' => 'room@example.com',
        ]);

        $response->assertRedirect('/checkout/room');
        $this->assertDatabaseHas('bookings', [
            'room_id' => $room->id,
            'room_name' => 'Meeting Room A',
            'venue_package' => 'halfday',
            'venue_duration_hours' => 4,
            'venue_price_per_person' => 40000,
            'service_charge_rate' => 5,
            'service_charge' => 32000,
            'institution' => 'PT Contoh Nusantara',
            'visitor_category' => 'venue',
            'visitor_count' => 8,
            'day_pass_price' => 640000,
            'total_price' => 672000,
        ]);
        $this->assertTrue(BookingDate::whereDate('date', $dates[0])
            ->where('start_time', '09:00:00')
            ->where('end_time', '13:00:00')
            ->exists());
        $this->assertTrue(BookingDate::whereDate('date', $dates[1])
            ->where('start_time', '13:00:00')
            ->where('end_time', '17:00:00')
            ->exists());
        $this->assertDatabaseHas('payments', ['amount' => 672000]);
    }

    public function test_venue_booking_applies_per_person_package_rate_and_five_percent_charge(): void
    {
        $packages = [
            ['key' => 'per_jam', 'hours' => 1, 'price' => 25000, 'fee' => 6250, 'total' => 131250],
            ['key' => 'fullday', 'hours' => 8, 'price' => 55000, 'fee' => 13750, 'total' => 288750],
        ];
        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldReceive('createTransaction')
            ->times(count($packages))
            ->andReturn('/checkout/package');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        foreach ($packages as $index => $package) {
            $room = Room::create([
                'name' => 'Meeting Room '.$index,
                'type' => 'Meeting Room',
                'capacity' => 10,
                'price_per_session' => 150000,
                'facilities' => [],
                'status' => 'active',
            ]);
            $date = now()->addDays(3 + $index)->toDateString();

            $response = $this->post(route('booking.store'), [
                'room_id' => $room->id,
                'venue_package' => $package['key'],
                'dates' => [$date],
                'date' => $date,
                'duration' => 1,
                'arrival_times' => [$date => '09:00'],
                'visitor_count' => 5,
                'customer_name' => 'Package Customer',
                'institution' => 'PT Contoh',
                'customer_phone' => '08123456789',
                'customer_email' => 'package@example.com',
            ]);

            $response->assertRedirect('/checkout/package');
            $this->assertDatabaseHas('bookings', [
                'room_id' => $room->id,
                'venue_package' => $package['key'],
                'venue_duration_hours' => $package['hours'],
                'venue_price_per_person' => $package['price'],
                'day_pass_price' => $package['price'] * 5,
                'service_charge' => $package['fee'],
                'total_price' => $package['total'],
            ]);
            $booking = Booking::where('room_id', $room->id)->firstOrFail();
            $this->assertTrue($booking->bookingDates()
                ->whereDate('date', $date)
                ->where('start_time', '09:00:00')
                ->where('end_time', sprintf('%02d:00:00', 9 + $package['hours']))
                ->exists());
        }
    }

    public function test_room_booking_requires_an_institution(): void
    {
        $room = Room::create([
            'name' => 'Meeting Room B',
            'type' => 'Meeting Room',
            'capacity' => 10,
            'price_per_session' => 150000,
            'facilities' => [],
            'status' => 'active',
        ]);
        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldNotReceive('createTransaction');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        $date = now()->addDays(2)->toDateString();
        $response = $this->from(route('booking.index'))->post(route('booking.store'), [
            'room_id' => $room->id,
            'dates' => [$date],
            'date' => $date,
            'duration' => 1,
            'arrival_times' => [$date => '09:00'],
            'departure_times' => [$date => '12:00'],
            'visitor_count' => 5,
            'customer_name' => 'Room Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'room@example.com',
        ]);

        $response->assertRedirect(route('booking.index'))
            ->assertSessionHasErrors('institution');
        $this->assertSame(0, Booking::count());
    }

    public function test_room_booking_enforces_minimum_attendees_for_meeting_and_training_rooms(): void
    {
        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldNotReceive('createTransaction');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        foreach ([
            ['type' => 'Meeting Room', 'capacity' => 10, 'visitors' => 4],
            ['type' => 'Training Room', 'capacity' => 25, 'visitors' => 19],
        ] as $index => $scenario) {
            $room = Room::create([
                'name' => $scenario['type'].' '.$index,
                'type' => $scenario['type'],
                'capacity' => $scenario['capacity'],
                'price_per_session' => 150000,
                'facilities' => [],
                'status' => 'active',
            ]);
            $date = now()->addDays(4 + $index)->toDateString();

            $response = $this->from(route('booking.index'))->post(route('booking.store'), [
                'room_id' => $room->id,
                'venue_package' => 'per_jam',
                'dates' => [$date],
                'date' => $date,
                'duration' => 1,
                'arrival_times' => [$date => '09:00'],
                'visitor_count' => $scenario['visitors'],
                'customer_name' => 'Minimum Test',
                'institution' => 'PT Contoh',
                'customer_phone' => '08123456789',
                'customer_email' => 'minimum@example.com',
            ]);

            $response->assertRedirect(route('booking.index'))
                ->assertSessionHasErrors('visitor_count');
        }

        $this->assertSame(0, Booking::count());
    }

    public function test_availability_check_marks_an_overlapping_room_schedule_as_unavailable(): void
    {
        $room = Room::create([
            'name' => 'Meeting Room Availability Check',
            'type' => 'Meeting Room',
            'capacity' => 10,
            'price_per_session' => 150000,
            'facilities' => [],
            'status' => 'active',
        ]);
        $date = now()->addDays(2)->toDateString();
        $booking = Booking::create([
            'booking_code' => 'NSS-ROOM-AVAILABILITY',
            'room_id' => $room->id,
            'room_name' => $room->name,
            'room_price_per_session' => $room->price_per_session,
            'customer_name' => 'Existing Room Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'existing-room@example.com',
            'booking_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration' => 1,
            'visitor_count' => 10,
            'day_pass_price' => 250000,
            'product_total' => 0,
            'total_price' => 250000,
            'status' => 'pending',
            'visitor_category' => 'venue',
        ]);
        $booking->bookingDates()->create([
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        $overlappingResponse = $this->postJson(route('booking.check'), [
            'room_id' => $room->id,
            'venue_package' => 'per_jam',
            'dates' => [$date],
            'arrival_times' => [$date => '09:00'],
            'departure_times' => [$date => '10:00'],
            'visitor_count' => 10,
        ]);

        $overlappingResponse->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('message', 'Venue sudah dipesan pada salah satu jadwal pilihan.');

        $availableResponse = $this->postJson(route('booking.check'), [
            'room_id' => $room->id,
            'venue_package' => 'per_jam',
            'dates' => [$date],
            'arrival_times' => [$date => '13:00'],
            'departure_times' => [$date => '14:00'],
            'visitor_count' => 10,
        ]);

        $availableResponse->assertOk()
            ->assertJsonPath('available', true);
    }

    public function test_booking_availability_is_scoped_to_the_selected_room(): void
    {
        $bookedRoom = Room::create([
            'name' => 'Booked Meeting Room',
            'type' => 'Meeting Room',
            'capacity' => 10,
            'price_per_session' => 150000,
            'facilities' => [],
            'status' => 'active',
        ]);
        $availableRoom = Room::create([
            'name' => 'Available Meeting Room',
            'type' => 'Meeting Room',
            'capacity' => 10,
            'price_per_session' => 150000,
            'facilities' => [],
            'status' => 'active',
        ]);
        $date = now()->addDays(2)->toDateString();
        $booking = Booking::create([
            'booking_code' => 'NSS-SPECIFIC-ROOM-AVAILABILITY',
            'room_id' => $bookedRoom->id,
            'room_name' => $bookedRoom->name,
            'room_price_per_session' => $bookedRoom->price_per_session,
            'customer_name' => 'Existing Room Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'existing-room@example.com',
            'booking_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration' => 1,
            'visitor_count' => 10,
            'day_pass_price' => 250000,
            'product_total' => 0,
            'total_price' => 250000,
            'status' => 'confirmed',
            'visitor_category' => 'venue',
        ]);
        $booking->bookingDates()->create([
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        $response = $this->postJson(route('booking.check'), [
            'room_id' => $availableRoom->id,
            'venue_package' => 'per_jam',
            'dates' => [$date],
            'arrival_times' => [$date => '09:00'],
            'departure_times' => [$date => '10:00'],
            'visitor_count' => 10,
        ]);

        $response->assertOk()
            ->assertJsonPath('available', true);
    }

    public function test_room_booking_rejects_an_overlapping_time_slot(): void
    {
        $room = Room::create([
            'name' => 'Training Room',
            'type' => 'Training Room',
            'capacity' => 20,
            'price_per_session' => 200000,
            'facilities' => [],
            'status' => 'active',
        ]);
        $date = now()->addDays(2)->toDateString();
        $existingBooking = Booking::create([
            'booking_code' => 'NSS-ROOM-CAPACITY',
            'room_id' => $room->id,
            'room_name' => $room->name,
            'room_price_per_session' => $room->price_per_session,
            'customer_name' => 'Existing Room Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'existing-room@example.com',
            'booking_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'duration' => 1,
            'visitor_count' => 10,
            'day_pass_price' => 200000,
            'product_total' => 0,
            'total_price' => 200000,
            'status' => 'confirmed',
            'visitor_category' => 'venue',
        ]);
        $existingBooking->bookingDates()->create([
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);
        $paymentService = Mockery::mock(PaymentServiceInterface::class);
        $paymentService->shouldNotReceive('createTransaction');
        $this->app->instance(PaymentServiceInterface::class, $paymentService);

        $response = $this->from(route('booking.index'))
            ->withHeader('referer', route('booking.index'))
            ->post(route('booking.store'), [
                'room_id' => $room->id,
                'venue_package' => 'per_jam',
                'dates' => [$date],
                'date' => $date,
                'duration' => 1,
                'arrival_times' => [$date => '11:00'],
                'departure_times' => [$date => '14:00'],
                'visitor_count' => 20,
                'customer_name' => 'New Room Customer',
                'institution' => 'PT Contoh',
                'customer_phone' => '08123456789',
                'customer_email' => 'new-room@example.com',
            ]);

        $response->assertRedirect(route('booking.index'))
            ->assertSessionHas('error', 'Venue sudah dipesan pada salah satu jadwal pilihan. Silakan pilih jam atau tanggal lain.');
        $this->assertSame(1, Booking::count());
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
            'visitor_count' => 1,
            'customer_name' => 'Test Customer',
            'customer_phone' => '08123456789',
            'customer_email' => 'customer@example.com',
        ]);

        $response->assertRedirect(route('booking.index'))
            ->assertSessionHasErrors('departure_time');
        $this->assertSame(0, Booking::count());
    }
}
