<?php

namespace App\Console\Commands;

use App\Models\AudioLibrary;
use App\Models\BellSchedule;
use App\Models\HardwareCommandQueue;
use App\Models\Room;
use App\Services\BellHardwareGate;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckBellSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bell:check-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and trigger bell schedules that should ring now';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Jam sekolah (WIB). Waktu yang DISIMPAN ke database memakai now() (zona
        // aplikasi) agar tidak terjadi selisih zona waktu antar bagian aplikasi.
        $now = BellHardwareGate::now();
        $currentDay = strtolower($now->englishDayOfWeek); // monday, tuesday, etc
        $currentTime = $now->format('H:i'); // HH:MM format

        $this->info("Checking bell schedules for {$currentDay} at {$currentTime} WIB...");

        // Find schedules that should ring now - hanya jenis bel yang AKTIF & OTOMATIS
        $schedules = BellSchedule::with(['bellType', 'audioLibrary'])
            ->where('day', $currentDay)
            ->whereBetween('time', [$currentTime . ':00', $currentTime . ':59'])
            ->whereHas('bellType', fn ($q) => $q->where('is_active', true)->where('is_automatic', true))
            ->get()
            ->filter(fn ($schedule) => BellHardwareGate::isScheduleActiveToday($schedule, $now));

        if ($schedules->isEmpty()) {
            $this->info('No bell schedules for current time.');
            return Command::SUCCESS;
        }

        if (!BellHardwareGate::hardwareEnabled()) {
            $this->info('Integrasi hardware dinonaktifkan di Pengaturan - bel hanya diputar di browser.');
            return Command::SUCCESS;
        }

        foreach ($schedules as $schedule) {
            try {
                $this->info("Processing: {$schedule->bellType->name} - {$schedule->audioLibrary->title}");

                // Satu trigger per jadwal per hari (kunci yang sama dengan jalur browser)
                if (!BellHardwareGate::claim($schedule, $now)) {
                    $this->warn("  → Already triggered, skipping");
                    continue;
                }

                // Get audio library with duration
                $audio = $schedule->audioLibrary;
                if (!$audio) {
                    $this->error("  → Audio library not found");
                    continue;
                }

                // Durasi audio dari kolom `duration` (dulu salah baca `duration_seconds`
                // yang tidak ada, sehingga speaker selalu mati setelah 60 detik)
                $audioDuration = (int) ($audio->duration ?: 60);
                $start = now();

                // Get all active rooms with hardware_address
                $allRooms = Room::active()
                    ->whereNotNull('hardware_address')
                    ->get();

                if ($allRooms->isEmpty()) {
                    $this->error("  → No active rooms with hardware address");
                    continue;
                }

                // Separate parents and children
                $parents = $allRooms->filter(fn($room) => $room->isParent());
                $children = $allRooms->filter(fn($room) => $room->requiresParent());

                $this->info("  → Found {$parents->count()} parents, {$children->count()} children");

                // Step 1a: Activate PARENTS first (HORN and CTRL ROOM)
                foreach ($parents as $parent) {
                    HardwareCommandQueue::create([
                        'command_type' => 'activate_parent',
                        'payload' => [
                            'hardware_address' => $parent->hardware_address,
                            'room_id' => $parent->id,
                            'room_name' => $parent->room_name,
                            'trigger_type' => 'ON_PARENT',
                            'schedule_id' => $schedule->id,
                            'step' => '1a_ON_PARENT',
                        ],
                        'status' => 'pending',
                        'scheduled_at' => $start,
                        'expires_at' => $start->copy()->addMinutes(5),
                    ]);
                }

                $this->info("  → Step 1a: {$parents->count()} PARENT(s) activation queued");

                // Step 1b: Activate CHILDREN after parents (2 second delay)
                $childActivationTime = $start->copy()->addSeconds(2);
                foreach ($children as $child) {
                    HardwareCommandQueue::create([
                        'command_type' => 'test_speaker',
                        'payload' => [
                            'hardware_address' => $child->hardware_address,
                            'room_id' => $child->id,
                            'room_name' => $child->room_name,
                            'parent_address' => $child->parent_hardware_address,
                            'duration_seconds' => 2,
                            'trigger_type' => 'ON_CHILD',
                            'schedule_id' => $schedule->id,
                            'step' => '1b_ON_CHILD',
                        ],
                        'status' => 'pending',
                        'scheduled_at' => $childActivationTime,
                        'expires_at' => $start->copy()->addMinutes(5),
                    ]);
                }

                $this->info("  → Step 1b: {$children->count()} CHILD(ren) activation queued (at {$childActivationTime->format('H:i:s')})");

                // Step 2: Play Audio - After children activation (4 seconds total from start)
                $playTime = $start->copy()->addSeconds(4);
                $playCommand = HardwareCommandQueue::create([
                    'command_type' => 'play_audio',
                    'payload' => [
                        'audio_id' => $audio->id,
                        'audio_file' => $audio->file_path,
                        'audio_title' => $audio->title,
                        'duration_seconds' => $audioDuration,
                        'room_count' => $allRooms->count(),
                        'schedule_id' => $schedule->id,
                        'bell_type' => $schedule->bellType->name,
                        'step' => '2_PLAY_AUDIO',
                    ],
                    'status' => 'pending',
                    'scheduled_at' => $playTime,
                    'expires_at' => $playTime->copy()->addMinutes(10),
                ]);

                $this->info("  → Step 2: PLAY AUDIO queued (at {$playTime->format('H:i:s')}, duration: {$audioDuration}s)");

                // Step 3a: Turn off CHILDREN first (reverse order - children before parents)
                $offTime = $playTime->copy()->addSeconds($audioDuration + 2);
                foreach ($children as $child) {
                    HardwareCommandQueue::create([
                        'command_type' => 'stop_speaker',
                        'payload' => [
                            'hardware_address' => $child->hardware_address,
                            'room_id' => $child->id,
                            'room_name' => $child->room_name,
                            'trigger_type' => 'OFF_CHILD',
                            'schedule_id' => $schedule->id,
                            'step' => '3a_OFF_CHILD',
                        ],
                        'status' => 'pending',
                        'scheduled_at' => $offTime,
                        'expires_at' => $offTime->copy()->addMinutes(5),
                    ]);
                }

                $this->info("  → Step 3a: {$children->count()} CHILD(ren) deactivation queued (at {$offTime->format('H:i:s')})");

                // Step 3b: Turn off PARENTS last (1 second after children)
                $parentOffTime = $offTime->copy()->addSecond();
                foreach ($parents as $parent) {
                    HardwareCommandQueue::create([
                        'command_type' => 'stop_speaker',
                        'payload' => [
                            'hardware_address' => $parent->hardware_address,
                            'room_id' => $parent->id,
                            'room_name' => $parent->room_name,
                            'trigger_type' => 'OFF_PARENT',
                            'schedule_id' => $schedule->id,
                            'step' => '3b_OFF_PARENT',
                        ],
                        'status' => 'pending',
                        'scheduled_at' => $parentOffTime,
                        'expires_at' => $parentOffTime->copy()->addMinutes(5),
                    ]);
                }

                $this->info("  → Step 3b: {$parents->count()} PARENT(s) deactivation queued (at {$parentOffTime->format('H:i:s')})");
                $this->info("  ✓ Complete workflow: PARENTS ON → CHILDREN ON → PLAY ({$audioDuration}s) → CHILDREN OFF → PARENTS OFF");

            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
                \Log::error("Bell schedule trigger error", [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("✓ Bell schedule check completed");
        return Command::SUCCESS;
    }
}
