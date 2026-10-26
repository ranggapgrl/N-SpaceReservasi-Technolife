@extends('admin.layouts.app')

@section('page_title', 'Dashboard')

@section('content')
@php
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;

    // Data tanggal dipakai bersama oleh ringkasan mingguan dan kalender bulanan.
    $today = Carbon::today();
    $weekDays = CarbonPeriod::create($today, $today->copy()->addDays(6));
    $monthStart = Carbon::parse($currentMonth ?? Carbon::now())->startOfMonth();
    $monthEnd = $monthStart->copy()->endOfMonth();
    $leadBlanks = $monthStart->dayOfWeekIso - 1;

    $statusMap = [
        'confirmed' => ['Terkonfirmasi', 'bg-emerald-50 text-emerald-700 ring-emerald-600/15'],
        'pending' => ['Menunggu pembayaran', 'bg-amber-50 text-amber-700 ring-amber-600/15'],
        'cancelled' => ['Dibatalkan', 'bg-gray-100 text-gray-600 ring-gray-500/10'],
    ];

    $hatch = 'background-image:repeating-linear-gradient(135deg,transparent 0 7px,rgba(177,0,44,.08) 7px 8px)';
@endphp

<div class="mx-auto max-w-7xl space-y-10 pb-12" x-data="{ showCalendarModal: false }"
     @keydown.escape.window="showCalendarModal = false">

    {{-- Sapaan dan pintasan menuju daftar booking. --}}
    <header class="flex flex-col gap-5 border-b border-gray-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-[#b1002c]">Ringkasan operasional</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#1b1c1c] sm:text-4xl">
                Selamat datang, {{ Auth::user()->name }}
            </h2>
            <p class="mt-2 text-sm text-gray-500">
                Pantau aktivitas dan kebutuhan hari ini, {{ $today->translatedFormat('l, d F Y') }}.
            </p>
        </div>
        <a href="{{ route('admin.bookings.index') }}"
           class="inline-flex w-fit items-center gap-2 rounded-lg bg-[#b1002c] px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#8f0023] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
            Buka daftar booking
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14m-6-6 6 6-6 6"/>
            </svg>
        </a>
    </header>

    {{-- Ringkasan hanya menampilkan metrik booking dan pendapatan; layanan bersifat open space, bukan booking ruangan. --}}
    <section aria-labelledby="overview-heading">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h3 id="overview-heading" class="text-lg font-semibold text-[#1b1c1c]">Sekilas hari ini</h3>
                <p class="mt-1 text-sm text-gray-500">Angka utama untuk membantu menentukan prioritas.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pendapatan terkonfirmasi</p>
                        <p class="mt-1 text-xs text-gray-400">Booking untuk hari ini</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m4-9.5c0-1.38-1.79-2.5-4-2.5s-4 1.12-4 2.5 1.79 2.5 4 2.5 4 1.12 4 2.5-1.79 2.5-4 2.5-4-1.12-4-2.5M5 12a7 7 0 1 0 14 0 7 7 0 0 0-14 0Z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-5 text-2xl font-semibold tabular-nums tracking-tight text-[#1b1c1c]">
                    <span class="mr-1 text-base font-medium text-gray-400">Rp</span>{{ number_format($todayRevenue ?? 0, 0, ',', '.') }}
                </p>
            </article>

            <article class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Booking hari ini</p>
                        <p class="mt-1 text-xs text-gray-400">Semua status booking</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-[#b1002c]" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V4m8 3V4M5 10h14M6 6h12a1 1 0 0 1 1 1v12H5V7a1 1 0 0 1 1-1Z"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-5 text-2xl font-semibold tabular-nums tracking-tight text-[#1b1c1c]">{{ $todayBookingsCount ?? 0 }}</p>
            </article>

            <article class="rounded-xl border {{ ($pendingBookingsCount ?? 0) > 0 ? 'border-amber-200 bg-amber-50/50' : 'border-gray-200 bg-white' }} p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Menunggu pembayaran</p>
                        <p class="mt-1 text-xs text-gray-400">Perlu ditindaklanjuti</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg {{ ($pendingBookingsCount ?? 0) > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' }}" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l2.5 1.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-5 flex items-baseline justify-between gap-3">
                    <p class="text-2xl font-semibold tabular-nums tracking-tight {{ ($pendingBookingsCount ?? 0) > 0 ? 'text-amber-800' : 'text-[#1b1c1c]' }}">{{ $pendingBookingsCount ?? 0 }}</p>
                    @if(($pendingBookingsCount ?? 0) > 0)
                        <a href="{{ route('admin.bookings.index') }}"
                           class="text-sm font-semibold text-[#8f0023] underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                            Periksa booking
                        </a>
                    @endif
                </div>
            </article>

        </div>
    </section>

    {{-- Ketersediaan menunjukkan tanggal tutup dan booking terkonfirmasi, bukan reservasi ruang. --}}
    <section aria-labelledby="availability-heading">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 id="availability-heading" class="text-lg font-semibold text-[#1b1c1c]">Ketersediaan 7 hari ke depan</h3>
                <p class="mt-1 text-sm text-gray-500">Lihat jumlah booking yang sudah terkonfirmasi dan tanggal yang ditutup.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.blocked-dates.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                    Atur tanggal tutup
                </a>
                <button type="button" @click="showCalendarModal = true"
                        class="rounded-lg bg-[#b1002c] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#8f0023] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                    Lihat kalender
                </button>
            </div>
        </div>

        <ol class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach($weekDays as $date)
                @php
                    $dateStr = $date->format('Y-m-d');
                    $isClosed = isset($blockedDates[$dateStr]);
                    $reason = $blockedDates[$dateStr] ?? null;
                    $bookings = $dailyBookings[$dateStr] ?? 0;
                    $isToday = $date->isToday();
                    $hasMonthData = $date->isSameMonth($monthStart);
                @endphp
                <li class="relative min-h-36 rounded-xl border p-4 {{ $isClosed ? 'border-rose-200 bg-rose-50/70' : ($isToday ? 'border-[#b1002c]/40 bg-rose-50/40' : 'border-gray-200 bg-white') }}"
                    @if($isClosed) style="{{ $hatch }}" @endif
                    @if($isToday) aria-current="date" @endif>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium {{ $isToday ? 'text-[#b1002c]' : 'text-gray-500' }}">
                                {{ $date->translatedFormat('l') }}
                            </p>
                            <p class="mt-1 text-2xl font-semibold tabular-nums text-[#1b1c1c]">
                                {{ $date->format('d') }}
                                <span class="text-sm font-medium text-gray-400">{{ $date->translatedFormat('M') }}</span>
                            </p>
                        </div>
                        @if($isToday)
                            <span class="rounded-full bg-[#b1002c] px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-white">Hari ini</span>
                        @endif
                    </div>

                    <div class="mt-5 border-t border-gray-200/80 pt-3">
                        @if(!$hasMonthData)
                            <p class="text-sm font-semibold text-gray-700">Perlu dicek</p>
                            <p class="mt-1 text-xs text-gray-500">Di luar data bulan ini</p>
                        @elseif($isClosed)
                            <p class="text-sm font-semibold text-rose-700">Ditutup</p>
                            @if($reason)
                                <p class="mt-1 break-words text-xs text-rose-700/80">{{ $reason }}</p>
                            @endif
                        @else
                            <p class="text-sm font-semibold text-emerald-700">Tersedia</p>
                            <p class="mt-1 text-xs text-gray-500">
                                @if($bookings > 0)
                                    {{ $bookings }} booking terkonfirmasi
                                @else
                                    Belum ada booking
                                @endif
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Lima booking terbaru membantu admin mengecek pesanan yang baru masuk. --}}
    <section aria-labelledby="booking-heading">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 id="booking-heading" class="text-lg font-semibold text-[#1b1c1c]">Booking terbaru</h3>
                <p class="mt-1 text-sm text-gray-500">Lima pesanan terakhir yang masuk ke sistem.</p>
            </div>
            <a href="{{ route('admin.bookings.index') }}"
            class="w-fit text-sm font-semibold text-[#b1002c] underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                Lihat semua booking
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <div class="mt-5 overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <caption class="sr-only">Daftar lima booking terbaru</caption>
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 font-semibold">Pelanggan</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold">Tanggal kunjungan</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-semibold">Total</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentBookings ?? [] as $booking)
                            @php
                                [$statusLabel, $statusClass] = $statusMap[$booking->status] ?? [ucfirst($booking->status), 'bg-gray-100 text-gray-600 ring-gray-500/10'];
                            @endphp
                            <tr class="transition-colors hover:bg-gray-50/80">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#1b1c1c]">{{ $booking->customer_name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ ucfirst($booking->visitor_category) }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-gray-800">{{ Carbon::parse($booking->booking_date)->translatedFormat('d M Y') }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ $booking->duration }} hari</p>
                                </td>
                                <td class="px-5 py-4 text-right font-semibold tabular-nums text-[#1b1c1c]">
                                    Rp{{ number_format($booking->total_price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center">
                                    <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-500" aria-hidden="true">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V4m8 3V4M5 10h14M6 6h12a1 1 0 0 1 1 1v12H5V7a1 1 0 0 1 1-1Z"/>
                                        </svg>
                                    </span>
                                    <p class="mt-3 font-semibold text-[#1b1c1c]">Belum ada booking</p>
                                    <p class="mt-1 text-sm text-gray-500">Booking baru akan muncul di sini setelah pelanggan memesan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Kalender bulanan untuk melihat booking dan tanggal yang ditutup. --}}
    <div x-show="showCalendarModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4"
        x-transition.opacity.duration.200ms>
        <div @click.away="showCalendarModal = false"
            role="dialog" aria-modal="true" aria-labelledby="calendar-title"
            class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-3 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#b1002c]">Kalender ketersediaan</p>
                    <h3 id="calendar-title" class="mt-1 text-lg font-semibold text-[#1b1c1c]">
                        {{ $monthStart->translatedFormat('F Y') }}
                    </h3>
                </div>
                <button type="button" @click="showCalendarModal = false" aria-label="Tutup kalender"
                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                <div class="grid grid-cols-7 border-l border-t border-gray-200 text-center text-xs text-gray-500">
                    @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                        <div class="border-b border-r border-gray-200 bg-gray-50 py-2 font-semibold">{{ $dayName }}</div>
                    @endforeach

                    @for($i = 0; $i < $leadBlanks; $i++)
                        <div class="min-h-[4.5rem] border-b border-r border-gray-200 bg-gray-50/60"></div>
                    @endfor

                    @foreach(CarbonPeriod::create($monthStart, $monthEnd) as $monthDate)
                        @php
                            $monthDateString = $monthDate->format('Y-m-d');
                            $monthDateClosed = isset($blockedDates[$monthDateString]);
                            $monthDateReason = $blockedDates[$monthDateString] ?? null;
                            $monthDateBookings = $dailyBookings[$monthDateString] ?? 0;
                            $monthDateIsToday = $monthDate->isToday();
                        @endphp
                        <div class="relative min-h-[4.5rem] border-b border-r border-gray-200 p-1.5 text-left sm:p-2"
                            @if($monthDateClosed) style="{{ $hatch }}" title="Ditutup{{ $monthDateReason ? ': '.$monthDateReason : '' }}" @endif>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full px-1 text-sm tabular-nums
                                {{ $monthDateIsToday ? 'bg-[#b1002c] font-semibold text-white' : 'text-gray-700' }}">{{ $monthDate->format('j') }}</span>

                            @if($monthDateClosed)
                                <p class="mt-1 text-[10px] font-semibold text-rose-700 sm:text-xs">Ditutup</p>
                            @elseif($monthDateBookings > 0)
                                <p class="mt-1 text-[10px] text-gray-700 sm:text-xs"><span class="font-semibold tabular-nums">{{ $monthDateBookings }}</span> booking</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-600">
                    <span class="inline-flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-[#b1002c]"></span>Hari ini
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="h-3 w-3 rounded-sm border border-rose-200" style="{{ $hatch }}"></span>Tanggal ditutup
                    </span>
                    <span>Angka menunjukkan booking terkonfirmasi.</span>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <a href="{{ route('admin.blocked-dates.index') }}"
                class="text-sm font-semibold text-[#b1002c] underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b1002c]">
                    Kelola tanggal tutup
                </a>
                <button type="button" @click="showCalendarModal = false"
                        class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                    Tutup kalender
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
