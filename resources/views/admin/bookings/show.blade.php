@extends('admin.layouts.app')

@section('page_title', 'Detail Booking')

@section('content')

    <div class="max-w-3xl">
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center text-n-gray text-sm hover:text-n-primary mb-6 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>

        {{-- Flash message sukses/error --}}
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-6 text-sm font-medium">
            ✓ {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-6 text-sm font-medium">
            ✕ {{ session('error') }}
        </div>
        @endif

        {{-- Booking Code Header --}}
        <div class="bg-n-primary text-white rounded-2xl p-6 mb-6">
            <p class="text-white/70 text-sm mb-1">Kode Booking</p>
            <h2 class="text-3xl font-bold tracking-wider">{{ $booking->booking_code }}</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                {{-- Badge status booking --}}
                @if($booking->status === 'confirmed')
                    <span class="bg-green-400/30 text-green-100 border border-green-400/40 px-4 py-1 rounded-full text-sm font-semibold">✓ Confirmed</span>
                @elseif($booking->status === 'pending')
                    <span class="bg-orange-400/30 text-orange-100 border border-orange-400/40 px-4 py-1 rounded-full text-sm font-semibold">⏳ Pending</span>
                @else
                    <span class="bg-gray-400/30 text-gray-100 border border-gray-400/40 px-4 py-1 rounded-full text-sm font-semibold">✗ Cancelled</span>
                @endif

                {{--
                    Badge kategori pengunjung — menggunakan accessor dari model Booking
                    $booking->visitor_category_label memanggil getVisitorCategoryLabelAttribute()
                --}}
                <span class="bg-white/20 text-white px-4 py-1 rounded-full text-sm font-semibold">
                    {{ $booking->visitor_category_label }}
                </span>

                {{--
                    Badge status KTM — hanya tampil untuk mahasiswa yang punya KTM
                    Warna badge berbeda tergantung status verifikasi
                --}}
                @if($booking->visitor_category === 'mahasiswa' && $booking->ktm_path)
                    @if($booking->ktm_status === 'approved')
                        <span class="bg-green-400/30 text-green-100 border border-green-400/40 px-4 py-1 rounded-full text-sm font-semibold">🪪 KTM Disetujui</span>
                    @elseif($booking->ktm_status === 'rejected')
                        <span class="bg-red-400/30 text-red-100 border border-red-400/40 px-4 py-1 rounded-full text-sm font-semibold">🪪 KTM Ditolak</span>
                    @else
                        <span class="bg-yellow-400/30 text-yellow-100 border border-yellow-400/40 px-4 py-1 rounded-full text-sm font-semibold">🪪 KTM Menunggu Verifikasi</span>
                    @endif
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            {{-- Customer Info --}}
            <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6">
                <h3 class="font-semibold text-sm text-n-gray uppercase tracking-wider mb-4">Informasi Pelanggan</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-n-gray">Nama</dt>
                        <dd class="font-semibold text-n-charcoal">{{ $booking->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-n-gray">No. Telepon</dt>
                        <dd class="font-medium text-n-charcoal">{{ $booking->customer_phone }}</dd>
                    </div>
                    <div>
                        {{-- Kolom visitor_category baru --}}
                        <dt class="text-xs text-n-gray">Kategori</dt>
                        <dd class="font-medium text-n-charcoal">{{ $booking->visitor_category_label }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Room Info --}}
            <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6">
                <h3 class="font-semibold text-sm text-n-gray uppercase tracking-wider mb-4">Informasi Ruangan</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-n-gray">Nama Ruangan</dt>
                        <dd class="font-semibold text-n-charcoal">{{ $booking->room?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-n-gray">Tipe</dt>
                        <dd class="font-medium text-n-charcoal">{{ $booking->room?->type ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-n-gray">Harga Ruangan</dt>
                        <dd class="font-medium text-n-charcoal">Rp{{ number_format($booking->room_price ?? 0, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-sm text-n-gray uppercase tracking-wider mb-4">Jadwal</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-n-gray mb-1">Tanggal</p>
                    <p class="font-semibold">{{ \Carbon\Carbon::parse($booking->booking_date)->isoFormat('D MMMM Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-n-gray mb-1">Waktu Mulai</p>
                    <p class="font-semibold">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-n-gray mb-1">Waktu Selesai</p>
                    <p class="font-semibold">{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs text-n-gray mb-1">Durasi</p>
                    <p class="font-semibold">{{ $booking->duration }} hari</p>
                </div>
            </div>
        </div>

        {{--
            ═══════════════════════════════════════════════
            SECTION KTM MAHASISWA (BARU)
            ═══════════════════════════════════════════════

            Section ini hanya ditampilkan jika:
            1. Kategori pengunjung adalah 'mahasiswa'
            2. Ada file KTM yang diunggah (ktm_path tidak null)

            Jika mahasiswa belum upload KTM, ditampilkan pesan peringatan.
        --}}
        @if($booking->visitor_category === 'mahasiswa')
        <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-sm text-n-gray uppercase tracking-wider mb-4">Verifikasi KTM Mahasiswa</h3>

            @if($booking->ktm_path)
                {{-- Ada KTM yang diunggah — tampilkan info dan tombol aksi --}}
                <div class="space-y-4">

                    {{-- Status KTM saat ini --}}
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-n-gray">Status KTM:</span>
                        @if($booking->ktm_status === 'approved')
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">✓ Disetujui</span>
                        @elseif($booking->ktm_status === 'rejected')
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">✕ Ditolak</span>
                            @if($booking->ktm_reject_reason)
                            <span class="text-xs text-n-gray">— {{ $booking->ktm_reject_reason }}</span>
                            @endif
                        @else
                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">⏳ Menunggu Verifikasi</span>
                        @endif
                    </div>

                    {{--
                        Tombol "Lihat KTM":
                        Mengarah ke route 'admin.bookings.ktm.view' yang memanggil
                        AdminBookingController@viewKtm untuk membaca file dari storage privat.
                        File dibuka di tab baru (target="_blank").
                    --}}
                    <div>
                        <a href="{{ route('admin.bookings.ktm.view', $booking) }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-n-bg border border-n-border text-n-charcoal rounded-xl text-sm font-medium hover:border-n-primary hover:text-n-primary transition-colors">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat File KTM
                        </a>
                    </div>

                    {{-- Tombol Aksi Verifikasi (hanya jika belum approved) --}}
                    @if($booking->ktm_status !== 'approved')
                    <div class="flex flex-wrap gap-3">

                        {{--
                            FORM SETUJUI KTM:
                            PATCH ke route 'admin.bookings.ktm.approve'
                            Memanggil AdminBookingController@approveKtm
                            Tidak perlu form input tambahan, hanya booking ID yang dikirim via route
                        --}}
                        <form action="{{ route('admin.bookings.ktm.approve', $booking) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    onclick="return confirm('Setujui KTM mahasiswa ini?')"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Setujui KTM
                            </button>
                        </form>

                        {{--
                            FORM TOLAK KTM (dengan modal alasan):
                            Menampilkan/menyembunyikan form alasan penolakan secara toggle.
                            Alasan penolakan wajib diisi sebelum submit.
                        --}}
                        <button type="button"
                                onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak KTM
                        </button>
                    </div>

                    {{--
                        Form alasan penolakan — tersembunyi by default (class 'hidden')
                        Muncul saat tombol "Tolak KTM" ditekan
                        PATCH ke route 'admin.bookings.ktm.reject'
                    --}}
                    <div id="reject-form" class="hidden mt-2">
                        <form action="{{ route('admin.bookings.ktm.reject', $booking) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="bg-red-50 border border-red-200 rounded-xl p-4 space-y-3">
                                <p class="text-sm font-semibold text-red-800">Alasan Penolakan KTM</p>
                                {{--
                                    Field 'reject_reason' wajib diisi.
                                    Validasi di backend: min 10 karakter, max 500 karakter.
                                --}}
                                <textarea name="reject_reason"
                                          rows="3"
                                          required
                                          minlength="10"
                                          maxlength="500"
                                          placeholder="Contoh: KTM tidak jelas/buram. Silakan upload ulang dengan foto yang lebih terang."
                                          class="w-full px-3 py-2 border border-red-200 rounded-lg text-sm focus:outline-none focus:border-red-400 bg-white resize-none"></textarea>
                                @error('reject_reason')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <div class="flex gap-2">
                                    <button type="submit"
                                            class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors">
                                        Kirim Penolakan
                                    </button>
                                    <button type="button"
                                            onclick="document.getElementById('reject-form').classList.add('hidden')"
                                            class="px-4 py-2 bg-white border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 transition-colors">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif

                    {{-- Tampilkan alasan penolakan jika KTM ditolak --}}
                    @if($booking->ktm_status === 'rejected' && $booking->ktm_reject_reason)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-red-700 mb-1">Alasan Penolakan:</p>
                        <p class="text-sm text-red-800">{{ $booking->ktm_reject_reason }}</p>
                        <p class="text-xs text-red-600 mt-2">Pelanggan dapat mengupload ulang KTM melalui halaman cek status.</p>
                    </div>
                    @endif

                </div>

            @else
                {{-- Mahasiswa tapi belum upload KTM --}}
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                    <p class="text-sm text-orange-800 font-medium">⚠ Belum Ada KTM</p>
                    <p class="text-xs text-orange-700 mt-1">Pelanggan ini mendaftar sebagai mahasiswa tetapi belum mengunggah KTM.</p>
                </div>
            @endif
        </div>
        @endif

        {{-- Items --}}
        @if($booking->items && $booking->items->count())
        <div class="bg-white rounded-2xl border border-n-border shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-n-border">
                <h3 class="font-semibold">Item Pesanan</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-n-bg text-n-gray">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">Produk</th>
                        <th class="px-6 py-3 text-center font-medium">Qty</th>
                        <th class="px-6 py-3 text-right font-medium">Harga</th>
                        <th class="px-6 py-3 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-n-border">
                    @foreach($booking->items as $item)
                    <tr>
                        <td class="px-6 py-3">{{ $item->product?->name ?? 'Produk dihapus' }}</td>
                        <td class="px-6 py-3 text-center">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right font-medium">Rp{{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Summary & Payment --}}
        <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-sm text-n-gray uppercase tracking-wider mb-4">Ringkasan Pembayaran</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-n-gray">Harga Ruangan ({{ $booking->visitor_category_label }})</span>
                    <span>Rp{{ number_format($booking->room_price ?? 0, 0, ',', '.') }}</span>
                </div>
                @if($booking->product_total > 0)
                <div class="flex justify-between">
                    <span class="text-n-gray">Produk Tambahan</span>
                    <span>Rp{{ number_format($booking->product_total, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($booking->payment)
                <div class="flex justify-between">
                    <span class="text-n-gray">Metode Pembayaran</span>
                    <span>{{ ucfirst($booking->payment->payment_method ?? '-') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-n-gray">Status Pembayaran</span>
                    <span class="{{ $booking->payment->status === 'paid' ? 'text-green-600 font-medium' : 'text-orange-600' }}">
                        {{ ucfirst($booking->payment->status ?? '-') }}
                    </span>
                </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-n-border mt-2">
                    <span class="font-semibold text-n-charcoal">Total</span>
                    <span class="font-bold text-n-primary text-lg">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Tombol WhatsApp -- Tombol ini MEMBUKA WhatsApp, bukan berarti pesan sudah terkirim --}}
        @if($booking->customer_phone)
        <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6 mb-6">
            <h3 class="font-semibold text-sm text-n-gray uppercase tracking-wider mb-3">Hubungi Pelanggan</h3>
            @php
                // Format nomor telepon untuk WhatsApp: ganti awalan 0 dengan +62
                $waNumber = ltrim($booking->customer_phone, '0');
                if (!str_starts_with($waNumber, '62')) $waNumber = '62' . $waNumber;
                
                // Buat pesan konfirmasi otomatis dengan data booking
                $waMessage = urlencode(
                    "Halo {$booking->customer_name}, konfirmasi reservasi N-Space Anda:\n\n" .
                    "📋 Kode: {$booking->booking_code}\n" .
                    "📅 Tanggal: " . \Carbon\Carbon::parse($booking->booking_date)->isoFormat('D MMMM Y') . "\n" .
                    "⏱ Durasi: {$booking->duration} hari\n" .
                    "💳 Total: Rp" . number_format($booking->total_price, 0, ',', '.') . "\n" .
                    "✅ Status: " . ucfirst($booking->status)
                );
            @endphp
            <a href="https://wa.me/{{ $waNumber }}?text={{ $waMessage }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-500 text-white rounded-xl text-sm font-semibold hover:bg-green-600 transition-colors">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Hubungi via WhatsApp
            </a>
            <p class="text-xs text-n-gray mt-2">
                ⚠ Tombol ini membuka WhatsApp. Pesan dianggap terkirim hanya setelah Anda mengirimnya secara manual.
            </p>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex items-center space-x-3">
            @if($booking->status !== 'confirmed')
            <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="confirmed">
                <button type="submit"
                        class="bg-green-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                    ✓ Konfirmasi Booking
                </button>
            </form>
            @endif

            @if($booking->status !== 'cancelled')
            <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit"
                        onclick="return confirm('Yakin ingin membatalkan booking ini?')"
                        class="bg-red-50 border border-red-200 text-red-600 px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors">
                    Batalkan Booking
                </button>
            </form>
            @endif

            <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Yakin ingin menghapus booking ini? Tindakan ini tidak dapat dibatalkan.')"
                        class="text-n-gray border border-n-border px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-n-bg transition-colors">
                    Hapus
                </button>
            </form>
        </div>
    </div>

@endsection
