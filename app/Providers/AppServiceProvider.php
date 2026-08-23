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

        // Share app settings to all views
        view()->composer('*', function ($view) {
            try {
                // Check if settings table exists before querying
                if (\Schema::hasTable('settings')) {
                    $view->with('appName', \App\Models\Setting::get('app_name', config('app.name')));
                    $view->with('appLogo', \App\Models\Setting::get('app_logo'));
                } else {
                    // Table doesn't exist yet, use defaults
                    $view->with('appName', config('app.name', 'Bel Sekolah'));
                    $view->with('appLogo', null);
                }
            } catch (\Throwable $e) {
                // Fallback if any error occurs
                \Log::warning('AppServiceProvider view composer error: ' . $e->getMessage());
                $view->with('appName', config('app.name', 'Bel Sekolah'));
                $view->with('appLogo', null);
            }
        });
    }
}
