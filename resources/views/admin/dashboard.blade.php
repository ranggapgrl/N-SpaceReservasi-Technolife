@extends('admin.layouts.app')

@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-8 pb-12">

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Stat Card 1 -->
        <div class="bg-white p-6 rounded-2xl border border-n-border/60 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
            <span class="text-xs font-semibold text-n-gray uppercase tracking-wider">Pendapatan Hari Ini</span>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-n-primary">Rp{{ number_format($todayRevenue ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="bg-white p-6 rounded-2xl border border-n-border/60 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
            <span class="text-xs font-semibold text-n-gray uppercase tracking-wider">Ruangan Aktif</span>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-n-charcoal">{{ $activeRoomsCount ?? 0 }}</span>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="bg-white p-6 rounded-2xl border border-n-border/60 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
            <span class="text-xs font-semibold text-n-gray uppercase tracking-wider">Pembayaran Pending</span>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-amber-500">{{ $pendingBookingsCount ?? 0 }}</span>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="bg-white p-6 rounded-2xl border border-n-border/60 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
            <span class="text-xs font-semibold text-n-gray uppercase tracking-wider">Booking Hari Ini</span>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-bold text-n-charcoal">{{ $todayBookingsCount ?? 0 }}</span>
            </div>
        </div>

    </div>

    <!-- Matriks Jadwal Ketersediaan Jam Ruangan (Desain Elegan) -->
    <div class="bg-white rounded-3xl border border-n-border/60 shadow-sm overflow-hidden">
        
        <!-- Header Section -->
        <div class="p-6 sm:p-8 border-b border-n-border/40 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h3 class="font-bold text-n-charcoal text-xl tracking-tight">Jadwal Ketersediaan Ruangan</h3>
                <p class="text-xs text-n-gray font-light mt-1">Pantau status slot jam operasional ruangan secara visual dan real-time.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto justify-between md:justify-end">
                <!-- Keterangan Status -->
                <div class="flex items-center gap-4 text-xs font-medium text-n-gray">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span> Tersedia</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-rose-500 rounded-full"></span> Terisi</span>
                </div>

                <!-- Filter Tanggal -->
                <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center gap-2 bg-[#f8fafc] px-3 py-2 rounded-xl border border-n-border/60">
                    <svg class="w-4 h-4 text-n-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                        class="bg-transparent text-xs font-medium text-n-charcoal outline-none cursor-pointer">
                </form>
            </div>
        </div>

        <!-- Tabel Matriks -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-[#f8fafc] text-[11px] font-bold text-n-gray uppercase tracking-wider border-b border-n-border/40">
                        <th class="py-4 px-6 sm:px-8 w-56">Ruangan</th>
                        @foreach($hours as $hour)
                            <th class="py-4 px-1 text-center font-mono">{{ $hour }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($rooms as $room)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-5 px-6 sm:px-8 bg-white sticky left-0">
                                <span class="font-semibold text-n-charcoal block text-sm">{{ $room->name }}</span>
                                <span class="text-[11px] text-n-gray font-light">Kapasitas {{ $room->capacity }} orang</span>
                            </td>

                            @foreach($hours as $hour)
                                @php
                                    $isBooked = false;
                                    $bookedBy = '';
                                    
                                    foreach($bookings as $booking) {
                                        if($booking->room_id == $room->id) {
                                            $startH = substr($booking->start_time, 0, 5);
                                            $endH = substr($booking->end_time, 0, 5);
                                            
                                            if($hour >= $startH && $hour < $endH) {
                                                $isBooked = true;
                                                $bookedBy = $booking->customer_name;
                                                break;
                                            }
                                        }
                                    }
                                @endphp

                                <td class="py-5 px-1 text-center">
                                    @if($isBooked)
                                        <!-- Slot Terisi (Merah Elegan) -->
                                        <div class="mx-auto w-9 h-9 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center font-bold text-xs cursor-pointer group relative shadow-xs transition-all hover:scale-105" title="Terisi oleh: {{ $bookedBy }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            
                                            <!-- Tooltip Popover -->
                                            <div class="absolute bottom-full mb-2 hidden group-hover:block bg-n-charcoal text-white text-[11px] font-normal py-1.5 px-3 rounded-lg whitespace-nowrap z-20 shadow-xl">
                                                <span class="text-rose-400 font-semibold">Terisi:</span> {{ $bookedBy }}
                                            </div>
                                        </div>
                                    @else
                                        <!-- Slot Kosong (Minimalis / Bersih) -->
                                        <div class="mx-auto w-9 h-9 rounded-xl bg-gray-50/80 border border-gray-100 text-emerald-600 flex items-center justify-center text-xs transition-all hover:bg-emerald-50/50 hover:border-emerald-200 group" title="Tersedia">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 group-hover:scale-125 transition-transform"></span>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($hours) + 1 }}" class="py-12 text-center text-n-gray font-light">Tidak ada ruangan aktif saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table Section: Booking Terbaru -->
    <div class="bg-white rounded-3xl border border-n-border/60 shadow-sm overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-n-border/40 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-n-charcoal text-xl tracking-tight">Booking Terbaru</h3>
                <p class="text-xs text-n-gray font-light mt-1">Daftar transaksi pesanan ruang dan tambahan F&B terbaru.</p>
            </div>
            <a href="{{ route('admin.bookings.index') }}" class="text-xs sm:text-sm font-semibold text-n-primary hover:underline flex items-center gap-1">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-[#f8fafc] text-[11px] font-bold text-n-gray uppercase tracking-wider border-b border-n-border/40">
                        <th class="py-4 px-6 sm:px-8">Pelanggan</th>
                        <th class="py-4 px-6 sm:px-8">Ruangan</th>
                        <th class="py-4 px-6 sm:px-8">Jadwal</th>
                        <th class="py-4 px-6 sm:px-8">Total</th>
                        <th class="py-4 px-6 sm:px-8">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($recentBookings ?? [] as $booking)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-4 px-6 sm:px-8 font-semibold text-n-charcoal">
                                {{ $booking->customer_name }}
                            </td>
                            <td class="py-4 px-6 sm:px-8 text-n-gray font-medium">
                                {{ $booking->room->name ?? '-' }}
                            </td>
                            <td class="py-4 px-6 sm:px-8 text-n-gray text-xs">
                                {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 sm:px-8 font-bold text-n-charcoal">
                                Rp{{ number_format($booking->total_price, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 sm:px-8">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                    {{ $booking->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $booking->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $booking->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : '' }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-n-gray font-light">
                                Belum ada riwayat booking.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection