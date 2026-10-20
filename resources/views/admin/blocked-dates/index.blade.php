@extends('admin.layouts.app')

@section('page_title', 'Kelola Tanggal Pemesanan')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Flash message --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">
        ✓ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">
        ✕ {{ session('error') }}
    </div>
    @endif

    {{--
        FORM TUTUP TANGGAL
        POST ke route 'admin.blocked-dates.store'
        Admin mengisi tanggal dan alasan penutupan (opsional).
    --}}
    <div class="bg-white rounded-2xl border border-n-border shadow-sm p-6">
        <h2 class="font-semibold text-n-charcoal text-base mb-4">Tutup Tanggal Baru</h2>
        <form action="{{ route('admin.blocked-dates.store') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            {{-- Input tanggal — min="today" mencegah memilih tanggal yang sudah lewat --}}
            <div class="flex-1">
                <label class="block text-xs text-n-gray mb-1 font-medium">Tanggal</label>
                <input type="date" name="date" required min="{{ date('Y-m-d') }}"
                    value="{{ old('date') }}"
                    class="w-full px-3 py-2.5 border border-n-border rounded-xl text-sm focus:outline-none focus:border-n-primary">
                @error('date')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            {{-- Alasan opsional: contoh "Libur Nasional", "Maintenance AC" --}}
            <div class="flex-1">
                <label class="block text-xs text-n-gray mb-1 font-medium">Alasan (opsional)</label>
                <input type="text" name="reason" value="{{ old('reason') }}"
                    placeholder="cth: Libur Nasional, Maintenance"
                    class="w-full px-3 py-2.5 border border-n-border rounded-xl text-sm focus:outline-none focus:border-n-primary">
            </div>
            <div class="flex items-end">
            <button type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 text-white rounded-xl text-sm font-semibold transition-colors hover:opacity-90" style="background-color: #b1002c;">
                Tutup Tanggal
            </button>
            </div>
        </form>
    </div>

    {{-- DAFTAR TANGGAL YANG SUDAH DITUTUP --}}
    <div class="bg-white rounded-2xl border border-n-border shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-n-border">
            <h2 class="font-semibold text-n-charcoal text-base">Tanggal yang Ditutup</h2>
            <p class="text-xs text-n-gray mt-0.5">Pelanggan tidak dapat memesan pada tanggal-tanggal berikut.</p>
        </div>

        @forelse($blockedDates as $bd)
        <div class="flex items-center justify-between px-6 py-4 border-b border-n-border/40 last:border-0 hover:bg-gray-50/50">
            <div>
                {{-- Format tanggal yang mudah dibaca: 13 Oktober 2026 --}}
                <p class="font-semibold text-n-charcoal text-sm">
                    {{ $bd->date->isoFormat('D MMMM Y') }}
                    {{-- Tandai tanggal yang sudah lewat --}}
                    @if($bd->date->isPast())
                        <span class="ml-2 text-xs text-n-gray font-normal">(sudah lewat)</span>
                    @endif
                </p>
                <p class="text-xs text-n-gray mt-0.5">{{ $bd->reason ?? 'Tidak ada alasan' }}</p>
            </div>
            {{--
                DELETE: Membuka kembali tanggal.
                Menggunakan @method('DELETE') karena HTML form hanya mendukung GET/POST.
                Laravel akan membaca _method=DELETE dan meneruskan ke route yang benar.
            --}}
            <form action="{{ route('admin.blocked-dates.destroy', $bd) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Buka kembali tanggal ini?')"
                        class="text-xs text-red-500 hover:text-red-700 font-medium px-3 py-1.5 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                    Buka Kembali
                </button>
            </form>
        </div>
        @empty
        <div class="px-6 py-10 text-center text-n-gray text-sm">
            Belum ada tanggal yang ditutup.
        </div>
        @endforelse

        {{-- Paginasi --}}
        @if($blockedDates->hasPages())
        <div class="px-6 py-4 border-t border-n-border">
            {{ $blockedDates->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
