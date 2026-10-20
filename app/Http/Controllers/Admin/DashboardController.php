<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\BlockedDate;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $todayRevenue = Booking::whereDate('booking_date', today())
                        ->where('status', 'confirmed')
                        ->sum('total_price');
                        
        $activeRoomsCount = Room::where('status', 'active')->count();
        $pendingBookingsCount = Booking::where('status', 'pending')->count();
        $todayBookingsCount = Booking::whereDate('booking_date', today())->count();
        
        $recentBookings = Booking::with(['room'])->latest()->take(5)->get();

        // ── Data untuk Jendela / Modal Kalender Sebulan Penuh ──
        $currentMonth = $request->input('month', date('Y-m'));
        $startDate = Carbon::parse($currentMonth . '-01')->startOfMonth();
        $endDate = Carbon::parse($currentMonth . '-01')->endOfMonth();

        $blockedDates = BlockedDate::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('reason', 'date')
            ->toArray();

        $dailyBookings = Booking::whereBetween('booking_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('status', 'confirmed')
            ->selectRaw('booking_date, count(*) as total_visitors')
            ->groupBy('booking_date')
            ->pluck('total_visitors', 'booking_date')
            ->toArray();

        return view('admin.dashboard', compact(
            'todayRevenue',
            'activeRoomsCount',
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