<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production (when behind reverse proxy like Coolify)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Share app settings to all views.
        // Composer '*' terpanggil untuk SETIAP view & komponen Blade, jadi nilainya
        // dihitung sekali per request (once) dan diambil dari cache setting.
        view()->composer('*', function ($view) {
            $shared = once(function () {
                try {
                    return [
                        'appName' => \App\Models\Setting::get('app_name', config('app.name', 'Bel Sekolah')),
                        'appLogo' => \App\Models\Setting::get('app_logo'),
                    ];
                } catch (\Throwable $e) {
                    // Tabel belum ada (instalasi baru) / database bermasalah
                    \Log::warning('AppServiceProvider view composer error: ' . $e->getMessage());
                    return ['appName' => config('app.name', 'Bel Sekolah'), 'appLogo' => null];
                }
            });

            $view->with($shared);
        });
    }
}
