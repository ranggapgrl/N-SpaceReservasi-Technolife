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
    public function lockCapacityDates(array $dates): void
    {
        $dates = collect($dates)
            ->map(fn (string $date): string => Carbon::parse($date)->toDateString())
            ->unique()
            ->sort()
            ->values();

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
        array $schedules,
        int $visitorCount
    ): bool {
        $capacity = (int) config('booking.open_space_capacity');

        if ($visitorCount > $capacity) {
            return false;
        }

        $schedules = collect($schedules)->keyBy('date');

        if ($schedules->isEmpty()) {
            return false;
        }

        $firstDate = Carbon::parse($schedules->keys()->sort()->first());
        $lastDate = Carbon::parse($schedules->keys()->sort()->last());
        $existingBookings = Booking::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNull('room_id')
            ->where(function ($query) use ($schedules, $firstDate, $lastDate): void {
                $query->whereHas('bookingDates', function ($dateQuery) use ($schedules): void {
                    $dateQuery->where(function ($selectedDatesQuery) use ($schedules): void {
                        foreach ($schedules->keys() as $date) {
                            $selectedDatesQuery->orWhereDate('date', $date);
                        }
                    });
                })->orWhere(function ($legacyQuery) use ($firstDate, $lastDate): void {
                    $legacyQuery->whereDoesntHave('bookingDates')
                        ->whereBetween('booking_date', [
                            $firstDate->copy()->subDays(29)->toDateString(),
                            $lastDate->toDateString(),
                        ]);
                });
            })
            ->with('bookingDates')
            ->get(['id', 'booking_date', 'start_time', 'end_time', 'duration', 'visitor_count']);

        foreach ($schedules as $date => $schedule) {
            $occupiedVisitors = 0;

            foreach ($existingBookings as $booking) {
                if ($booking->bookingDates->isNotEmpty()) {
                    $bookingDate = $booking->bookingDates->first(
                        fn ($bookingDate): bool => $bookingDate->date->toDateString() === $date
                    );
                    $includesDate = $bookingDate !== null;
                } else {
                    $bookingStart = Carbon::parse($booking->booking_date);
                    $bookingEnd = $bookingStart->copy()->addDays(max(0, (int) $booking->duration - 1));
                    $currentDate = Carbon::parse($date);
                    $includesDate = $currentDate->gte($bookingStart) && $currentDate->lte($bookingEnd);
                    $bookingDate = null;
                }

                if (! $includesDate) {
                    continue;
                }

                $existingStartTime = $bookingDate?->start_time ?? $booking->start_time;
                $existingEndTime = $bookingDate?->end_time ?? $booking->end_time;

                if ($existingStartTime < $schedule['end_time'] && $existingEndTime > $schedule['start_time']) {
                    $occupiedVisitors += (int) $booking->visitor_count;
                }
            }

            if ($occupiedVisitors + $visitorCount > $capacity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Private venues are exclusive: any overlapping booking blocks that room for that interval.
     */
    public function roomIsAvailable(int $roomId, array $schedules): bool
    {
        $schedules = collect($schedules)->keyBy('date');

        if ($schedules->isEmpty()) {
            return false;
        }

        $bookings = Booking::query()
            ->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereHas('bookingDates', function ($dateQuery) use ($schedules): void {
                $dateQuery->where(function ($selectedDatesQuery) use ($schedules): void {
                    foreach ($schedules->keys() as $date) {
                        $selectedDatesQuery->orWhereDate('date', $date);
                    }
                });
            })
            ->with('bookingDates')
            ->get(['id', 'room_id', 'start_time', 'end_time']);

        foreach ($schedules as $date => $schedule) {
            foreach ($bookings as $booking) {
                $existingDate = $booking->bookingDates->first(
                    fn ($bookingDate): bool => $bookingDate->date->toDateString() === $date
                );

                if (! $existingDate) {
                    continue;
                }

                $existingStartTime = $existingDate->start_time ?? $booking->start_time;
                $existingEndTime = $existingDate->end_time ?? $booking->end_time;

                if ($existingStartTime < $schedule['end_time'] && $existingEndTime > $schedule['start_time']) {
                    return false;
                }
            }
        }

        return true;
    }
}
