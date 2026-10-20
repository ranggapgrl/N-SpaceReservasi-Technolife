@extends('admin.layouts.app')

@section('page_title', 'Dashboard')

@section('content')
@php
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;

    $today      = Carbon::today();
    $weekDays   = CarbonPeriod::create($today, $today->copy()->addDays(6));
    $monthStart = Carbon::parse($currentMonth ?? Carbon::now())->startOfMonth();
    $monthEnd   = $monthStart->copy()->endOfMonth();
    $leadBlanks = $monthStart->dayOfWeekIso - 1; // minggu dimulai Senin

    $statusMap = [
        'confirmed' => ['Terkonfirmasi', 'bg-emerald-500'],
        'pending'   => ['Menunggu pembayaran', 'bg-amber-500'],
        'cancelled' => ['Dibatalkan', 'bg-gray-400'],
    ];

    // Pola garis miring untuk menandai tanggal tutup
    $hatch = 'background-image:repeating-linear-gradient(135deg,transparent 0 7px,rgba(0,0,0,.045) 7px 8px)';
@endphp

<div class="mx-auto max-w-6xl space-y-12 pb-16" x-data="{ showCalendarModal: false }"
     @keydown.escape.window="showCalendarModal = false">

    {{-- ── SAPAAN & TANGGAL ── --}}
    <header>
        <p class="text-sm text-gray-500">{{ $today->translatedFormat('l, d F Y') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">Ringkasan hari ini</h1>
    </header>

    {{-- ── RINGKASAN ── --}}
    <section aria-label="Ringkasan hari ini"
             class="grid border-y border-gray-200 sm:grid-cols-[1.4fr_1fr_1fr] divide-y divide-gray-200 sm:divide-y-0 sm:divide-x">

        <div class="py-6 sm:pr-8">
            <p class="text-sm text-gray-500">Pendapatan</p>
            <p class="mt-2 text-4xl font-semibold tabular-nums tracking-tight text-gray-900">
                <span class="mr-1 text-xl font-normal text-gray-400">Rp</span>{{ number_format($todayRevenue ?? 0, 0, ',', '.') }}
            </p>
        </div>

        <div class="py-6 sm:px-8">
            <p class="text-sm text-gray-500">Booking masuk</p>
            <p class="mt-2 text-4xl font-semibold tabular-nums tracking-tight text-gray-900">{{ $todayBookingsCount ?? 0 }}</p>
        </div>

        <div class="py-6 sm:pl-8">
            <p class="text-sm text-gray-500">Menunggu pembayaran</p>
            <p class="mt-2 flex items-baseline gap-3">
                <span class="text-4xl font-semibold tabular-nums tracking-tight {{ ($pendingBookingsCount ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $pendingBookingsCount ?? 0 }}</span>
                @if(($pendingBookingsCount ?? 0) > 0)
                    <a href="{{ route('admin.bookings.index') }}"
                       class="text-sm font-medium text-[#b1002c] underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                        Periksa
                    </a>
                @endif
            </p>
        </div>
    </section>

    {{-- ── KETERSEDIAAN 7 HARI ── --}}
    <section aria-labelledby="slot-heading">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 id="slot-heading" class="text-lg font-semibold text-gray-900">Ketersediaan 7 hari ke depan</h2>
                <p class="mt-1 text-sm text-gray-500">Jumlah pengunjung dan tanggal yang ditutup untuk umum.</p>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('admin.blocked-dates.index') }}"
                   class="rounded-md px-3 py-2 font-medium text-gray-700 hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#b1002c]">
                    Atur tanggal tutup
                </a>
                <button type="button" @click="showCalendarModal = true"
                        class="rounded-md bg-[#b1002c] px-4 py-2 font-medium text-white transition-colors hover:bg-[#8f0023] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                    Lihat kalender bulan ini
                </button>
            </div>
        </div>

        <ol class="mt-6 flex snap-x overflow-x-auto rounded-lg border border-gray-200 bg-white lg:grid lg:grid-cols-7 lg:overflow-visible divide-x divide-gray-200">
            @foreach($weekDays as $date)
                @php
                    $dateStr  = $date->format('Y-m-d');
                    $isClosed = isset($blockedDates[$dateStr]);
                    $reason   = $blockedDates[$dateStr] ?? null;
                    $visitors = $dailyBookings[$dateStr] ?? 0;
                    $isToday  = $date->isToday();
                @endphp
                <li class="relative min-w-[9rem] snap-start p-4 lg:min-w-0"
                    @if($isClosed) style="{{ $hatch }}" @endif
                    @if($isToday) aria-current="date" @endif>

                    @if($isToday)
                        <span class="absolute inset-x-0 top-0 h-0.5 bg-[#b1002c]"></span>
                    @endif

                    <p class="text-sm {{ $isToday ? 'font-medium text-[#b1002c]' : 'text-gray-500' }}">
                        {{ $isToday ? 'Hari ini' : $date->translatedFormat('l') }}
                    </p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-900">
                        {{ $date->format('d') }}
                        <span class="text-sm font-normal text-gray-400">{{ $date->translatedFormat('M') }}</span>
                    </p>

                    <p class="mt-4 text-sm {{ $visitors > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                        @if($isClosed)
                            &nbsp;
                        @elseif($visitors > 0)
                            <span class="font-semibold tabular-nums">{{ $visitors }}</span> pengunjung
                        @else
                            Belum ada booking
                        @endif
                    </p>

                    <p class="mt-2 flex items-start gap-2 text-sm {{ $isClosed ? 'text-rose-700' : 'text-gray-600' }}">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full {{ $isClosed ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                        <span>
                            {{ $isClosed ? 'Ditutup' : 'Tersedia' }}
                            @if($isClosed && $reason)
                                <span class="block text-xs text-gray-500">{{ $reason }}</span>
                            @endif
                        </span>
                    </p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- ── BOOKING TERBARU ── --}}
    <section aria-labelledby="booking-heading">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 id="booking-heading" class="text-lg font-semibold text-gray-900">Booking terbaru</h2>
                <p class="mt-1 text-sm text-gray-500">Pesanan day pass yang baru masuk.</p>
            </div>
            <a href="{{ route('admin.bookings.index') }}"
               class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#b1002c]">
                Semua booking
            </a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 bg-white">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-gray-500">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-medium">Pelanggan</th>
                        <th scope="col" class="px-5 py-3 font-medium">Tanggal kunjungan</th>
                        <th scope="col" class="px-5 py-3 text-right font-medium">Total</th>
                        <th scope="col" class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentBookings ?? [] as $booking)
                        @php
                            [$statusLabel, $statusDot] = $statusMap[$booking->status] ?? [ucfirst($booking->status), 'bg-gray-400'];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $booking->customer_name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($booking->visitor_category) }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-gray-900">{{ Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $booking->duration }} hari</p>
                            </td>
                            <td class="px-5 py-4 text-right font-medium tabular-nums text-gray-900">
                                Rp{{ number_format($booking->total_price, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 text-gray-700">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusDot }}"></span>
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-14 text-center">
                                <p class="font-medium text-gray-900">Belum ada booking</p>
                                <p class="mt-1 text-sm text-gray-500">Pesanan baru akan muncul di sini begitu pelanggan memesan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ── MODAL KALENDER BULANAN ── --}}
    <div x-show="showCalendarModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 p-4"
         x-transition.opacity.duration.200ms>

        <div @click.away="showCalendarModal = false"
             role="dialog" aria-modal="true" aria-labelledby="calendar-title"
             class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 id="calendar-title" class="text-lg font-semibold text-gray-900">
                    Kalender {{ $monthStart->translatedFormat('F Y') }}
                </h3>
                <button type="button" @click="showCalendarModal = false" aria-label="Tutup kalender"
                        class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#b1002c]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div class="grid grid-cols-7 border-l border-t border-gray-200 text-center text-xs text-gray-500">
                    @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dayName)
                        <div class="border-b border-r border-gray-200 bg-gray-50 py-2 font-medium">{{ $dayName }}</div>
                    @endforeach

                    @for($i = 0; $i < $leadBlanks; $i++)
                        <div class="min-h-[4.5rem] border-b border-r border-gray-200 bg-gray-50/60"></div>
                    @endfor

                    @foreach(CarbonPeriod::create($monthStart, $monthEnd) as $mDate)
                        @php
                            $mStr     = $mDate->format('Y-m-d');
                            $mClosed  = isset($blockedDates[$mStr]);
                            $mReason  = $blockedDates[$mStr] ?? null;
                            $mVisitor = $dailyBookings[$mStr] ?? 0;
                            $mToday   = $mDate->isToday();
                        @endphp
                        <div class="relative min-h-[4.5rem] border-b border-r border-gray-200 p-2 text-left"
                             @if($mClosed) style="{{ $hatch }}" title="Ditutup{{ $mReason ? ': '.$mReason : '' }}" @endif>
                            <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full px-1 text-sm tabular-nums
                                {{ $mToday ? 'bg-[#b1002c] font-semibold text-white' : 'text-gray-700' }}">{{ $mDate->format('j') }}</span>

                            @if($mClosed)
                                <p class="mt-1 text-xs font-medium text-rose-700">Tutup</p>
                            @elseif($mVisitor > 0)
                                <p class="mt-1 text-xs text-gray-900"><span class="font-semibold tabular-nums">{{ $mVisitor }}</span> orang</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-500">
                    <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-[#b1002c]"></span>Hari ini</span>
                    <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-sm border border-gray-300" style="{{ $hatch }}"></span>Ditutup</span>
                    <span>Angka di bawah tanggal = jumlah pengunjung</span>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 px-6 py-4">
                <a href="{{ route('admin.blocked-dates.index') }}"
                   class="text-sm font-medium text-[#b1002c] underline-offset-4 hover:underline">Atur tanggal tutup</a>
                <button type="button" @click="showCalendarModal = false"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection