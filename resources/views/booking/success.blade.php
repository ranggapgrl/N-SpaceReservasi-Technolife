@extends('layouts.app')

@section('title', 'Booking Berhasil | Needs Study Space')

@section('content')

<style>
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-up {
        animation: slideUpFade 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
    .delay-100 { animation-delay: 150ms; }

    @media print {
        body * { visibility: hidden; }
        #receiptCard, #receiptCard * { visibility: visible; opacity: 1 !important; transform: none !important; }
        #receiptCard {
            position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 20px;
            box-shadow: none !important; border: 1px solid #e5e7eb !important; border-radius: 0 !important;
            animation: none !important;
        }
        .no-print { display: none !important; }
    }
</style>

<div class="max-w-lg mx-auto py-10 px-4 sm:px-6 min-h-[85vh] flex flex-col justify-center">
    
    {{-- Kotak Utama (Struk Digital) --}}
    <div id="receiptCard" class="animate-slide-up bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 relative overflow-hidden mb-8">
        
        <div class="absolute top-0 left-0 w-full h-2 bg-n-primary"></div>

        <div class="p-6 sm:p-8">
            <div class="text-center pb-6 border-b border-dashed border-gray-200 mt-2">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-green-50 text-green-500 mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-n-charcoal tracking-tight">NEEDS STUDY SPACE</h2>
                <p class="text-xs font-medium text-n-gray mt-1 uppercase tracking-wider">Struk Pemesanan & Pembayaran Lunas</p>
            </div>

            @php
                $bookingIdCode = $booking->booking_code ?? ('#NDY-' . date('Ymd', strtotime($booking->created_at)) . sprintf('%02d', $booking->id));
                $customerName = $booking->customer_name ?? '-';
                $customerPhone = $booking->customer_phone ?? '-';
                $passType = 'Study Space Area (All-Access)';
                
                // Mengambil data produk/snack yang dipesan (menyesuaikan nama relasi, misal products atau orderItems)
                $orderedProducts = $booking->products ?? $booking->items ?? collect();
            @endphp

            <div class="py-6 space-y-4 text-sm sm:text-base border-b border-dashed border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-n-gray">ID Booking</span>
                    <span class="font-bold text-n-charcoal font-mono">{{ $bookingIdCode }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-n-gray">Nama Pemesan</span>
                    <span class="font-medium text-n-charcoal">{{ $customerName }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-n-gray">No. WhatsApp</span>
                    <span class="font-medium text-n-charcoal">{{ $customerPhone }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-n-gray">Jenis Akses</span>
                    <span class="font-semibold text-n-primary">{{ $passType }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-n-gray">Tanggal</span>
                    <span class="font-medium text-n-charcoal">{{ date('d M Y', strtotime($booking->booking_date)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-n-gray">Durasi Sewa</span>
                    <span class="font-medium text-n-charcoal">{{ $booking->duration }} Hari Full-day</span>
                </div>
            </div>

            {{-- Menampilkan Daftar Makanan / Snack Jika Ada --}}
            @if($orderedProducts->count() > 0)
            <div class="py-4 border-b border-dashed border-gray-200">
                <p class="text-xs font-semibold text-n-gray uppercase tracking-wider mb-2">Tambahan Snack & Minuman:</p>
                <div class="space-y-2 text-sm">
                    @foreach($orderedProducts as $item)
                    @php
                        $prodName = $item->name ?? $item->product->name ?? 'Produk';
                        $prodQty = $item->pivot->quantity ?? $item->quantity ?? 1;
                        $prodPrice = $item->pivot->price ?? $item->price ?? 0;
                    @endphp
                    <div class="flex justify-between items-center text-n-charcoal">
                        <span>{{ $prodName }} <span class="text-xs text-n-gray">(x{{ $prodQty }})</span></span>
                        <span class="font-medium">Rp{{ number_format($prodPrice * $prodQty, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-gray-50 p-5 rounded-xl flex justify-between items-center border border-gray-100 mt-5 mb-2">
                <div>
                    <p class="text-xs text-n-gray uppercase tracking-wider font-semibold mb-1">Total Dibayar</p>
                    <div class="flex items-center gap-1.5">
                        <span class="flex w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-xs text-green-600 font-medium">Lunas (Midtrans Gateway)</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-n-primary">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="text-center pt-5 text-xs text-n-gray/70">
                Terima kasih telah menggunakan sistem pembayaran mandiri Needs Study Space.
            </div>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="space-y-3.5 no-print w-full animate-slide-up delay-100">
        
        {{-- Tombol WhatsApp untuk HRD / Admin --}}
        @php
            $adminPhone = '6282118045141'; // Nomor WhatsApp Admin / HRD

            // Merakit teks produk untuk pesan WhatsApp
            $snackText = "";
            if($orderedProducts->count() > 0) {
                $snackText = "%0A*SNACK & MINUMAN*%0A";
                foreach($orderedProducts as $item) {
                    $pName = $item->name ?? $item->product->name ?? 'Produk';
                    $pQty = $item->pivot->quantity ?? $item->quantity ?? 1;
                    $pSubtotal = ($item->pivot->price ?? $item->price ?? 0) * $pQty;
                    $snackText .= "- {$pName} (x{$pQty}) : Rp" . number_format($pSubtotal, 0, ',', '.') . "%0A";
                }
            }

            $message = "Halo Admin Needs Study Space,%0A%0A" .
                       "Saya telah menyelesaikan pembayaran otomatis via Midtrans. Berikut detail pesanan saya:%0A%0A" .
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
                       "- Status: *LUNAS (Otomatis)*%0A%0A" .
                       "Mohon verifikasinya. Terima kasih!";

            $whatsappUrl = "https://wa.me/{$adminPhone}?text=" . $message;
        @endphp

        <a href="{{ $whatsappUrl }}" target="_blank" 
            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3.5 px-4 rounded-xl text-sm sm:text-base transition-all flex items-center justify-center shadow-md hover:shadow-lg group">
            <svg class="w-5 h-5 mr-3 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            <span>Kirim Notifikasi Pembayaran ke WhatsApp Admin</span>
        </a>

        {{-- Tombol Unduh / Cetak Struk --}}
        <button type="button" onclick="window.print()" class="w-full flex items-center justify-center bg-white border border-gray-200 text-gray-700 font-semibold py-3.5 px-4 rounded-xl hover:bg-gray-50 transition-all shadow-sm group">
            <svg class="w-5 h-5 mr-3 text-gray-500 group-hover:text-gray-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Unduh / Cetak Struk (PDF)</span>
        </button>

        {{-- Tombol Kembali --}}
        <div class="text-center pt-4 pb-2">
            <a href="{{ url('/') }}" class="inline-block text-sm font-medium text-gray-400 hover:text-gray-800 transition-colors border-b border-transparent hover:border-gray-800 pb-0.5">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection