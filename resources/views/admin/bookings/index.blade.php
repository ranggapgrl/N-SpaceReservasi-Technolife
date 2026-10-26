@extends('admin.layouts.app')

@section('page_title', 'Manajemen Booking')

@section('content')

    @php
        // Daftar tab status booking (kunci kosong = semua)
        $tabs = [
            ''          => 'Semua',
            'pending'   => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
        ];

        // Titik warna status booking
        $statusDot = [
            'confirmed' => 'bg-emerald-500',
            'pending'   => 'bg-amber-500',
            'cancelled' => 'bg-gray-400',
        ];
    @endphp

    <div class="mx-auto max-w-7xl">

        {{--
            ══ BAR PENCARIAN ══════════════════════════════════════════════
            Form GET: saat submit, parameter 'search' ditambahkan ke URL.
            Pencarian di BookingController@index berdasarkan:
            booking_code, customer_name, customer_phone
        --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-n-gray">
                <span class="font-semibold tabular-nums text-n-charcoal">{{ $bookings->total() }}</span> booking
                @if($status || $ktmFilter || $search) sesuai filter @endif
            </p>

            <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex w-full gap-2 sm:w-auto" role="search">
                {{-- Pertahankan filter yang sedang aktif saat mencari --}}
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                @if($ktmFilter)
                    <input type="hidden" name="ktm" value="{{ $ktmFilter }}">
                @endif

                <div class="relative flex-1 sm:w-72 sm:flex-none">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-n-gray" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    <input type="search"
                        name="search"
                        value="{{ $search ?? '' }}"
                        aria-label="Cari booking"
                        placeholder="Cari kode, nama, atau telepon"
                        class="w-full rounded-lg border border-n-border bg-white py-2 pl-9 pr-3 text-sm focus:border-[#b1002c] focus:outline-none focus:ring-2 focus:ring-[#b1002c]/20">
                </div>
                <button type="submit"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]"
                        style="background-color: #b1002c;">
                    Cari
                </button>
                @if($search)
                <a href="{{ route('admin.bookings.index', array_filter(['status' => $status, 'ktm' => $ktmFilter])) }}"
                   class="rounded-lg border border-n-border bg-white px-4 py-2 text-sm text-n-gray transition-colors hover:bg-n-bg">
                    Reset
                </a>
                @endif
            </form>
        </div>

        {{--
            ══ FILTER STATUS BOOKING ══════════════════════════════════════
            URL berubah saat klik tab; controller membaca ?status=...
        --}}
        <nav aria-label="Filter status booking" class="flex gap-6 overflow-x-auto border-b border-n-border">
            @foreach($tabs as $key => $label)
                @php $isActive = $status === ($key ?: null) || ($key === '' && !$status); @endphp
                <a href="{{ route('admin.bookings.index', array_filter(['status' => $key ?: null, 'search' => $search, 'ktm' => $ktmFilter])) }}"
                   @if($isActive) aria-current="page" @endif
                   class="-mb-px whitespace-nowrap border-b-2 px-1 pb-3 text-sm font-medium transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]
                          {{ $isActive ? 'border-[#b1002c] text-[#b1002c]' : 'border-transparent text-n-gray hover:border-n-border hover:text-n-charcoal' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        {{--
            ══ FILTER KTM ═════════════════════════════════════════════════
            Untuk melihat booking mahasiswa berdasarkan status KTM.
            Badge menunjukkan jumlah KTM yang menunggu verifikasi.
        --}}
        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
            <span class="mr-1 text-n-gray">Verifikasi KTM</span>

            @php
                $ktmOptions = [
                    ''         => 'Semua',
                    'pending'  => 'Perlu diverifikasi',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                ];
            @endphp
            @foreach($ktmOptions as $key => $label)
                @php $ktmActive = $key === '' ? !$ktmFilter : $ktmFilter === $key; @endphp
                <a href="{{ route('admin.bookings.index', array_filter(['ktm' => $key ?: null, 'status' => $status, 'search' => $search])) }}"
                   @if($ktmActive) aria-current="true" @endif
                   class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]
                          {{ $ktmActive ? 'border-[#b1002c] bg-[#b1002c] text-white' : 'border-n-border bg-white text-n-gray hover:border-gray-300 hover:text-n-charcoal' }}">
                    {{ $label }}
                    @if($key === 'pending' && $pendingKtmCount > 0)
                        {{-- Badge jumlah KTM pending --}}
                        <span class="min-w-[1.25rem] rounded-full px-1.5 text-center text-xs font-semibold tabular-nums {{ $ktmActive ? 'bg-white text-[#b1002c]' : 'bg-orange-500 text-white' }}">{{ $pendingKtmCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Info hasil pencarian --}}
        @if($search)
        <p class="mt-4 text-sm text-n-gray">
            Hasil pencarian untuk "<strong class="text-n-charcoal">{{ $search }}</strong>":
            {{ $bookings->total() }} ditemukan.
        </p>
        @endif

        {{-- ══ TABEL BOOKING ══ --}}
        <div class="mt-6 overflow-hidden rounded-xl border border-n-border bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-n-border bg-n-bg text-n-gray">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Kode booking</th>
                            <th scope="col" class="px-4 py-3 font-medium">Pelanggan</th>
                            <th scope="col" class="px-4 py-3 font-medium">Kategori</th>
                            <th scope="col" class="px-4 py-3 font-medium">Jadwal</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Total</th>
                            <th scope="col" class="px-4 py-3 font-medium">Status</th>
                            <th scope="col" class="px-4 py-3 font-medium">KTM</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-n-border text-n-charcoal">
                        @forelse($bookings as $booking)
                        <tr class="transition-colors hover:bg-n-bg/60">
                            <td class="px-4 py-4">
                                <a href="{{ route('admin.bookings.show', $booking) }}"
                                class="font-medium tracking-wide hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#b1002c]"
                                style="color: #b1002c;">{{ $booking->booking_code }}</a>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-medium">{{ $booking->customer_name }}</p>
                                <p class="text-xs text-n-gray">{{ $booking->customer_phone }}</p>
                            </td>
                            {{-- Kategori pengunjung --}}
                            <td class="px-4 py-4">
                                {{ $booking->visitor_category === 'mahasiswa' ? 'Mahasiswa' : 'Umum' }}
                            </td>
                            <td class="px-4 py-4">
                                <p>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</p>
                                <p class="text-xs text-n-gray">{{ $booking->duration }} hari</p>
                            </td>
                            <td class="px-4 py-4 text-right font-medium tabular-nums">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusDot[$booking->status] ?? 'bg-gray-400' }}"></span>
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            {{-- Status KTM: hanya relevan untuk mahasiswa, umum menampilkan "—" --}}
                            <td class="px-4 py-4">
                                @if($booking->visitor_category === 'mahasiswa')
                                    @if($booking->ktm_status === 'approved')
                                        <span class="inline-flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Disetujui</span>
                                    @elseif($booking->ktm_status === 'rejected')
                                        <span class="inline-flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Ditolak</span>
                                    @elseif($booking->ktm_status === 'pending')
                                        <span class="inline-flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Menunggu</span>
                                    @else
                                        <span class="text-xs text-n-gray">Belum upload</span>
                                    @endif
                                @else
                                    <span class="text-n-gray" aria-label="Tidak berlaku">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Tombol Detail --}}
                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                       class="inline-flex items-center rounded-lg border border-n-border bg-white px-3 py-1.5 text-xs font-medium text-n-charcoal transition-colors hover:border-[#b1002c] hover:text-[#b1002c] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                                        Detail
                                    </a>

                                    {{-- Tombol Konfirmasi --}}
