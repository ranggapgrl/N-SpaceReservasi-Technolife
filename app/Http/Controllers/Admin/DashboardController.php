<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;

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

        $selectedDate = $request->input('date', date('Y-m-d'));
        $rooms = Room::where('status', 'active')->get();
        
        // Saring HANYA status 'confirmed' agar jika selesai/dibatalkan, jam langsung hijau kembali
        $bookings = Booking::where('booking_date', $selectedDate)
            ->where('status', 'confirmed')
            ->get();

        $hours = [
            '08:00', '09:00', '10:00', '11:00', '12:00', 
            '13:00', '14:00', '15:00', '16:00', '17:00',
            '18:00', '19:00', '20:00', '21:00'
        ];

        return view('admin.dashboard', compact(
            'todayRevenue',
            'activeRoomsCount',
            'pendingBookingsCount',
            'todayBookingsCount',
            'recentBookings',
            'rooms',
            'bookings',
            'hours',
            'selectedDate'
        ));
    }
}