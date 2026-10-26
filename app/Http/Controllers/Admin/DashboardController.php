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
        $currentMonth = $request->input('month', date('Y-m'));
        $startDate = Carbon::parse($currentMonth . '-01')->startOfMonth();
        $endDate = Carbon::parse($currentMonth . '-01')->endOfMonth();

        // Alasan tanggal yang ditutup ditampilkan pada kalender dan ringkasan mingguan.
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

        // Mengirim seluruh ringkasan dan data kalender ke halaman dashboard admin.
        return view('admin.dashboard', compact(
            'todayRevenue',
            'pendingBookingsCount',
            'todayBookingsCount',
            'recentBookings',
            'blockedDates',
            'dailyBookings',
            'currentMonth',
            'startDate',
            'endDate'
        ));
    }
}