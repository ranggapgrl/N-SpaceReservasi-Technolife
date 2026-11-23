<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Memastikan parameter bulan sesuai format YYYY-MM sebelum dipakai untuk mengambil data kalender.
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        // Ringkasan dashboard: pembayaran lunas untuk kunjungan hari ini, booking hari ini,
        // dan jumlah booking yang masih menunggu pembayaran.
        $todayRevenue = Payment::verifiedPaid()
            ->whereHas('booking', fn ($query) => $query->where(function ($dateQuery): void {
                $dateQuery->whereDate('booking_date', today())
                    ->orWhereHas('bookingDates', fn ($bookingDateQuery) => $bookingDateQuery->whereDate('date', today()));
            }))
            ->sum('amount');

        $pendingBookingsCount = Booking::where('status', 'pending')->count();
        $todayBookingsCount = Booking::whereDate('booking_date', today())
            ->orWhereHas('bookingDates', fn ($query) => $query->whereDate('date', today()))
            ->count();

        // Mengambil lima booking terbaru untuk tabel aktivitas pada dashboard.
        $recentBookings = Booking::latest()->take(5)->get();

        // Menyiapkan rentang bulan yang dipakai untuk kalender ketersediaan.
        $currentMonth = $request->input('month') ?: now()->format('Y-m');
        $startDate = Carbon::createFromFormat('!Y-m', $currentMonth)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Data bulan terpilih digunakan oleh kalender bulanan.
        $blockedDates = BlockedDate::whereDate('date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('date', '<=', $endDate->format('Y-m-d'))
            ->pluck('reason', 'date')
            ->toArray();

        // Ambil booking yang jatuh pada tanggal pilihan; booking lama tetap memakai booking_date/duration.
        $monthBookings = Booking::where('status', 'confirmed')
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->whereHas('bookingDates', fn ($dateQuery) => $dateQuery
                    ->whereDate('date', '>=', $startDate->format('Y-m-d'))
                    ->whereDate('date', '<=', $endDate->format('Y-m-d')))
                    ->orWhere(function ($legacyQuery) use ($startDate, $endDate): void {
                        $legacyQuery->whereDoesntHave('bookingDates')
                            ->whereBetween('booking_date', [
                                $startDate->copy()->subDays(29)->format('Y-m-d'),
                                $endDate->format('Y-m-d'),
                            ]);
                    });
            })
            ->with('bookingDates')
            ->get(['id', 'booking_date', 'duration']);

        $dailyBookings = [];
        foreach ($monthBookings as $booking) {
            foreach ($this->bookingDateValues($booking) as $bookingDate) {
                if ($bookingDate >= $startDate->format('Y-m-d') && $bookingDate <= $endDate->format('Y-m-d')) {
                    $dailyBookings[$bookingDate] = ($dailyBookings[$bookingDate] ?? 0) + 1;
                }
            }
        }

        // Booking per tanggal mengisi panel detail saat admin memilih hari di kalender.
        $calendarBookingRecords = Booking::where(function ($query) use ($startDate, $endDate): void {
            $query->whereHas('bookingDates', fn ($dateQuery) => $dateQuery
                ->whereDate('date', '>=', $startDate->format('Y-m-d'))
                ->whereDate('date', '<=', $endDate->format('Y-m-d')))
                ->orWhere(function ($legacyQuery) use ($startDate, $endDate): void {
                    $legacyQuery->whereDoesntHave('bookingDates')
                        ->whereBetween('booking_date', [
                            $startDate->copy()->subDays(29)->format('Y-m-d'),
                            $endDate->format('Y-m-d'),
                        ]);
                });
        })
            ->latest('booking_date')
            ->with('bookingDates')
            ->get(['id', 'booking_code', 'customer_name', 'booking_date', 'duration', 'status', 'total_price']);

        $calendarBookings = collect();
        foreach ($calendarBookingRecords as $booking) {
            foreach ($this->bookingDateValues($booking) as $bookingDate) {
                if ($bookingDate >= $startDate->format('Y-m-d') && $bookingDate <= $endDate->format('Y-m-d')) {
                    $calendarBookings[$bookingDate] ??= collect();
                    $calendarBookings[$bookingDate]->push($booking);
                }
            }
        }

        // Ringkasan mingguan tetap mengacu pada minggu ini, walau bulan kalender sedang diganti.
        $weekStart = today();
        $weekEnd = $weekStart->copy()->addDays(6);

        // Mengambil alasan penutupan untuk setiap hari pada ringkasan tujuh hari ke depan.
        $weeklyBlockedDates = BlockedDate::whereDate('date', '>=', $weekStart->format('Y-m-d'))
            ->whereDate('date', '<=', $weekEnd->format('Y-m-d'))
            ->pluck('reason', 'date')
            ->toArray();

        // Hitung booking terkonfirmasi untuk setiap tanggal pada ringkasan mingguan.
        $weeklyBookingRecords = Booking::where('status', 'confirmed')
            ->where(function ($query) use ($weekStart, $weekEnd): void {
                $query->whereHas('bookingDates', fn ($dateQuery) => $dateQuery
                    ->whereDate('date', '>=', $weekStart->format('Y-m-d'))
                    ->whereDate('date', '<=', $weekEnd->format('Y-m-d')))
                    ->orWhere(function ($legacyQuery) use ($weekStart, $weekEnd): void {
                        $legacyQuery->whereDoesntHave('bookingDates')
                            ->whereBetween('booking_date', [
                                $weekStart->copy()->subDays(29)->format('Y-m-d'),
                                $weekEnd->format('Y-m-d'),
                            ]);
                    });
            })
            ->with('bookingDates')
            ->get(['id', 'booking_date', 'duration']);

        $weeklyBookings = [];
        foreach ($weeklyBookingRecords as $booking) {
            foreach ($this->bookingDateValues($booking) as $bookingDate) {
                if ($bookingDate >= $weekStart->format('Y-m-d') && $bookingDate <= $weekEnd->format('Y-m-d')) {
                    $weeklyBookings[$bookingDate] = ($weeklyBookings[$bookingDate] ?? 0) + 1;
                }
            }
        }

        // Mengirim seluruh ringkasan dan data kalender ke halaman dashboard admin.
        return view('admin.dashboard', compact(
            'todayRevenue',
            'pendingBookingsCount',
            'todayBookingsCount',
            'recentBookings',
            'blockedDates',
            'dailyBookings',
            'calendarBookings',
            'weeklyBlockedDates',
            'weeklyBookings',
            'currentMonth',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Return selected dates for new bookings and the original continuous range for legacy bookings.
     */
    private function bookingDateValues(Booking $booking): array
    {
        if ($booking->bookingDates->isNotEmpty()) {
            return $booking->bookingDates
                ->map(fn ($bookingDate): string => $bookingDate->date->format('Y-m-d'))
                ->all();
        }

        $startDate = Carbon::parse($booking->booking_date);

        return collect(range(0, max(0, (int) $booking->duration - 1)))
            ->map(fn (int $offset): string => $startDate->copy()->addDays($offset)->format('Y-m-d'))
            ->all();
    }

    public function revenue(Request $request)
    {
        // Periode tetap mengikuti tanggal booking; hanya pembayaran yang sudah lunas dihitung.
        $selectedMonth = $request->input('month', Carbon::now()->format('m'));
        $selectedYear = $request->input('year', Carbon::now()->year);

        $periodPayments = Payment::verifiedPaid()
            ->whereHas('booking', function ($query) use ($selectedMonth, $selectedYear): void {
                $query->whereYear('created_at', $selectedYear)
                    ->whereMonth('created_at', $selectedMonth);
            });

        // Jumlahkan nominal pada catatan pembayaran, bukan total booking yang belum tentu dibayar.
        $filteredRevenue = (clone $periodPayments)->sum('amount');

        $yearRevenue = Payment::verifiedPaid()
            ->whereHas('booking', fn ($query) => $query->whereYear('created_at', $selectedYear))
            ->sum('amount');

        // Rincian hanya menampilkan booking dengan pembayaran berstatus lunas.
        $transactions = Booking::with('payment')
            ->whereYear('created_at', $selectedYear)
            ->whereMonth('created_at', $selectedMonth)
            ->whereHas('payment', fn ($query) => $query->verifiedPaid())
            ->latest()
            ->paginate(15);

        return view('admin.revenue.index', compact(
            'filteredRevenue',
            'yearRevenue',
            'selectedMonth',
            'selectedYear',
            'transactions'
        ));
    }
}
