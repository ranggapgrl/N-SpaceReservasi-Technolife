<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use Illuminate\Http\Request;

class BlockedDateController extends Controller
{
    // Tampilkan semua tanggal yang diblokir, diurutkan dari yang terbaru
    public function index()
    {
        $blockedDates = BlockedDate::orderBy('date', 'asc')->paginate(15);
        return view('admin.blocked-dates.index', compact('blockedDates'));
    }

    // Simpan tanggal baru yang diblokir
    public function store(Request $request)
    {
        $request->validate([
            // 'after_or_equal:today' → tidak bisa menutup tanggal yang sudah lewat
            'date'   => 'required|date|after_or_equal:today|unique:blocked_dates,date',
            'reason' => 'nullable|string|max:255',
        ], [
            'date.unique'            => 'Tanggal ini sudah ditutup sebelumnya.',
            'date.after_or_equal'    => 'Tidak bisa menutup tanggal yang sudah lewat.',
        ]);

        BlockedDate::create($request->only('date', 'reason'));

        return back()->with('success', 'Tanggal ' . $request->date . ' berhasil ditutup.');
    }

    // Hapus tanggal yang diblokir (membuka kembali tanggal tersebut)
    public function destroy(BlockedDate $blockedDate)
    {
        $date = $blockedDate->date->format('Y-m-d');
        $blockedDate->delete();
        return back()->with('success', 'Tanggal ' . $date . ' berhasil dibuka kembali.');
    }
}
