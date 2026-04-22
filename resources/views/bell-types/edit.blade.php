<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Jenis Bel') }}
            </h2>
            <a href="{{ route('bell-types.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Kembali') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('bell-types.update', $bellType) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                Nama Jenis Bel <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $bellType->name) }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('name') border-red-500 @enderror"
                                placeholder="Contoh: Jadwal Normal" required>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                Hari Aktif <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($days as $value => $label)
                                    @php
                                        $oldActiveDays = old('active_days', $bellType->active_days ?? []);
                                        $isChecked = is_array($oldActiveDays) && in_array($value, $oldActiveDays);
                                    @endphp
                                    <div class="flex items-center">
                                        <input type="checkbox" name="active_days[]" id="day_{{ $value }}" value="{{ $value }}"
                                            {{ $isChecked ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="day_{{ $value }}" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                            {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('active_days')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Pilih minimal 1 hari</p>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_automatic" id="is_automatic" value="1"
                                    {{ old('is_automatic', $bellType->is_automatic) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="is_automatic" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Aktifkan Mode Otomatis
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Jika dicentang, bel akan berbunyi otomatis sesuai jadwal</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Update') }}
                            </button>
                            <a href="{{ route('bell-types.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Batal') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
