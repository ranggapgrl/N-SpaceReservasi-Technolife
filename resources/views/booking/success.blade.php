@extends('layouts.app')

@section('title', 'Invoice Booking | Needs Study Space')

@section('content')

@php
    // Redirect ke halaman ini tidak membuktikan pembayaran; status lunas hanya berasal dari notifikasi server Midtrans.
    $paymentStatus = $booking->payment?->status;
    $isPaymentPaid = $paymentStatus === 'paid' && $booking->payment?->verified_at !== null;
    $isSimulatedPayment = $paymentStatus === 'simulated';
    $bookingIdCode = $booking->booking_code ?? ('#NDY-' . date('Ymd', strtotime($booking->created_at)) . sprintf('%02d', $booking->id));
    $customerName = $booking->customer_name ?? '-';
    $customerPhone = $booking->customer_phone ?? '-';
    $passType = 'Study Space Area (All-Access)';
    $orderedProducts = $booking->products ?? $booking->items ?? collect();
@endphp

<style>
    @media print {
        @page { margin: 12mm; }
        body * { visibility: hidden; }
        #receiptCard, #receiptCard * { visibility: visible; }
        #receiptCard {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: none;
            margin: 0;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            animation: none !important;
        }
        .invoice-shell { display: block !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; }
        .no-print { display: none !important; }
    }
</style>

