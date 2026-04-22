<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="dashboardStatus()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Message -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}! 👋</h3>
                    <p class="text-blue-100">Sistem Manajemen Bel Sekolah MTsN 2 Kota Malang</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Users Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 mr-4">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Users</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ $stats['total_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Audio Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 mr-4">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Audio Library</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ $stats['total_audio'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Schedules Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 mr-4">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Schedules</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ $stats['total_schedules'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bell Types Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900 mr-4">
                                <svg class="w-8 h-8 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Bell Types</p>
                                <p class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ $stats['total_bell_types'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Active Bell Type & Quick Actions -->
                <div class="lg:col-span-1 space-y-6">

                    <!-- Active Bell Type -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Jenis Bel Aktif</h3>
                            @if($stats['active_bell_type'])
                                <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-green-800 dark:text-green-300">{{ $stats['active_bell_type']->name }}</p>
                                            <p class="text-sm text-green-600 dark:text-green-400">{{ $stats['active_schedules'] }} jadwal aktif</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded">
                                    <p class="text-yellow-700 dark:text-yellow-300">Belum ada jenis bel yang diaktifkan</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <a href="{{ route('bell-schedules.create') }}" class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-700 dark:text-blue-300 group-hover:text-blue-800 dark:group-hover:text-blue-200">Tambah Jadwal Baru</span>
                                </a>

                                <a href="{{ route('audio-libraries.create') }}" class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/40 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span class="text-sm font-medium text-green-700 dark:text-green-300 group-hover:text-green-800 dark:group-hover:text-green-200">Upload Audio</span>
                                </a>

                                <a href="{{ route('bell-types.create') }}" class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/40 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300 group-hover:text-purple-800 dark:group-hover:text-purple-200">Tambah Jenis Bel</span>
                                </a>

                                <a href="{{ route('settings.index') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors group">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-800 dark:group-hover:text-gray-200">Pengaturan Sistem</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Bell Types List -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Jenis Bel</h3>
                            <div class="space-y-2">
                                @forelse($bellTypes as $bellType)
                                    <div class="flex items-center justify-between p-3 {{ $bellType->is_active ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-700' }} rounded-lg">
                                        <div class="flex items-center">
                                            @if($bellType->is_active)
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-3"></span>
                                            @else
                                                <span class="w-2 h-2 bg-gray-400 rounded-full mr-3"></span>
                                            @endif
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $bellType->name }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $bellType->bell_schedules_count }} jadwal</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada jenis bel</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Today's Schedule & Recent Audio -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Today's Schedule -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Jadwal Hari Ini</h3>
                                <a href="{{ route('bell-schedules.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                    Lihat Semua →
                                </a>
                            </div>

                            @if($todaySchedules->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Hari
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Waktu
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Audio
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Keterangan
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($todaySchedules as $schedule)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200 capitalize">
                                                        @php
                                                            $days = [
                                                                'monday' => 'Senin',
                                                                'tuesday' => 'Selasa',
                                                                'wednesday' => 'Rabu',
                                                                'thursday' => 'Kamis',
                                                                'friday' => 'Jumat',
                                                                'saturday' => 'Sabtu',
                                                                'sunday' => 'Minggu',
                                                            ];
                                                        @endphp
                                                        {{ $days[$schedule->day] ?? ucfirst($schedule->day) }}
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                                                        {{ date('H:i', strtotime($schedule->time)) }}
                                                    </td>
                                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $schedule->audioLibrary->title }}
                                                    </td>
                                                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                        <div class="max-w-xs truncate" title="{{ $schedule->keterangan }}">
                                                            {{ $schedule->keterangan ?? '-' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-4 text-center" x-data="{ time: '{{ date('H:i', strtotime($schedule->time)) }}' }">
                                                        <!-- Selesai (waktu sudah lewat) -->
                                                        <span x-show="isSchedulePassed(time)" class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-full">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Selesai
                                                        </span>
                                                        <!-- Sedang Berlangsung (waktu sama dengan sekarang) -->
                                                        <span x-show="!isSchedulePassed(time) && isScheduleNow(time)" class="inline-flex items-center px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 text-xs rounded-full font-semibold">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Sekarang
                                                        </span>
                                                        <!-- Menunggu (waktu belum tiba) -->
                                                        <span x-show="!isSchedulePassed(time) && !isScheduleNow(time)" class="inline-flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs rounded-full">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Menunggu
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400">Tidak ada jadwal untuk hari ini</p>
                                    <a href="{{ route('bell-schedules.create') }}" class="inline-block mt-4 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        + Tambah Jadwal
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Audio -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Audio Library Terbaru</h3>
                                <a href="{{ route('audio-libraries.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                    Lihat Semua →
                                </a>
                            </div>

                            @if($recentAudio->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentAudio as $audio)
                                        <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                            <div class="flex-shrink-0 mr-4">
                                                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-grow">
                                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $audio->title }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $audio->created_at->diffForHumans() }}</p>
                                            </div>
                                            <a href="{{ route('audio-libraries.show', $audio->id) }}" class="flex-shrink-0 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400">Belum ada audio</p>
                                    <a href="{{ route('audio-libraries.create') }}" class="inline-block mt-4 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        + Upload Audio
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Schedule Statistics Chart -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Statistik Jadwal per Jenis Bel</h3>

                            @if($scheduleStats->count() > 0)
                                <div class="space-y-4">
                                    @foreach($scheduleStats as $stat)
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $stat['name'] }}</span>
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $stat['count'] }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                <div class="h-2.5 rounded-full {{ $stat['is_active'] ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $stats['total_schedules'] > 0 ? ($stat['count'] / $stats['total_schedules'] * 100) : 0 }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Belum ada data statistik</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
