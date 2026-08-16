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
    try {
        // Check database connection
        \DB::connection()->getPdo();

        // Check Redis connection (if using Redis)
        if (config('cache.default') === 'redis') {
            \Cache::driver('redis')->get('health_check');
        }

        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => 'connected',
                'cache' => 'connected',
                'app' => 'running'
            ]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
            'timestamp' => now()->toIso8601String()
        ], 503);
    }
});

// Hardware Bridge API (secured with Bearer token)
Route::prefix('hardware')->group(function () {
    Route::get('/pending-commands', [HardwareApiController::class, 'getPendingCommands']);
    Route::post('/report-result', [HardwareApiController::class, 'reportResult']);
    Route::get('/config', [HardwareApiController::class, 'getConfig']);
    Route::post('/heartbeat', [HardwareApiController::class, 'heartbeat']);
});
