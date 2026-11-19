<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Bell Schedule Checker - runs every minute to check if any bell should ring
Schedule::command('bell:check-schedule')
    ->everyMinute()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/bell-scheduler.log'));

// Heartbeat scheduler: dipakai /api/health dan healthcheck container scheduler
// untuk memastikan scheduler benar-benar berjalan setiap menit.
Schedule::call(function () {
    \Illuminate\Support\Facades\Cache::put('health:scheduler_heartbeat', now()->timestamp, now()->addMinutes(10));
    @touch(storage_path('framework/scheduler-heartbeat'));
})->everyMinute()->name('health-heartbeat');

// Bersihkan data hardware lama agar tabel tidak terus membesar (query polling bridge tetap cepat)
Schedule::call(function () {
    \App\Models\HardwareCommandQueue::whereIn('status', ['completed', 'failed', 'expired'])
        ->where('created_at', '<', now()->subDays(7))
        ->delete();
    \App\Models\HardwareLog::where('created_at', '<', now()->subDays(30))->delete();
})->dailyAt('01:00')->timezone('Asia/Jakarta')->name('prune-hardware-data')->withoutOverlapping();
