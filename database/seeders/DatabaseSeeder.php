<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
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

        // Seed Products (Snack & Minuman)
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
