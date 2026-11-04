@extends('admin.layouts.app')

@section('page_title', 'Laporan Pendapatan')

@section('content')
@php
    use Carbon\Carbon;

    $selectedPeriod = Carbon::createFromDate((int) $selectedYear, (int) $selectedMonth, 1)
        ->translatedFormat('F Y');
@endphp

<style>
    .revenue-report {
        font-family: Inter, sans-serif;
    }

    .revenue-report h2,
    .revenue-report h3,
    .revenue-report h4 {
        font-family: Poppins, sans-serif;
    }
</style>

<div class="revenue-report mx-auto max-w-7xl space-y-8 pb-12">
    {{-- Judul dan filter dipisahkan supaya periode laporan selalu terlihat jelas. --}}
    <header class="flex flex-col gap-6 border-b border-[#EAEAEA] pb-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-[#B1002C]">Keuangan</p>
            <h2 class="mt-2 text-[28px] font-semibold leading-[1.3] tracking-tight text-[#1B1C1C] sm:text-4xl">
                Laporan pendapatan
            </h2>
            <p class="mt-2 max-w-2xl text-base leading-[1.6] text-[#5C3F3F]">
                Pantau nominal pembayaran yang sudah dikonfirmasi, beserta rincian booking pada periode pilihan.
            </p>
        </div>

        <form method="GET" action="{{ route('admin.revenue.index') }}"
              class="grid grid-cols-2 gap-3 rounded-xl border border-[#EAEAEA] bg-white p-4 sm:flex sm:items-end">
            <div class="min-w-0">
                <label for="revenue-month" class="mb-1.5 block text-sm font-semibold tracking-[0.02em] text-[#1B1C1C]">Bulan</label>
                <select id="revenue-month" name="month"
                        class="min-h-10 w-full rounded-xl border border-[#EAEAEA] bg-white px-3 text-sm text-[#1B1C1C] outline-none focus:border-[#B1002C] focus:ring-2 focus:ring-[#B1002C]/15 sm:min-w-36">
                    @for($month = 1; $month <= 12; $month++)
                        <option value="{{ sprintf('%02d', $month) }}" {{ $selectedMonth == sprintf('%02d', $month) ? 'selected' : '' }}>
                            {{ Carbon::create(null, $month, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="min-w-0">
                <label for="revenue-year" class="mb-1.5 block text-sm font-semibold tracking-[0.02em] text-[#1B1C1C]">Tahun</label>
                <select id="revenue-year" name="year"
                        class="min-h-10 w-full rounded-xl border border-[#EAEAEA] bg-white px-3 text-sm text-[#1B1C1C] outline-none focus:border-[#B1002C] focus:ring-2 focus:ring-[#B1002C]/15 sm:min-w-28">
                    @for($year = date('Y'); $year >= 2025; $year--)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <button type="submit"
                    class="col-span-2 inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-[#B1002C] px-5 text-sm font-semibold text-white transition-colors hover:bg-[#9F0028] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B1002C] sm:col-span-1">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18l-7 8v6l-4 2v-8L3 4Z"/>
                </svg>
                Terapkan
            </button>
        </form>
    </header>

    {{-- Keterangan periode dan verifikasi mencegah angka dianggap sebagai semua booking atau pembayaran simulasi. --}}
    <aside class="flex gap-3 rounded-xl border border-[#EAEAEA] bg-white px-4 py-4 sm:px-5" aria-label="Cara membaca laporan">
        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#FBF9F8] text-[#B1002C]" aria-hidden="true">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16v-4m0-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </span>
        <p class="text-base leading-[1.6] text-[#5C3F3F]">
            Laporan ini dikelompokkan berdasarkan <strong class="font-semibold text-[#1B1C1C]">bulan booking dibuat</strong>.
            Hanya pembayaran lunas yang sudah diverifikasi server Midtrans yang dihitung; booking menunggu dan pembayaran simulasi tidak termasuk.
        </p>
    </aside>

    <section aria-label="Ringkasan pendapatan" class="grid gap-4 sm:grid-cols-2">
        <article class="rounded-xl border border-[#EAEAEA] border-l-4 border-l-[#B1002C] bg-white p-6">
            <p class="text-sm font-medium tracking-[0.02em] text-[#5C3F3F]">Pendapatan booking bulan {{ $selectedPeriod }}</p>
            <p class="mt-3 break-words text-3xl font-semibold tabular-nums tracking-tight text-[#1B1C1C] sm:text-4xl">
                <span class="mr-1 text-lg font-medium text-[#5F5E5E]">Rp</span>{{ number_format($filteredRevenue, 0, ',', '.') }}
            </p>
            <p class="mt-3 text-xs leading-5 text-[#5C3F3F]">Akumulasi pembayaran terverifikasi dari booking yang dibuat pada bulan ini.</p>
        </article>

        <article class="rounded-xl border border-[#EAEAEA] bg-white p-6">
            <p class="text-sm font-medium tracking-[0.02em] text-[#5C3F3F]">Pendapatan booking tahun {{ $selectedYear }}</p>
            <p class="mt-3 break-words text-3xl font-semibold tabular-nums tracking-tight text-[#1B1C1C] sm:text-4xl">
                <span class="mr-1 text-lg font-medium text-[#5F5E5E]">Rp</span>{{ number_format($yearRevenue, 0, ',', '.') }}
            </p>
            <p class="mt-3 text-xs leading-5 text-[#5C3F3F]">Total pembayaran terverifikasi untuk booking yang dibuat sepanjang tahun tersebut.</p>
        </article>
    </section>

    <section aria-labelledby="transactions-heading" class="overflow-hidden rounded-xl border border-[#EAEAEA] bg-white">
        <div class="flex flex-col gap-1 border-b border-[#EAEAEA] px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h3 id="transactions-heading" class="text-[22px] font-semibold leading-[1.4] text-[#1B1C1C]">Pembayaran terverifikasi</h3>
                <p class="mt-1 text-sm text-[#5C3F3F]">Booking dibuat pada {{ $selectedPeriod }}.</p>
            </div>
            <span class="text-sm tabular-nums text-[#5F5E5E]">{{ number_format($transactions->total(), 0, ',', '.') }} transaksi</span>
        </div>

        @if($transactions->isEmpty())
            <div class="px-5 py-12 text-center sm:px-8">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#FBF9F8] text-[#B1002C]" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 7h16M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm3 8h4"/>
                    </svg>
                </span>
                <h4 class="mt-4 text-[22px] font-semibold leading-[1.4] text-[#1B1C1C]">Belum ada pembayaran terverifikasi</h4>
                <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-[#5C3F3F]">
                    Tidak ada pembayaran lunas yang terverifikasi untuk booking yang dibuat pada {{ $selectedPeriod }}.
                </p>
            </div>
        @else
            {{-- Kartu ringkas menggantikan tabel lebar pada layar kecil. --}}
            <ul class="divide-y divide-[#EAEAEA] md:hidden">
                @foreach($transactions as $transaction)
                    <li class="space-y-3 px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('admin.bookings.show', $transaction) }}"
                                   class="break-all text-sm font-semibold text-[#B1002C] underline-offset-2 hover:underline">
                                    {{ $transaction->booking_code }}
                                </a>
                                <p class="mt-1 truncate text-sm font-medium text-[#1B1C1C]">{{ $transaction->customer_name }}</p>
                                <p class="mt-0.5 text-xs text-[#5F5E5E]">{{ $transaction->visitor_category_label ?? ucfirst($transaction->visitor_category ?? '-') }}</p>
                            </div>
                            <p class="shrink-0 text-right text-sm font-semibold tabular-nums text-[#1B1C1C]">
                                Rp{{ number_format($transaction->payment->amount, 0, ',', '.') }}
                            </p>
                        </div>
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 border-t border-gray-100 pt-3 text-xs">
                            <div>
                                <dt class="text-[#5F5E5E]">Booking dibuat</dt>
                                <dd class="mt-0.5 font-medium text-[#5C3F3F]">{{ $transaction->created_at->translatedFormat('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-[#5F5E5E]">Pembayaran dikonfirmasi</dt>
                                <dd class="mt-0.5 font-medium text-[#5C3F3F]">
                                    {{ $transaction->payment->paid_at ? Carbon::parse($transaction->payment->paid_at)->translatedFormat('d M Y, H:i') : '—' }}
                                </dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-[#5F5E5E]">Tanggal kunjungan</dt>
                                <dd class="mt-0.5 font-medium text-[#5C3F3F]">{{ Carbon::parse($transaction->booking_date)->translatedFormat('d M Y') }}</dd>
                            </div>
                        </dl>
                    </li>
                @endforeach
            </ul>

            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="bg-[#FBF9F8] text-xs uppercase tracking-wide text-[#5F5E5E]">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 font-semibold">Kode booking</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold">Pelanggan</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold">Booking dibuat</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold">Pembayaran dikonfirmasi</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold">Tanggal kunjungan</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-semibold">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EAEAEA]">
                        @foreach($transactions as $transaction)
                            <tr class="transition-colors hover:bg-[#FBF9F8]">
                                <td class="whitespace-nowrap px-5 py-4">
                                    <a href="{{ route('admin.bookings.show', $transaction) }}" class="font-semibold text-[#B1002C] underline-offset-2 hover:underline">
                                        {{ $transaction->booking_code }}
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-[#1B1C1C]">{{ $transaction->customer_name }}</p>
                                    <p class="mt-0.5 text-xs text-[#5F5E5E]">{{ $transaction->visitor_category_label ?? ucfirst($transaction->visitor_category ?? '-') }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-[#5C3F3F]">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-[#5C3F3F]">
                                    {{ $transaction->payment->paid_at ? Carbon::parse($transaction->payment->paid_at)->translatedFormat('d M Y, H:i') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-[#5C3F3F]">{{ Carbon::parse($transaction->booking_date)->translatedFormat('d M Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right font-semibold tabular-nums text-[#1B1C1C]">
                                    Rp{{ number_format($transaction->payment->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="border-t border-[#EAEAEA] px-4 py-4 sm:px-6">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
