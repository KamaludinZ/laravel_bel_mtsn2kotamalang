<?php

namespace App\Http\Controllers;

use App\Models\HardwareConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Healthcheck aplikasi.
 *
 * - KRITIS (gagal => HTTP 503, container dianggap unhealthy): database & storage.
 * - PERINGATAN (status "degraded", tetap HTTP 200): cache, scheduler, hardware bridge.
 *   Container web tidak boleh di-restart hanya karena scheduler/bridge bermasalah.
 *
 * Detail error hanya ditampilkan bila APP_DEBUG=true (tidak membocorkan info server).
 */
class HealthController extends Controller
{
    /** Scheduler dianggap mati jika heartbeat lebih tua dari ini (detik). */
    private const SCHEDULER_STALE_SECONDS = 180;

    public function __invoke(): JsonResponse
    {
        $checks = [];
        $critical = true;
        $degraded = false;

        // 1. Database (kritis)
        $checks['database'] = $this->run(function () {
            DB::select('select 1');
            return 'ok';
        });
        if ($checks['database'] !== 'ok') {
            $critical = false;
        }

        // 2. Storage bisa ditulis (kritis: sesi file, log, cache view, upload audio)
        $checks['storage'] = is_writable(storage_path('framework')) && is_writable(storage_path('logs'))
            ? 'ok'
            : 'not_writable';
        if ($checks['storage'] !== 'ok') {
            $critical = false;
        }

        // 3. Cache (store yang sedang dipakai, round-trip baca/tulis)
        $checks['cache'] = $this->run(function () {
            $key = 'health:ping';
            $value = (string) microtime(true);
            Cache::put($key, $value, 60);
            return Cache::get($key) === $value ? 'ok' : 'mismatch';
        });
        if ($checks['cache'] !== 'ok') {
            $degraded = true;
        }

        // 4. Scheduler (bel hardware dari server bergantung pada ini)
        $lastBeat = $this->run(fn () => Cache::get('health:scheduler_heartbeat'), null);
        $schedulerAge = is_numeric($lastBeat) ? now()->timestamp - (int) $lastBeat : null;
        $checks['scheduler'] = [
            'status' => $schedulerAge !== null && $schedulerAge <= self::SCHEDULER_STALE_SECONDS ? 'ok' : 'stale',
            'last_run_seconds_ago' => $schedulerAge,
        ];
        if ($checks['scheduler']['status'] !== 'ok') {
            $degraded = true;
        }

        // 5. Hardware bridge (informasi; tidak memengaruhi status jika integrasi tidak dipakai)
        $checks['hardware_bridge'] = $this->run(function () {
            $config = HardwareConfig::primary();
            if (!$config || !$config->last_connected_at) {
                return ['status' => 'never_connected'];
            }
            return [
                'status' => $config->isOnline() ? 'online' : 'offline',
                'last_seen_seconds_ago' => (int) $config->last_connected_at->diffInSeconds(now(), true),
            ];
        }, ['status' => 'unknown']);

        $status = !$critical ? 'unhealthy' : ($degraded ? 'degraded' : 'healthy');

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $critical ? 200 : 503)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Jalankan satu pengecekan; kembalikan 'error' (atau $fallback) bila gagal.
     */
    private function run(callable $check, $fallback = 'error')
    {
        try {
            return $check();
        } catch (\Throwable $e) {
            Log::warning('Healthcheck gagal: ' . $e->getMessage());
            if ($fallback === 'error' && config('app.debug')) {
                return 'error: ' . $e->getMessage();
            }
            return $fallback;
        }
    }
}
