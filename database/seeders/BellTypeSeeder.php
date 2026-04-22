<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BellTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bellTypes = [
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Hari Senin - Kamis',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Hari Jumat',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Hari Sabtu',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Hari Libur',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('bell_types')->insert($bellTypes);
    }
}
