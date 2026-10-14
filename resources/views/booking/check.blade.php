@extends('layouts.app')

@section('title', 'Riwayat Cek Booking | Needs Study Space')

@section('content')

{{-- Tambahan Style Animasi untuk Tombol Cari Tiket --}}
<style>
    .btn-cari-tiket {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s ease;
    }
    .btn-cari-tiket:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.15);
    }
    .btn-cari-tiket:active {
        transform: translateY(0) scale(0.98);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
</style>

{{-- Navbar dan footer berasal dari layouts/app.blade.php --}}

{{-- ========== MAIN CONTENT ========== --}}
<section class="bg-n-bg pt-10 md:pt-12 pb-20 md:pb-24 px-5 md:px-10 flex-grow">
    <div class="max-w-4xl mx-auto">
        
        {{-- Header Teks --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-n-charcoal tracking-tight mb-3">
                Riwayat <span class="text-n-primary">Pesanan Anda.</span>
            </h1>
            <p class="text-sm md:text-base text-n-gray font-light max-w-md mx-auto leading-relaxed">
                Daftar tiket akses yang pernah Anda pesan dari perangkat ini.
            </p>
        </div>

        {{-- Form Pencarian Manual (Responsif, Anti Gencet, & Beranimasi) --}}
        <form action="" method="GET" class="max-w-2xl mx-auto mb-12 flex flex-col sm:flex-row gap-3">
            
            <input type="text" name="keyword" value="{{ request('keyword') }}" 
                placeholder="Cari Kode Booking atau No. WhatsApp..." 
                class="w-full px-5 py-3.5 rounded-xl border border-n-border/60 focus:border-n-primary focus:ring-4 focus:ring-n-primary/10 outline-none text-sm transition-all shadow-sm"
                style="flex: 1 1 auto;">
            
            <button type="submit" class="btn-cari-tiket bg-n-primary text-white px-8 py-3.5 rounded-xl text-sm font-semibold shadow-sm whitespace-nowrap cursor-pointer"
                    style="flex-shrink: 0; min-width: 150px;">
                Cari Tiket
            </button>
            
        </form>

        {{-- Daftar Riwayat Booking --}}
        @if(isset($bookings) && $bookings->count() > 0)
            <div class="space-y-6">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-2xl p-6 md:p-8 border border-n-border/60 hover:border-n-border hover:shadow-lg transition-all duration-300 flex flex-col gap-6">
                        
                        {{-- Baris Atas: Kode Booking & Status --}}
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-n-border/40 pb-5">
                            <div>
                                <span class="text-xs text-n-gray uppercase tracking-wider block mb-1 font-medium">Kode Booking</span>
                                <span class="text-xl font-bold text-n-charcoal tracking-tight font-mono">{{ $booking->booking_code }}</span>
                            </div>
                            <span class="px-4 py-1.5 text-xs font-bold rounded-full uppercase tracking-wider
                                {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $booking->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ $booking->status }}
                            </span>
                        </div>

                        {{-- Baris Tengah: Detail Akses & Jadwal --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-n-gray block mb-1 font-light">Jenis Akses</span>
                                <span class="font-semibold text-n-charcoal text-base">Study Space Area (All-Access)</span>
                            </div>
                            <div>
                                <span class="text-xs text-n-gray block mb-1 font-light">Jadwal Kedatangan</span>
                                <span class="font-medium text-n-charcoal">
                                    {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }} <span class="text-gray-300">|</span> 
                                    {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                </span>
                            </div>
                        </div>

                        {{-- Baris Bawah: Total Harga & Tombol Aksi --}}
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-5 border-t border-n-border/40">
                            <div>
                                <span class="text-xs text-n-gray block mb-0.5 font-light">Total Pembayaran</span>
                                <span class="text-xl font-bold text-n-primary">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <a href="{{ route('booking.success', $booking->id) }}" class="flex-1 sm:flex-none text-center bg-n-charcoal hover:bg-black text-white px-6 py-3 rounded-xl text-sm font-medium transition-colors shadow-sm">
                                    Lihat Tiket
                                </a>
                                @if($booking->status === 'pending')
                                    <a href="{{ route('checkout.simulate', $booking->booking_code) }}" class="flex-1 sm:flex-none text-center bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-xl text-sm font-medium transition-colors shadow-sm">
                                        Bayar Sekarang
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{--
                            ═══ STATUS KTM & UPLOAD ULANG (BARU) ═══════════════

                            Bagian ini HANYA ditampilkan untuk pelanggan mahasiswa.
                            
                            Kondisi yang ditangani:
                            1. ktm_status = 'pending'  → Menunggu verifikasi admin
                            2. ktm_status = 'approved' → KTM disetujui, tidak perlu aksi
                            3. ktm_status = 'rejected' → Form upload ulang ditampilkan
                            4. ktm_path = null         → Belum upload (tidak seharusnya terjadi)
                        --}}
                        @if($booking->visitor_category === 'mahasiswa')
                        <div class="border-t border-n-border/40 pt-5">
                            @if($booking->ktm_status === 'approved')
                                {{-- KTM Disetujui --}}
                                <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium">KTM Disetujui</span> — Anda mendapatkan tarif mahasiswa.
                                </div>

                            @elseif($booking->ktm_status === 'pending')
                                {{-- KTM Menunggu Verifikasi --}}
                                <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium">KTM sedang diverifikasi</span> — Harap tunggu konfirmasi dari admin.
                                </div>

                            @elseif($booking->ktm_status === 'rejected')
                                {{--
                                    KTM Ditolak — Tampilkan form upload ulang.
                                    
                                    Form ini menggunakan:
                                    - enctype="multipart/form-data" untuk upload file
                                    - POST ke route 'booking.reupload_ktm'
                                    - Verifikasi identitas: booking_code + customer_phone
                                    
                                    Toggle form menggunakan JavaScript sederhana (getElementById).
                                --}}
                                <div class="space-y-3">
                                    <div class="flex items-start gap-2 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" class="flex-none mt-0.5">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <span class="font-semibold">KTM Ditolak</span>
                                            @if($booking->ktm_reject_reason)
                                            <p class="mt-1 text-red-600">Alasan: {{ $booking->ktm_reject_reason }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tombol toggle form upload ulang --}}
                                    <button type="button"
                                            onclick="document.getElementById('reupload-{{ $booking->id }}').classList.toggle('hidden')"
                                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-n-primary text-white rounded-xl text-sm font-semibold hover:bg-n-primary/90 transition-colors">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Upload Ulang KTM
                                    </button>

                                    {{--
                                        Form Upload Ulang KTM.
                                        Tersembunyi by default (class 'hidden').
                                        
                                        Verifikasi identitas menggunakan booking_code + customer_phone
                                        karena pelanggan tidak perlu login.
                                        Ini cukup aman karena kedua data bersifat unik per booking.
                                    --}}
                                    <div id="reupload-{{ $booking->id }}" class="hidden">
                                        <form action="{{ route('booking.reupload_ktm') }}" method="POST"
                                            enctype="multipart/form-data"
                                            class="bg-gray-50 border border-n-border rounded-xl p-4 space-y-3">
                                            @csrf
                                            {{-- Data identitas tersembunyi (tidak perlu diisi manual) --}}
                                            <input type="hidden" name="booking_code" value="{{ $booking->booking_code }}">
                                            <input type="hidden" name="customer_phone" value="{{ $booking->customer_phone }}">

                                            <p class="text-sm font-semibold text-n-charcoal">Upload KTM Baru</p>

                                            {{-- Input file KTM baru --}}
                                            <div>
                                                <label class="block text-xs text-n-gray mb-1">File KTM (JPG, PNG, atau PDF, maks. 2MB)</label>
                                                <input type="file"
                                                    name="ktm_file"
                                                    required
                                                    accept=".jpg,.jpeg,.png,.pdf"
                                                    class="block w-full text-sm text-n-gray border border-n-border rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-n-primary">
                                            </div>

                                            @if(session('success') && str_contains(session('success'), 'KTM'))
                                            <p class="text-xs text-green-600 font-medium">✓ {{ session('success') }}</p>
                                            @endif
                                            @if(session('error') && str_contains(session('error'), 'KTM'))
                                            <p class="text-xs text-red-600 font-medium">✕ {{ session('error') }}</p>
                                            @endif

                                            <div class="flex gap-2">
                                                <button type="submit"
                                                        class="px-4 py-2 bg-n-primary text-white rounded-lg text-sm font-semibold hover:bg-n-primary/90 transition-colors">
                                                    Kirim KTM Baru
                                                </button>
                                                <button type="button"
                                                        onclick="document.getElementById('reupload-{{ $booking->id }}').classList.add('hidden')"
                                                        class="px-4 py-2 bg-white border border-n-border text-n-gray rounded-lg text-sm hover:bg-n-bg transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            @elseif(!$booking->ktm_path)
                                {{-- Belum upload KTM --}}
                                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 border border-n-border rounded-xl px-4 py-3">
                                    <span>🪪 Belum ada KTM yang diunggah untuk booking ini.</span>
                                </div>
                            @endif
                        </div>
                        @endif

                    </div>
                @endforeach
            </div>

        @else
            {{-- Jika Belum Ada Riwayat --}}
            <div class="bg-white border border-n-border/60 rounded-2xl p-12 text-center shadow-sm max-w-2xl mx-auto">
                <div class="w-16 h-16 bg-n-bg text-n-gray rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-n-charcoal mb-1">Belum Ada Riwayat Ditemukan</h3>
                <p class="text-sm text-n-gray font-light mb-6">Kami tidak menemukan riwayat di perangkat ini atau dari pencarian Anda.</p>
                <a href="{{ route('booking.index') }}" class="inline-block bg-n-charcoal text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-black transition-colors shadow-sm">
                    Pesan Akses Sekarang
                </a>
            </div>
        @endif

    </div>
</section>

@endsection