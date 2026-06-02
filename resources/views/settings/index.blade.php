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

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                            <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">Informasi Update</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                Gunakan tombol di bawah untuk cek update. Jika tersedia, sistem akan meminta konfirmasi sebelum update diproses.
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
