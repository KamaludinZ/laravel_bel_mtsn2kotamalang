<?php

namespace App\Http\Controllers;

use App\Models\HardwareCommandQueue;
use App\Models\HardwareConfig;
use App\Models\HardwareLog;
use App\Models\Room;
use App\Models\Setting;
use App\Models\SpeakerZone;
use Illuminate\Http\Request;

class HardwareController extends Controller
{
    /**
     * Display hardware dashboard
     */
    public function index()
    {
        $config = HardwareConfig::primary();
        $zones = SpeakerZone::ordered()->get();

        // Get rooms for grid display (exclude HORN and CTRLROOM as they are in Audio Control)
        $allRooms = Room::with('speakerZone')
            ->active()
            ->whereNotIn('room_type', ['HORN', 'CTRLROOM'])
            ->orderByRaw("CASE
                WHEN group_name LIKE 'GROUP %' THEN CAST(SUBSTRING(group_name FROM 7) AS INTEGER)
                WHEN group_name LIKE 'CUSTOM %' THEN 100 + CAST(SUBSTRING(group_name FROM 8) AS INTEGER)
                ELSE 999
            END")
            ->orderBy('room_name')
            ->get();

        // Get editable rooms (same as grid - 40 rooms: 32 active + 8 reserved)
        $rooms = $allRooms;

        $groups = Room::getAllGroups();
        $roomTypes = Room::getAllTypes();

        $recentLogs = HardwareLog::with(['commandQueue', 'speakerZone'])
            ->recent(20)
            ->get();

        $stats = [
            'pending_commands' => HardwareCommandQueue::pending()->count(),
            'today_executions' => HardwareLog::today()->count(),
            'today_success' => HardwareLog::today()->byStatus('success')->count(),
            'today_failed' => HardwareLog::today()->byStatus('failed')->count(),
            'bridge_status' => $config && $config->isOnline() ? 'online' : 'offline',
        ];

        // Load custom group labels
        $customLabels = Setting::get('hardware_group_labels');
        $groupLabels = $customLabels ? json_decode($customLabels, true) : [];

        // Get parent channels for Kelola Speaker Zones tab
        $parentChannels = Room::whereIn('room_type', ['HORN', 'CTRLROOM'])->get();

        return view('hardware.index', compact('config', 'zones', 'allRooms', 'rooms', 'groups', 'roomTypes', 'recentLogs', 'stats', 'groupLabels', 'parentChannels'));
    }

    /**
     * Test speaker zone
     */
    public function testSpeaker(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|uuid|exists:speaker_zones,id',
            'duration' => 'nullable|integer|min:1|max:60',
        ]);

        $zone = SpeakerZone::findOrFail($validated['zone_id']);
        $duration = $validated['duration'] ?? 5; // Default 5 seconds

