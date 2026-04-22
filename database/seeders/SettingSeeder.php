<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'key' => 'app_name',
                'value' => 'Bel Sekolah MTsN 2 Kota Malang',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'key' => 'app_logo',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('settings')->insert($settings);
    }
}
