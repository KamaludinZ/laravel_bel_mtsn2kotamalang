<?php

namespace App\Services;

use App\Models\BellSchedule;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Aturan bersama untuk memicu bel hardware, dipakai oleh:
 *  - scheduler server (bell:check-schedule)  -> jalur UTAMA
 *  - halaman bel di browser (/api/queue-hardware-trigger) -> CADANGAN bila scheduler mati
 *
 * Menjamin: hanya jenis bel aktif + otomatis + hari aktif, integrasi hardware
 * menyala, dan SATU trigger per jadwal per hari (kunci cache bersama).
 */
class BellHardwareGate
{
    /** Scheduler dianggap hidup jika heartbeat-nya lebih baru dari ini (detik). */
    public const SCHEDULER_ALIVE_SECONDS = 150;

    public static function now(): Carbon
    {
        return Carbon::now(config('app.bell_timezone', config('app.timezone')));
    }

    public static function hardwareEnabled(): bool
    {
        return Setting::get('hardware_integration_enabled', 'false') === 'true';
    }

    /**
     * Jadwal milik jenis bel yang aktif, otomatis, dan hari ini termasuk hari aktifnya.
     */
    public static function isScheduleActiveToday(BellSchedule $schedule, Carbon $now): bool
    {
        $type = $schedule->bellType;
        $day = strtolower($now->englishDayOfWeek);

        return $type
            && $type->is_active
            && $type->is_automatic
            && $schedule->day === $day
            && in_array($day, $type->active_days ?? [], true);
    }

    public static function schedulerAlive(): bool
    {
        try {
            $beat = Cache::get('health:scheduler_heartbeat');
        } catch (\Throwable $e) {
            return false;
        }

        return is_numeric($beat) && (now()->timestamp - (int) $beat) <= self::SCHEDULER_ALIVE_SECONDS;
    }

    /**
     * Ambil hak memicu hardware untuk jadwal ini hari ini. False = sudah dipicu.
     */
    public static function claim(BellSchedule $schedule, Carbon $now): bool
    {
        $key = 'bell-hw-trigger:' . $schedule->id . ':' . $now->format('Y-m-d');

        return Cache::add($key, true, now()->addMinutes(15));
    }
}
