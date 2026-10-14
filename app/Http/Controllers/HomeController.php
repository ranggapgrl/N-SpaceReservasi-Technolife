<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Get one representative room per type (with facilities)
        $roomTypes = Room::where('status', 'active')
            ->get()
            ->groupBy('type')
            ->map(fn($rooms) => $rooms->first());

        $products = Product::where('is_active', true)->take(4)->get();

        return view('home', compact('roomTypes', 'products'));
    }
}
