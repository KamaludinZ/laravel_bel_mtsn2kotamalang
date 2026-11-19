<?php

namespace App\Http\Controllers;

use App\Models\BellSchedule;
use App\Models\BellType;
use App\Models\HardwareCommandQueue;
use App\Models\Setting;
use App\Models\SpeakerZone;
use App\Services\BellHardwareGate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Waktu "sekarang" menurut zona waktu sekolah (default Asia/Jakarta).
     * config('app.timezone') masih UTC, jadi jangan pakai Carbon::now() polos
     * untuk menentukan hari/jam bel.
     */
    private function schoolNow(): Carbon
    {
        return Carbon::now(config('app.bell_timezone', 'Asia/Jakarta'));
    }

    /**
     * Display the public bell display page.
     */
    public function index()
    {
        try {
            $bellTypes = BellType::orderBy('is_active', 'desc')->orderBy('name')->get();
            $activeBellType = BellType::where('is_active', true)->first();
            $audioLibraries = \App\Models\AudioLibrary::orderBy('title')->get();

            return view('public.index', compact('bellTypes', 'activeBellType', 'audioLibraries'));
        } catch (\Exception $e) {
            // Log error and show user-friendly message
            \Log::error('PublicController index error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            // Return simple error view or JSON for debugging
            return response()->json([
                'error' => 'Application error',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while loading the page',
                'file' => config('app.debug') ? $e->getFile() : null,
                'line' => config('app.debug') ? $e->getLine() : null,
            ], 500);
        }
    }

    /**
     * Activate a bell type from public page.
     */
    public function activateBellType(Request $request)
    {
        $request->validate([
            'bell_type_id' => 'required|exists:bell_types,id',
        ]);

        // Deactivate all bell types
        BellType::query()->update(['is_active' => false]);

        // Activate selected bell type
        $bellType = BellType::findOrFail($request->bell_type_id);
        $bellType->update(['is_active' => true]);

        return redirect()->route('public.index')
            ->with('success', 'Jenis bel "' . $bellType->name . '" berhasil diaktifkan');
    }

    /**
     * Get today's bell schedules for the active bell type.
     */
    public function getTodaySchedule()
    {
        // Get active bell type
        $activeBellType = BellType::where('is_active', true)->first();

        if (!$activeBellType) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada jenis bel yang aktif',
                'data' => []
            ]);
        }

        // Get current day in English (lowercase) - menurut zona waktu sekolah
        $now = $this->schoolNow();
        $today = strtolower($now->format('l')); // monday, tuesday, etc.

        // Check if today is in the active days
        if (!in_array($today, $activeBellType->active_days ?? [])) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada jadwal untuk hari ini',
                'bell_type' => $activeBellType->name,
                'is_automatic' => $activeBellType->is_automatic,
                'data' => []
            ]);
        }

        // Get schedules for today
        $schedules = BellSchedule::with('audioLibrary')
            ->where('bell_type_id', $activeBellType->id)
            ->where('day', $today)
            ->orderBy('time')
            ->get()
            ->filter(fn ($schedule) => $schedule->audioLibrary !== null)
            ->values()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'time' => date('H:i', strtotime($schedule->time)),
                    'time_full' => date('H:i:s', strtotime($schedule->time)),
                    'keterangan' => $schedule->keterangan,
                    'audio' => [
                        'id' => $schedule->audioLibrary->id,
                        'title' => $schedule->audioLibrary->title,
                        'file_url' => asset('storage/' . $schedule->audioLibrary->file_path),
                        'duration' => $schedule->audioLibrary->duration,
                        'duration_formatted' => $schedule->audioLibrary->formatted_duration,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diambil',
            'bell_type' => $activeBellType->name,
            'is_automatic' => $activeBellType->is_automatic,
            'current_time' => $now->format('H:i:s'),
            'current_date' => $now->format('Y-m-d'),
            'day' => $today,
            'data' => $schedules
        ]);
    }

    /**
     * Get current server time.
     */
    public function getCurrentTime()
    {
        $now = $this->schoolNow();

        return response()->json([
            'success' => true,
            'time' => $now->format('H:i:s'),
            'date' => $now->format('Y-m-d'),
            'datetime' => $now->toIso8601String(),
            // Epoch ms + offset zona waktu sekolah: browser menghitung jam bel
            // dari sini sehingga tidak bergantung pada zona waktu perangkat.
            'timestamp_ms' => (int) $now->getPreciseTimestamp(3),
            'utc_offset_minutes' => $now->utcOffset(),
            'timezone' => $now->getTimezone()->getName(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Queue hardware trigger from frontend
     */
    public function queueHardwareTrigger(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|uuid|exists:bell_schedules,id',
            'audio_duration' => 'nullable|integer',
        ]);

        $schedule = BellSchedule::with(['audioLibrary', 'bellType'])->findOrFail($validated['schedule_id']);

        // Endpoint ini publik, jadi hanya terima jadwal yang memang sedang jatuh tempo:
        // jenis bel aktif + otomatis + hari aktif, dan 0..5 menit setelah jam jadwal.
        $now = BellHardwareGate::now();
        $scheduleAt = $now->copy()->setTimeFromTimeString(date('H:i:s', strtotime($schedule->time)));
        $secondsSince = $scheduleAt->diffInSeconds($now, false);

        $isDue = BellHardwareGate::isScheduleActiveToday($schedule, $now)
            && $secondsSince >= 0
            && $secondsSince <= 300;

        if (!$isDue || !$schedule->audioLibrary) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak sedang jatuh tempo'], 422);
        }

        // Scheduler server adalah jalur utama bel hardware. Browser hanya cadangan
        // ketika scheduler tidak berjalan, agar hardware tidak dipicu dua kali.
        if (BellHardwareGate::schedulerAlive()) {
            return response()->json(['success' => true, 'handled_by' => 'scheduler']);
        }

        // Banyak browser/tab bisa membuka halaman ini sekaligus: hanya SEKALI per jadwal per hari.
        if (!BellHardwareGate::claim($schedule, $now)) {
            return response()->json(['success' => true, 'duplicate' => true]);
        }

        \Log::warning("Scheduler tidak aktif - bel hardware dipicu dari browser (cadangan) untuk jadwal {$schedule->id}");
        $this->triggerHardware($schedule, $validated['audio_duration'] ?? null);

        return response()->json(['success' => true]);
    }

    /**
     * Queue hardware trigger command
     */
    private function triggerHardware($schedule, $duration = null)
    {
        try {
            // ===== CHECK IF HARDWARE INTEGRATION IS ENABLED =====
            $hardwareEnabled = Setting::get('hardware_integration_enabled', 'false') === 'true';

            if (!$hardwareEnabled) {
                \Log::info('Hardware integration disabled - skipping hardware trigger for schedule: ' . $schedule->id);
                return; // Skip hardware trigger, audio will only play in browser
            }

            // Get all active zones from all active rooms (including PARENT channels)
            $rooms = \App\Models\Room::with('speakerZone')
                ->active()
                ->whereNotNull('speaker_zone_id')
                ->get();

            $zones = $rooms->pluck('speakerZone.modbus_channel')->unique()->values()->toArray();

            if (empty($zones)) {
                \Log::warning('No active rooms with speaker zones found for hardware trigger');
                return;
            }

            // Calculate duration from audio or use default
            $audioDuration = $schedule->audioLibrary->duration ?? 180; // 3 minutes default
            $triggerDuration = $duration ?? $audioDuration;

            // STEP 1: Activate ON ALL - Turn on all speakers (PARENT + all groups)
            HardwareCommandQueue::create([
                'command_type' => 'trigger_bell',
                'payload' => [
                    'zones' => $zones,
                    'duration_seconds' => (int) $triggerDuration,
                    'schedule_id' => $schedule->id,
                    'audio_id' => $schedule->audioLibrary->id,
                    'audio_title' => $schedule->audioLibrary->title,
                    'trigger_type' => 'SCHEDULE_ON_ALL',
                    'room_count' => $rooms->count(),
                ],
                'status' => 'pending',
                'scheduled_at' => now(),
                'expires_at' => now()->addMinutes(10),
            ]);

            // STEP 2: Schedule OFF ALL command after audio duration + 2 seconds buffer
            $offAllDelay = (int) $triggerDuration + 2;
            HardwareCommandQueue::create([
                'command_type' => 'stop_all',
                'payload' => [
                    'zones' => $zones,
                    'action' => 'stop',
                    'trigger_type' => 'SCHEDULE_OFF_ALL',
                    'schedule_id' => $schedule->id,
                ],
                'status' => 'pending',
                'scheduled_at' => now()->addSeconds($offAllDelay),
                'expires_at' => now()->addMinutes(15),
            ]);

            \Log::info("Hardware trigger queued for schedule {$schedule->id}: ON ALL ({$triggerDuration}s) -> OFF ALL (after {$offAllDelay}s), zones: " . implode(',', $zones));

        } catch (\Exception $e) {
            \Log::error('Error queueing hardware trigger: ' . $e->getMessage());
        }
    }
}
