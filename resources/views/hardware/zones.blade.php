<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Kelola Speaker Zones') }}
            </h2>
            <a href="{{ route('hardware.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                ← Kembali ke Dashboard
            </a>
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Informasi Speaker Zones</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Kelola konfigurasi speaker zones untuk kontrol hardware. Setiap zone merepresentasikan satu channel output Modbus.
                        </p>
                    </div>

                    <div class="space-y-6">
                        @forelse($zones as $zone)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 {{ !$zone->is_enabled ? 'bg-gray-50 dark:bg-gray-900/20' : '' }}">
                                <form action="{{ route('hardware.zones.update', $zone) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <!-- Zone Name -->
                                        <div>
                                            <label for="name_{{ $zone->id }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                Nama Zone <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="name" id="name_{{ $zone->id }}"
                                                value="{{ old('name', $zone->name) }}"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="Contoh: Lantai 1 - Kelas" required>
                                        </div>

                                        <!-- Modbus Channel -->
                                        <div>
                                            <label for="modbus_channel_{{ $zone->id }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                Modbus Channel <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" name="modbus_channel" id="modbus_channel_{{ $zone->id }}"
                                                value="{{ old('modbus_channel', $zone->modbus_channel) }}"
                                                min="1" max="8"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="1-8" required>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Channel 1-8 (harus unik)</p>
                                        </div>

                                        <!-- Description -->
                                        <div class="md:col-span-2">
                                            <label for="description_{{ $zone->id }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                Deskripsi
                                            </label>
                                            <textarea name="description" id="description_{{ $zone->id }}" rows="2"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="Deskripsi lokasi atau area zone ini">{{ old('description', $zone->description) }}</textarea>
                                        </div>

                                        <!-- Default Duration -->
                                        <div>
                                            <label for="default_duration_seconds_{{ $zone->id }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                Default Duration (detik) <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" name="default_duration_seconds" id="default_duration_seconds_{{ $zone->id }}"
                                                value="{{ old('default_duration_seconds', $zone->default_duration_seconds) }}"
                                                min="1" max="3600"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="5" required>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Durasi default saat test speaker (1-3600 detik)</p>
                                        </div>

                                        <!-- Enable/Disable Toggle -->
                                        <div class="flex items-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="hidden" name="is_enabled" value="0">
                                                <input type="checkbox" name="is_enabled" value="1"
                                                    class="sr-only peer"
                                                    {{ old('is_enabled', $zone->is_enabled) ? 'checked' : '' }}>
                                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                                                    Zone Aktif
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                            Simpan Perubahan
                                        </button>

                                        @if($zone->is_enabled)
                                            <button type="button" onclick="testZone('{{ $zone->id }}', {{ $zone->default_duration_seconds }})"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                                Test Speaker
                                            </button>
                                        @endif

                                        <div class="ml-auto text-sm text-gray-500 dark:text-gray-400">
                                            <span class="px-2 py-1 rounded-full {{ $zone->is_enabled ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                {{ $zone->is_enabled ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada speaker zones</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada speaker zone yang dikonfigurasi. Silakan tambahkan melalui database seeder.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    @if($zones->isNotEmpty())
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-md font-semibold mb-3">Catatan Penting:</h4>
                            <ul class="list-disc list-inside space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <li>Setiap Modbus Channel harus unik (tidak boleh duplikat)</li>
                                <li>Channel 1-8 sesuai dengan output relay/speaker fisik pada perangkat Modbus</li>
                                <li>Zone yang nonaktif tidak akan ditrigger saat bel otomatis berbunyi</li>
                                <li>Default duration digunakan sebagai nilai awal saat test speaker manual</li>
                                <li>Untuk menambah zone baru, gunakan database seeder atau migration</li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function testZone(zoneId, defaultDuration) {
            if (!confirm(`Test speaker zone ini selama ${defaultDuration} detik?`)) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("hardware.test-speaker") }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';

            const zoneInput = document.createElement('input');
            zoneInput.type = 'hidden';
            zoneInput.name = 'zone_id';
            zoneInput.value = zoneId;

            const durationInput = document.createElement('input');
            durationInput.type = 'hidden';
            durationInput.name = 'duration';
            durationInput.value = defaultDuration;

            form.appendChild(csrfInput);
            form.appendChild(zoneInput);
            form.appendChild(durationInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</x-app-layout>
