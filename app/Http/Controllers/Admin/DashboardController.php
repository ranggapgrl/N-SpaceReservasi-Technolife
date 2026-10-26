<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BlockedDate;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Memastikan parameter bulan sesuai format YYYY-MM sebelum dipakai untuk mengambil data kalender.
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        // Ringkasan dashboard: pendapatan terkonfirmasi hari ini, booking hari ini,
        // dan jumlah booking yang masih menunggu pembayaran.
        $todayRevenue = Booking::whereDate('booking_date', today())
                        ->where('status', 'confirmed')
                        ->sum('total_price');

        $pendingBookingsCount = Booking::where('status', 'pending')->count();
        $todayBookingsCount = Booking::whereDate('booking_date', today())->count();

        // Mengambil lima booking terbaru untuk tabel aktivitas pada dashboard.
        $recentBookings = Booking::latest()->take(5)->get();

        // Menyiapkan rentang bulan yang dipakai untuk kalender ketersediaan.
        $currentMonth = $request->input('month') ?: now()->format('Y-m');
        $startDate = Carbon::createFromFormat('!Y-m', $currentMonth)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Data bulan terpilih digunakan oleh kalender bulanan.
        $blockedDates = BlockedDate::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('reason', 'date')
            ->toArray();

        // Jumlah booking terkonfirmasi per tanggal untuk informasi kepadatan pengunjung.
        $dailyBookings = Booking::whereBetween('booking_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('status', 'confirmed')
            ->selectRaw('booking_date, count(*) as total_visitors')
            ->groupBy('booking_date')
            ->pluck('total_visitors', 'booking_date')
            ->toArray();

        // Booking per tanggal mengisi panel detail saat admin memilih hari di kalender.
        $calendarBookings = Booking::whereBetween('booking_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->latest('booking_date')
            ->get(['id', 'booking_code', 'customer_name', 'booking_date', 'status', 'total_price'])
            ->groupBy(fn (Booking $booking) => Carbon::parse($booking->booking_date)->format('Y-m-d'));

        // Ringkasan mingguan tetap mengacu pada minggu ini, walau bulan kalender sedang diganti.
        $weekStart = today();
        $weekEnd = $weekStart->copy()->addDays(6);

        // Mengambil alasan penutupan untuk setiap hari pada ringkasan tujuh hari ke depan.
        $weeklyBlockedDates = BlockedDate::whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->pluck('reason', 'date')
            ->toArray();

        // Menghitung booking terkonfirmasi per hari untuk ditampilkan pada ringkasan mingguan.
        $weeklyBookings = Booking::whereBetween('booking_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->where('status', 'confirmed')
            ->selectRaw('booking_date, count(*) as total_visitors')
            ->groupBy('booking_date')
            ->pluck('total_visitors', 'booking_date')
            ->toArray();

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
}