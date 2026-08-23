<?php

use App\Http\Controllers\AudioLibraryController;
use App\Http\Controllers\BellScheduleController;
use App\Http\Controllers\BellTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HardwareController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Emergency debug route - absolutely minimal
Route::get('/ping', function() {
    return 'PONG - Laravel is working!';
});

// Public Routes
Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::post('/activate-bell-type', [PublicController::class, 'activateBellType'])->name('public.activate-bell-type');
Route::get('/api/today-schedule', [PublicController::class, 'getTodaySchedule'])->name('api.today-schedule');
Route::get('/api/current-time', [PublicController::class, 'getCurrentTime'])->name('api.current-time');

// API untuk queue hardware trigger (dipanggil dari JavaScript)
Route::post('/api/queue-hardware-trigger', [PublicController::class, 'queueHardwareTrigger'])->name('api.queue-hardware-trigger');

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

// Debug route - test database and models
Route::get('/debug', function() {
    try {
        $data = [
            'status' => 'OK',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_connected' => false,
            'tables_exist' => [],
            'models_count' => [],
        ];

        // Test database connection
        try {
            \DB::connection()->getPdo();
            $data['database_connected'] = true;
        } catch (\Exception $e) {
            $data['database_error'] = $e->getMessage();
        }

        // Check if tables exist
        $tables = ['bell_types', 'audio_libraries', 'bell_schedules', 'settings', 'users'];
        foreach ($tables as $table) {
            $data['tables_exist'][$table] = \Schema::hasTable($table);
        }

        // Try to count records
        if ($data['tables_exist']['bell_types']) {
            $data['models_count']['bell_types'] = \App\Models\BellType::count();
        }
        if ($data['tables_exist']['audio_libraries']) {
            $data['models_count']['audio_libraries'] = \App\Models\AudioLibrary::count();
        }

        return response()->json($data);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

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
    Route::get('bell-schedules/export', [BellScheduleController::class, 'export'])->name('bell-schedules.export');
    Route::get('bell-schedules/export-importable', [BellScheduleController::class, 'exportImportable'])->name('bell-schedules.export-importable');
    Route::get('bell-schedules/download-template', [BellScheduleController::class, 'downloadTemplate'])->name('bell-schedules.download-template');
    Route::post('bell-schedules/bulk-update', [BellScheduleController::class, 'bulkUpdate'])->name('bell-schedules.bulk-update');
    Route::post('bell-schedules/bulk-destroy', [BellScheduleController::class, 'bulkDestroy'])->name('bell-schedules.bulk-destroy');
    Route::resource('bell-schedules', BellScheduleController::class);

    // Settings Management
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('settings/toggle-hardware', [SettingController::class, 'toggleHardware'])->name('settings.toggle-hardware');
    Route::post('settings/clear-cache', [SettingController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('settings/clear-logs', [SettingController::class, 'clearLogs'])->name('settings.clear-logs');
    Route::post('settings/check-update', [SettingController::class, 'checkGithubUpdate'])->name('settings.check-update');
    Route::post('settings/run-update', [SettingController::class, 'runGithubUpdate'])->name('settings.run-update');

    // Backup & Restore Routes
    Route::post('settings/backup/create', [SettingController::class, 'createBackup'])->name('settings.backup.create');
    Route::get('settings/backup/download/{filename}', [SettingController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::delete('settings/backup/delete/{filename}', [SettingController::class, 'deleteBackup'])->name('settings.backup.delete');
    Route::post('settings/backup/restore', [SettingController::class, 'restoreBackup'])->name('settings.backup.restore');
    Route::post('settings/backup/upload', [SettingController::class, 'uploadBackup'])->name('settings.backup.upload');

    // Hardware Management Routes
    Route::prefix('hardware')->name('hardware.')->group(function () {
        Route::get('/', [HardwareController::class, 'index'])->name('index');
        Route::post('/test-speaker', [HardwareController::class, 'testSpeaker'])->name('test-speaker');
        Route::post('/test-all-zones', [HardwareController::class, 'testAllZones'])->name('test-all-zones');
        Route::post('/off-all', [HardwareController::class, 'offAll'])->name('off-all');
        Route::post('/test-room', [HardwareController::class, 'testRoom'])->name('test-room');
        Route::post('/test-group', [HardwareController::class, 'testGroup'])->name('test-group');
        Route::post('/test-type', [HardwareController::class, 'testType'])->name('test-type');
        Route::post('/update-room-name', [HardwareController::class, 'updateRoomName'])->name('update-room-name');
        Route::post('/bulk-update-room-names', [HardwareController::class, 'bulkUpdateRoomNames'])->name('bulk-update-room-names');
        Route::post('/bulk-update-rooms', [HardwareController::class, 'bulkUpdateRooms'])->name('bulk-update-rooms');
        Route::post('/update-group-labels', [HardwareController::class, 'updateGroupLabels'])->name('update-group-labels');
        Route::post('/update-parent-channel', [HardwareController::class, 'updateParentChannel'])->name('update-parent-channel');
        Route::post('/update-config', [HardwareController::class, 'updateConfig'])->name('update-config');
        Route::get('/logs', [HardwareController::class, 'logs'])->name('logs');
        Route::post('/logs/clear', [HardwareController::class, 'clearOldLogs'])->name('logs.clear');
        Route::get('/zones', [HardwareController::class, 'zones'])->name('zones');
        Route::patch('/zones/{zone}', [HardwareController::class, 'updateZone'])->name('zones.update');
    });
});

require __DIR__.'/auth.php';
