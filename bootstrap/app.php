<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust proxies for HTTPS detection behind reverse proxy (Coolify)
        $middleware->trustProxies(at: '*');

        // Endpoint trigger hardware dipanggil otomatis oleh halaman publik yang bisa
        // terbuka berjam-jam (sesi/CSRF bisa kedaluwarsa -> 419). Endpoint ini
        // divalidasi ketat di controller (jadwal aktif, hari ini, waktu sekarang).
        $middleware->validateCsrfTokens(except: [
            'api/queue-hardware-trigger',
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'hardware.auth' => \App\Http\Middleware\HardwareBridgeAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
