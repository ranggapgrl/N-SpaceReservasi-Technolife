<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    // Field yang boleh diisi melalui create() dan update() dari data tervalidasi.
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'is_active',
    ];

    /**
     * Pertahankan dukungan URL lama dan ubah path unggahan lokal menjadi URL publik.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            // Ambil nilai atribut aktif agar URL juga tersedia saat model baru dibuat atau diedit.
            $image = $this->getAttribute('image');

            if (! $image) {
                return null;
            }

            // URL penuh dan path absolut dipertahankan demi kompatibilitas data gambar lama.
            if (filter_var($image, FILTER_VALIDATE_URL) || str_starts_with($image, '/')) {
                return $image;
            }

            // Path relatif hasil upload disajikan melalui disk public Laravel.
            return Storage::disk('public')->url($image);
        });
    }
}
