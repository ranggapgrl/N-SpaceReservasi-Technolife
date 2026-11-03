@extends('admin.layouts.app')

@section('page_title', 'Laporan Pendapatan')

@section('content')
@php
    use Carbon\Carbon;
@endphp

<div class="mx-auto max-w-6xl space-y-8 pb-16">

    {{-- Header & Navigasi Filter --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Laporan Pendapatan</h1>
            <p class="mt-1 text-sm text-gray-500">Pengecekan rincian transaksi dan omzet berdasarkan periode.</p>
        </div>

        {{-- Form Filter Bulan & Tahun --}}
        <form method="GET" action="{{ route('admin.revenue.index') }}" class="flex flex-wrap items-center gap-2">
            <select name="month" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#b1002c] focus:outline-none focus:ring-1 focus:ring-[#b1002c]">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $selectedMonth == sprintf('%02d', $m) ? 'selected' : '' }}>
                        {{ Carbon::create(null, $m, 1)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>

            <select name="year" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-[#b1002c] focus:outline-none focus:ring-1 focus:ring-[#b1002c]">
                @for($y = date('Y'); $y >= 2025; $y--)
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <button type="submit" class="rounded-lg bg-[#b1002c] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#8f0023]">
                Filter
            </button>
        </form>
    </div>

    {{-- Kartu Ringkasan Filter --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-gray-500">
                Pendapatan Bulan {{ Carbon::create(null, (int)$selectedMonth, 1)->translatedFormat('F') }} {{ $selectedYear }}
            </p>
            <p class="mt-2 text-3xl font-semibold tabular-nums tracking-tight text-[#b1002c]">
                <span class="mr-1 text-lg font-normal text-gray-400">Rp</span>{{ number_format($filteredRevenue, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-gray-500">Total Pendapatan Tahun {{ $selectedYear }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums tracking-tight text-gray-900">
                <span class="mr-1 text-lg font-normal text-gray-400">Rp</span>{{ number_format($yearRevenue, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Tabel Rincian Transaksi --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h3 class="font-semibold text-gray-900">Daftar Pembayaran Lunas</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/50 text-gray-500">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-medium">Kode Booking</th>
                        <th scope="col" class="px-6 py-3 font-medium">Pelanggan</th>
                        <th scope="col" class="px-6 py-3 font-medium">Tanggal Transaksi</th>
                        <th scope="col" class="px-6 py-3 font-medium">Tanggal Kunjungan</th>
                        <th scope="col" class="px-6 py-3 text-right font-medium">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-[#b1002c]">
                                <a href="{{ route('admin.bookings.show', $trx) }}" class="hover:underline">
                                    {{ $trx->booking_code }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $trx->customer_name }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($trx->visitor_category) }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $trx->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ Carbon::parse($trx->booking_date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold tabular-nums text-gray-900">
                                Rp{{ number_format($trx->payment->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data transaksi pada bulan {{ Carbon::create(null, (int)$selectedMonth, 1)->translatedFormat('F') }} {{ $selectedYear }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
        <div class="border-t border-gray-200 px-6 py-4">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection