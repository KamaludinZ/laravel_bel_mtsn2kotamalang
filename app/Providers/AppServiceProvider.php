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
                $view->with('appName', \App\Models\Setting::get('app_name', config('app.name')));
                $view->with('appLogo', \App\Models\Setting::get('app_logo'));
            } catch (\Exception $e) {
                // Fallback if settings table doesn't exist yet or has issues
                $view->with('appName', config('app.name', 'Laravel'));
                $view->with('appLogo', null);
            }
        });
    }
}
