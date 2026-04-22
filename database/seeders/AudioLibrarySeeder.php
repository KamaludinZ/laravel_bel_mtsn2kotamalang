<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AudioLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $audioLibraries = [
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'title' => 'Bel Masuk Sekolah',
                'file_path' => 'audio/bell-entrance.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'title' => 'Bel Masuk Kelas',
                'file_path' => 'audio/bell-class-start.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'title' => 'Bel Istirahat',
                'file_path' => 'audio/bell-break.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'title' => 'Bel Pulang',
                'file_path' => 'audio/bell-home.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'title' => 'Musik Islami',
                'file_path' => 'audio/islamic-music.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('audio_libraries')->insert($audioLibraries);
    }
}
