<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    public function scopeVerifiedPaid(Builder $query): Builder
    {
        return $query->where('status', 'paid')
            ->whereNotNull('verified_at');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
