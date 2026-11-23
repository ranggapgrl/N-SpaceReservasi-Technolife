<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'facilities' => 'nullable|string',
            'status' => 'required|in:active,maintenance,inactive',
        ]);

        $minimumAttendees = (new Room(['type' => $validated['type']]))->minimumAttendees();
        if ($validated['capacity'] < $minimumAttendees) {
            throw ValidationException::withMessages([
                'capacity' => "Kapasitas minimal untuk tipe ruangan ini adalah {$minimumAttendees} orang.",
            ]);
        }

        // Keep the legacy database column populated with the base hourly rate; checkout uses package rates from config.
        $validated['price_per_session'] = (int) config('booking.venue_packages.per_jam.price_per_person');

        // Convert newline-separated facilities to array
        if (! empty($validated['facilities'])) {
            $validated['facilities'] = array_values(array_filter(
                array_map('trim', explode("\n", $validated['facilities']))
            ));
        } else {
            $validated['facilities'] = [];
        }

        $imagePath = $this->storeUploadedImage($request);
        if ($imagePath !== null) {
            $validated['image'] = $imagePath;
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
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'facilities' => 'nullable|string',
            'status' => 'required|in:active,maintenance,inactive',
        ]);

        $minimumAttendees = (new Room(['type' => $validated['type']]))->minimumAttendees();
        if ($validated['capacity'] < $minimumAttendees) {
            throw ValidationException::withMessages([
                'capacity' => "Kapasitas minimal untuk tipe ruangan ini adalah {$minimumAttendees} orang.",
            ]);
        }

        // Convert newline-separated facilities to array
        if (! empty($validated['facilities'])) {
            $validated['facilities'] = array_values(array_filter(
                array_map('trim', explode("\n", $validated['facilities']))
            ));
        } else {
            $validated['facilities'] = [];
        }

        $previousImage = $room->getRawOriginal('image');
        $imagePath = $this->storeUploadedImage($request);
        if ($imagePath !== null) {
            $validated['image'] = $imagePath;
        }

        $room->update($validated);

        if ($imagePath !== null && $previousImage !== $imagePath) {
            $this->deleteStoredImage($previousImage);
        }

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        if ($room->bookings()->exists()) {
            return back()->with('error', 'Ruangan ini sudah memiliki riwayat booking. Ubah statusnya menjadi nonaktif agar riwayat tetap tersimpan.');
        }

        $images = array_map(
            fn (string $imageField): ?string => $room->getRawOriginal($imageField),
            ['image', 'image_2', 'image_3']
        );
        $room->delete();

        foreach ($images as $image) {
            $this->deleteStoredImage($image);
        }

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }

    /**
     * Simpan foto venue yang dipilih admin ke disk publik.
     */
    private function storeUploadedImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $imagePath = $request->file('image')->store('rooms', 'public');

        if ($imagePath === false) {
            throw ValidationException::withMessages([
                'image' => 'Foto venue gagal disimpan. Silakan coba lagi.',
            ]);
        }

        return $imagePath;
    }

    /**
     * Hapus hanya file venue yang disimpan aplikasi, bukan URL atau data gambar lama.
     */
    private function deleteStoredImage(?string $image): void
    {
        if (! $image || preg_match('/\Arooms\/[A-Za-z0-9_-]+\.(?:jpe?g|png|webp)\z/i', $image) !== 1) {
            return;
        }

        Storage::disk('public')->delete($image);
    }
}
