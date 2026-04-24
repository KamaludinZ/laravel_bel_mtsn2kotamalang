<?php

use App\Http\Controllers\AudioLibraryController;
use App\Http\Controllers\BellScheduleController;
use App\Http\Controllers\BellTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::post('/activate-bell-type', [PublicController::class, 'activateBellType'])->name('public.activate-bell-type');
Route::get('/api/today-schedule', [PublicController::class, 'getTodaySchedule'])->name('api.today-schedule');
Route::get('/api/current-time', [PublicController::class, 'getCurrentTime'])->name('api.current-time');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Test route untuk debug
Route::get('/test-dashboard', function() {
    return response()->json([
        'status' => 'OK',
        'users' => \App\Models\User::count(),
        'audio' => \App\Models\AudioLibrary::count(),
        'schedules' => \App\Models\BellSchedule::count(),
        'bell_types' => \App\Models\BellType::count(),
    ]);
})->middleware(['auth']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Audio Library Management
    Route::resource('audio-libraries', AudioLibraryController::class);

    // Bell Types Management
    Route::post('bell-types/{bellType}/activate', [BellTypeController::class, 'activate'])->name('bell-types.activate');
    Route::resource('bell-types', BellTypeController::class);

    // Bell Schedules Management
    Route::get('bell-schedules/import', [BellScheduleController::class, 'importForm'])->name('bell-schedules.import.form');
    Route::post('bell-schedules/import', [BellScheduleController::class, 'import'])->name('bell-schedules.import');
    Route::get('bell-schedules/download-template', [BellScheduleController::class, 'downloadTemplate'])->name('bell-schedules.download-template');
    Route::post('bell-schedules/bulk-update', [BellScheduleController::class, 'bulkUpdate'])->name('bell-schedules.bulk-update');
    Route::post('bell-schedules/bulk-destroy', [BellScheduleController::class, 'bulkDestroy'])->name('bell-schedules.bulk-destroy');
    Route::resource('bell-schedules', BellScheduleController::class);

    // Settings Management
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/clear-cache', [SettingController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('settings/clear-logs', [SettingController::class, 'clearLogs'])->name('settings.clear-logs');
});

require __DIR__.'/auth.php';
