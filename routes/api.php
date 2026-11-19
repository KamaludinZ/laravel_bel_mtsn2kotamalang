<?php

use App\Http\Controllers\Api\HardwareApiController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PublicController;
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

// Health Check Endpoint (Docker healthcheck, Coolify & monitoring)
Route::get('/health', HealthController::class)->name('api.health');

// Endpoint polling halaman bel publik. Sengaja di grup API (stateless):
// tiap layar bel melakukan polling berkala, jadi jangan membuat/menulis sesi.
Route::get('/today-schedule', [PublicController::class, 'getTodaySchedule'])->name('api.today-schedule');
Route::get('/current-time', [PublicController::class, 'getCurrentTime'])->name('api.current-time');
Route::post('/queue-hardware-trigger', [PublicController::class, 'queueHardwareTrigger'])
    ->middleware('throttle:30,1')
    ->name('api.queue-hardware-trigger');

// Hardware Bridge API (secured with Bearer token)
Route::prefix('hardware')->middleware('hardware.auth')->group(function () {
    Route::get('/pending-commands', [HardwareApiController::class, 'getPendingCommands']);
    Route::post('/report-result', [HardwareApiController::class, 'reportResult']);
    Route::get('/config', [HardwareApiController::class, 'getConfig']);
    Route::post('/heartbeat', [HardwareApiController::class, 'heartbeat']);
});
