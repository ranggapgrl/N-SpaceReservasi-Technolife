@extends('admin.layouts.app')

@section('page_title', 'Manajemen Booking')

@section('content')

    {{--
        ══ BAR PENCARIAN ══════════════════════════════════════════════════════
        Form GET sederhana — saat submit, parameter 'search' ditambahkan ke URL.
        Pencarian dilakukan di BookingController@index berdasarkan:
        - booking_code, customer_name, customer_phone
    --}}
    <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex gap-2 mb-5">
        {{-- Pertahankan filter status yang sedang aktif saat mencari --}}
        @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        @if($ktmFilter)
            <input type="hidden" name="ktm" value="{{ $ktmFilter }}">
        @endif
        <input type="text"
            name="search"
            value="{{ $search ?? '' }}"
            placeholder="Cari kode, nama, atau telepon..."
            class="flex-1 max-w-xs px-4 py-2 border border-n-border rounded-xl text-sm focus:outline-none focus:border-n-primary">
        <button type="submit"
                class="px-4 py-2 bg-n-primary text-white rounded-xl text-sm font-medium hover:bg-n-primary/90 transition-colors">
            Cari
        </button>
        @if($search)
        <a href="{{ route('admin.bookings.index', array_filter(['status' => $status, 'ktm' => $ktmFilter])) }}"
        class="px-4 py-2 bg-n-bg border border-n-border text-n-gray rounded-xl text-sm hover:bg-white transition-colors">
            Reset
        </a>
        @endif
    </form>

    {{--
        ══ TAB FILTER STATUS BOOKING ═══════════════════════════════════════════
        Filter berdasarkan status booking (pending/confirmed/cancelled).
        URL berubah saat klik tab, controller membaca parameter ?status=...
    --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @php
            $tabs = [
                ''          => 'Semua',
                'pending'   => 'Pending',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
            ];
        @endphp
        <div class="flex space-x-1 bg-white border border-n-border rounded-xl p-1 inline-flex">
            @foreach($tabs as $key => $label)
                <a href="{{ route('admin.bookings.index', array_filter(['status' => $key ?: null, 'search' => $search, 'ktm' => $ktmFilter])) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                        {{ $status === ($key ?: null) || ($key === '' && !$status)
                            ? 'bg-n-primary text-white'
                            : 'text-n-gray hover:text-n-charcoal' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{--
            ══ FILTER KTM (BARU) ═══════════════════════════════════════════════
            Filter khusus untuk melihat booking mahasiswa berdasarkan status KTM.
            Badge orange menunjukkan jumlah KTM yang menunggu verifikasi.
        --}}
        <div class="flex space-x-1 bg-white border border-n-border rounded-xl p-1 inline-flex ml-0 md:ml-2">
            <span class="px-3 py-2 text-xs text-n-gray font-medium self-center">KTM:</span>
            <a href="{{ route('admin.bookings.index', array_filter(['ktm' => null, 'status' => $status, 'search' => $search])) }}"
               class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ !$ktmFilter ? 'bg-n-primary text-white' : 'text-n-gray hover:text-n-charcoal' }}">
                Semua
            </a>
            <a href="{{ route('admin.bookings.index', array_filter(['ktm' => 'pending', 'status' => $status, 'search' => $search])) }}"
               class="px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 {{ $ktmFilter === 'pending' ? 'bg-n-primary text-white' : 'text-n-gray hover:text-n-charcoal' }}">
                Perlu Diverifikasi
                @if($pendingKtmCount > 0)
                {{-- Badge jumlah KTM pending --}}
                <span class="bg-orange-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center">
                    {{ $pendingKtmCount }}
                </span>
                @endif
            </a>
            <a href="{{ route('admin.bookings.index', array_filter(['ktm' => 'approved', 'status' => $status, 'search' => $search])) }}"
               class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $ktmFilter === 'approved' ? 'bg-n-primary text-white' : 'text-n-gray hover:text-n-charcoal' }}">
                Disetujui
            </a>
            <a href="{{ route('admin.bookings.index', array_filter(['ktm' => 'rejected', 'status' => $status, 'search' => $search])) }}"
               class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $ktmFilter === 'rejected' ? 'bg-n-primary text-white' : 'text-n-gray hover:text-n-charcoal' }}">
                Ditolak
            </a>
        </div>
    </div>

    {{-- Info hasil pencarian --}}
    @if($search)
    <p class="text-sm text-n-gray mb-4">
        Menampilkan hasil pencarian untuk "<strong class="text-n-charcoal">{{ $search }}</strong>"
        — {{ $bookings->total() }} hasil ditemukan.
    </p>
    @endif

    <div class="bg-white rounded-2xl border border-n-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-n-bg text-n-gray">
                    <tr>
                        <th class="px-6 py-4 font-medium">Kode Booking</th>
                        <th class="px-6 py-4 font-medium">Pelanggan</th>
                        <th class="px-6 py-4 font-medium">Kategori</th>
                        <th class="px-6 py-4 font-medium">Ruangan</th>
                        <th class="px-6 py-4 font-medium">Jadwal</th>
                        <th class="px-6 py-4 font-medium">Total</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        {{-- Kolom KTM baru --}}
                        <th class="px-6 py-4 font-medium">KTM</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-n-border text-n-charcoal">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-n-bg/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-n-primary">{{ $booking->booking_code }}</td>
                        <td class="px-6 py-4">
                            {{ $booking->customer_name }}<br>
                            <span class="text-xs text-n-gray">{{ $booking->customer_phone }}</span>
                        </td>
                        {{-- Kolom kategori pengunjung (baru) --}}
                        <td class="px-6 py-4">
                            @if($booking->visitor_category === 'mahasiswa')
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs font-semibold">Mahasiswa</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs font-semibold">Umum</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $booking->room?->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}<br>
                            <span class="text-xs text-n-gray">{{ $booking->duration }} hari</span>
                        </td>
                        <td class="px-6 py-4 font-medium">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @if($booking->status === 'confirmed')
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Confirmed</span>
                            @elseif($booking->status === 'pending')
                                <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold">Cancelled</span>
                            @endif
                        </td>
                        {{--
                            Kolom status KTM (baru):
                            Hanya relevan untuk mahasiswa. Umum menampilkan tanda "—".
                        --}}
                        <td class="px-6 py-4">
                            @if($booking->visitor_category === 'mahasiswa')
                                @if($booking->ktm_status === 'approved')
                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-semibold">✓ OK</span>
                                @elseif($booking->ktm_status === 'rejected')
                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-semibold">✕ Ditolak</span>
                                @elseif($booking->ktm_status === 'pending')
                                    <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full text-xs font-semibold">⏳ Pending</span>
                                @else
                                    <span class="text-xs text-n-gray">Belum upload</span>
                                @endif
                            @else
                                <span class="text-xs text-n-gray">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end space-x-2">
                                {{-- Tombol Detail --}}
                                <a href="{{ route('admin.bookings.show', $booking) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-n-bg border border-n-border text-n-charcoal rounded-lg text-xs font-medium hover:border-n-primary hover:text-n-primary transition-colors">
                                    Detail
                                </a>

                                {{-- Tombol Konfirmasi --}}
                                @if($booking->status !== 'confirmed')
                                <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1.5 bg-green-50 border border-green-200 text-green-700 rounded-lg text-xs font-medium hover:bg-green-100 transition-colors">
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
                                            class="inline-flex items-center px-3 py-1.5 bg-red-50 border border-red-200 text-red-600 rounded-lg text-xs font-medium hover:bg-red-100 transition-colors">
                                        Batal
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-n-gray">
                            Tidak ada booking {{ $status ? "dengan status \"$status\"" : '' }}
                            {{ $search ? "yang cocok dengan pencarian \"$search\"" : '' }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="px-6 py-4 border-t border-n-border">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>

@endsection
