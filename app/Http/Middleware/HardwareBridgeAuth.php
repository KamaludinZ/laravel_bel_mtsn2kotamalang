<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HardwareBridgeAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->bearerToken();
        // Lewat config, bukan env(): setelah `php artisan config:cache` (dipakai di
        // production) env() di luar file config mengembalikan null.
        $expectedToken = (string) config('services.hardware_bridge.token');

        if ($expectedToken === '' || $token === '' || !hash_equals($expectedToken, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid API Token'
            ], 401);
        }

        return $next($request);
    }
}
