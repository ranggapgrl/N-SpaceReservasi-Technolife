<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    // Kolom yang boleh diisi massal (mass assignment)
    protected $fillable = ['date', 'reason'];

    // Cast kolom 'date' ke tipe Carbon agar bisa dipakai ->format(), ->isoFormat(), dll.
    protected $casts = ['date' => 'date'];
}
