<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::latest()->paginate(10);
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'capacity'       => 'required|integer|min:1',
            'price_per_hour' => 'required|numeric|min:0',
            'image'          => 'nullable|url|max:2048',
            'image_2'        => 'nullable|url|max:2048',
            'image_3'        => 'nullable|url|max:2048',
            'facilities'     => 'nullable|string',
            'status'         => 'required|in:active,maintenance,inactive',
        ]);

        // Convert newline-separated facilities to array
        if (!empty($validated['facilities'])) {
            $validated['facilities'] = array_values(array_filter(
                array_map('trim', explode("\n", $validated['facilities']))
            ));
        } else {
            $validated['facilities'] = [];
        }

        Room::create($validated);

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'capacity'       => 'required|integer|min:1',
            'price_per_hour' => 'required|numeric|min:0',
            'image'          => 'nullable|url|max:2048',
            'image_2'        => 'nullable|url|max:2048',
            'image_3'        => 'nullable|url|max:2048',
            'facilities'     => 'nullable|string',
            'status'         => 'required|in:active,maintenance,inactive',
        ]);

        // Convert newline-separated facilities to array
        if (!empty($validated['facilities'])) {
            $validated['facilities'] = array_values(array_filter(
                array_map('trim', explode("\n", $validated['facilities']))
            ));
        } else {
            $validated['facilities'] = [];
        }

        $room->update($validated);

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Ruangan berhasil dihapus.');
    }
}
