<?php

namespace Database\Seeders;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]
        );

        $member = User::firstOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Member User',
                'username' => 'member',
                'password' => Hash::make('member'),
                'role' => 'member',
            ]
        );

        Stock::firstOrCreate(
            ['user_id' => $member->id, 'name' => 'Mechanical Keyboard RGB'],
            [
                'sku' => 'SKU-KB-001',
                'description' => 'Keyboard mekanikal hotswap dengan switch tactile dan backlight RGB.',
                'quantity' => 25,
                'unit' => 'pcs',
                'price' => 450000,
                'min_stock' => 5,
                'status' => 'available',
            ]
        );

        Stock::firstOrCreate(
            ['user_id' => $member->id, 'name' => 'Mouse Wireless Ergonomis'],
            [
                'sku' => 'SKU-MS-002',
                'description' => 'Mouse wireless dual mode Bluetooth & 2.4GHz dengan sensor presisi.',
                'quantity' => 3,
                'unit' => 'pcs',
                'price' => 175000,
                'min_stock' => 5,
                'status' => 'low_stock',
            ]
        );

        Stock::firstOrCreate(
            ['user_id' => $member->id, 'name' => 'Monitor IPS 24 Inch 144Hz'],
            [
                'sku' => 'SKU-MN-003',
                'description' => 'Monitor layar datar IPS 24 inci resolusi Full HD dengan refresh rate 144Hz.',
                'quantity' => 0,
                'unit' => 'unit',
                'price' => 2150000,
                'min_stock' => 3,
                'status' => 'out_of_stock',
            ]
        );
    }
}
