<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detail Jenis Bel') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('bell-types.edit', $bellType) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('bell-types.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Kembali') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Nama Jenis Bel</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $bellType->name }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Hari Aktif</h3>
                        @php
                            $dayNames = [
                                'monday' => 'Senin',
                                'tuesday' => 'Selasa',
                                'wednesday' => 'Rabu',
                                'thursday' => 'Kamis',
                                'friday' => 'Jumat',
                                'saturday' => 'Sabtu',
                                'sunday' => 'Minggu',
                            ];
                            $activeDays = array_map(function($day) use ($dayNames) {
                                return $dayNames[$day] ?? $day;
                            }, $bellType->active_days ?? []);
                        @endphp
                        <p class="text-gray-700 dark:text-gray-300">{{ implode(', ', $activeDays) }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Mode Otomatis</h3>
                        @if($bellType->is_automatic)
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                Aktif
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Status</h3>
                        @if($bellType->is_active)
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                Aktif
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        @if(!$bellType->is_active)
                            <form action="{{ route('bell-types.activate', $bellType) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    {{ __('Aktifkan Jenis Bel Ini') }}
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('bell-types.edit', $bellType) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ __('Edit Jenis Bel') }}
                        </a>
                        <form action="{{ route('bell-types.destroy', $bellType) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus jenis bel ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Hapus Jenis Bel') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($bellType->bellSchedules->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Jadwal Bel</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Hari
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Waktu
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Audio
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($bellType->bellSchedules as $schedule)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $dayNames[$schedule->day] ?? $schedule->day }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ date('H:i', strtotime($schedule->time)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $schedule->audioLibrary->title }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <p class="text-gray-500 dark:text-gray-400 text-center">Belum ada jadwal untuk jenis bel ini.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
