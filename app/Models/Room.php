<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Room extends Model
{
    protected $guarded = [];

    protected $casts = [
        'facilities' => 'array',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->resolveImageUrl($this->getAttribute('image')));
    }

    private function resolveImageUrl(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if (filter_var($image, FILTER_VALIDATE_URL) || str_starts_with($image, '/')) {
            return $image;
        }

        return Storage::disk('public')->url($image);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function minimumAttendees(): int
    {
        $type = Str::lower($this->type);

        if (Str::contains($type, ['training', 'pelatihan', 'latihan'])) {
            return (int) config('booking.venue_minimum_attendees.training', 20);
        }

        if (Str::contains($type, ['meeting', 'rapat'])) {
            return (int) config('booking.venue_minimum_attendees.meeting', 5);
        }

        return 1;
    }
}
