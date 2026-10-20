@extends('admin.layouts.app')

@section('page_title', 'Detail Booking')

@section('content')

    <div class="max-w-5xl">
        <a href="{{ route('admin.bookings.index') }}" class="mb-6 inline-flex items-center rounded-md text-sm text-n-gray transition-colors hover:text-n-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-n-primary">
            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>

        {{-- Flash message sukses/error --}}
        @if(session('success'))
        <div role="status" class="mb-6 rounded-lg border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div role="alert" class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
            {{ session('error') }}
        </div>
        @endif

        {{-- Header: kode booking, status, dan total --}}
        <div class="mb-6 flex flex-col gap-6 rounded-xl bg-[#b1002c] p-6 text-white sm:flex-row sm:items-end sm:justify-between sm:p-8">
            <div>
                <p class="mb-1 text-sm text-white/70">Kode Booking</p>
                <h2 class="text-3xl font-semibold tracking-wider">{{ $booking->booking_code }}</h2>

                <div class="mt-4 flex flex-wrap gap-2 text-sm">
                    {{-- Status booking --}}
                    @if($booking->status === 'confirmed')
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>Confirmed</span>
                    @elseif($booking->status === 'pending')
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"><span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>Pending</span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"><span class="h-1.5 w-1.5 rounded-full bg-gray-300"></span>Cancelled</span>
                    @endif

                    {{-- Kategori pengunjung (accessor getVisitorCategoryLabelAttribute) --}}
                    <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1">{{ $booking->visitor_category_label }}</span>

                    {{-- Status KTM: hanya untuk mahasiswa yang sudah mengunggah KTM --}}
                    @if($booking->visitor_category === 'mahasiswa' && $booking->ktm_path)
                        @if($booking->ktm_status === 'approved')
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>KTM disetujui</span>
                        @elseif($booking->ktm_status === 'rejected')
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"><span class="h-1.5 w-1.5 rounded-full bg-rose-200"></span>KTM ditolak</span>
                        @else
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"><span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>KTM menunggu verifikasi</span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="sm:text-right">
                <p class="text-sm text-white/70">Total</p>
                <p class="mt-1 text-3xl font-semibold tabular-nums">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- ═════════ KOLOM UTAMA ═════════ --}}
            <div class="space-y-6 lg:col-span-2">

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    {{-- Customer Info --}}
                    <div class="rounded-xl border border-n-border bg-white p-6">
                        <h3 class="mb-2 text-base font-semibold text-n-charcoal">Pelanggan</h3>
                        <dl class="divide-y divide-n-border text-sm">
                            <div class="py-3">
                                <dt class="text-xs text-n-gray">Nama</dt>
                                <dd class="mt-0.5 font-semibold text-n-charcoal">{{ $booking->customer_name }}</dd>
                            </div>
                            <div class="py-3">
                                <dt class="text-xs text-n-gray">No. Telepon</dt>
                                <dd class="mt-0.5 font-medium text-n-charcoal">{{ $booking->customer_phone }}</dd>
                            </div>
                            <div class="py-3 pb-0">
                                <dt class="text-xs text-n-gray">Kategori</dt>
                                <dd class="mt-0.5 font-medium text-n-charcoal">{{ $booking->visitor_category_label }}</dd>
                            </div>
                        </dl>
                    </div>


                {{-- Schedule --}}
                <div class="rounded-xl border border-n-border bg-white p-6">
                    <h3 class="mb-4 text-base font-semibold text-n-charcoal">Jadwal</h3>
                    <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <dt class="mb-1 text-xs text-n-gray">Tanggal</dt>
                            <dd class="text-sm font-semibold">{{ \Carbon\Carbon::parse($booking->booking_date)->isoFormat('D MMMM Y') }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-n-gray">Waktu mulai</dt>
                            <dd class="text-sm font-semibold tabular-nums">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-n-gray">Waktu selesai</dt>
                            <dd class="text-sm font-semibold tabular-nums">{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-n-gray">Durasi</dt>
                            <dd class="text-sm font-semibold">{{ $booking->duration }} hari</dd>
                        </div>
                    </dl>
                </div>

                {{--
                    SECTION KTM MAHASISWA
                    Tampil hanya jika kategori pengunjung 'mahasiswa'.
                    Jika belum ada file KTM, tampil pesan peringatan.
                --}}
                @if($booking->visitor_category === 'mahasiswa')
                <div class="rounded-xl border border-n-border bg-white p-6">
                    <h3 class="mb-4 text-base font-semibold text-n-charcoal">Verifikasi KTM mahasiswa</h3>

                    @if($booking->ktm_path)
                        <div class="space-y-4">

                            {{-- Status KTM saat ini --}}
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-n-gray">Status KTM</span>
                                @if($booking->ktm_status === 'approved')
                                    <span class="inline-flex items-center gap-2 font-medium text-green-700"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Disetujui</span>
                                @elseif($booking->ktm_status === 'rejected')
                                    <span class="inline-flex items-center gap-2 font-medium text-red-700"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Ditolak</span>
                                @else
                                    <span class="inline-flex items-center gap-2 font-medium text-orange-700"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Menunggu verifikasi</span>
                                @endif
                            </div>

                            {{-- Lihat KTM: AdminBookingController@viewKtm (storage privat), buka di tab baru --}}
                            <div>
                                <a href="{{ route('admin.bookings.ktm.view', $booking) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-2 rounded-lg border border-n-border bg-n-bg px-4 py-2 text-sm font-medium text-n-charcoal transition-colors hover:border-n-primary hover:text-n-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-n-primary">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat file KTM
                                </a>
                            </div>

                            {{-- Aksi verifikasi (hanya jika belum approved) --}}
                            @if($booking->ktm_status !== 'approved')
                            <div class="flex flex-wrap gap-3">

                                {{-- Setujui KTM: PATCH admin.bookings.ktm.approve --}}
                                <form action="{{ route('admin.bookings.ktm.approve', $booking) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            onclick="return confirm('Setujui KTM mahasiswa ini?')"
                                            class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Setujui KTM
                                    </button>
                                </form>

                                {{-- Tolak KTM: toggle form alasan penolakan --}}
                                <button type="button"
                                        onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-5 py-2.5 text-sm font-semibold text-red-600 transition-colors hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak KTM
                                </button>
                            </div>

                            {{-- Form alasan penolakan: tersembunyi sampai tombol "Tolak KTM" ditekan --}}
                            <div id="reject-form" class="hidden">
                                <form action="{{ route('admin.bookings.ktm.reject', $booking) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="space-y-3 rounded-lg border border-red-200 bg-red-50 p-4">
                                        <label for="reject_reason" class="text-sm font-semibold text-red-800">Alasan penolakan</label>
                                        {{-- Wajib diisi. Validasi backend: min 10, max 500 karakter. --}}
                                        <textarea id="reject_reason"
                                                  name="reject_reason"
                                                  rows="3"
                                                  required
                                                  minlength="10"
                                                  maxlength="500"
                                                  placeholder="Contoh: KTM tidak jelas/buram. Silakan upload ulang dengan foto yang lebih terang."
                                                  class="w-full resize-none rounded-lg border border-red-200 bg-white px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-200"></textarea>
                                        @error('reject_reason')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                        <div class="flex gap-2">
                                            <button type="submit"
                                                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-700">
                                                Kirim penolakan
                                            </button>
                                            <button type="button"
                                                    onclick="document.getElementById('reject-form').classList.add('hidden')"
                                                    class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm text-red-600 transition-colors hover:bg-red-50">
                                                Batal
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            @endif

                            {{-- Alasan penolakan jika KTM ditolak --}}
                            @if($booking->ktm_status === 'rejected' && $booking->ktm_reject_reason)
                            <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                                <p class="mb-1 text-xs font-semibold text-red-700">Alasan penolakan</p>
                                <p class="text-sm text-red-800">{{ $booking->ktm_reject_reason }}</p>
                                <p class="mt-2 text-xs text-red-600">Pelanggan dapat mengupload ulang KTM melalui halaman cek status.</p>
                            </div>
                            @endif

                        </div>

                    @else
                        {{-- Mahasiswa tapi belum upload KTM --}}
                        <div class="rounded-lg border-l-4 border-orange-400 bg-orange-50 p-4">
                            <p class="text-sm font-medium text-orange-800">Belum ada KTM</p>
                            <p class="mt-1 text-xs text-orange-700">Pelanggan ini mendaftar sebagai mahasiswa tetapi belum mengunggah KTM.</p>
                        </div>
                    @endif
                </div>
                @endif

                {{-- Items --}}
                @if($booking->items && $booking->items->count())
                <div class="overflow-hidden rounded-xl border border-n-border bg-white">
                    <div class="border-b border-n-border px-6 py-4">
                        <h3 class="text-base font-semibold text-n-charcoal">Item pesanan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[480px] text-sm">
                            <thead class="bg-n-bg text-n-gray">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left font-medium">Produk</th>
                                    <th scope="col" class="px-6 py-3 text-center font-medium">Qty</th>
                                    <th scope="col" class="px-6 py-3 text-right font-medium">Harga</th>
                                    <th scope="col" class="px-6 py-3 text-right font-medium">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-n-border">
                                @foreach($booking->items as $item)
                                <tr>
                                    <td class="px-6 py-3">{{ $item->product?->name ?? 'Produk dihapus' }}</td>
                                    <td class="px-6 py-3 text-center tabular-nums">{{ $item->quantity }}</td>
                                    <td class="px-6 py-3 text-right tabular-nums">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-medium tabular-nums">Rp{{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            {{-- ═════════ KOLOM SAMPING: pembayaran, kontak, tindakan ═════════ --}}
            <aside class="space-y-6 self-start lg:sticky lg:top-6">

                {{-- Summary & Payment --}}
                <div class="rounded-xl border border-n-border bg-white p-6">
                    <h3 class="mb-4 text-base font-semibold text-n-charcoal">Ringkasan pembayaran</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-n-gray">Tarif Day Pass ({{ $booking->visitor_category_label }})</span>
                            <span class="tabular-nums">Rp{{ number_format($booking->room_price ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @if($booking->product_total > 0)
                        <div class="flex justify-between gap-4">
                            <span class="text-n-gray">Produk tambahan</span>
                            <span class="tabular-nums">Rp{{ number_format($booking->product_total, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($booking->payment)
                        <div class="flex justify-between gap-4">
                            <span class="text-n-gray">Metode</span>
                            <span>{{ ucfirst($booking->payment->payment_method ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-n-gray">Status</span>
                            <span class="inline-flex items-center gap-2 {{ $booking->payment->status === 'paid' ? 'font-medium text-green-600' : 'text-orange-600' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $booking->payment->status === 'paid' ? 'bg-green-500' : 'bg-orange-500' }}"></span>
                                {{ ucfirst($booking->payment->status ?? '-') }}
                            </span>
                        </div>
                        @endif
                        <div class="mt-2 flex items-baseline justify-between border-t border-n-border pt-3">
                            <span class="font-semibold text-n-charcoal">Total</span>
                            <span class="text-lg font-semibold tabular-nums text-n-primary">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tombol WhatsApp: MEMBUKA WhatsApp, bukan berarti pesan sudah terkirim --}}
                @if($booking->customer_phone)
                <div class="rounded-xl border border-n-border bg-white p-6">
                    <h3 class="mb-3 text-base font-semibold text-n-charcoal">Hubungi pelanggan</h3>
                    @php
                        // Format nomor telepon untuk WhatsApp: ganti awalan 0 dengan 62
                        $waNumber = ltrim($booking->customer_phone, '0');
                        if (!str_starts_with($waNumber, '62')) $waNumber = '62' . $waNumber;

                        // Pesan konfirmasi otomatis dengan data booking
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
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-green-500 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Hubungi via WhatsApp
                    </a>
                    <p class="mt-3 text-xs text-n-gray">
                        Tombol ini hanya membuka WhatsApp. Pesan terkirim setelah Anda mengirimnya secara manual.
                    </p>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div class="rounded-xl border border-n-border bg-white p-6">
                    <h3 class="mb-4 text-base font-semibold text-n-charcoal">Tindakan</h3>
                    <div class="space-y-3">
                        @if($booking->status !== 'confirmed')
                        <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit"
                                    class="w-full rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                Konfirmasi booking
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
                                    class="w-full rounded-lg border border-red-200 bg-white px-6 py-2.5 text-sm font-semibold text-red-600 transition-colors hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                                Batalkan booking
                            </button>
                        </form>
                        @endif

                        <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="border-t border-n-border pt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Yakin ingin menghapus booking ini? Tindakan ini tidak dapat dibatalkan.')"
                                    class="w-full rounded-lg px-6 py-2 text-sm font-medium text-n-gray transition-colors hover:bg-red-50 hover:text-red-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-red-600">
                                Hapus booking
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
        </div>
    </div>

@endsection