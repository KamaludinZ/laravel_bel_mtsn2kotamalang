<?php

namespace App\Providers;

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
        // Share app settings to all views
        view()->composer('*', function ($view) {
            $view->with('appName', \App\Models\Setting::get('app_name', config('app.name')));
            $view->with('appLogo', \App\Models\Setting::get('app_logo'));
        });
    }
}
