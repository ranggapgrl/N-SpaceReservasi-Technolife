<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Menambahkan kolom KTM dan kategori pengunjung ke tabel bookings
 *
 * Kolom baru:
 *  - visitor_category : kategori pengunjung (mahasiswa/umum)
 *  - ktm_path         : path file KTM yang diunggah (disimpan privat di storage/app/ktm/)
 *  - ktm_status       : status verifikasi KTM oleh admin (pending/approved/rejected)
 *  - ktm_reject_reason: alasan penolakan KTM jika admin menolak
 *
 * Kenapa tidak buat tabel baru?
 *  -> Karena data KTM adalah bagian dari booking, bukan entitas terpisah.
 *     Jika dipisah akan membutuhkan JOIN yang tidak perlu.
 */
return new class extends Migration
{
    /**
     * Jalankan migration (tambah kolom).
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Kategori pengunjung: mahasiswa (35rb/hari) atau umum (55rb/hari)
            // Default 'umum' agar data lama yang tidak punya kategori tetap valid
            $table->string('visitor_category')->default('umum')->after('status');

            // Path file KTM di storage privat (contoh: ktm/NDY-20261013-ABCD.jpg)
            // Nullable karena pelanggan umum tidak wajib upload KTM
            $table->string('ktm_path')->nullable()->after('visitor_category');

            // Status verifikasi KTM: pending (belum dicek), approved (disetujui), rejected (ditolak)
            // Null berarti tidak ada KTM (pelanggan umum)
            $table->string('ktm_status')->nullable()->after('ktm_path');

            // Alasan admin menolak KTM — hanya diisi jika ktm_status = 'rejected'
            $table->text('ktm_reject_reason')->nullable()->after('ktm_status');
        });
    }

    /**
     * Rollback migration (hapus kolom yang ditambahkan).
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['visitor_category', 'ktm_path', 'ktm_status', 'ktm_reject_reason']);
        });
    }
};
