<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Jadwal Bel') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('bell-schedules.import.form') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Import Excel') }}
                </a>
                <a href="{{ route('bell-schedules.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Tambah Jadwal') }}
                </a>
            </div>
        </div>
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

            <!-- Filter Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('bell-schedules.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="bell_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Filter Jenis Bel
                            </label>
                            <select name="bell_type_id" id="bell_type_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="">Semua Jenis</option>
                                @foreach($bellTypes as $bellType)
                                    <option value="{{ $bellType->id }}" {{ request('bell_type_id') == $bellType->id ? 'selected' : '' }}>
                                        {{ $bellType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Filter Hari
                            </label>
                            <select name="day" id="day" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="">Semua Hari</option>
                                <option value="monday" {{ request('day') == 'monday' ? 'selected' : '' }}>Senin</option>
                                <option value="tuesday" {{ request('day') == 'tuesday' ? 'selected' : '' }}>Selasa</option>
                                <option value="wednesday" {{ request('day') == 'wednesday' ? 'selected' : '' }}>Rabu</option>
                                <option value="thursday" {{ request('day') == 'thursday' ? 'selected' : '' }}>Kamis</option>
                                <option value="friday" {{ request('day') == 'friday' ? 'selected' : '' }}>Jumat</option>
                                <option value="saturday" {{ request('day') == 'saturday' ? 'selected' : '' }}>Sabtu</option>
                                <option value="sunday" {{ request('day') == 'sunday' ? 'selected' : '' }}>Minggu</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Filter') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($schedules->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            No
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Jenis Bel
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Hari
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Waktu
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Audio
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Keterangan
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($schedules as $index => $schedule)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $schedules->firstItem() + $index }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $schedule->bellType->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
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
                                                @endphp
                                                {{ $dayNames[$schedule->day] ?? $schedule->day }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ date('H:i', strtotime($schedule->time)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $schedule->audioLibrary->title }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                <div class="max-w-xs truncate" title="{{ $schedule->keterangan }}">
                                                    {{ $schedule->keterangan ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('bell-schedules.edit', $schedule) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                                    Edit
                                                </a>
                                                <form action="{{ route('bell-schedules.destroy', $schedule) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $schedules->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">
                            Belum ada data jadwal. <a href="{{ route('bell-schedules.create') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Tambah jadwal baru</a> atau <a href="{{ route('bell-schedules.import.form') }}" class="text-green-600 hover:text-green-800 dark:text-green-400">import dari Excel</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
