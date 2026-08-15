<?php

namespace Database\Seeders;

use App\Models\HardwareConfig;
use App\Models\SpeakerZone;
use Illuminate\Database\Seeder;

class HardwareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding hardware configuration...');

        // Create default hardware config
        HardwareConfig::updateOrCreate(
            ['config_key' => 'primary_device'],
            [
                'device_type' => 'modbus_rs485',
                'connection_type' => 'usb',
                'com_port' => 'COM3', // Sesuaikan dengan COM port Anda
                'baud_rate' => 9600,
                'data_bits' => 8,
                'parity' => 'N',
                'stop_bits' => 1,
                'modbus_address' => 1,
                'is_enabled' => true,
                'auto_reconnect' => true,
                'timeout_ms' => 1000,
            ]
        );

        $this->command->info('✓ Hardware config created');

        // Create speaker zones
        $zones = [
            ['name' => 'Halaman Sekolah', 'modbus_channel' => 1, 'description' => 'Speaker area halaman utama'],
            ['name' => 'Ruang Kelas Lantai 1', 'modbus_channel' => 2, 'description' => 'Speaker kelas lt 1'],
            ['name' => 'Ruang Kelas Lantai 2', 'modbus_channel' => 3, 'description' => 'Speaker kelas lt 2'],
            ['name' => 'Masjid', 'modbus_channel' => 4, 'description' => 'Speaker masjid sekolah'],
            ['name' => 'Kantor Guru', 'modbus_channel' => 5, 'description' => 'Speaker kantor guru'],
            ['name' => 'Perpustakaan', 'modbus_channel' => 6, 'description' => 'Speaker perpustakaan'],
            ['name' => 'Laboratorium', 'modbus_channel' => 7, 'description' => 'Speaker lab komputer/IPA'],
            ['name' => 'Reserved', 'modbus_channel' => 8, 'description' => 'Channel cadangan', 'is_enabled' => false],
        ];

        foreach ($zones as $index => $zone) {
            SpeakerZone::updateOrCreate(
                ['modbus_channel' => $zone['modbus_channel']],
                array_merge($zone, [
                    'sort_order' => $index + 1,
                    'default_duration_seconds' => 180, // 3 menit
                    'volume_level' => 100,
                    'relay_mode' => 'normally_open',
                    'is_enabled' => $zone['is_enabled'] ?? true,
                ])
            );
        }

        $this->command->info('✓ Created ' . count($zones) . ' speaker zones');
        $this->command->info('');
        $this->command->info('Hardware configuration seeded successfully! 🎉');
    }
}
