<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $appName ?? config('app.name', 'Bel Sekolah') }}</title>

        <!-- Favicon -->
        @php
            $appLogo = \App\Models\Setting::where('key', 'app_logo')->value('value');
        @endphp
        @if($appLogo)
            <link rel="icon" type="image/png" href="{{ asset('storage/' . $appLogo) }}">
            <link rel="apple-touch-icon" href="{{ asset('storage/' . $appLogo) }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <!-- Logo Section -->
            <div class="mb-8">
                <a href="/" class="flex flex-col items-center gap-3">
                    @php
                        $appLogo = \App\Models\Setting::where('key', 'app_logo')->value('value');
                        $appName = \App\Models\Setting::where('key', 'app_name')->value('value');
                    @endphp
                    @if($appLogo)
                        <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo" class="h-20 w-auto">
                    @else
                        <x-application-logo class="w-20 h-20 fill-current text-white" />
                    @endif
                    <h1 class="text-2xl font-bold text-white text-center">{{ $appName ?? config('app.name') }}</h1>
                </a>
            </div>

            <!-- Form Card with Glassmorphism -->
            <div class="w-full sm:max-w-md">
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 shadow-xl overflow-hidden sm:rounded-2xl px-8 py-8">
                    {{ $slot }}
                </div>

                <!-- Back to Home Link -->
                <div class="mt-6 text-center">
                    <a href="/" class="inline-flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
