<?php

namespace App\Console\Commands;

use App\Models\AudioLibrary;
use App\Models\BellSchedule;
use App\Models\HardwareCommandQueue;
use App\Models\Room;
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
        $now = Carbon::now('Asia/Jakarta');
        $currentDay = strtolower($now->englishDayOfWeek); // monday, tuesday, etc
        $currentTime = $now->format('H:i'); // HH:MM format

        $this->info("Checking bell schedules for {$currentDay} at {$currentTime}...");

        // Find schedules that should ring now
        $schedules = BellSchedule::with(['bellType', 'audioLibrary'])
            ->where('day', $currentDay)
            ->where('time', $currentTime)
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('No bell schedules for current time.');
            return Command::SUCCESS;
        }

        foreach ($schedules as $schedule) {
            try {
                $this->info("Processing: {$schedule->bellType->name} - {$schedule->audioLibrary->title}");

                // Check if already processed in the last minute (avoid duplicate triggers)
                $recentCommand = HardwareCommandQueue::where('created_at', '>=', $now->copy()->subMinute())
                    ->where('command_type', 'play_audio')
                    ->whereJsonContains('payload->schedule_id', $schedule->id)
                    ->first();

                if ($recentCommand) {
                    $this->warn("  → Already triggered recently, skipping");
                    continue;
                }

                // Get audio library with duration
                $audio = $schedule->audioLibrary;
                if (!$audio) {
                    $this->error("  → Audio library not found");
                    continue;
                }

                // Calculate audio duration (use stored duration or default 60 seconds)
                $audioDuration = $audio->duration_seconds ?? 60;

                // Get all active rooms with speaker zones
                $rooms = Room::with('speakerZone')
                    ->active()
                    ->whereNotNull('speaker_zone_id')
                    ->get();

                if ($rooms->isEmpty()) {
                    $this->error("  → No active rooms with speaker zones");
                    continue;
                }

                $zones = $rooms->pluck('speakerZone.modbus_channel')->unique()->values()->toArray();

                // Step 1: ON ALL - Activate all speakers (PARENT + all groups)
                $onAllCommand = HardwareCommandQueue::create([
                    'command_type' => 'trigger_bell',
                    'payload' => [
                        'zones' => $zones,
                        'duration_seconds' => 2, // Short activation signal
                        'trigger_type' => 'ON_ALL',
                        'room_count' => $rooms->count(),
                        'schedule_id' => $schedule->id,
                        'step' => '1_ON_ALL',
                    ],
                    'status' => 'pending',
                    'scheduled_at' => $now,
                    'expires_at' => $now->copy()->addMinutes(5),
                ]);

                $this->info("  → Step 1: ON ALL command queued (ID: {$onAllCommand->id})");

                // Step 2: Play Audio - After ON ALL signal
                $playTime = $now->copy()->addSeconds(3); // Wait 3 seconds after ON ALL
                $playCommand = HardwareCommandQueue::create([
                    'command_type' => 'play_audio',
                    'payload' => [
                        'zones' => $zones,
                        'audio_id' => $audio->id,
                        'audio_file' => $audio->file_path,
                        'audio_title' => $audio->title,
                        'duration_seconds' => $audioDuration,
                        'room_count' => $rooms->count(),
                        'schedule_id' => $schedule->id,
                        'bell_type' => $schedule->bellType->name,
                        'step' => '2_PLAY_AUDIO',
                    ],
                    'status' => 'pending',
                    'scheduled_at' => $playTime,
                    'expires_at' => $playTime->copy()->addMinutes(10),
                ]);

                $this->info("  → Step 2: PLAY AUDIO command queued (ID: {$playCommand->id}, scheduled at {$playTime->format('H:i:s')})");

                // Step 3: OFF ALL - Turn off all speakers after audio finishes
                $offTime = $playTime->copy()->addSeconds($audioDuration + 2); // Wait for audio + 2 sec buffer
                $offAllCommand = HardwareCommandQueue::create([
                    'command_type' => 'stop_all',
                    'payload' => [
                        'zones' => $zones,
                        'action' => 'stop',
                        'trigger_type' => 'OFF_ALL',
                        'room_count' => $rooms->count(),
                        'schedule_id' => $schedule->id,
                        'step' => '3_OFF_ALL',
                    ],
                    'status' => 'pending',
                    'scheduled_at' => $offTime,
                    'expires_at' => $offTime->copy()->addMinutes(5),
                ]);

                $this->info("  → Step 3: OFF ALL command queued (ID: {$offAllCommand->id}, scheduled at {$offTime->format('H:i:s')})");
                $this->info("  ✓ Complete workflow scheduled: ON ALL → PLAY ({$audioDuration}s) → OFF ALL");

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
