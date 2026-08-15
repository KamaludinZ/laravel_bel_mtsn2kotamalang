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

// Hardware Bridge API (secured with Bearer token)
Route::prefix('hardware')->group(function () {
    Route::get('/pending-commands', [HardwareApiController::class, 'getPendingCommands']);
    Route::post('/report-result', [HardwareApiController::class, 'reportResult']);
    Route::get('/config', [HardwareApiController::class, 'getConfig']);
    Route::post('/heartbeat', [HardwareApiController::class, 'heartbeat']);
});
