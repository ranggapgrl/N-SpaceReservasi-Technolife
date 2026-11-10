<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Lock all calendar days in a booking so concurrent requests cannot oversell seats.
     */
    public function lockCapacityDates(string $date, int $duration): void
    {
        $dates = collect(range(0, $duration - 1))
            ->map(fn (int $offset): string => Carbon::parse($date)->addDays($offset)->toDateString());

        DB::table('booking_capacity_locks')->insertOrIgnore(
            $dates->map(fn (string $bookingDate): array => ['booking_date' => $bookingDate])->all()
        );

        DB::table('booking_capacity_locks')
            ->whereIn('booking_date', $dates)
            ->orderBy('booking_date')
            ->lockForUpdate()
            ->get(['booking_date']);
    }

    /**
     * Determine whether a booking fits within the shared open-space capacity.
     */
    public function hasCapacity(
        string $date,
        string $startTime,
        string $endTime,
        int $duration,
        int $visitorCount
    ): bool {
        $capacity = (int) config('booking.open_space_capacity');
        if ($visitorCount > $capacity) {
            return false;
        }

        $startDate = Carbon::parse($date);
        $endDate = $startDate->copy()->addDays($duration - 1);
        $existingBookings = Booking::query()
            ->whereBetween('booking_date', [$startDate->copy()->subDays(29)->toDateString(), $endDate->toDateString()])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['booking_date', 'start_time', 'end_time', 'duration', 'visitor_count']);

        for ($offset = 0; $offset < $duration; $offset++) {
            $currentDate = $startDate->copy()->addDays($offset);
            $occupiedVisitors = 0;

            foreach ($existingBookings as $booking) {
                $bookingStart = Carbon::parse($booking->booking_date);
                $bookingEnd = $bookingStart->copy()->addDays(max(0, (int) $booking->duration - 1));

                if ($currentDate->lt($bookingStart) || $currentDate->gt($bookingEnd)) {
                    continue;
                }

                if ($booking->start_time < $endTime && $booking->end_time > $startTime) {
                    $occupiedVisitors += (int) $booking->visitor_count;
                }
            }

            if ($occupiedVisitors + $visitorCount > $capacity) {
                return false;
            }
        }

        return true;
    }
}