        // Create command
        HardwareCommandQueue::create([
            'command_type' => 'test_speaker',
            'payload' => [
                'zone' => $zone->modbus_channel,
                'zone_id' => $zone->id,
                'duration_seconds' => $duration,
            ],
            'status' => 'pending',
            'scheduled_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);

        return redirect()->back()->with('success', "Test speaker {$zone->name} selama {$duration} detik telah dijadwalkan");
    }

    /**
     * Update hardware configuration
     */
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'com_port' => 'required|string',
            'baud_rate' => 'required|integer',
            'modbus_address' => 'required|integer|min:1|max:247',
            'timeout_ms' => 'nullable|integer|min:100|max:5000',
        ]);

        $config = HardwareConfig::primary();

        if (!$config) {
            $config = HardwareConfig::create(array_merge($validated, [
                'config_key' => 'primary_device',
                'device_type' => 'modbus_rs485',
                'is_enabled' => true,
            ]));
        } else {
            $config->update($validated);
        }

        return redirect()->back()->with('success', 'Konfigurasi hardware berhasil diupdate');
    }

    /**
     * Display hardware logs
     */
    public function logs(Request $request)
    {
        $query = HardwareLog::with(['commandQueue', 'speakerZone'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(50);

        return view('hardware.logs', compact('logs'));
    }

    /**
     * Display speaker zones management
     */
    public function zones()
    {
        $zones = SpeakerZone::ordered()->get();
        return view('hardware.zones', compact('zones'));
    }

    /**
     * Update speaker zone
     */
    public function updateZone(Request $request, SpeakerZone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_enabled' => 'boolean',
            'default_duration_seconds' => 'required|integer|min:1|max:3600',
            'modbus_channel' => 'required|integer|min:1|max:8|unique:speaker_zones,modbus_channel,' . $zone->id,
        ]);

        $zone->update($validated);

        return redirect()->back()->with('success', "Zone {$zone->name} berhasil diupdate");
    }

    /**
     * Test all zones at once (ON ALL - aktivasi PARENT + semua grup)
     */
    public function testAllZones(Request $request)
    {
        $duration = $request->input('duration', 5);
        $commandsCreated = 0;

        // Get all active rooms with hardware_address
        $allRooms = Room::active()
            ->whereNotNull('hardware_address')
            ->get();

        if ($allRooms->isEmpty()) {
            $errorMsg = 'Tidak ada room aktif dengan hardware address';
            if ($request->expectsJson()) {
                return response()->json(['error' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Separate parents and children
        $parents = $allRooms->filter(fn($room) => $room->isParent());
        $children = $allRooms->filter(fn($room) => $room->requiresParent());

        // STEP 1: Activate all parents FIRST (HORN and CTRL ROOM)
        foreach ($parents as $parent) {
            HardwareCommandQueue::create([
                'command_type' => 'activate_parent',
                'payload' => [
                    'hardware_address' => $parent->hardware_address,
                    'room_id' => $parent->id,
                    'room_name' => $parent->room_name,
                    'trigger_type' => 'ON_ALL_PARENT',
                ],
                'status' => 'pending',
                'scheduled_at' => now(),
                'expires_at' => now()->addMinutes(5),
            ]);
            $commandsCreated++;
        }

        // STEP 2: Activate all children AFTER parents (2 second delay)
        $childActivationTime = now()->addSeconds(2);
        foreach ($children as $child) {
            HardwareCommandQueue::create([
                'command_type' => 'test_speaker',
                'payload' => [
                    'hardware_address' => $child->hardware_address,
                    'room_id' => $child->id,
                    'room_name' => $child->room_name,
                    'parent_address' => $child->parent_hardware_address,
                    'duration_seconds' => $duration,
                    'trigger_type' => 'ON_ALL_CHILD',
                ],
                'status' => 'pending',
                'scheduled_at' => $childActivationTime,
                'expires_at' => now()->addMinutes(5),
            ]);
            $commandsCreated++;
        }

        $message = sprintf(
            'ON ALL: %d parents akan diaktifkan dulu, kemudian %d children (total %d rooms) selama %d detik',
            $parents->count(),
            $children->count(),
            $allRooms->count(),
            $duration
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'parents_count' => $parents->count(),
                'children_count' => $children->count(),
                'total_rooms' => $allRooms->count(),
                'commands_created' => $commandsCreated,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Test individual room
     */
    public function testRoom(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'duration' => 'nullable|integer|min:1|max:300',
        ]);

        $room = Room::with('speakerZone')->findOrFail($validated['room_id']);

        if (!$room->is_active) {
            $errorMsg = "Room {$room->room_name} sedang nonaktif";
            if ($request->expectsJson()) {
                return response()->json(['error' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $duration = $validated['duration'] ?? 5;
        $commandsCreated = 0;

        // STEP 1: Check if room requires parent activation
        if ($room->requiresParent()) {
            $parent = $room->getParentRoom();

            if ($parent) {
                // Activate parent FIRST (using hardware_address directly)
                HardwareCommandQueue::create([
                    'command_type' => 'activate_parent',
                    'payload' => [
                        'hardware_address' => $parent->hardware_address,
                        'room_id' => $parent->id,
                        'room_name' => $parent->room_name,
                        'parent_for' => $room->room_name,
                    ],
                    'status' => 'pending',
                    'scheduled_at' => now(),
                    'expires_at' => now()->addMinutes(5),
                ]);
                $commandsCreated++;

                // STEP 2: Activate child room after parent (1 second delay)
                HardwareCommandQueue::create([
                    'command_type' => 'test_speaker',
                    'payload' => [
                        'hardware_address' => $room->hardware_address,
                        'room_id' => $room->id,
                        'room_name' => $room->room_name,
                        'parent_address' => $parent->hardware_address,
                        'duration_seconds' => $duration,
                        // Keep speaker_zone for backward compatibility
                        'zone' => $room->speakerZone?->modbus_channel,
                        'zone_id' => $room->speakerZone?->id,
                    ],
                    'status' => 'pending',
                    'scheduled_at' => now()->addSecond(),
                    'expires_at' => now()->addMinutes(5),
                ]);
                $commandsCreated++;

                $message = "Test room {$room->room_name}: Parent ({$parent->room_name}) akan diaktifkan dulu, kemudian room selama {$duration} detik";
            } else {
                $errorMsg = "Parent hardware address {$room->parent_hardware_address} tidak ditemukan";
                if ($request->expectsJson()) {
                    return response()->json(['error' => $errorMsg], 400);
                }
                return redirect()->back()->with('error', $errorMsg);
            }
        } else {
            // Room is standalone (HORN/CTRL ROOM) or doesn't require parent
            HardwareCommandQueue::create([
                'command_type' => 'test_speaker',
                'payload' => [
                    'hardware_address' => $room->hardware_address,
                    'room_id' => $room->id,
                    'room_name' => $room->room_name,
                    'duration_seconds' => $duration,
                    // Keep speaker_zone for backward compatibility
                    'zone' => $room->speakerZone?->modbus_channel,
                    'zone_id' => $room->speakerZone?->id,
                ],
                'status' => 'pending',
                'scheduled_at' => now(),
                'expires_at' => now()->addMinutes(5),
            ]);
            $commandsCreated++;

            $message = "Test room {$room->room_name} ({$room->group_name}) selama {$duration} detik telah dijadwalkan";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'commands_created' => $commandsCreated,
                'room' => [
                    'id' => $room->id,
                    'name' => $room->room_name,
                    'group' => $room->group_name,
                    'hardware_address' => $room->hardware_address,
                    'requires_parent' => $room->requiresParent(),
                ]
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Test rooms by group
     */
    public function testGroup(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string',
            'duration' => 'nullable|integer|min:1|max:300',
        ]);

        $rooms = Room::with('speakerZone')
            ->active()
            ->byGroup($validated['group_name'])
            ->whereNotNull('speaker_zone_id')
            ->get();

        if ($rooms->isEmpty()) {
            $errorMsg = "Tidak ada room aktif di {$validated['group_name']}";
            if ($request->expectsJson()) {
                return response()->json(['error' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $duration = $validated['duration'] ?? 5;
        $zones = $rooms->pluck('speakerZone.modbus_channel')->unique()->values()->toArray();

        HardwareCommandQueue::create([
            'command_type' => 'trigger_bell',
            'payload' => [
                'zones' => $zones,
                'group_name' => $validated['group_name'],
                'room_count' => $rooms->count(),
                'duration_seconds' => $duration,
            ],
            'status' => 'pending',
            'scheduled_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);

        $message = "Test group {$validated['group_name']} ({$rooms->count()} rooms) selama {$duration} detik telah dijadwalkan";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'group_name' => $validated['group_name'],
                'room_count' => $rooms->count(),
                'zones' => $zones
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Test rooms by type
     */
    public function testType(Request $request)
    {
        $validated = $request->validate([
            'room_type' => 'required|string',
            'duration' => 'nullable|integer|min:1|max:300',
        ]);

        $rooms = Room::active()
            ->byType($validated['room_type'])
            ->whereNotNull('hardware_address')
            ->get();

        if ($rooms->isEmpty()) {
            $errorMsg = "Tidak ada room aktif dengan tipe {$validated['room_type']}";
            if ($request->expectsJson()) {
                return response()->json(['error' => $errorMsg], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $duration = $validated['duration'] ?? 5;
        $now = now();

        // Separate parents and children
        $parents = $rooms->filter(fn($room) => $room->isParent());
        $children = $rooms->filter(fn($room) => $room->requiresParent());

        // If testing parent types (HORN or CTRLROOM), just activate them
        if ($parents->isNotEmpty()) {
            foreach ($parents as $parent) {
                HardwareCommandQueue::create([
                    'command_type' => 'activate_parent',
                    'payload' => [
                        'hardware_address' => $parent->hardware_address,
                        'room_id' => $parent->id,
                        'room_name' => $parent->room_name,
                        'duration_seconds' => $duration,
                        'trigger_type' => 'TEST_TYPE',
                    ],
                    'status' => 'pending',
                    'scheduled_at' => $now,
                    'expires_at' => $now->copy()->addMinutes(5),
                ]);
            }

            $message = "Test tipe {$validated['room_type']} ({$parents->count()} parent rooms) selama {$duration} detik telah dijadwalkan";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'room_type' => $validated['room_type'],
                    'room_count' => $parents->count(),
                ]);
            }

            return redirect()->back()->with('success', $message);
        }

        // If testing child types, activate their parents first, then children
        if ($children->isNotEmpty()) {
            // Get unique parents for these children
            $parentAddresses = $children->pluck('parent_hardware_address')->unique()->filter();
            $parentRooms = Room::whereIn('hardware_address', $parentAddresses)->get();

            // Step 1: Activate parents
            foreach ($parentRooms as $parent) {
                HardwareCommandQueue::create([
                    'command_type' => 'activate_parent',
                    'payload' => [
                        'hardware_address' => $parent->hardware_address,
                        'room_id' => $parent->id,
                        'room_name' => $parent->room_name,
                        'trigger_type' => 'TEST_TYPE_PARENT',
                        'for_room_type' => $validated['room_type'],
                    ],
                    'status' => 'pending',
                    'scheduled_at' => $now,
                    'expires_at' => $now->copy()->addMinutes(5),
                ]);
            }

            // Step 2: Activate children (2 second delay)
            $childActivationTime = $now->copy()->addSeconds(2);
            foreach ($children as $child) {
                HardwareCommandQueue::create([
                    'command_type' => 'test_speaker',
                    'payload' => [
                        'hardware_address' => $child->hardware_address,
                        'room_id' => $child->id,
                        'room_name' => $child->room_name,
                        'parent_address' => $child->parent_hardware_address,
                        'duration_seconds' => $duration,
                        'trigger_type' => 'TEST_TYPE_CHILD',
                    ],
                    'status' => 'pending',
                    'scheduled_at' => $childActivationTime,
                    'expires_at' => $now->copy()->addMinutes(5),
                ]);
            }

            $message = "Test tipe {$validated['room_type']}: {$parentRooms->count()} parent(s) + {$children->count()} child room(s) selama {$duration} detik telah dijadwalkan";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'room_type' => $validated['room_type'],
                    'room_count' => $children->count(),
                    'parent_count' => $parentRooms->count(),
                ]);
            }

            return redirect()->back()->with('success', $message);
        }

        // Fallback: no valid rooms found
        $errorMsg = "Tidak ada room dengan hardware address yang valid untuk tipe {$validated['room_type']}";
        if ($request->expectsJson()) {
            return response()->json(['error' => $errorMsg], 400);
        }
        return redirect()->back()->with('error', $errorMsg);
    }

    /**
     * Clear old logs
     */
    public function clearOldLogs(Request $request)
    {
        $days = $request->input('days', 30);

        $deleted = HardwareLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->back()->with('success', "Berhasil menghapus {$deleted} log lama (>{$days} hari)");
    }

    /**
     * Turn off all speakers (OFF ALL - matikan PARENT + semua grup)
     */
    public function offAll(Request $request)
    {
        // Clear all pending commands
        $pendingDeleted = HardwareCommandQueue::pending()->delete();

        // Get all zones from active rooms (including PARENT channels)
        $rooms = Room::with('speakerZone')
            ->active()
            ->whereNotNull('speaker_zone_id')
            ->get();

        $zones = $rooms->pluck('speakerZone.modbus_channel')->unique()->values()->toArray();

        if (!empty($zones)) {
            HardwareCommandQueue::create([
                'command_type' => 'stop_all',
                'payload' => [
                    'zones' => $zones,
                    'action' => 'stop',
                    'trigger_type' => 'OFF_ALL',
                    'room_count' => $rooms->count(),
                ],
                'status' => 'pending',
                'scheduled_at' => now(),
                'expires_at' => now()->addMinutes(1),
            ]);
        }

        $message = "OFF ALL: Semua speaker dimatikan (" . $rooms->count() . " rooms, " . count($zones) . " zones). {$pendingDeleted} perintah pending dibatalkan.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'pending_deleted' => $pendingDeleted,
                'zones_count' => count($zones),
                'room_count' => $rooms->count()
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update room name
     */
    public function updateRoomName(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|uuid|exists:rooms,id',
            'room_name' => 'required|string|max:100',
        ]);

        $room = Room::findOrFail($validated['room_id']);
        $oldName = $room->room_name;
        $room->room_name = $validated['room_name'];
        $room->save();

        return redirect()->back()->with('success', "Nama room berhasil diubah dari '{$oldName}' menjadi '{$room->room_name}'");
    }

    /**
     * Bulk update room names
     */
    public function bulkUpdateRoomNames(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|json',
        ]);

        $updates = json_decode($validated['updates'], true);
        $count = 0;

        foreach ($updates as $update) {
            if (isset($update['room_id']) && isset($update['room_name'])) {
                $room = Room::find($update['room_id']);
                if ($room) {
                    $room->room_name = $update['room_name'];
                    $room->save();
                    $count++;
                }
            }
        }

        return redirect()->back()->with('success', "Berhasil mengupdate {$count} nama room");
    }

    /**
     * Bulk update rooms data (code, name, group, hardware)
     */
    public function bulkUpdateRooms(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|json',
        ]);

        $updates = json_decode($validated['updates'], true);
        $count = 0;
        $errors = [];

        foreach ($updates as $update) {
            if (isset($update['room_id'])) {
                $room = Room::find($update['room_id']);
                if ($room) {
                    try {
                        // Update basic room data
                        if (isset($update['room_code'])) {
                            $room->room_code = $update['room_code'];
                        }
                        if (isset($update['room_name'])) {
                            $room->room_name = $update['room_name'];
                        }
                        if (isset($update['group_name'])) {
                            $room->group_name = $update['group_name'];
                        }

                        // Update speaker zone
                        if (isset($update['speaker_zone_id'])) {
                            $room->speaker_zone_id = $update['speaker_zone_id'] !== '' ? $update['speaker_zone_id'] : null;
                        }

                        $room->save();

                        // Update hardware address
                        if (isset($update['hardware_address'])) {
                            // Simply update the hardware_address field
                            $room->hardware_address = $update['hardware_address'] !== '' ? $update['hardware_address'] : null;
                            $room->save();
                        }

                        $count++;
                    } catch (\Exception $e) {
                        $errors[] = "Room {$room->room_name}: " . $e->getMessage();
                    }
                }
            }
        }

        if (!empty($errors)) {
            return redirect()->back()->with('warning', "Berhasil mengupdate {$count} room. Errors: " . implode(', ', $errors));
        }

        return redirect()->back()->with('success', "Berhasil mengupdate {$count} room data");
    }

    /**
     * Update group labels
     */
    public function updateGroupLabels(Request $request)
    {
        try {
            $validated = $request->validate([
                'labels' => 'required|json',
            ]);

            $labels = json_decode($validated['labels'], true);

            // Validate decoded JSON
            if (!is_array($labels)) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Format data tidak valid'], 400);
                }
                return redirect('/hardware')->with('error', 'Format data tidak valid');
            }

            // Save to settings
            Setting::set('hardware_group_labels', json_encode($labels));

            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Label grup berhasil diupdate']);
            }

            return redirect('/hardware')->with('success', 'Label grup berhasil diupdate');
        } catch (\Exception $e) {
            \Log::error('Error updating group labels: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect('/hardware')->with('error', 'Gagal update label grup: ' . $e->getMessage());
        }
    }

    /**
     * Update parent channel (HORN, CTRLROOM)
     */
    public function updateParentChannel(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'room_code' => 'required|string|max:10',
            'room_name' => 'required|string|max:50',
            'hardware_address' => 'nullable|string|max:10',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Verify this is actually a parent channel
        if (!in_array($room->room_type, ['HORN', 'CTRLROOM'])) {
            return redirect()->back()->with('error', 'Hanya parent channel (HORN/CTRLROOM) yang bisa diupdate di sini');
        }

        // Check if room_code is unique (except for current room)
        $existingCode = Room::where('room_code', $validated['room_code'])
            ->where('id', '!=', $room->id)
            ->first();

        if ($existingCode) {
            return redirect()->back()->with('error', "Code '{$validated['room_code']}' sudah digunakan oleh room lain");
        }

        // Update room data
        $room->room_code = $validated['room_code'];
        $room->room_name = $validated['room_name'];
        $room->hardware_address = $validated['hardware_address'] ?: null;
        $room->save();

        return redirect()->back()->with('success', "Parent channel {$room->room_type} berhasil diupdate");
    }
}
