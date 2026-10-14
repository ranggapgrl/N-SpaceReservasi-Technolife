<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel blocked_dates untuk menyimpan tanggal yang ditutup admin.
     * Admin bisa menutup tanggal tertentu agar pelanggan tidak bisa booking.
     */
    public function up(): void
    {
        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            // Tanggal yang diblokir (format: YYYY-MM-DD), harus unik agar tidak duplikat
            $table->date('date')->unique();
            // Alasan penutupan, opsional (contoh: "Libur Nasional", "Maintenance")
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_dates');
    }
};
