<?php

use App\Http\Controllers\Api\HardwareApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Hardware Bridge API routes for Python Bridge Service
| These endpoints are polled by the bridge service running on school PC
|
*/

// Health Check Endpoint (for Docker healthcheck and monitoring)
Route::get('/health', function () {
    $services = [];
    $healthy = true;

    try {
        // Check database connection
        try {
            \DB::connection()->getPdo();
            $services['database'] = 'connected';
        } catch (\Exception $e) {
            $services['database'] = 'error: ' . $e->getMessage();
            $healthy = false;
        }

        // Check Redis connection (if using Redis)
        try {
            if (config('cache.default') === 'redis') {
                \Cache::driver('redis')->get('health_check');
                $services['cache'] = 'connected';
            } else {
                $services['cache'] = 'not_configured';
            }
        } catch (\Exception $e) {
            $services['cache'] = 'error: ' . $e->getMessage();
            // Don't mark as unhealthy if cache fails - app can still work
        }

        $services['app'] = 'running';

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'services' => $services
        ], $healthy ? 200 : 503);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
            'timestamp' => now()->toIso8601String()
        ], 503);
    }
});

// Hardware Bridge API (secured with Bearer token)
Route::prefix('hardware')->middleware('hardware.auth')->group(function () {
    Route::get('/pending-commands', [HardwareApiController::class, 'getPendingCommands']);
    Route::post('/report-result', [HardwareApiController::class, 'reportResult']);
    Route::get('/config', [HardwareApiController::class, 'getConfig']);
    Route::post('/heartbeat', [HardwareApiController::class, 'heartbeat']);
});
