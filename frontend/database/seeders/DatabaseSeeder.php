<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat user admin
        User::firstOrCreate(
            ['email' => 'admin@smarthub.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // Buat kategori awal
        $categories = [
            ['name' => 'Elektronik',   'description' => 'Peralatan elektronik dan komputer'],
            ['name' => 'Jaringan',     'description' => 'Peralatan jaringan dan konektivitas'],
            ['name' => 'Alat Ukur',   'description' => 'Instrumen pengukuran dan kalibrasi'],
            ['name' => 'Perlengkapan', 'description' => 'Perlengkapan dan aksesori pendukung'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // Buat data peralatan awal
        $elektronik   = Category::where('name', 'Elektronik')->first();
        $jaringan     = Category::where('name', 'Jaringan')->first();
        $alatUkur     = Category::where('name', 'Alat Ukur')->first();
        $perlengkapan = Category::where('name', 'Perlengkapan')->first();

        $items = [
            ['category_id' => $elektronik->id,   'code' => 'ELK-001', 'name' => 'Laptop Lenovo ThinkPad', 'stock' => 5],
            ['category_id' => $elektronik->id,   'code' => 'ELK-002', 'name' => 'Proyektor Epson EB-X41', 'stock' => 3],
            ['category_id' => $jaringan->id,     'code' => 'NET-001', 'name' => 'Switch 24-Port D-Link',  'stock' => 2],
            ['category_id' => $jaringan->id,     'code' => 'NET-002', 'name' => 'Kabel UTP Cat6 (50m)',   'stock' => 10],
            ['category_id' => $alatUkur->id,     'code' => 'UKR-001', 'name' => 'Multimeter Digital',     'stock' => 4],
            ['category_id' => $perlengkapan->id, 'code' => 'PKL-001', 'name' => 'Extension Cord 5m',      'stock' => 8],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(['code' => $item['code']], $item);
        }
    }
}
