<x-layouts.public-layout>
    <div x-data="{ ...bellSystem(), userMenuOpen: false }" x-init="init()" class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white/10 backdrop-blur-sm border-b border-white/20 py-6">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        @if($appLogo)
                            <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo" class="h-16 w-auto">
                        @endif
                        <h1 class="text-3xl font-bold text-white">{{ $appName ?? config('app.name') }}</h1>
                    </div>
                    @auth
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50"
                                 style="display: none;">

                                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <span class="font-medium">Dashboard</span>
                                </a>

                                <div class="border-t border-gray-200 my-1"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span class="font-medium">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Login Admin
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 container mx-auto px-4 py-8">
            <!-- Digital Clock -->
            <div class="text-center mb-8">
                <div class="text-7xl md:text-8xl font-bold text-white mb-3 font-mono" x-text="currentTime">00:00:00</div>
                <div class="text-xl md:text-2xl text-white/80" x-text="currentDate">Loading...</div>
            </div>

            <!-- Main Grid: Schedule (Left) & Controls (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">

                <!-- LEFT COLUMN: Today's Schedule (2/3 width) -->
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold text-white mb-4">Jadwal Hari Ini</h2>

                    <div x-show="schedules.length === 0" class="text-center">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 border border-white/20">
                            <svg class="w-16 h-16 text-white/50 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xl text-white/70">Tidak ada jadwal untuk hari ini</p>
                        </div>
                    </div>

                    <div x-show="schedules.length > 0" class="bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/20">
                                <thead class="bg-white/20">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase">Waktu</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase">Nama Audio</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase">Durasi</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase">Keterangan</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase">Status</th>
                                        @auth
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-white uppercase">Aksi</th>
                                        @endauth
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10">
                                    <template x-for="(schedule, index) in schedules" :key="schedule.id">
                                        <tr :class="{'bg-green-500/20': isCurrentSchedule(schedule), 'hover:bg-white/5': !isCurrentSchedule(schedule)}" class="transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-white font-medium" x-text="index + 1"></span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-xl md:text-2xl font-bold text-white" x-text="schedule.time"></span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-500/30 rounded-lg flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                                        </svg>
                                                    </div>
                                                    <span class="text-sm md:text-base text-white" x-text="schedule.audio.title"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="text-sm font-mono text-blue-300" x-text="schedule.audio.duration_formatted || '00:00'"></span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm text-white/80" x-text="schedule.keterangan || '-'"></span>
                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                <!-- Sedang Diputar (Automatic atau Manual) -->
                                                <span x-show="isCurrentSchedule(schedule) || isSchedulePlaying(schedule.id)" class="inline-flex items-center px-2 py-1 bg-green-500 text-white text-xs rounded-full font-semibold animate-pulse">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                                    </svg>
                                                    Diputar
                                                </span>
                                                <!-- Selesai (waktu sudah lewat) -->
                                                <span x-show="!isCurrentSchedule(schedule) && !isSchedulePlaying(schedule.id) && isSchedulePassed(schedule)" class="inline-flex items-center px-2 py-1 bg-gray-600 text-white text-xs rounded-full">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Selesai
                                                </span>
                                                <!-- Sedang Berlangsung (waktu sama dengan sekarang tapi belum/tidak otomatis) -->
                                                <span x-show="!isCurrentSchedule(schedule) && !isSchedulePlaying(schedule.id) && !isSchedulePassed(schedule) && isScheduleNow(schedule)" class="inline-flex items-center px-2 py-1 bg-yellow-500 text-white text-xs rounded-full font-semibold">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Sekarang
                                                </span>
                                                <!-- Menunggu (waktu belum tiba) -->
                                                <span x-show="!isCurrentSchedule(schedule) && !isSchedulePlaying(schedule.id) && !isSchedulePassed(schedule) && !isScheduleNow(schedule)" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs rounded-full">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Menunggu
                                                </span>
                                            </td>
                                            @auth
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button @click="playScheduleAudio(schedule)"
                                                        :disabled="isSchedulePlaying(schedule.id)"
                                                        :class="isSchedulePlaying(schedule.id) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600'"
                                                        class="bg-green-500 text-white p-1.5 rounded transition-colors"
                                                        title="Play">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                                        </svg>
                                                    </button>
                                                    <button @click="pauseScheduleAudio()"
                                                        :disabled="!isSchedulePlaying(schedule.id)"
                                                        :class="!isSchedulePlaying(schedule.id) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-600'"
                                                        class="bg-red-500 text-white p-1.5 rounded transition-colors"
                                                        title="Pause">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </button>
                                                    <button @click="forceStopScheduleAudio(schedule.id)"
                                                        :disabled="!isSchedulePlaying(schedule.id) && !(currentPlayingSchedule && currentPlayingSchedule.id === schedule.id)"
                                                        :class="(!isSchedulePlaying(schedule.id) && !(currentPlayingSchedule && currentPlayingSchedule.id === schedule.id)) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-orange-600'"
                                                        class="bg-orange-500 text-white p-1.5 rounded transition-colors"
                                                        title="Stop Paksa">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M5 4a1 1 0 00-1 1v10a1 1 0 001 1h10a1 1 0 001-1V5a1 1 0 00-1-1H5z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                            @endauth
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Controls (1/3 width) -->
                <div class="lg:col-span-1 space-y-4">

                    <!-- Active Bell Type Info (Compact) -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <h3 class="text-sm font-semibold text-white/70 mb-2">Jenis Bel Aktif</h3>
                        <p class="text-xl font-bold text-yellow-300 mb-1" x-text="bellType">-</p>
                        <p class="text-xs text-white/70" x-show="isAutomatic">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Mode Otomatis
                            </span>
                        </p>
                    </div>

                    <!-- Bell Type Selector (Auth Only - Compact) -->
                    @auth
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <h3 class="text-sm font-semibold text-white/70 mb-3">Ganti Jenis Bel</h3>

                        @if (session('success'))
                            <div class="mb-3 bg-green-500/20 border border-green-400/30 text-green-100 px-3 py-2 rounded-lg text-xs flex items-center gap-2">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('public.activate-bell-type') }}" method="POST" class="space-y-3">
                            @csrf
                            <select name="bell_type_id" id="bell_type_id" required
                                class="w-full bg-white/20 border border-white/30 text-white text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-300 focus:border-transparent">
                                <option value="" class="text-gray-900">Pilih Jenis Bel</option>
                                @foreach($bellTypes as $type)
                                    <option value="{{ $type->id }}" {{ $activeBellType && $activeBellType->id == $type->id ? 'selected' : '' }} class="text-gray-900">
                                        {{ $type->name }} {{ $type->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bell_type_id')
                                <p class="text-xs text-red-300">{{ $message }}</p>
                            @enderror
                            <button type="submit"
                                class="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold py-2 px-4 text-sm rounded-lg transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Aktifkan
                            </button>
                        </form>
                    </div>

                    <!-- Manual Audio Player (Auth Only - Compact) -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <h3 class="text-sm font-semibold text-white/70 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Putar Audio Manual
                        </h3>

                        <div class="space-y-3">
                            <select x-model="manualAudioId" id="manual_audio"
                                class="w-full bg-white/20 border border-white/30 text-white text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-300 focus:border-transparent">
                                <option value="" class="text-gray-900">-- Pilih Audio --</option>
                                @foreach($audioLibraries as $audio)
                                    <option value="{{ $audio->id }}" class="text-gray-900">{{ $audio->title }}</option>
                                @endforeach
                            </select>

                            <div class="flex gap-2">
                                <button @click="playManualAudio()"
                                    :disabled="!manualAudioId || isManualPlaying"
                                    :class="!manualAudioId || isManualPlaying ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600'"
                                    class="flex-1 bg-green-500 text-white font-semibold py-2 px-3 text-sm rounded-lg transition-colors flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                    </svg>
                                    Play
                                </button>
                                <button @click="pauseManualAudio()"
                                    :disabled="!isManualPlaying"
                                    :class="!isManualPlaying ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-600'"
                                    class="flex-1 bg-red-500 text-white font-semibold py-2 px-3 text-sm rounded-lg transition-colors flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Pause
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Now Playing Info (when audio is playing) -->
                    <div x-show="currentPlayingSchedule || currentSchedulePlayingId || isManualPlaying" class="bg-gradient-to-br from-green-500/30 to-blue-500/30 backdrop-blur-sm rounded-xl p-4 border border-green-400/30">
                        <h3 class="text-sm font-semibold text-white/70 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                            </svg>
                            Sedang Diputar
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-white font-semibold" x-text="nowPlayingTitle">-</p>
                            </div>
                            <div class="flex justify-between text-xs text-white/80">
                                <span x-text="currentTimeFormatted">00:00</span>
                                <span x-text="durationFormatted">00:00</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2.5">
                                <div class="bg-gradient-to-r from-green-400 to-blue-500 h-2.5 rounded-full transition-all duration-300" :style="`width: ${playbackProgress}%`"></div>
                            </div>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>

            <!-- Hidden Audio Elements -->
            <audio x-ref="audioPlayer" class="hidden"></audio>
            <audio x-ref="manualAudioPlayer" class="hidden"></audio>
            <audio x-ref="scheduleAudioPlayer" class="hidden"></audio>
        </main>

        <!-- Footer -->
        <footer class="bg-white/10 backdrop-blur-sm border-t border-white/20 py-4">
            <div class="container mx-auto px-4 text-center text-white/70">
                <p>&copy; {{ date('Y') }} {{ $appName ?? config('app.name') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script>
        function bellSystem() {
            return {
                currentTime: '00:00:00',
                currentDate: '',
                schedules: [],
                bellType: '-',
                isAutomatic: false,
                currentPlayingSchedule: null,
                clockInterval: null,
                checkInterval: null,
                progressInterval: null,
                manualAudioId: '',
                isManualPlaying: false,
                audioLibraries: @json($audioLibraries),
                currentSchedulePlayingId: null,
                lastTriggeredScheduleKey: null,
                playedScheduleKeys: new Set(),
                serverOffsetMs: 0,
                serverSyncInterval: null,
                scheduleRefreshInterval: null,
                catchUpWindowSeconds: 90,
                // Progress tracking
                playbackProgress: 0,
                currentTimeFormatted: '00:00',
                durationFormatted: '00:00',
                nowPlayingTitle: '-',
                // Manual audio progress
                manualProgress: 0,
                manualCurrentTimeFormatted: '00:00',
                manualDurationFormatted: '00:00',

                init() {
                    this.updateClock();
                    this.fetchSchedule();
                    this.syncServerTime();

                    // Update clock every second (based on server offset)
                    this.clockInterval = setInterval(() => {
                        this.updateClock();
                    }, 1000);

                    // Check for schedule every 5 seconds
                    this.checkInterval = setInterval(() => {
                        this.checkAndPlaySchedule();
                    }, 5000);

                    // Fetch schedule every 5 minutes
                    this.scheduleRefreshInterval = setInterval(() => {
                        this.fetchSchedule();
                    }, 300000);

                    // Sync server time every 30 seconds
                    this.serverSyncInterval = setInterval(() => {
                        this.syncServerTime();
                    }, 30000);

                    // Re-check quickly when tab/app comes back to foreground
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) {
                            this.syncServerTime();
                            this.checkAndPlaySchedule(true);
                        }
                    });

                    window.addEventListener('focus', () => {
                        this.syncServerTime();
                        this.checkAndPlaySchedule(true);
                    });

                    window.addEventListener('pageshow', () => {
                        this.syncServerTime();
                        this.checkAndPlaySchedule(true);
                    });
                },

                updateClock() {
                    const now = this.getServerNow();
                    this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                },

                getServerNow() {
                    return new Date(Date.now() + this.serverOffsetMs);
                },

                async syncServerTime() {
                    try {
                        const requestStart = Date.now();
                        const response = await fetch('/api/current-time');
                        const data = await response.json();
                        const requestEnd = Date.now();

                        if (data.success && data.datetime) {
                            const serverTimeMs = new Date(data.datetime).getTime();
                            const networkLatencyHalf = Math.floor((requestEnd - requestStart) / 2);
                            const estimatedServerNow = serverTimeMs + networkLatencyHalf;
                            this.serverOffsetMs = estimatedServerNow - Date.now();
                        }
                    } catch (error) {
                        console.error('Error syncing server time:', error);
                    }
                },

                async fetchSchedule() {
                    try {
                        const response = await fetch('/api/today-schedule');
                        const data = await response.json();

                        if (data.success) {
                            this.schedules = data.data;
                            this.bellType = data.bell_type || '-';
                            this.isAutomatic = data.is_automatic || false;
                        }
                    } catch (error) {
                        console.error('Error fetching schedule:', error);
                    }
                },

                checkAndPlaySchedule(forceCatchUp = false) {
                    if (!this.isAutomatic) return;

                    const now = this.getServerNow();
                    const currentDateStr = now.toISOString().slice(0, 10); // YYYY-MM-DD

                    this.schedules.forEach(schedule => {
                        const [hours, minutes] = (schedule.time || '00:00').split(':').map(Number);
                        const scheduleDateTime = new Date(now);
                        scheduleDateTime.setHours(hours || 0, minutes || 0, 0, 0);

                        const diffSeconds = Math.floor((now.getTime() - scheduleDateTime.getTime()) / 1000);
                        const scheduleMinuteKey = `${currentDateStr}-${schedule.id}-${schedule.time}`;

                        // batas trigger:
                        // - normal interval: toleransi 0..5 detik
                        // - force catch-up saat kembali fokus: toleransi 0..catchUpWindowSeconds
                        const allowedWindow = forceCatchUp ? this.catchUpWindowSeconds : 5;
                        const isWithinWindow = diffSeconds >= 0 && diffSeconds <= allowedWindow;

                        if (
                            isWithinWindow &&
                            !this.playedScheduleKeys.has(scheduleMinuteKey) &&
                            this.lastTriggeredScheduleKey !== scheduleMinuteKey &&
                            !this.isSchedulePlaying(schedule.id) &&
                            !(this.currentPlayingSchedule && this.currentPlayingSchedule.id === schedule.id)
                        ) {
                            this.lastTriggeredScheduleKey = scheduleMinuteKey;
                            this.playedScheduleKeys.add(scheduleMinuteKey);
                            this.playAudio(schedule);
                        }
                    });
                },

                playAudio(schedule) {
                    this.currentPlayingSchedule = schedule;
                    this.currentSchedulePlayingId = schedule.id;
                    this.nowPlayingTitle = schedule.audio.title;
                    const audio = this.$refs.audioPlayer;
                    audio.src = schedule.audio.file_url;

                    audio.play()
                        .then(() => {
                            console.log('Playing:', schedule.audio.title);
                            this.startProgressTracking(audio, schedule.audio.duration);
                        })
                        .catch(error => {
                            console.error('Error playing audio:', error);
                            this.currentPlayingSchedule = null;
                            this.currentSchedulePlayingId = null;
                        });

                    audio.onended = () => {
                        this.currentPlayingSchedule = null;
                        this.currentSchedulePlayingId = null;
                        this.stopProgressTracking();
                    };
                },

                isCurrentSchedule(schedule) {
                    return this.currentPlayingSchedule?.id === schedule.id;
                },

                playManualAudio() {
                    if (!this.manualAudioId) return;

                    const selectedAudio = this.audioLibraries.find(audio => audio.id === this.manualAudioId);
                    if (!selectedAudio) return;

                    this.nowPlayingTitle = selectedAudio.title;
                    const manualPlayer = this.$refs.manualAudioPlayer;
                    manualPlayer.src = '{{ asset('storage') }}/' + selectedAudio.file_path;

                    manualPlayer.play()
                        .then(() => {
                            this.isManualPlaying = true;
                            console.log('Manual playing:', selectedAudio.title);
                            this.startProgressTracking(manualPlayer, selectedAudio.duration);
                        })
                        .catch(error => {
                            console.error('Error playing manual audio:', error);
                            this.isManualPlaying = false;
                        });

                    manualPlayer.onended = () => {
                        this.isManualPlaying = false;
                        this.stopProgressTracking();
                    };
                },

                pauseManualAudio() {
                    const manualPlayer = this.$refs.manualAudioPlayer;
                    manualPlayer.pause();
                    this.isManualPlaying = false;
                    this.stopProgressTracking();
                },

                playScheduleAudio(schedule) {
                    if (!schedule || !schedule.audio) return;

                    this.nowPlayingTitle = schedule.audio.title;
                    const schedulePlayer = this.$refs.scheduleAudioPlayer;
                    schedulePlayer.src = schedule.audio.file_url;

                    schedulePlayer.play()
                        .then(() => {
                            this.currentSchedulePlayingId = schedule.id;
                            console.log('Playing schedule audio:', schedule.audio.title);
                            this.startProgressTracking(schedulePlayer, schedule.audio.duration);
                        })
                        .catch(error => {
                            console.error('Error playing schedule audio:', error);
                            this.currentSchedulePlayingId = null;
                        });

                    schedulePlayer.onended = () => {
                        this.currentSchedulePlayingId = null;
                        this.stopProgressTracking();
                    };
                },

                pauseScheduleAudio() {
                    const schedulePlayer = this.$refs.scheduleAudioPlayer;
                    const autoPlayer = this.$refs.audioPlayer;

                    if (!schedulePlayer.paused) {
                        schedulePlayer.pause();
                        schedulePlayer.currentTime = 0;
                    }

                    if (!autoPlayer.paused) {
                        autoPlayer.pause();
                        autoPlayer.currentTime = 0;
                    }

                    this.currentSchedulePlayingId = null;
                    this.currentPlayingSchedule = null;
                    this.stopProgressTracking();
                },

                forceStopScheduleAudio(scheduleId) {
                    if (!this.isSchedulePlaying(scheduleId) && !(this.currentPlayingSchedule && this.currentPlayingSchedule.id === scheduleId)) {
                        return;
                    }

                    this.pauseScheduleAudio();
                },

                isSchedulePlaying(scheduleId) {
                    return this.currentSchedulePlayingId === scheduleId;
                },

                isSchedulePassed(schedule) {
                    // Check if schedule time has passed
                    const now = new Date();
                    const currentTimeStr = now.toTimeString().slice(0, 5); // HH:MM

                    // Compare times
                    return schedule.time < currentTimeStr;
                },

                isScheduleNow(schedule) {
                    // Check if schedule time is exactly now
                    const now = new Date();
                    const currentTimeStr = now.toTimeString().slice(0, 5); // HH:MM

                    return schedule.time === currentTimeStr;
                },

                // Progress tracking methods
                startProgressTracking(audioElement, duration) {
                    this.stopProgressTracking(); // Clear any existing interval

                    if (duration) {
                        this.durationFormatted = this.formatTime(duration);
                    }

                    this.progressInterval = setInterval(() => {
                        if (audioElement && !audioElement.paused) {
                            const currentTime = audioElement.currentTime;
                            const audioDuration = audioElement.duration || duration || 0;

                            this.currentTimeFormatted = this.formatTime(currentTime);

                            if (audioDuration > 0) {
                                this.playbackProgress = (currentTime / audioDuration) * 100;
                                if (!duration) {
                                    this.durationFormatted = this.formatTime(audioDuration);
                                }
                            }
                        }
                    }, 500); // Update every 500ms
                },

                stopProgressTracking() {
                    if (this.progressInterval) {
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }
                    this.playbackProgress = 0;
                    this.currentTimeFormatted = '00:00';
                    this.durationFormatted = '00:00';
                },

                startManualProgressTracking(audioElement, duration) {
                    this.stopManualProgressTracking();

                    if (duration) {
                        this.manualDurationFormatted = this.formatTime(duration);
                    }

                    this.manualProgressInterval = setInterval(() => {
                        if (audioElement && !audioElement.paused) {
                            const currentTime = audioElement.currentTime;
                            const audioDuration = audioElement.duration || duration || 0;

                            this.manualCurrentTimeFormatted = this.formatTime(currentTime);

                            if (audioDuration > 0) {
                                this.manualProgress = (currentTime / audioDuration) * 100;
                                if (!duration) {
                                    this.manualDurationFormatted = this.formatTime(audioDuration);
                                }
                            }
                        }
                    }, 500);
                },

                stopManualProgressTracking() {
                    if (this.manualProgressInterval) {
                        clearInterval(this.manualProgressInterval);
                        this.manualProgressInterval = null;
                    }
                    this.manualProgress = 0;
                    this.manualCurrentTimeFormatted = '00:00';
                    this.manualDurationFormatted = '00:00';
                },

                formatTime(seconds) {
                    if (!seconds || isNaN(seconds)) return '00:00';

                    const hrs = Math.floor(seconds / 3600);
                    const mins = Math.floor((seconds % 3600) / 60);
                    const secs = Math.floor(seconds % 60);

                    if (hrs > 0) {
                        return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    }

                    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                }
            }
        }
    </script>
</x-layouts.public-layout>
