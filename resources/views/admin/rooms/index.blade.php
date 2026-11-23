@extends('admin.layouts.app')

@section('page_title', 'Manajemen Ruangan')

@section('content')

<div style="--color-n-primary: #B1002C; --color-n-primary-dark: #9F0028; --color-n-bg: #FBF9F8; --color-n-charcoal: #1B1C1C; --color-n-gray: #5C3F3F; --color-n-border: #EAEAEA; font-family: Inter, sans-serif;">
    @if(session('error'))
        <div role="alert" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div role="status" class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <p class="text-n-gray text-sm">Total {{ $rooms->total() }} ruangan</p>
        <a href="{{ route('admin.rooms.create') }}"
           class="inline-flex items-center text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors hover:opacity-90"
           style="background-color: #b1002c;">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            + Tambah Ruangan
        </a>
    </div>

    <div class="mb-6 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-[#4f555a]">
        Tarif berlaku untuk semua venue:
        @foreach(config('booking.venue_packages') as $package)
            {{ $package['label'] }} {{ $package['duration_hours'] }} jam Rp{{ number_format($package['price_per_person'], 0, ',', '.') }}/orang{{ $loop->last ? '.' : ',' }}
        @endforeach
        Setiap paket dikenakan Service Charge {{ config('booking.venue_service_charge_percent') }}%.
    </div>

    <div class="bg-white rounded-2xl border border-n-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-n-bg text-n-gray">
                    <tr>
                        <th class="px-6 py-4 font-medium">Nama Ruangan</th>
                        <th class="px-6 py-4 font-medium">Tipe</th>
                        <th class="px-6 py-4 font-medium">Kapasitas</th>
                        <th class="px-6 py-4 font-medium">Minimum Order</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-n-border text-n-charcoal">
                    @forelse($rooms as $room)
                    <tr class="hover:bg-n-bg/50 transition-colors">
                        <td class="px-6 py-4 font-medium">{{ $room->name }}</td>
                        <td class="px-6 py-4 text-n-gray">{{ $room->type }}</td>
                        <td class="px-6 py-4">{{ $room->capacity }} orang</td>
                        <td class="px-6 py-4">{{ $room->minimumAttendees() }} orang</td>
                        <td class="px-6 py-4">
                            @if($room->status === 'active')
                                <span class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-[#b1002c]">Aktif</span>
                            @elseif($room->status === 'maintenance')
                                <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-semibold">Maintenance</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.rooms.edit', $room) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-n-bg border border-n-border text-n-charcoal rounded-lg text-xs font-medium hover:border-[#b1002c] hover:text-[#b1002c] transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Yakin ingin menghapus ruangan ini?')"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-50 border border-red-200 text-red-600 rounded-lg text-xs font-medium hover:bg-red-100 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-n-gray">
                            Belum ada ruangan. <a href="{{ route('admin.rooms.create') }}" class="font-medium text-[#b1002c] hover:underline">Tambah sekarang</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rooms->hasPages())
            <div class="px-6 py-4 border-t border-n-border">
                {{ $rooms->links() }}
            </div>
        @endif
</div>

@endsection
