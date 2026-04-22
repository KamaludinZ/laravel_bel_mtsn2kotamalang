<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detail Audio') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('audio-libraries.edit', $audioLibrary) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('audio-libraries.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Kembali') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Judul Audio</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $audioLibrary->title }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Nama File</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ basename($audioLibrary->file_path) }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Preview Audio</h3>
                        <audio controls class="w-full max-w-md">
                            <source src="{{ asset('storage/' . $audioLibrary->file_path) }}" type="audio/mpeg">
                            Browser tidak mendukung audio.
                        </audio>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Dibuat Pada</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $audioLibrary->created_at->format('d M Y H:i') }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Diupdate Pada</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $audioLibrary->updated_at->format('d M Y H:i') }}</p>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <a href="{{ route('audio-libraries.edit', $audioLibrary) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ __('Edit Audio') }}
                        </a>
                        <form action="{{ route('audio-libraries.destroy', $audioLibrary) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus audio ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Hapus Audio') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
