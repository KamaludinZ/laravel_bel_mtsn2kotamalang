<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan & Monitoring Aplikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-800 dark:border-green-600 dark:text-green-200" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-800 dark:border-red-600 dark:text-red-200" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Tab Navigation -->
            <div class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button onclick="switchTab('settings')" id="tab-settings" class="tab-button active border-blue-500 text-blue-600 dark:text-blue-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Pengaturan
                        </button>
                        <button onclick="switchTab('monitoring')" id="tab-monitoring" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Monitoring Sistem
                        </button>
                        <button onclick="switchTab('performance')" id="tab-performance" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Performa
                        </button>
                        <button onclick="switchTab('security')" id="tab-security" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Keamanan
                        </button>
                        <button onclick="switchTab('logs')" id="tab-logs" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Log Sistem
                        </button>
                        <button onclick="switchTab('database')" id="tab-database" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Database
                        </button>
                        <button onclick="switchTab('backup')" id="tab-backup" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Backup & Restore
                        </button>
                        <button onclick="switchTab('update')" id="tab-update" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Update
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab Content: Settings -->
            <div id="content-settings" class="tab-content">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Pengaturan Aplikasi</h3>

                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-6">
                                <label for="app_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Nama Aplikasi <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $appName) }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white @error('app_name') border-red-500 @enderror"
                                    placeholder="Contoh: Bel Sekolah MTsN 2 Kota Malang" required>
                                @error('app_name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Logo Aplikasi Saat Ini
                                </label>
                                @if($appLogo)
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo Aplikasi" class="h-20 w-auto mb-2 bg-gray-100 dark:bg-gray-700 p-2 rounded">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ basename($appLogo) }}</p>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Belum ada logo</p>
                                @endif

                                <input type="file" name="app_logo" id="app_logo" accept=".jpeg,.jpg,.png,.svg"
                                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 @error('app_logo') border-red-500 @enderror">
                                @error('app_logo')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Format: JPEG, PNG, JPG, SVG (max 2MB)</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Hardware Integration Toggle -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Integrasi Hardware Speaker</h3>

                        <form action="{{ route('settings.toggle-hardware') }}" method="POST">
                            @csrf

                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-3">
                                        <label for="hardware_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-300 mr-3">
                                            Aktifkan Kontrol Hardware Speaker Fisik
                                        </label>

                                        <!-- Toggle Switch -->
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                   name="hardware_enabled"
                                                   id="hardware_enabled"
                                                   value="1"
                                                   class="sr-only peer"
                                                   {{ $hardwareEnabled ? 'checked' : '' }}
                                                   onchange="this.form.submit()">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>

                                    <!-- Status Indicator -->
                                    <div class="mb-4">
                                        @if($hardwareEnabled)
                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Aktif - Speaker fisik akan bunyi
                                            </div>
                                        @else
                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Nonaktif - Hanya browser audio
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Description -->
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        <p class="mb-2"><strong>Jika Diaktifkan:</strong></p>
                                        <ul class="list-disc list-inside ml-4 space-y-1">
                                            <li>Bel otomatis akan mentrigger speaker fisik melalui Modbus RS485</li>
                                            <li>Audio juga tetap diputar di browser sebagai backup</li>
                                            <li>Menu Hardware Control dapat diakses untuk konfigurasi</li>
                                        </ul>

                                        <p class="mt-3 mb-2"><strong>Jika Dinonaktifkan:</strong></p>
                                        <ul class="list-disc list-inside ml-4 space-y-1">
                                            <li>Bel hanya akan diputar melalui browser saja</li>
                                            <li>Tidak ada trigger ke hardware speaker fisik</li>
                                            <li>Menu Hardware Control tidak akan muncul</li>
                                        </ul>
                                    </div>

                                    <!-- Link to Hardware Dashboard (only when enabled) -->
                                    @if($hardwareEnabled)
                                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <a href="{{ route('hardware.index') }}"
                                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                                </svg>
                                                Buka Dashboard Hardware
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Monitoring -->
            <div id="content-monitoring" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <!-- System Info Cards -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">PHP Version</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $monitoring['system']['php_version'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Laravel Version</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $monitoring['system']['laravel_version'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Environment</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($monitoring['system']['environment']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Details -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Informasi Sistem</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Server Software</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $monitoring['system']['server_software'] }}</p>
                            </div>
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Timezone</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $monitoring['system']['timezone'] }}</p>
                            </div>
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Memory Limit</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $monitoring['system']['memory_limit'] }}</p>
                            </div>
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Max Execution Time</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $monitoring['system']['max_execution_time'] }}s</p>
                            </div>
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Upload Max Filesize</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $monitoring['system']['upload_max_filesize'] }}</p>
                            </div>
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Post Max Size</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $monitoring['system']['post_max_size'] }}</p>
                            </div>
                        </div>

                        <!-- Disk Usage -->
                        <div class="mt-6">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Disk Usage</p>
                            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                                <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $monitoring['system']['disk_usage_percent'] }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $monitoring['system']['disk_free_space'] }} free of {{ $monitoring['system']['disk_total_space'] }}
                                ({{ $monitoring['system']['disk_usage_percent'] }}% used)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Performance -->
            <div id="content-performance" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Database Query Time</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $monitoring['performance']['database_query_time'] }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Cache Driver</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ ucfirst($monitoring['performance']['cache_driver']) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Storage Used</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $monitoring['performance']['storage_used'] }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Statistik Data</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $monitoring['performance']['total_users'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Users</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $monitoring['performance']['total_schedules'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Jadwal</p>
                            </div>
                            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $monitoring['performance']['total_audio'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Audio</p>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-4">
                            <form action="{{ route('settings.clear-cache') }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Yakin ingin membersihkan cache?')" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 transition">
                                    Clear Cache
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Security -->
            <div id="content-security" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Failed Login Attempts</p>
                        <p class="text-2xl font-bold {{ $monitoring['security']['failed_login_attempts'] > 10 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $monitoring['security']['failed_login_attempts'] }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active Sessions</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $monitoring['security']['active_sessions'] }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Suspicious Activities</p>
                        <p class="text-2xl font-bold {{ $monitoring['security']['suspicious_activities'] > 20 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $monitoring['security']['suspicious_activities'] }}
                        </p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Security Checklist</h3>
                        <div class="space-y-3">
                            @foreach($monitoring['security']['security_checks'] as $check => $status)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ ucwords(str_replace('_', ' ', $check)) }}</span>
                                    @if($check === 'debug_mode')
                                        <span class="px-3 py-1 text-xs rounded-full {{ $status ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                            {{ $status ? 'ON (Disable di Production!)' : 'OFF' }}
                                        </span>
                                    @elseif($check === 'https_enabled')
                                        <span class="px-3 py-1 text-xs rounded-full {{ $status ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                            {{ $status ? 'Yes' : 'No (Recommended)' }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs rounded-full {{ $status ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $status ? 'Yes' : 'No' }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Logs -->
            <div id="content-logs" class="tab-content hidden">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">System Logs</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">File size: {{ $monitoring['logs']['file_size'] }} | Total entries: {{ $monitoring['logs']['total_entries'] }}</p>
                            </div>
                            <form action="{{ route('settings.clear-logs') }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Yakin ingin menghapus semua log?')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                    Clear Logs
                                </button>
                            </form>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Timestamp</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Level</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Message</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($monitoring['logs']['entries'] as $log)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $log['timestamp'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ str_contains($log['level'], 'ERROR') ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                                    {{ str_contains($log['level'], 'WARNING') ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                    {{ str_contains($log['level'], 'INFO') ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                                ">
                                                    {{ $log['level'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-xs text-gray-900 dark:text-gray-100">{{ $log['message'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No log entries found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Database -->
            <div id="content-database" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Database Driver</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ ucfirst($monitoring['database']['driver']) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Database Name</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $monitoring['database']['database'] }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Connection Status</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $monitoring['database']['connection_status'] }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Database Tables</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Table Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Size</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($monitoring['database']['tables'] as $table)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $table->table_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if($monitoring['database']['driver'] === 'mysql')
                                                    {{ $table->size_mb }} MB
                                                @elseif($monitoring['database']['driver'] === 'pgsql')
                                                    {{ $table->size_mb }}
                                                @else
                                                    {{ $table->size_mb }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No table data available</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Backup & Restore -->
            <div id="content-backup" class="tab-content hidden">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Backup & Restore</h3>

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 mb-6">
                            <p class="text-sm text-blue-700 dark:text-blue-300 mb-2 font-semibold">⚠️ Penting</p>
                            <ul class="text-xs text-gray-600 dark:text-gray-300 list-disc list-inside space-y-1">
                                <li>Backup mencakup database (jadwal bel, audio, users) dan files (.env, audio, logos)</li>
                                <li>Restore akan menimpa data yang ada - pastikan backup sudah benar</li>
                                <li>Simpan file backup di lokasi aman (download ke komputer lokal)</li>
                                <li>Lakukan backup secara berkala (minimal 1x seminggu)</li>
                            </ul>
                        </div>

                        <!-- Create Backup Section -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold mb-3 text-gray-900 dark:text-gray-100">Buat Backup Baru</h4>

                            <form action="{{ route('settings.backup.create') }}" method="POST" class="space-y-4">
                                @csrf

                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        Tipe Backup <span class="text-red-500">*</span>
                                    </label>
                                    <select name="backup_type" required
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <option value="full">Full Backup (Database + Files)</option>
                                        <option value="database">Database Only</option>
                                        <option value="files">Files Only (.env, audio, logos)</option>
                                    </select>
                                </div>

                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                    </svg>
                                    Buat Backup Sekarang
                                </button>
                            </form>
                        </div>

                        <hr class="my-6 border-gray-200 dark:border-gray-700">

                        <!-- Backups List -->
                        <div>
                            <h4 class="text-md font-semibold mb-3 text-gray-900 dark:text-gray-100">
                                Daftar Backup ({{ count($backups) }})
                            </h4>

                            @if(count($backups) > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr>
                                                <th class="px-4 py-3">Nama File</th>
                                                <th class="px-4 py-3">Tanggal</th>
                                                <th class="px-4 py-3">Ukuran</th>
                                                <th class="px-4 py-3">Tipe</th>
                                                <th class="px-4 py-3 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($backups as $backup)
                                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                                        {{ $backup['name'] }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $backup['date'] }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        {{ $backup['size'] }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if(str_contains($backup['name'], 'database'))
                                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                                Database
                                                            </span>
                                                        @elseif(str_contains($backup['name'], 'files'))
                                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                                Files
                                                            </span>
                                                        @else
                                                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                                                Full
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <div class="flex justify-end gap-2">
                                                            <!-- Download Button -->
                                                            <a href="{{ route('settings.backup.download', $backup['name']) }}"
                                                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                </svg>
                                                                Download
                                                            </a>

                                                            <!-- Restore Button -->
                                                            <button type="button" onclick="confirmRestore('{{ $backup['name'] }}')"
                                                                class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-xs font-semibold rounded hover:bg-yellow-700 transition">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                                </svg>
                                                                Restore
                                                            </button>

                                                            <!-- Delete Button -->
                                                            <form action="{{ route('settings.backup.delete', $backup['name']) }}" method="POST" class="inline"
                                                                onsubmit="return confirm('Hapus backup ini? Aksi tidak dapat dibatalkan.')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700 transition">
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="mt-2">Belum ada backup. Buat backup pertama Anda sekarang!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Restore Confirmation Modal -->
            <div id="restoreModal" class="hidden fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">⚠️ Konfirmasi Restore</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Restore akan menimpa semua data yang ada dengan data dari backup. Proses ini tidak dapat dibatalkan.
                    </p>
                    <p class="text-sm font-semibold text-red-600 dark:text-red-400 mb-6">
                        Pastikan Anda sudah membuat backup terbaru sebelum restore!
                    </p>

                    <form action="{{ route('settings.backup.restore') }}" method="POST" id="restoreForm">
                        @csrf
                        <input type="hidden" name="backup_file" id="restoreFileName">

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="confirm_restore" value="1" required
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-900 dark:text-gray-300">
                                    Saya mengerti dan ingin melanjutkan restore
                                </span>
                            </label>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="closeRestoreModal()"
                                class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                Ya, Restore Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tab Content: Update -->
            <div id="content-update" class="tab-content hidden">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Update Aplikasi</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Versi Aplikasi Saat Ini</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ session('update_info.current_version', config('app.version', 'v1.0.1')) }}
                                </p>
                            </div>
                            <div class="p-4 rounded-lg border
                                {{ session('update_info.needs_update') ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-700' : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700' }}">
                                <p class="text-sm {{ session('update_info.needs_update') ? 'text-yellow-700 dark:text-yellow-300' : 'text-green-700 dark:text-green-300' }}">
                                    Status Update
                                </p>
                                <p class="text-lg font-bold {{ session('update_info.needs_update') ? 'text-yellow-700 dark:text-yellow-300' : 'text-green-700 dark:text-green-300' }}">
                                    {{ session('update_info.needs_update') ? 'Perlu Update' : 'Sudah Terbaru' }}
                                </p>
                                @if(session('update_info.latest_version'))
                                    <p class="text-xs mt-1 {{ session('update_info.needs_update') ? 'text-yellow-700 dark:text-yellow-300' : 'text-green-700 dark:text-green-300' }}">
                                        Versi GitHub: {{ session('update_info.latest_version') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 mb-4">
                            <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">Informasi Update</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                Gunakan tombol di bawah untuk cek update. Jika tersedia, sistem akan meminta konfirmasi sebelum update diproses.
                            </p>
                        </div>

                        <!-- Repository Path Configuration (untuk troubleshooting) -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Konfigurasi Repository (Opsional)</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                Jika mendapat error "Repository git tidak ditemukan", isi path lengkap ke direktori aplikasi di bawah ini:
                            </p>
                            <form action="{{ route('settings.update') }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="update_repo_path"
                                    value="{{ old('update_repo_path', \App\Models\Setting::get('update_repo_path', base_path())) }}"
                                    placeholder="Contoh: /var/www/html/laravel_bel atau C:\laravel_bel_mtsn2kotamalang"
                                    class="flex-1 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <button type="submit"
                                    class="px-4 py-2 bg-gray-600 text-white text-xs font-semibold rounded-lg hover:bg-gray-700 transition">
                                    Simpan Path
                                </button>
                            </form>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Path saat ini: <code class="bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded">{{ base_path() }}</code>
                            </p>
                        </div>

                        <div class="mt-6 space-y-4">
                            <form action="{{ route('settings.check-update') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                    Cek Update
                                </button>
                            </form>

                            @if(session('update_info.needs_update'))
                                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 font-semibold mb-2">Update tersedia</p>
                                    <form action="{{ route('settings.run-update') }}" method="POST" onsubmit="return confirm('Update ditemukan. Lanjutkan proses update sekarang?')">
                                        @csrf
                                        <input type="hidden" name="confirm_update" value="1">
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                                            Ya, Lanjutkan Update
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if(session('update_logs'))
                                <div class="p-4 bg-gray-900 text-green-300 rounded-lg overflow-auto">
                                    <p class="text-xs font-semibold mb-2 text-gray-200">Log Proses Update:</p>
                                    <pre class="text-xs whitespace-pre-wrap">{{ implode("\n\n", session('update_logs')) }}</pre>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Restore Modal Functions
        function confirmRestore(filename) {
            document.getElementById('restoreFileName').value = filename;
            document.getElementById('restoreModal').classList.remove('hidden');
        }

        function closeRestoreModal() {
            document.getElementById('restoreModal').classList.add('hidden');
            document.getElementById('restoreForm').reset();
        }

        function switchTab(tabName) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active state from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });

            // Show selected content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Add active state to selected button
            const activeButton = document.getElementById('tab-' + tabName);
            activeButton.classList.add('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
            activeButton.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        }
    </script>
</x-app-layout>