c                                    @if($booking->status !== 'confirmed' && $booking->payment?->payment_method === 'cash_on_site' && $booking->payment->status !== 'paid')
                                        <span class="text-xs font-medium text-amber-700">Cek pembayaran di detail</span>
                                    @elseif($booking->status !== 'confirmed')
                                    <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit"
                                                class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium text-emerald-700 transition-colors hover:bg-emerald-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-600">
                                            Konfirmasi
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Tombol Batal --}}
                                    @if($booking->status !== 'cancelled')
                                    <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit"
                                                onclick="return confirm('Yakin ingin membatalkan booking ini?')"
                                                class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-red-600">
                                            Batal
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <p class="font-medium text-n-charcoal">
                                    Tidak ada booking
                                    {{ $status ? "dengan status \"$status\"" : '' }}
                                    {{ $search ? "yang cocok dengan pencarian \"$search\"" : '' }}
                                </p>
                                @if($status || $ktmFilter || $search)
                                    <a href="{{ route('admin.bookings.index') }}" class="mt-2 inline-block text-sm font-medium hover:underline" style="color: #b1002c;">
                                        Hapus semua filter
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
            <div class="border-t border-n-border px-6 py-4">
                {{ $bookings->links() }}
            </div>
            @endif
        </div>
    </div>

@endsection