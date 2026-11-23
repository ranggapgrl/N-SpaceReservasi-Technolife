<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Room::query()
            ->whereIn('name', ['Ruang Meeting', 'Ruangan Training'])
            ->get()
            ->each(function (Room $room): void {
                if ($room->bookings()->exists()) {
                    $room->update(['status' => 'inactive']);

                    return;
                }

                $room->delete();
            });

        $rooms = [
            [
                'name' => 'Ruang Bhaswara',
                'type' => 'Training Room',
                'description' => 'Rentang kapasitas katalog: 30-100 orang.',
                'capacity' => 100,
            ],
            [
                'name' => 'Ruang Bhaskara',
                'type' => 'Training Room',
                'description' => 'Rentang kapasitas katalog: 30-100 orang.',
                'capacity' => 100,
            ],
            [
                'name' => 'Ruang Bimantara',
                'type' => 'Training Room',
                'description' => 'Rentang kapasitas katalog: 30-48 orang.',
                'capacity' => 48,
            ],
            [
                'name' => 'Ruang Biwara',
                'type' => 'Training Room',
                'description' => 'Rentang kapasitas katalog: 30-48 orang.',
                'capacity' => 48,
            ],
            [
                'name' => 'Ruang Meeting Sadewa',
                'type' => 'Meeting Room',
                'description' => 'Luas 9 m2.',
                'capacity' => 7,
            ],
            [
                'name' => 'Ruang Meeting Danuraga',
                'type' => 'Meeting Room',
                'description' => 'Luas 15 m2.',
                'capacity' => 13,
            ],
            [
                'name' => 'Ruang Meeting Diwangkara',
                'type' => 'Meeting Room',
                'description' => 'Luas 28 m2.',
                'capacity' => 16,
            ],
            [
                'name' => 'Ruang Meeting Abimanyu',
                'type' => 'Meeting Room',
                'description' => 'Luas 28 m2.',
                'capacity' => 18,
            ],
            [
                'name' => 'Ruang Meeting Bhimasena VVIP',
                'type' => 'Meeting Room',
                'description' => 'Luas 103.7 m2.',
                'capacity' => 45,
            ],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['name' => $room['name']],
                [
                    ...$room,
                    'price_per_session' => (int) config('booking.venue_packages.per_jam.price_per_person'),
                    'facilities' => [],
                    'status' => 'active',
                ]
            );
        }
    }
}
