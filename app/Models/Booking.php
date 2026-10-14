<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Booking extends Model
{
    /**
     * $guarded = [] artinya semua kolom bisa diisi massal (mass assignment).
     * Alternatif: gunakan $fillable dan daftarkan nama kolom secara eksplisit.
     *
     * Karena proyek ini sudah memakai $guarded = [], kita pertahankan polanya.
     */
    protected $guarded = [];

    // ═══════════════════════════════════════════════
    //  RELASI DATABASE
    // ═══════════════════════════════════════════════

    /**
     * Relasi many-to-one ke model Room.
     * Setiap booking terhubung ke satu ruangan.
     *
     * Penggunaan: $booking->room->name
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Relasi one-to-many ke model BookingItem.
     * Satu booking bisa memiliki banyak item produk tambahan.
     *
     * Penggunaan: $booking->items (koleksi)
     */
    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    /**
     * Relasi one-to-one ke model Payment.
     * Setiap booking memiliki satu catatan pembayaran.
     *
     * Penggunaan: $booking->payment->status
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ═══════════════════════════════════════════════
    //  ACCESSOR (Helper untuk tampilan)
    // ═══════════════════════════════════════════════

    /**
     * Mengembalikan label kategori pengunjung dalam bahasa Indonesia.
     *
     * Penggunaan: $booking->visitor_category_label
     * Contoh output: "Mahasiswa" atau "Umum"
     */
    public function getVisitorCategoryLabelAttribute(): string
    {
        return match ($this->visitor_category) {
            'mahasiswa' => 'Mahasiswa',
            'umum'      => 'Umum',
            default     => ucfirst($this->visitor_category ?? '-'),
        };
    }

    /**
     * Mengembalikan label status KTM yang mudah dibaca.
     *
     * Penggunaan: $booking->ktm_status_label
     * Contoh output: "Menunggu Verifikasi", "Disetujui", "Ditolak", atau "—"
     */
    public function getKtmStatusLabelAttribute(): string
    {
        return match ($this->ktm_status) {
            'pending'  => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => '—', // Tidak ada KTM (pelanggan umum)
        };
    }

    /**
     * Mengecek apakah booking ini memerlukan KTM.
     * Hanya mahasiswa yang wajib upload KTM.
     *
     * Penggunaan: if ($booking->requiresKtm()) { ... }
     */
    public function requiresKtm(): bool
    {
        return $this->visitor_category === 'mahasiswa';
    }

    /**
     * Mengecek apakah KTM sudah diverifikasi dan disetujui.
     *
     * Penggunaan: if ($booking->isKtmApproved()) { ... }
     */
    public function isKtmApproved(): bool
    {
        return $this->ktm_status === 'approved';
    }
}
