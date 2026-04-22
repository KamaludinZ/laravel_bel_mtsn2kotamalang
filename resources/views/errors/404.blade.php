<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>404 - Halaman Tidak Ditemukan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="max-w-lg w-full">
            <!-- Error Card -->
            <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-8 shadow-xl text-center">
                <!-- Error Code -->
                <div class="mb-6">
                    <h1 class="text-9xl font-bold text-white/90 mb-2">404</h1>
                    <div class="w-24 h-1 bg-white/50 mx-auto rounded-full"></div>
                </div>

                <!-- Error Icon -->
                <div class="mb-6">
                    <svg class="w-24 h-24 text-white/70 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <!-- Error Message -->
                <h2 class="text-3xl font-bold text-white mb-3">Halaman Tidak Ditemukan</h2>
                <p class="text-white/80 text-lg mb-8">
                    Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin halaman telah dipindahkan atau dihapus.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-blue-900 font-semibold rounded-lg hover:bg-white/90 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Kembali ke Beranda
                    </a>
                    <button onclick="history.back()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-all border border-white/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Halaman Sebelumnya
                    </button>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-6 text-center">
                <p class="text-white/60 text-sm">
                    Jika Anda yakin ini adalah kesalahan sistem, silakan hubungi administrator.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