<div class="invoice-shell mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <header class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-n-primary">Reservasi · Invoice</p>
            <h1 class="text-3xl font-semibold leading-tight text-n-charcoal sm:text-5xl">Rincian booking</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 sm:text-base">
                Simpan rincian ini sebagai bukti pemesanan Anda. Status pembayaran ditampilkan sesuai konfirmasi server.
            </p>
        </div>
        <p class="shrink-0 text-sm text-gray-500">{{ date('d M Y', strtotime($booking->created_at)) }}</p>
    </header>

    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1.65fr)_minmax(18rem,0.9fr)]">
        <article id="receiptCard" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="h-1.5 w-full bg-n-primary"></div>
            <div class="p-5 sm:p-8">
                <div class="flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="font-sans text-lg font-semibold tracking-tight text-n-charcoal">NEEDS STUDY SPACE</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-[0.12em] text-gray-500">Bukti pemesanan</p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">ID Booking</p>
                        <p class="mt-1 break-all font-mono text-sm font-bold text-n-charcoal">{{ $bookingIdCode }}</p>
                    </div>
                </div>

                <section class="mt-6" aria-labelledby="payment-status-heading">
                    <div @class([
                        'flex items-start gap-3 rounded-xl border p-4',
                        'border-emerald-200 bg-emerald-50' => $isPaymentPaid,
                        'border-amber-200 bg-amber-50' => ! $isPaymentPaid,
                    ])>
                        <span @class([
                            'mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
                            'bg-emerald-100 text-emerald-700' => $isPaymentPaid,
                            'bg-amber-100 text-amber-800' => ! $isPaymentPaid,
                        ]) aria-hidden="true">
                            @if($isPaymentPaid)
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 13 4 4L19 7"/>
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z"/>
                                </svg>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <h2 id="payment-status-heading" class="font-semibold text-n-charcoal">
                                @if($isPaymentPaid)
                                    Struk Pemesanan &amp; Pembayaran Lunas
                                @elseif($isSimulatedPayment)
                                    Simulasi Pembayaran — Bukan Transaksi Nyata
                                @else
                                    Booking Tercatat — Menunggu Konfirmasi Pembayaran
                                @endif
                            </h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                @if($isPaymentPaid)
                                    Pembayaran lunas telah dikonfirmasi oleh Midtrans.
                                @elseif($isSimulatedPayment)
                                    Data ini hanya untuk pengujian dan tidak dihitung sebagai pendapatan.
                                @else
                                    Pembayaran belum memiliki konfirmasi server yang valid.
                                @endif
                            </p>
                        </div>
                    </div>
                </section>

                <section class="mt-8" aria-labelledby="booking-details-heading">
                    <h2 id="booking-details-heading" class="font-headline text-lg font-semibold text-n-charcoal">Informasi booking</h2>
                    <dl class="mt-4 grid grid-cols-1 gap-x-8 sm:grid-cols-2">
                        <div class="border-t border-gray-100 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Nama pemesan</dt>
                            <dd class="mt-1 break-words text-sm font-medium text-n-charcoal">{{ $customerName }}</dd>
                        </div>
                        <div class="border-t border-gray-100 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">No. WhatsApp</dt>
                            <dd class="mt-1 break-words text-sm font-medium text-n-charcoal">{{ $customerPhone }}</dd>
                        </div>
                        <div class="border-t border-gray-100 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal</dt>
                            <dd class="mt-1 text-sm font-medium text-n-charcoal">{{ date('d M Y', strtotime($booking->booking_date)) }}</dd>
                        </div>
                        <div class="border-t border-gray-100 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Waktu</dt>
                            <dd class="mt-1 text-sm font-medium text-n-charcoal">
                                {{ date('H:i', strtotime($booking->start_time)) }}–{{ date('H:i', strtotime($booking->end_time)) }}
                            </dd>
                        </div>
                        <div class="border-t border-gray-100 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Durasi</dt>
                            <dd class="mt-1 text-sm font-medium text-n-charcoal">{{ $booking->duration }} hari full-day</dd>
                        </div>
                        <div class="border-t border-gray-100 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Jumlah pengunjung</dt>
                            <dd class="mt-1 text-sm font-medium text-n-charcoal">{{ $booking->visitor_count }} orang</dd>
                        </div>
                        <div class="border-t border-gray-100 py-3 sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Jenis akses</dt>
                            <dd class="mt-1 text-sm font-medium text-n-charcoal">{{ $passType }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="mt-8" aria-labelledby="cost-breakdown-heading">
                    <h2 id="cost-breakdown-heading" class="font-headline text-lg font-semibold text-n-charcoal">Rincian biaya</h2>
                    <div class="mt-4 divide-y divide-gray-100 border-y border-gray-100">
                        <div class="flex items-start justify-between gap-4 py-3 text-sm">
                            <span class="text-gray-600">Study Space <span class="text-gray-400">· {{ $booking->duration }} hari</span></span>
                            <span class="shrink-0 font-medium text-n-charcoal">Rp{{ number_format($booking->day_pass_price, 0, ',', '.') }}</span>
                        </div>
                        @foreach($orderedProducts as $item)
                            @php
                                $prodName = $item->name ?? $item->product->name ?? 'Produk';
                                $prodQty = $item->pivot->quantity ?? $item->quantity ?? 1;
                                $prodPrice = $item->pivot->price ?? $item->price ?? 0;
                            @endphp
                            <div class="flex items-start justify-between gap-4 py-3 text-sm">
                                <span class="min-w-0 text-gray-600">{{ $prodName }} <span class="text-gray-400">×{{ $prodQty }}</span></span>
                                <span class="shrink-0 font-medium text-n-charcoal">Rp{{ number_format($prodPrice * $prodQty, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-end justify-between gap-4 rounded-xl bg-gray-50 p-4 sm:p-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ $isPaymentPaid ? 'Total Dibayar' : 'Total Tagihan' }}
                            </p>
                            @if($isPaymentPaid)
                                <p class="mt-1 text-xs font-medium text-emerald-700">Lunas · dikonfirmasi Midtrans</p>
                            @elseif($isSimulatedPayment)
                                <p class="mt-1 text-xs font-medium text-amber-800">Transaksi simulasi</p>
                            @else
                                <p class="mt-1 text-xs font-medium text-amber-800">Menunggu konfirmasi pembayaran</p>
                            @endif
                        </div>
                        <p class="shrink-0 text-xl font-bold tracking-tight text-n-primary sm:text-2xl">
                            Rp{{ number_format($booking->total_price, 0, ',', '.') }}
                        </p>
                    </div>
                </section>

                <p class="mt-6 text-center text-xs leading-5 text-gray-500">
                    Terima kasih telah menggunakan Needs Study Space.
                </p>
            </div>
        </article>

        <aside class="no-print space-y-4">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-n-primary">Langkah berikutnya</p>
                <h2 class="mt-2 font-headline text-xl font-semibold text-n-charcoal">Simpan invoice ini</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">
                    @if($isPaymentPaid)
                        Pembayaran Anda sudah terverifikasi. Simpan atau cetak invoice ini untuk catatan saat kunjungan.
                    @elseif($isSimulatedPayment)
                        Ini adalah pembayaran simulasi, bukan transaksi nyata. Gunakan invoice ini hanya untuk keperluan pengujian.
                    @else
                        Invoice ini mencatat booking Anda, tetapi belum menjadi bukti pembayaran lunas. Perbarui status setelah pembayaran diproses.
                    @endif
                </p>
                @unless($isPaymentPaid || $isSimulatedPayment)
                    <a href="{{ route('booking.success', $booking->booking_code) }}"
                       class="mt-3 inline-flex min-h-10 items-center text-sm font-semibold text-n-primary underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--n-primary)]">
                        Perbarui status pembayaran
                    </a>
                @endunless
            </section>

            @php
                $adminPhone = '6282118045141';
                $snackText = '';
                if($orderedProducts->count() > 0) {
                    $snackText = "%0A*SNACK & MINUMAN*%0A";
                    foreach($orderedProducts as $item) {
                        $pName = $item->name ?? $item->product->name ?? 'Produk';
                        $pQty = $item->pivot->quantity ?? $item->quantity ?? 1;
                        $pSubtotal = ($item->pivot->price ?? $item->price ?? 0) * $pQty;
                        $snackText .= "- {$pName} (x{$pQty}) : Rp" . number_format($pSubtotal, 0, ',', '.') . "%0A";
                    }
                }

                $paymentMessage = $isPaymentPaid
                    ? "Pembayaran saya telah dikonfirmasi oleh Midtrans."
                    : "Mohon bantu cek status booking dan pembayaran saya.";
                $paymentLabel = $isPaymentPaid
                    ? "LUNAS (dikonfirmasi Midtrans)"
                    : ($isSimulatedPayment ? "SIMULASI (bukan pembayaran nyata)" : "MENUNGGU KONFIRMASI");

                $message = "Halo Admin Needs Study Space,%0A%0A" .
                           "{$paymentMessage} Berikut detail pesanan saya:%0A%0A" .
                           "*DETAIL PEMESANAN*%0A" .
                           "- ID Booking: *{$bookingIdCode}*%0A" .
                           "- Nama: {$customerName}%0A" .
                           "- No. WA: {$customerPhone}%0A%0A" .
                           "*INFORMASI AKSES*%0A" .
                           "- Jenis: {$passType}%0A" .
                           "- Tanggal: " . date('d-m-Y', strtotime($booking->booking_date)) . "%0A" .
                           "- Durasi: {$booking->duration} Hari Full-day%0A" .
                           "{$snackText}%0A" .
                           "*PEMBAYARAN*%0A" .
                           "- Total: Rp" . number_format($booking->total_price, 0, ',', '.') . "%0A" .
                           "- Status: *{$paymentLabel}*%0A%0A" .
                           "Mohon verifikasinya. Terima kasih!";

                $whatsappUrl = "https://wa.me/{$adminPhone}?text=" . $message;
            @endphp

            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
               class="flex min-h-12 w-full items-center justify-center gap-3 rounded-xl bg-green-700 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-green-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-800 sm:text-base">
                <svg class="h-5 w-5 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Kirim notifikasi ke WhatsApp admin</span>
            </a>

            <button type="button" onclick="window.print()"
                    class="flex min-h-12 w-full items-center justify-center gap-3 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-n-charcoal hover:border-gray-400 hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--n-primary)]">
                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4-4 4m0 0-4-4m4 4V4"/>
                </svg>
                <span>Cetak atau simpan sebagai PDF</span>
            </button>

            <p class="px-2 pt-1 text-center text-sm text-gray-500">
                <a href="{{ url('/') }}" class="font-medium text-n-primary underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--n-primary)]">
                    Kembali ke beranda
                </a>
            </p>
        </aside>
    </div>
</div>
@endsection
