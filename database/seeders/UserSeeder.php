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
        // Use updateOrCreate to make seeder idempotent (safe to run multiple times)
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@mtsn2kotamalang.sch.id'],
            [
                'name' => 'Admin MTsN 2 Kota Malang',
                'password' => \Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'operator@mtsn2kotamalang.sch.id'],
            [
                'name' => 'Operator Sekolah',
                'password' => \Hash::make('operator123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
