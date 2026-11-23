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
        array $dates,
        string $startTime,
        string $endTime,
        int $visitorCount
    ): bool {
        $capacity = (int) config('booking.open_space_capacity');

        if ($visitorCount > $capacity) {
            return false;
        }

        $dates = collect($dates)
            ->map(fn (string $date): string => Carbon::parse($date)->toDateString())
            ->unique()
            ->sort()
            ->values();

        if ($dates->isEmpty()) {
            return false;
        }

        $firstDate = Carbon::parse($dates->first());
        $lastDate = Carbon::parse($dates->last());
        $existingBookings = Booking::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($dates, $firstDate, $lastDate): void {
                $query->whereHas('bookingDates', function ($dateQuery) use ($dates): void {
                    $dateQuery->where(function ($selectedDatesQuery) use ($dates): void {
                        foreach ($dates as $date) {
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

        foreach ($dates as $date) {
            $occupiedVisitors = 0;

            foreach ($existingBookings as $booking) {
                if ($booking->bookingDates->isNotEmpty()) {
                    $includesDate = $booking->bookingDates->contains(
                        fn ($bookingDate): bool => $bookingDate->date->toDateString() === $date
                    );
                } else {
                    $bookingStart = Carbon::parse($booking->booking_date);
                    $bookingEnd = $bookingStart->copy()->addDays(max(0, (int) $booking->duration - 1));
                    $currentDate = Carbon::parse($date);
                    $includesDate = $currentDate->gte($bookingStart) && $currentDate->lte($bookingEnd);
                }

                if (! $includesDate) {
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
