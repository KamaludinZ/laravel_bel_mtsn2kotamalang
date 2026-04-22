<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>403 - Akses Ditolak</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-orange-900 via-orange-800 to-red-900 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="max-w-lg w-full">
            <!-- Error Card -->
            <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-8 shadow-xl text-center">
                <!-- Error Code -->
                <div class="mb-6">
                    <h1 class="text-9xl font-bold text-white/90 mb-2">403</h1>
                    <div class="w-24 h-1 bg-white/50 mx-auto rounded-full"></div>
                </div>

                <!-- Error Icon -->
                <div class="mb-6">
                    <svg class="w-24 h-24 text-white/70 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <!-- Error Message -->
                <h2 class="text-3xl font-bold text-white mb-3">Akses Ditolak</h2>
                <p class="text-white/80 text-lg mb-8">
                    Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
                    Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
                </p>

                <!-- Additional Info -->
                @if(isset($exception) && $exception->getMessage())
                    <div class="mb-6 p-4 bg-white/10 rounded-lg">
                        <p class="text-white/80 text-sm">
                            {{ $exception->getMessage() }}
                        </p>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-orange-900 font-semibold rounded-lg hover:bg-white/90 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Kembali ke Beranda
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-all border border-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-all border border-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Login
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-6 text-center">
                <p class="text-white/60 text-sm">
                    Error Code: 403 Forbidden | Akses tidak diizinkan
                </p>
            </div>
        </div>
    </div>
</body>
</html>
