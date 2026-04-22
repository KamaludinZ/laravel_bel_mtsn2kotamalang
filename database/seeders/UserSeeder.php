<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin MTsN 2 Kota Malang',
            'email' => 'admin@mtsn2kotamalang.sch.id',
            'password' => \Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        \App\Models\User::create([
            'name' => 'Operator Sekolah',
            'email' => 'operator@mtsn2kotamalang.sch.id',
            'password' => \Hash::make('operator123'),
            'email_verified_at' => now(),
        ]);
    }
}
