<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>500 - Internal Server Error</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-red-900 via-red-800 to-orange-900 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="max-w-lg w-full">
            <!-- Error Card -->
            <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-8 shadow-xl text-center">
                <!-- Error Code -->
                <div class="mb-6">
                    <h1 class="text-9xl font-bold text-white/90 mb-2">500</h1>
                    <div class="w-24 h-1 bg-white/50 mx-auto rounded-full"></div>
                </div>

                <!-- Error Icon -->
                <div class="mb-6">
                    <svg class="w-24 h-24 text-white/70 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <!-- Error Message -->
                <h2 class="text-3xl font-bold text-white mb-3">Internal Server Error</h2>
                <p class="text-white/80 text-lg mb-8">
                    Maaf, terjadi kesalahan pada server. Tim kami telah diberitahu dan sedang bekerja untuk memperbaikinya.
                </p>

                <!-- Technical Info (Only in debug mode) -->
                @if(config('app.debug') && isset($exception))
                    <div class="mb-6 p-4 bg-black/30 rounded-lg text-left">
                        <p class="text-white/90 font-mono text-sm break-all">
                            <strong>Error:</strong> {{ $exception->getMessage() }}
                        </p>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-red-900 font-semibold rounded-lg hover:bg-white/90 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Kembali ke Beranda
                    </a>
                    <button onclick="location.reload()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-all border border-white/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Muat Ulang Halaman
                    </button>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-6 text-center">
                <p class="text-white/60 text-sm">
                    Error Code: 500 | Jika masalah berlanjut, hubungi administrator sistem.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
