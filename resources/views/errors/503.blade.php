<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>503 - Dalam Pemeliharaan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="max-w-lg w-full">
            <!-- Maintenance Card -->
            <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-8 shadow-xl text-center">
                <!-- Maintenance Icon -->
                <div class="mb-6">
                    <div class="relative inline-block">
                        <svg class="w-24 h-24 text-white/70 mx-auto animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Status Code -->
                <div class="mb-4">
                    <span class="inline-block px-4 py-2 bg-white/20 rounded-full text-white/90 font-semibold text-sm">
                        Status: 503
                    </span>
                </div>

                <!-- Maintenance Message -->
                <h2 class="text-3xl font-bold text-white mb-3">Sistem Dalam Pemeliharaan</h2>
                <p class="text-white/80 text-lg mb-6">
                    Kami sedang melakukan pemeliharaan sistem untuk meningkatkan layanan.
                    Mohon maaf atas ketidaknyamanannya.
                </p>

                <!-- Estimated Time -->
                <div class="bg-white/10 rounded-lg p-4 mb-8">
                    <div class="flex items-center justify-center gap-2 text-white/90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Estimasi Waktu:</span>
                        <span>{{ $retryAfter ?? '30 menit' }}</span>
                    </div>
                </div>

                <!-- Progress Animation -->
                <div class="mb-8">
                    <div class="flex justify-center items-center gap-2">
                        <div class="w-3 h-3 bg-white/60 rounded-full animate-pulse" style="animation-delay: 0s"></div>
                        <div class="w-3 h-3 bg-white/60 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                        <div class="w-3 h-3 bg-white/60 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                    </div>
                </div>

                <!-- Action Button -->
                <button onclick="location.reload()" class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-white text-purple-900 font-semibold rounded-lg hover:bg-white/90 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Coba Lagi
                </button>
            </div>

            <!-- Footer Info -->
            <div class="mt-6 text-center space-y-2">
                <p class="text-white/60 text-sm">
                    Terima kasih atas kesabaran Anda.
                </p>
                <p class="text-white/50 text-xs">
                    Untuk informasi lebih lanjut, hubungi administrator sistem.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto refresh every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
