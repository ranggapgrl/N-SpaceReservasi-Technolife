<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@needyspace.com'],
            [
                'name' => 'Admin Needy Study Space',
                'password' => Hash::make('password'),
            ]
        );

        // Seed Rooms
        // Memakai updateOrCreate (kunci: name) supaya aman dijalankan berulang tanpa menggandakan data.
        Room::updateOrCreate(
            ['name' => 'Private Room 01'],
            [
                'type' => 'Private Room',
                'description' => 'Ruang pribadi yang tenang dan nyaman untuk belajar atau bekerja secara mandiri. Dilengkapi dengan pencahayaan optimal dan kursi ergonomis.',
                'capacity' => 2,
                'price_per_hour' => 25000,
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                'image_2' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80',
                'image_3' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80',
                'facilities' => ['WiFi Cepat', 'AC', 'Colokan Listrik', 'Kursi Ergonomis', 'Meja Luas', 'Pencahayaan Baca'],
                'status' => 'active',
            ]
        );

        Room::updateOrCreate(
            ['name' => 'Private Room 02'],
            [
                'type' => 'Private Room',
                'description' => 'Ruang pribadi yang tenang dan nyaman untuk belajar atau bekerja secara mandiri.',
                'capacity' => 2,
                'price_per_hour' => 25000,
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                'image_2' => null,
                'image_3' => null,
                'facilities' => ['WiFi Cepat', 'AC', 'Colokan Listrik', 'Kursi Ergonomis'],
                'status' => 'active',
            ]
        );

        Room::updateOrCreate(
            ['name' => 'Meeting Room 01'],
            [
                'type' => 'Meeting Room',
                'description' => 'Ruang rapat profesional yang ideal untuk diskusi kelompok, presentasi, atau sesi belajar bersama. Dilengkapi whiteboard dan monitor besar.',
                'capacity' => 8,
                'price_per_hour' => 50000,
                // Foto lama (photo-1582653291997-...) sudah 404, diganti foto yang masih aktif
                'image' => 'https://images.unsplash.com/photo-1600508774634-4e11d34730e2?auto=format&fit=crop&w=800&q=80',
                'image_2' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80',
                'image_3' => null,
                'facilities' => ['WiFi Cepat', 'AC', 'Whiteboard', 'Monitor HDMI', 'Colokan Listrik', 'Meja Besar', 'Proyektor'],
                'status' => 'active',
            ]
        );

        Room::updateOrCreate(
            ['name' => 'Coworking Desk 01'],
            [
                'type' => 'Coworking Space',
                'description' => 'Area coworking terbuka yang cocok untuk yang ingin suasana lebih dinamis. Tersedia meja individu di area bersama yang nyaman.',
                'capacity' => 1,
                'price_per_hour' => 15000,
                'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
                'image_2' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80',
                'image_3' => null,
                'facilities' => ['WiFi Cepat', 'AC', 'Colokan Listrik', 'Loker', 'Pantry'],
                'status' => 'active',
            ]
        );

        // Ruangan baru
        Room::updateOrCreate(
            ['name' => 'Group Study Room 01'],
            [
                'type' => 'Group Study Room',
                'description' => 'Ruang belajar kelompok yang santai untuk diskusi tugas, latihan presentasi, atau belajar bareng. Dilengkapi whiteboard dan meja yang cukup luas untuk berempat.',
                'capacity' => 4,
                'price_per_hour' => 35000,
                'image' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80',
                'image_2' => null,
                'image_3' => null,
                'facilities' => ['WiFi Cepat', 'AC', 'Whiteboard', 'Colokan Listrik', 'Meja Besar'],
                'status' => 'active',
            ]
        );

        // Seed Products
        Product::updateOrCreate(
            ['name' => 'Coffee Latte'],
            [
                'description' => 'Kopi latte panas atau dingin. Bahan pilihan, rasa premium.',
                'price' => 18000,
                'image' => 'https://images.unsplash.com/photo-1551030173-122aabc4489c?auto=format&fit=crop&w=400&q=80',
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Mineral Water'],
            [
                'description' => 'Air mineral dingin botol 600ml.',
                'price' => 5000,
                'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?auto=format&fit=crop&w=400&q=80',
                'is_active' => true,
            ]
        );

        Product::updateOrCreate(
            ['name' => 'Butter Croissant'],
            [
                'description' => 'Croissant mentega segar panggang.',
                'price' => 15000,
                'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=400&q=80',
                'is_active' => true,
            ]
        );
    }
}