<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Booking;
use Carbon\Carbon;

class BookingService
{
    /**
     * Get available room of a specific type for the given date, time, and duration
     */
    public function getAvailableRoom(string $roomType, string $date, string $startTime, int $duration)
    {
        $start = Carbon::parse("$date $startTime");
        $end = $start->copy()->addHours($duration);

        // Find rooms of this type that are active
        $rooms = Room::where('type', $roomType)
            ->where('status', 'active')
            ->get();

        foreach ($rooms as $room) {
            if ($this->isRoomAvailable($room->id, $date, $start->format('H:i:s'), $end->format('H:i:s'))) {
                return $room; // Return the first available physical room
            }
        }

        return null; // No rooms of this type available
    }

    /**
     * Check if a specific room is available for the given time slot
     */
    public function isRoomAvailable($roomId, $date, $startTime, $endTime)
    {
        $overlappingBookings = Booking::where('room_id', $roomId)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed']) // ignore cancelled/expired/completed (if completed frees it up)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // new booking starts inside an existing booking
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })->count();

        return $overlappingBookings === 0;
    }
}
