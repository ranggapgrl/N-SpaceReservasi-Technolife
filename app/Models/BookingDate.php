<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDate extends Model
{
    public $timestamps = false;

    protected $fillable = ['date', 'start_time', 'end_time'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
