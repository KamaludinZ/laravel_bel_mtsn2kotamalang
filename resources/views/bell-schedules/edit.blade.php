<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Jadwal Bel') }}
            </h2>
            <a href="{{ route('bell-schedules.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Kembali') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('bell-schedules.update', $bellSchedule) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label for="bell_type_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                Jenis Bel <span class="text-red-500">*</span>
                            </label>
                            <select name="bell_type_id" id="bell_type_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('bell_type_id') border-red-500 @enderror">
                                <option value="">Pilih Jenis Bel</option>
                                @foreach($bellTypes as $bellType)
                                    <option value="{{ $bellType->id }}" {{ old('bell_type_id', $bellSchedule->bell_type_id) == $bellType->id ? 'selected' : '' }}>
                                        {{ $bellType->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bell_type_id')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="day" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                Hari <span class="text-red-500">*</span>
                            </label>
                            <select name="day" id="day" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('day') border-red-500 @enderror">
                                <option value="">Pilih Hari</option>
                                @foreach($days as $value => $label)
                                    <option value="{{ $value }}" {{ old('day', $bellSchedule->day) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('day')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="time" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                Waktu <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="time" id="time" value="{{ old('time', date('H:i', strtotime($bellSchedule->time))) }}" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('time') border-red-500 @enderror">
                            @error('time')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="audio_library_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                Audio <span class="text-red-500">*</span>
                            </label>
                            <select name="audio_library_id" id="audio_library_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('audio_library_id') border-red-500 @enderror">
                                <option value="">Pilih Audio</option>
                                @foreach($audioLibraries as $audio)
                                    <option value="{{ $audio->id }}" {{ old('audio_library_id', $bellSchedule->audio_library_id) == $audio->id ? 'selected' : '' }}>
                                        {{ $audio->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('audio_library_id')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                Keterangan
                            </label>
                            <textarea name="keterangan" id="keterangan" rows="3"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('keterangan') border-red-500 @enderror"
                                placeholder="Tambahkan keterangan jadwal (opsional)...">{{ old('keterangan', $bellSchedule->keterangan) }}</textarea>
                            @error('keterangan')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Update') }}
                            </button>
                            <a href="{{ route('bell-schedules.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Batal') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
