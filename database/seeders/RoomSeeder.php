<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\SpeakerZone;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding rooms data...');

        // Get speaker zones for reference
        $zones = SpeakerZone::all()->keyBy('modbus_channel');

        // Data rooms berdasarkan screenshot
        $rooms = [
            // PARENT CHANNELS - Not part of any group
            ['no' => 41, 'room_code' => 'HORN', 'room_type' => 'HORN', 'room_name' => 'HORN', 'group_name' => 'PARENT', 'hardware_address' => '1-1', 'zone_channel' => 1],
            ['no' => 42, 'room_code' => 'CTRLROOM', 'room_type' => 'CTRLROOM', 'room_name' => 'CTRLROOM', 'group_name' => 'PARENT', 'hardware_address' => '10-4', 'zone_channel' => null],

            // CUSTOM 2 - MAHAD (now standalone in CUSTOM 2)
            ['no' => 44, 'room_code' => 'MAHAD1', 'room_type' => 'MAHAD', 'room_name' => 'MAHAD 1', 'group_name' => 'CUSTOM 2', 'hardware_address' => '21-2', 'zone_channel' => 7],

            // CUSTOM 1 - LIBRARY
            ['no' => 43, 'room_code' => 'LIBRARY', 'room_type' => 'LIBRARY', 'room_name' => 'LIBRARY', 'group_name' => 'CUSTOM 1', 'hardware_address' => '20-6', 'zone_channel' => 6],

            // GROUP 1 - Classes 7A-7F
            ['no' => 1, 'room_code' => '7A', 'room_type' => 'CLASS', 'room_name' => '7A', 'group_name' => 'GROUP 1', 'hardware_address' => '1-1', 'zone_channel' => 1],
            ['no' => 30, 'room_code' => '8K', 'room_type' => 'CLASS', 'room_name' => '8K', 'group_name' => 'GROUP 6', 'hardware_address' => '4-1', 'zone_channel' => 6],
            ['no' => 31, 'room_code' => '7C', 'room_type' => 'CLASS', 'room_name' => '7C', 'group_name' => 'GROUP 1', 'hardware_address' => '9-1', 'zone_channel' => 1],
            ['no' => 4, 'room_code' => '7D', 'room_type' => 'CLASS', 'room_name' => '7D', 'group_name' => 'GROUP 1', 'hardware_address' => '9-4', 'zone_channel' => 1],
            ['no' => 5, 'room_code' => '7E', 'room_type' => 'CLASS', 'room_name' => '7E', 'group_name' => 'GROUP 1', 'hardware_address' => '7-1', 'zone_channel' => 1],
            ['no' => 6, 'room_code' => '7F', 'room_type' => 'CLASS', 'room_name' => '7F', 'group_name' => 'GROUP 1', 'hardware_address' => '3-4', 'zone_channel' => 1],
            ['no' => 2, 'room_code' => '7B', 'room_type' => 'CLASS', 'room_name' => '7B', 'group_name' => 'GROUP 1', 'hardware_address' => '7-3', 'zone_channel' => 1],

            // GROUP 2 - Classes 7G-8A
            ['no' => 26, 'room_code' => '9F', 'room_type' => 'CLASS', 'room_name' => '9F', 'group_name' => 'GROUP 5', 'hardware_address' => '2-2', 'zone_channel' => 2],
            ['no' => 7, 'room_code' => '7G', 'room_type' => 'CLASS', 'room_name' => '7G', 'group_name' => 'GROUP 2', 'hardware_address' => '5-2', 'zone_channel' => 2],
            ['no' => 8, 'room_code' => '7H', 'room_type' => 'CLASS', 'room_name' => '7H', 'group_name' => 'GROUP 2', 'hardware_address' => '4-4', 'zone_channel' => 2],
            ['no' => 9, 'room_code' => '7I', 'room_type' => 'CLASS', 'room_name' => '7I', 'group_name' => 'GROUP 2', 'hardware_address' => '5-4', 'zone_channel' => 2],
            ['no' => 11, 'room_code' => '8A', 'room_type' => 'CLASS', 'room_name' => '8A', 'group_name' => 'GROUP 2', 'hardware_address' => '9-2', 'zone_channel' => 2],
            ['no' => 33, 'room_code' => '7J', 'room_type' => 'CLASS', 'room_name' => '7J', 'group_name' => 'GROUP 2', 'hardware_address' => null, 'zone_channel' => 2],

            // GROUP 3 - Classes 8B-8F
            ['no' => 12, 'room_code' => '8B', 'room_type' => 'CLASS', 'room_name' => '8B', 'group_name' => 'GROUP 3', 'hardware_address' => '9-3', 'zone_channel' => 3],
            ['no' => 13, 'room_code' => '8C', 'room_type' => 'CLASS', 'room_name' => '8C', 'group_name' => 'GROUP 3', 'hardware_address' => '7-2', 'zone_channel' => 3],
            ['no' => 14, 'room_code' => '8D', 'room_type' => 'CLASS', 'room_name' => '8D', 'group_name' => 'GROUP 3', 'hardware_address' => '7-4', 'zone_channel' => 3],
            ['no' => 15, 'room_code' => '8E', 'room_type' => 'CLASS', 'room_name' => '8E', 'group_name' => 'GROUP 3', 'hardware_address' => '6-4', 'zone_channel' => 3],
            ['no' => 16, 'room_code' => '8F', 'room_type' => 'CLASS', 'room_name' => '8F', 'group_name' => 'GROUP 3', 'hardware_address' => '6-3', 'zone_channel' => 3],
            ['no' => 17, 'room_code' => '8G', 'room_type' => 'CLASS', 'room_name' => '8G', 'group_name' => 'GROUP 4', 'hardware_address' => '3-2', 'zone_channel' => 4],

            // GROUP 4 - Classes 8G-8I
            ['no' => 18, 'room_code' => '8H', 'room_type' => 'CLASS', 'room_name' => '8H', 'group_name' => 'GROUP 4', 'hardware_address' => '3-3', 'zone_channel' => 4],
            ['no' => 20, 'room_code' => '8I', 'room_type' => 'CLASS', 'room_name' => '8I', 'group_name' => 'GROUP 4', 'hardware_address' => '4-2', 'zone_channel' => 4],
            ['no' => 10, 'room_code' => '8J', 'room_type' => 'CLASS', 'room_name' => '8J', 'group_name' => 'GROUP 2', 'hardware_address' => '4-3', 'zone_channel' => 2],

            // GROUP 5 - Classes 9A-9I
            ['no' => 27, 'room_code' => '9G', 'room_type' => 'CLASS', 'room_name' => '9G', 'group_name' => 'GROUP 6', 'hardware_address' => '2-3', 'zone_channel' => 6],
            ['no' => 25, 'room_code' => '9E', 'room_type' => 'CLASS', 'room_name' => '9E', 'group_name' => 'GROUP 6', 'hardware_address' => '1-2', 'zone_channel' => 6],
            ['no' => 29, 'room_code' => '9I', 'room_type' => 'CLASS', 'room_name' => '9I', 'group_name' => 'GROUP 5', 'hardware_address' => '3-1', 'zone_channel' => 5],
            ['no' => 28, 'room_code' => '9H', 'room_type' => 'CLASS', 'room_name' => '9H', 'group_name' => 'GROUP 6', 'hardware_address' => '2-4', 'zone_channel' => 6],
            ['no' => 24, 'room_code' => '9D', 'room_type' => 'CLASS', 'room_name' => '9D', 'group_name' => 'GROUP 5', 'hardware_address' => '6-2', 'zone_channel' => 5],
            ['no' => 23, 'room_code' => '9C', 'room_type' => 'CLASS', 'room_name' => '9C', 'group_name' => 'GROUP 5', 'hardware_address' => '5-3', 'zone_channel' => 5],
            ['no' => 22, 'room_code' => '9B', 'room_type' => 'CLASS', 'room_name' => '9B', 'group_name' => 'GROUP 5', 'hardware_address' => '8-3', 'zone_channel' => 5],
            ['no' => 21, 'room_code' => '9A', 'room_type' => 'CLASS', 'room_name' => '9A', 'group_name' => 'GROUP 4', 'hardware_address' => '6-1', 'zone_channel' => 4],

            // RESERVED SLOTS - 8 slots untuk masa depan
            ['no' => 51, 'room_code' => 'RES1', 'room_type' => 'RESERVED', 'room_name' => 'Reserved 1', 'group_name' => 'CUSTOM 1', 'hardware_address' => null, 'zone_channel' => null],
            ['no' => 52, 'room_code' => 'RES2', 'room_type' => 'RESERVED', 'room_name' => 'Reserved 2', 'group_name' => 'CUSTOM 1', 'hardware_address' => null, 'zone_channel' => null],
            ['no' => 53, 'room_code' => 'RES3', 'room_type' => 'RESERVED', 'room_name' => 'Reserved 3', 'group_name' => 'CUSTOM 1', 'hardware_address' => null, 'zone_channel' => null],
            ['no' => 54, 'room_code' => 'RES4', 'room_type' => 'RESERVED', 'room_name' => 'Reserved 4', 'group_name' => 'CUSTOM 1', 'hardware_address' => null, 'zone_channel' => null],
            ['no' => 55, 'room_code' => 'RES5', 'room_type' => 'RESERVED', 'room_name' => 'Reserved 5', 'group_name' => 'CUSTOM 1', 'hardware_address' => null, 'zone_channel' => null],
            ['no' => 56, 'room_code' => 'RES6', 'room_type' => 'RESERVED', 'room_name' => 'Reserved 6', 'group_name' => 'CUSTOM 1', 'hardware_address' => null, 'zone_channel' => null],
            ['no' => 57, 'room_code' => 'RES7', 'room_type' => 'RESERVED', 'room_name' => 'Reserved 7', 'group_name' => 'CUSTOM 1', 'hardware_address' => null, 'zone_channel' => null],
            ['no' => 58, 'room_code' => 'RES8', 'room_type' => 'RESERVED', 'room_name' => 'Reserved 8', 'group_name' => 'CUSTOM 1', 'hardware_address' => null, 'zone_channel' => null],
        ];

        $created = 0;
        $updated = 0;

        foreach ($rooms as $index => $roomData) {
            // Get speaker zone if channel is specified
            $zoneId = null;
            if (isset($roomData['zone_channel']) && $roomData['zone_channel']) {
                $zone = $zones->get($roomData['zone_channel']);
                if ($zone) {
                    $zoneId = $zone->id;
                }
            }

            // Use updateOrCreate to avoid duplicate key errors on re-deploy
            $room = Room::updateOrCreate(
                ['no' => $roomData['no']], // Match by 'no' field (unique)
                [
                    'room_code' => $roomData['room_code'],
                    'room_type' => $roomData['room_type'],
                    'room_name' => $roomData['room_name'],
                    'group_name' => $roomData['group_name'],
                    'hardware_address' => $roomData['hardware_address'],
                    'speaker_zone_id' => $zoneId,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            if ($room->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info('✓ Created ' . $created . ' rooms, Updated ' . $updated . ' rooms');
        $this->command->info('');
        $this->command->info('Rooms data seeded successfully! 🎉');
    }
}
