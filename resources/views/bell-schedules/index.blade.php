<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Jadwal Bel') }}
            </h2>
            <div class="flex gap-2">
                <button id="toggleEditMode" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Mode Edit') }}
                </button>
                <button id="toggleDeleteMode" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Mode Hapus') }}
                </button>
                <a href="{{ route('bell-schedules.export', ['bell_type_id' => request('bell_type_id'), 'day' => request('day')]) }}" class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 focus:bg-teal-700 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('Download Detail') }}
                </a>
                <a href="{{ route('bell-schedules.export-importable', ['bell_type_id' => request('bell_type_id'), 'day' => request('day')]) }}" class="inline-flex items-center px-4 py-2 bg-cyan-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-cyan-700 focus:bg-cyan-700 active:bg-cyan-900 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    {{ __('Download Import') }}
                </a>
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
                    <span class="block sm:inline whitespace-pre-line">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative dark:bg-yellow-800 dark:border-yellow-600 dark:text-yellow-200" role="alert">
                    <strong class="font-bold">Perhatian!</strong>
                    <span class="block sm:inline whitespace-pre-line">{{ session('warning') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-800 dark:border-red-600 dark:text-red-200" role="alert">
                    <span class="block sm:inline whitespace-pre-line">{{ session('error') }}</span>
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

            <!-- Action Bar for Bulk Operations -->
            <div id="deleteActionBar" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <span id="selectedCount" class="text-sm text-gray-700 dark:text-gray-300">0 item dipilih</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="cancelDelete" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="button" id="bulkDeleteBtn" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                            Hapus Terpilih
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Bar for Edit Mode -->
            <div id="editActionBar" class="hidden bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Mode Edit Aktif - Edit data langsung di tabel</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="cancelEdit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="button" id="saveEdit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($schedules->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="delete-mode-header hidden px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                </th>
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
                                                <th scope="col" class="normal-mode-header px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
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
                                            @foreach ($schedules as $index => $schedule)
                                                <tr>
                                                    <td class="delete-mode-cell hidden px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                        <input type="checkbox" name="schedule_ids[]" value="{{ $schedule->id }}" class="delete-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $schedules->firstItem() + $index }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        <span class="view-mode">{{ $schedule->bellType->name }}</span>
                                                        <select name="schedules[{{ $schedule->id }}][bell_type_id]" class="edit-mode hidden bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                            @foreach($bellTypes as $bellType)
                                                                <option value="{{ $bellType->id }}" {{ $schedule->bell_type_id == $bellType->id ? 'selected' : '' }}>
                                                                    {{ $bellType->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="schedules[{{ $schedule->id }}][id]" value="{{ $schedule->id }}">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        <span class="view-mode">{{ $dayNames[$schedule->day] ?? $schedule->day }}</span>
                                                        <select name="schedules[{{ $schedule->id }}][day]" class="edit-mode hidden bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                            @foreach($dayNames as $dayValue => $dayLabel)
                                                                <option value="{{ $dayValue }}" {{ $schedule->day == $dayValue ? 'selected' : '' }}>
                                                                    {{ $dayLabel }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        <span class="view-mode">{{ date('H:i', strtotime($schedule->time)) }}</span>
                                                        <input type="time" name="schedules[{{ $schedule->id }}][time]" value="{{ date('H:i', strtotime($schedule->time)) }}" class="edit-mode hidden bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        <span class="view-mode">{{ $schedule->audioLibrary->title }}</span>
                                                        <select name="schedules[{{ $schedule->id }}][audio_library_id]" class="edit-mode hidden bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                            @foreach($audioLibraries ?? [] as $audio)
                                                                <option value="{{ $audio->id }}" {{ $schedule->audio_library_id == $audio->id ? 'selected' : '' }}>
                                                                    {{ $audio->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                        <div class="view-mode max-w-xs truncate" title="{{ $schedule->keterangan }}">
                                                            {{ $schedule->keterangan ?? '-' }}
                                                        </div>
                                                        <input type="text" name="schedules[{{ $schedule->id }}][keterangan]" value="{{ $schedule->keterangan }}" class="edit-mode hidden bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Keterangan">
                                                    </td>
                                                    <td class="normal-mode-cell px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
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

    <script>
        let editMode = false;
        let deleteMode = false;

        // Toggle Edit Mode
        document.getElementById('toggleEditMode').addEventListener('click', function() {
            if (deleteMode) {
                alert('Keluar dari mode hapus terlebih dahulu');
                return;
            }

            editMode = !editMode;

            if (editMode) {
                // Show edit mode elements
                document.querySelectorAll('.edit-mode').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.view-mode').forEach(el => el.classList.add('hidden'));
                document.getElementById('editActionBar').classList.remove('hidden');
                document.querySelectorAll('.normal-mode-cell').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.normal-mode-header').forEach(el => el.classList.add('hidden'));
                this.textContent = 'Batalkan Edit';
                this.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                this.classList.add('bg-gray-600', 'hover:bg-gray-700');
            } else {
                // Hide edit mode elements
                document.querySelectorAll('.edit-mode').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.view-mode').forEach(el => el.classList.remove('hidden'));
                document.getElementById('editActionBar').classList.add('hidden');
                document.querySelectorAll('.normal-mode-cell').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.normal-mode-header').forEach(el => el.classList.remove('hidden'));
                this.textContent = 'Mode Edit';
                this.classList.add('bg-purple-600', 'hover:bg-purple-700');
                this.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            }
        });

        // Toggle Delete Mode
        document.getElementById('toggleDeleteMode').addEventListener('click', function() {
            if (editMode) {
                alert('Keluar dari mode edit terlebih dahulu');
                return;
            }

            deleteMode = !deleteMode;

            if (deleteMode) {
                document.querySelectorAll('.delete-mode-header').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.delete-mode-cell').forEach(el => el.classList.remove('hidden'));
                document.getElementById('deleteActionBar').classList.remove('hidden');
                document.querySelectorAll('.normal-mode-cell').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.normal-mode-header').forEach(el => el.classList.add('hidden'));
                this.textContent = 'Batalkan Hapus';
                this.classList.remove('bg-red-600', 'hover:bg-red-700');
                this.classList.add('bg-gray-600', 'hover:bg-gray-700');
            } else {
                document.querySelectorAll('.delete-mode-header').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.delete-mode-cell').forEach(el => el.classList.add('hidden'));
                document.getElementById('deleteActionBar').classList.add('hidden');
                document.querySelectorAll('.normal-mode-cell').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.normal-mode-header').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.delete-checkbox').forEach(el => el.checked = false);
                document.getElementById('selectAll').checked = false;
                updateSelectedCount();
                this.textContent = 'Mode Hapus';
                this.classList.add('bg-red-600', 'hover:bg-red-700');
                this.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            }
        });

        // Cancel Edit
        document.getElementById('cancelEdit').addEventListener('click', function() {
            document.getElementById('toggleEditMode').click();
        });

        // Cancel Delete
        document.getElementById('cancelDelete').addEventListener('click', function() {
            document.getElementById('toggleDeleteMode').click();
        });

        // Save Edit (Bulk Update)
        document.getElementById('saveEdit').addEventListener('click', function() {
            if (confirm('Yakin ingin menyimpan semua perubahan?')) {
                // Create form dynamically
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("bell-schedules.bulk-update") }}';

                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                // Collect all edit mode inputs that are visible
                const editInputs = document.querySelectorAll('.edit-mode:not(.hidden)');
                console.log('Total edit inputs found:', editInputs.length);

                let inputCount = 0;
                editInputs.forEach(input => {
                    if (input.name && input.value !== undefined) {
                        console.log('Input name:', input.name, 'Value:', input.value);
                        const clone = document.createElement('input');
                        clone.type = 'hidden';
                        clone.name = input.name;
                        clone.value = input.value;
                        form.appendChild(clone);
                        inputCount++;
                    }
                });

                // Also collect all hidden inputs with name containing 'schedules' and '[id]'
                const hiddenIdInputs = document.querySelectorAll('input[type="hidden"][name*="schedules"][name*="[id]"]');
                console.log('Total hidden ID inputs found:', hiddenIdInputs.length);

                hiddenIdInputs.forEach(input => {
                    if (input.name && input.value) {
                        console.log('Hidden ID input name:', input.name, 'Value:', input.value);
                        const clone = document.createElement('input');
                        clone.type = 'hidden';
                        clone.name = input.name;
                        clone.value = input.value;
                        form.appendChild(clone);
                        inputCount++;
                    }
                });

                console.log('Total inputs added to form:', inputCount);

                // Add form to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        });

        // Select All Checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.delete-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        // Individual Checkbox
        document.querySelectorAll('.delete-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                const allCheckboxes = document.querySelectorAll('.delete-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.delete-checkbox:checked');
                document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length;
            });
        });

        // Update Selected Count
        function updateSelectedCount() {
            const count = document.querySelectorAll('.delete-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = count + ' item dipilih';
        }

        // Bulk Delete
        document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
            const selectedCount = document.querySelectorAll('.delete-checkbox:checked').length;

            if (selectedCount === 0) {
                alert('Pilih minimal satu item untuk dihapus');
                return;
            }

            if (confirm(`Yakin ingin menghapus ${selectedCount} jadwal yang dipilih?`)) {
                // Create form dynamically
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("bell-schedules.bulk-destroy") }}';

                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                // Collect all checked checkboxes
                const checkedBoxes = document.querySelectorAll('.delete-checkbox:checked');
                checkedBoxes.forEach(checkbox => {
                    const clone = document.createElement('input');
                    clone.type = 'hidden';
                    clone.name = 'schedule_ids[]';
                    clone.value = checkbox.value;
                    form.appendChild(clone);
                });

                // Add form to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
</x-app-layout>
