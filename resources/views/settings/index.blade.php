<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Aplikasi') }}
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <label for="app_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                Nama Aplikasi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $appName) }}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('app_name') border-red-500 @enderror"
                                placeholder="Contoh: Bel Sekolah MTsN 2 Kota Malang" required>
                            @error('app_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Nama aplikasi akan ditampilkan di header dan halaman public</p>
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

                            <label for="app_logo" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                {{ $appLogo ? 'Ganti Logo (Opsional)' : 'Upload Logo' }}
                            </label>
                            <input type="file" name="app_logo" id="app_logo" accept=".jpeg,.jpg,.png,.svg"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 @error('app_logo') border-red-500 @enderror">
                            @error('app_logo')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Format yang didukung: JPEG, PNG, JPG, SVG (maksimal 2MB)</p>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 dark:bg-blue-900/20 dark:border-blue-500">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        <strong>Info:</strong> Perubahan nama aplikasi akan langsung terlihat di seluruh aplikasi setelah disimpan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Simpan Pengaturan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
