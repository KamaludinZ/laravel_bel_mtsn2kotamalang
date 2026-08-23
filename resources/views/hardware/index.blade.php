<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Hardware Control Dashboard') }}
        </h2>
    </x-slot>

    <style>
        /* Room button hover effects */
        button[title*="Zone"]:hover {
            z-index: 20 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        }
    </style>

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

            <!-- Bridge Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 {{ $stats['bridge_status'] === 'online' ? 'bg-green-500' : 'bg-red-500' }} rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bridge Status</p>
                            <p class="text-lg font-semibold {{ $stats['bridge_status'] === 'online' ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($stats['bridge_status']) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Commands</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $stats['pending_commands'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today Success</p>
                            <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $stats['today_success'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today Failed</p>
                            <p class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $stats['today_failed'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button onclick="switchTab('zone-control')" id="tab-zone-control" class="tab-button active border-blue-500 text-blue-600 dark:text-blue-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Zone Control
                        </button>
                        <button onclick="switchTab('config')" id="tab-config" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Konfigurasi Hardware
                        </button>
                        <button onclick="switchTab('zones')" id="tab-zones" class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Kelola Speaker Zones
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab Content: Zone Control -->
            <div id="content-zone-control" class="tab-content">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-4">
                        <div class="flex gap-4">
                            <!-- LEFT SIDE: Controls (Vertical Stack) -->
                            <div style="width: 320px; flex-shrink: 0;">
                                <!-- Group Buttons (2 cols x 4 rows) -->
                                <div class="mb-3">
                                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Group Controls</h4>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        @php
                                            $allGroupsOrder = ['GROUP 1', 'GROUP 2', 'GROUP 3', 'GROUP 4', 'GROUP 5', 'GROUP 6', 'CUSTOM 1', 'CUSTOM 2'];
                                            $groupsArray = is_array($groups) ? $groups : $groups->toArray();
                                        @endphp
                                        @foreach($allGroupsOrder as $group)
                                            @php
                                                $isActive = in_array($group, $groupsArray);
                                                $groupId = 'btn-group-' . str_replace(' ', '-', strtolower($group));
                                                $dotId = 'dot-group-' . str_replace(' ', '-', strtolower($group));
                                            @endphp
                                            @if($isActive)
                                                <button type="button" id="{{ $groupId }}" onclick="triggerGroup('{{ $group }}')"
                                                    class="relative w-full px-2 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs font-semibold shadow-sm hover:shadow transition">
                                                    <span class="pulse-dot" id="{{ $dotId }}"></span>
                                                    {{ $groupLabels[$group] ?? $group }}
                                                </button>
                                            @else
                                                <button type="button" disabled
                                                    class="w-full px-2 py-2 bg-gray-300 text-gray-500 rounded text-xs font-semibold cursor-not-allowed opacity-60"
                                                    title="Grup ini belum memiliki room">
                                                    {{ $groupLabels[$group] ?? $group }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Audio Control (2 cols x 2 rows) -->
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Audio Control</h4>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <button type="button" onclick="triggerParentChannel('HORN')" id="btn-horn"
                                            class="relative w-full px-2 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-semibold shadow-sm hover:shadow transition">
                                            <span class="pulse-dot" id="dot-horn"></span>
                                            HORN
                                        </button>
                                        <button type="button" onclick="triggerParentChannel('CTRLROOM')" id="btn-ctrlroom"
                                            class="relative w-full px-2 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs font-semibold shadow-sm hover:shadow transition">
                                            <span class="pulse-dot" id="dot-ctrlroom"></span>
                                            CTRL ROOM
                                        </button>
                                        <button type="button" onclick="triggerOnAll()" id="btn-on-all"
                                            class="relative w-full px-2 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-semibold shadow-sm hover:shadow transition">
                                            <span class="pulse-dot" id="dot-on-all"></span>
                                            ON ALL
                                        </button>
                                        <button type="button" onclick="triggerOffAll()" id="btn-off-all"
                                            class="relative w-full px-2 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-semibold shadow-sm hover:shadow transition">
                                            <span class="pulse-dot" id="dot-off-all"></span>
                                            OFF ALL
                                        </button>
                                    </div>

                                    <!-- Edit Buttons -->
                                    <div class="mt-3 grid grid-cols-2 gap-1.5">
                                        <button type="button" onclick="openEditGroupModal()"
                                            class="w-full px-2 py-1.5 bg-gray-600 hover:bg-gray-700 text-white rounded text-xs font-semibold shadow-sm hover:shadow transition flex items-center justify-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            Edit Groups
                                        </button>
                                        <button type="button" onclick="openEditRoomModal()"
                                            class="w-full px-2 py-1.5 bg-gray-600 hover:bg-gray-700 text-white rounded text-xs font-semibold shadow-sm hover:shadow transition flex items-center justify-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            Edit Rooms
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT SIDE: Room Grid 10x4 -->
                            <div class="flex-1">
                                <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Room Control Grid</h4>

                                @php
                                    // Map rooms by their original/initial room_code for grid positioning
                                    // Using 'no' field as the stable identifier for grid position
                                    $roomsByNo = $allRooms->keyBy('no');

                                    // Grid layout using 'no' field instead of room_code
                                    // This ensures room position remains stable even if code changes
                                    $gridLayout = [
                                        [1, 2, 31, 4, 5, 6, 7, 8, 9, 33],      // Row 1: 7A,7B,7C,7D,7E,7F,7G,7H,7I,7J
                                        [11, 12, 13, 14, 15, 16, 17, 18, 20, 10], // Row 2: 8A,8B,8C,8D,8E,8F,8G,8H,8I,8J
                                        [21, 22, 23, 24, 25, 26, 27, 28, 29, 30], // Row 3: 9A,9B,9C,9D,9E,9F,9G,9H,9I,9K (no 30 is 9K not 8K)
                                        [43, 44, 51, 52, 53, 54, 55, 56, 57, 58], // Row 4: LIBRARY, MAHAD1, RES1-RES8
                                    ];

                                    $groupColors = [
                                        'GROUP 1' => ['bg' => '#dbeafe', 'border' => '#3b82f6', 'text' => '#1e40af', 'badge' => '#2563eb'],
                                        'GROUP 2' => ['bg' => '#d1fae5', 'border' => '#10b981', 'text' => '#065f46', 'badge' => '#059669'],
                                        'GROUP 3' => ['bg' => '#e9d5ff', 'border' => '#a855f7', 'text' => '#6b21a8', 'badge' => '#9333ea'],
                                        'GROUP 4' => ['bg' => '#fed7aa', 'border' => '#f97316', 'text' => '#9a3412', 'badge' => '#ea580c'],
                                        'GROUP 5' => ['bg' => '#fce7f3', 'border' => '#ec4899', 'text' => '#9f1239', 'badge' => '#db2777'],
                                        'GROUP 6' => ['bg' => '#cffafe', 'border' => '#06b6d4', 'text' => '#155e75', 'badge' => '#0891b2'],
                                        'CUSTOM 1' => ['bg' => '#fef3c7', 'border' => '#f59e0b', 'text' => '#92400e', 'badge' => '#d97706'],
                                        'CUSTOM 2' => ['bg' => '#ffe4e6', 'border' => '#f43f5e', 'text' => '#9f1239', 'badge' => '#e11d48'],
                                    ];
                                @endphp

                                <!-- Grid 10 columns x 4 rows -->
                                @foreach($gridLayout as $rowIndex => $row)
                                    <div class="flex gap-1 mb-1">
                                        @foreach($row as $roomNo)
                                            @if($roomNo && isset($roomsByNo[$roomNo]))
                                                @php
                                                    $room = $roomsByNo[$roomNo];
                                                    $colors = $groupColors[$room->group_name] ?? ['bg' => '#f3f4f6', 'border' => '#9ca3af', 'text' => '#4b5563', 'badge' => '#6b7280'];
                                                    $roomBtnId = 'btn-room-' . $room->id;
                                                    $roomDotId = 'dot-room-' . $room->id;
                                                @endphp
                                                @if($room->speakerZone)
                                                    <button type="button" id="{{ $roomBtnId }}"
                                                        onclick="testRoom('{{ $room->id }}', '{{ $room->room_name }}', '{{ $room->group_name }}')"
                                                        title="Test {{ $room->room_name }} ({{ $groupLabels[$room->group_name] ?? $room->group_name }}) - Zone {{ $room->speakerZone->modbus_channel }}"
                                                        style="background-color: {{ $colors['bg'] }}; border-color: {{ $colors['border'] }}; color: {{ $colors['text'] }}; width: calc(10% - 4px); height: 60px; padding: 6px 4px;"
                                                        class="relative flex flex-col items-center justify-center rounded border-2 hover:shadow-md transition cursor-pointer flex-shrink-0">
                                                        <span class="pulse-dot" id="{{ $roomDotId }}" style="top: 3px; right: 3px; width: 8px; height: 8px;"></span>
                                                        <div class="text-center w-full px-0.5">
                                                            <div class="font-bold" style="font-size: 10px; line-height: 1.2; margin-bottom: 2px;">{{ $room->room_name }}</div>
                                                            <div class="opacity-70" style="font-size: 7px; line-height: 1.1;">{{ $groupLabels[$room->group_name] ?? $room->group_name }}</div>
                                                        </div>
                                                    </button>
                                                @else
                                                    <div style="width: calc(10% - 4px); height: 60px;"
                                                        class="flex items-center justify-center rounded border border-gray-300 bg-gray-100 opacity-30 flex-shrink-0">
                                                        <span class="text-xs text-gray-400">{{ $room->room_name }}</span>
                                                    </div>
                                                @endif
                                            @elseif($roomNo)
                                                <button type="button"
                                                    onclick="alert('Slot #{{ $roomNo }}: Fitur untuk menambahkan room baru akan segera tersedia')"
                                                    style="width: calc(10% - 4px); height: 60px;"
                                                    class="flex flex-col items-center justify-center rounded border-2 border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100 hover:border-gray-400 transition cursor-pointer flex-shrink-0">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    <span class="text-xs text-gray-400 mt-1" style="font-size: 7px;">Available</span>
                                                </button>
                                            @else
                                                <div style="width: calc(10% - 4px); height: 60px;" class="flex-shrink-0"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach

                                <!-- Legend -->
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <div class="text-xs">
                                        <div class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Legend:</div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="flex gap-2 items-center">
                                                <div class="w-4 h-4 rounded border-2" style="background: #dbeafe; border-color: #3b82f6;"></div>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $groupLabels['GROUP 1'] ?? 'GROUP 1' }}</span>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div class="w-4 h-4 rounded border-2" style="background: #d1fae5; border-color: #10b981;"></div>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $groupLabels['GROUP 2'] ?? 'GROUP 2' }}</span>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div class="w-4 h-4 rounded border-2" style="background: #e9d5ff; border-color: #a855f7;"></div>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $groupLabels['GROUP 3'] ?? 'GROUP 3' }}</span>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div class="w-4 h-4 rounded border-2" style="background: #fed7aa; border-color: #f97316;"></div>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $groupLabels['GROUP 4'] ?? 'GROUP 4' }}</span>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div class="w-4 h-4 rounded border-2" style="background: #fce7f3; border-color: #ec4899;"></div>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $groupLabels['GROUP 5'] ?? 'GROUP 5' }}</span>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div class="w-4 h-4 rounded border-2" style="background: #cffafe; border-color: #06b6d4;"></div>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $groupLabels['GROUP 6'] ?? 'GROUP 6' }}</span>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div class="w-4 h-4 rounded border-2" style="background: #fef3c7; border-color: #eab308;"></div>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $groupLabels['CUSTOM 1'] ?? 'CUSTOM 1' }}</span>
                                            </div>
                                            <div class="flex gap-2 items-center">
                                                <div class="w-4 h-4 rounded border-2" style="background: #e0e7ff; border-color: #6366f1;"></div>
                                                <span class="text-gray-600 dark:text-gray-400">{{ $groupLabels['CUSTOM 2'] ?? 'CUSTOM 2' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal: Edit Group Labels -->
                <div id="editGroupModal" class="hidden fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
                    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl transform transition-all">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    Edit Group Control Labels
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Customize display names for each group control button
                                </p>
                            </div>
                            <button onclick="closeEditGroupModal()"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-3">
                                @php
                                    $groupColors = [
                                        'GROUP 1' => '#3b82f6',
                                        'GROUP 2' => '#10b981',
                                        'GROUP 3' => '#a855f7',
                                        'GROUP 4' => '#f97316',
                                        'GROUP 5' => '#ec4899',
                                        'GROUP 6' => '#06b6d4',
                                        'CUSTOM 1' => '#eab308',
                                        'CUSTOM 2' => '#6366f1',
                                    ];
                                @endphp
                                @foreach(['GROUP 1', 'GROUP 2', 'GROUP 3', 'GROUP 4', 'GROUP 5', 'GROUP 6', 'CUSTOM 1', 'CUSTOM 2'] as $group)
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center gap-1.5 mb-1.5">
                                            <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $groupColors[$group] }}"></div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                {{ $group }}
                                            </label>
                                        </div>
                                        <input type="text"
                                            id="group_label_{{ str_replace(' ', '_', $group) }}"
                                            value="{{ $groupLabels[$group] ?? $group }}"
                                            placeholder="Custom label..."
                                            maxlength="20"
                                            class="w-full max-w-[200px] px-2.5 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white transition">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-2 p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 rounded-b-xl">
                            <button onclick="closeEditGroupModal()"
                                class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition shadow-sm">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancel
                                </span>
                            </button>
                            <button onclick="saveGroupLabels()"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition shadow-md hover:shadow-lg">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Save Changes
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal: Edit Room Names -->
                <div id="editRoomModal" class="hidden fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
                    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-5xl transform transition-all max-h-[85vh] flex flex-col">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    Edit Room Names
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Update room names - Changes highlighted automatically
                                </p>
                            </div>
                            <button onclick="closeEditRoomModal()"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Search and Filter Bar -->
                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/30 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                            <div class="flex gap-2 items-center">
                                <div class="flex-1 relative">
                                    <svg class="absolute left-2.5 top-1/2 transform -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" id="roomSearchInput" placeholder="Search..."
                                        onkeyup="filterRooms()"
                                        class="w-full pl-9 pr-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                                </div>
                                <select id="groupFilterSelect" onchange="filterRooms()"
                                    class="px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                                    <option value="">All Groups</option>
                                    @foreach(['GROUP 1', 'GROUP 2', 'GROUP 3', 'GROUP 4', 'GROUP 5', 'GROUP 6', 'CUSTOM 1', 'CUSTOM 2'] as $group)
                                        <option value="{{ $group }}">{{ $groupLabels[$group] ?? $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Modal Body - Table -->
                        <div class="overflow-y-auto flex-1">
                            <table class="w-full text-xs" id="roomsTable">
                                <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-10">
                                    <tr class="border-b border-gray-200 dark:border-gray-600">
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300" style="width: 40px;">No</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300" style="width: 60px;">Code</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">Room Name</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300" style="width: 100px;">Group</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300" style="width: 120px;">Speaker Zone</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300" style="width: 80px;">Hardware</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @php
                                        $roomGroupColors = [
                                            'GROUP 1' => '#3b82f6',
                                            'GROUP 2' => '#10b981',
                                            'GROUP 3' => '#a855f7',
                                            'GROUP 4' => '#f97316',
                                            'GROUP 5' => '#ec4899',
                                            'GROUP 6' => '#06b6d4',
                                            'CUSTOM 1' => '#eab308',
                                            'CUSTOM 2' => '#6366f1',
                                        ];
                                    @endphp
                                    @foreach($rooms as $index => $room)
                                        <tr class="room-item hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                            data-room-code="{{ $room->room_code }}"
                                            data-room-name="{{ $room->room_name }}"
                                            data-group-name="{{ $room->group_name }}"
                                            data-page="1">
                                            <!-- No -->
                                            <td class="px-2 py-2 text-gray-600 dark:text-gray-400 text-center room-number">
                                                {{ $index + 1 }}
                                            </td>
                                            <!-- Code (Editable) -->
                                            <td class="px-2 py-2">
                                                <input type="text"
                                                    id="room_code_{{ $room->id }}"
                                                    value="{{ $room->room_code }}"
                                                    data-room-id="{{ $room->id }}"
                                                    data-original-code="{{ $room->room_code }}"
                                                    placeholder="Code"
                                                    maxlength="10"
                                                    oninput="highlightChangedRoom(this)"
                                                    {{ $room->group_name === 'PARENT' ? 'readonly' : '' }}
                                                    class="room-code-input w-full px-1.5 py-0.5 text-xs font-bold border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white text-center {{ $room->group_name === 'PARENT' ? 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed' : '' }}">
                                            </td>
                                            <!-- Room Name (Editable - Max 3 words) -->
                                            <td class="px-2 py-2">
                                                <input type="text"
                                                    id="room_name_{{ $room->id }}"
                                                    value="{{ $room->room_name }}"
                                                    data-room-id="{{ $room->id }}"
                                                    data-original-name="{{ $room->room_name }}"
                                                    placeholder="Max 3 words..."
                                                    oninput="validateRoomName(this); highlightChangedRoom(this)"
                                                    class="room-name-input w-full px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white transition">
                                            </td>
                                            <!-- Group (Dropdown) -->
                                            <td class="px-2 py-2">
                                                <select
                                                    id="room_group_{{ $room->id }}"
                                                    data-room-id="{{ $room->id }}"
                                                    data-original-group="{{ $room->group_name }}"
                                                    onchange="highlightChangedRoom(this)"
                                                    class="room-group-select w-full px-1.5 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                                                    @foreach(['GROUP 1', 'GROUP 2', 'GROUP 3', 'GROUP 4', 'GROUP 5', 'GROUP 6', 'CUSTOM 1', 'CUSTOM 2'] as $group)
                                                        <option value="{{ $group }}" {{ $room->group_name == $group ? 'selected' : '' }}>{{ $groupLabels[$group] ?? $group }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <!-- Speaker Zone (Dropdown) -->
                                            <td class="px-2 py-2">
                                                <select
                                                    id="room_zone_{{ $room->id }}"
                                                    data-room-id="{{ $room->id }}"
                                                    data-original-zone="{{ $room->speaker_zone_id ?? '' }}"
                                                    onchange="highlightChangedRoom(this)"
                                                    class="room-zone-select w-full px-1.5 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                                                    <option value="">- None -</option>
                                                    @foreach($zones as $zone)
                                                        <option value="{{ $zone->id }}" {{ $room->speaker_zone_id == $zone->id ? 'selected' : '' }}>
                                                            Zone {{ $zone->modbus_channel }} - {{ $zone->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <!-- Hardware Address (Editable Dropdown) -->
                                            <td class="px-2 py-2">
                                                <select
                                                    id="room_hw_{{ $room->id }}"
                                                    data-room-id="{{ $room->id }}"
                                                    data-original-hw="{{ $room->hardware_address ?? '' }}"
                                                    onchange="highlightChangedRoom(this)"
                                                    class="room-hw-select w-full px-1.5 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                                                    <option value="">-</option>
                                                    @for($i = 1; $i <= 32; $i++)
                                                        @for($j = 1; $j <= 8; $j++)
                                                            @php $hwValue = "{$i}-{$j}"; @endphp
                                                            <option value="{{ $hwValue }}" {{ $room->hardware_address == $hwValue ? 'selected' : '' }}>{{ $hwValue }}</option>
                                                        @endfor
                                                    @endfor
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Modal Footer -->
                        <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 rounded-b-xl flex-shrink-0">
                            <!-- Pagination -->
                            <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    Showing <span id="showingStart">1</span>-<span id="showingEnd">10</span> of <span id="totalRooms">{{ $rooms->count() }}</span> rooms
                                </div>
                                <div class="flex items-center gap-1">
                                    <button onclick="previousPage()" id="prevPageBtn"
                                        class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Prev
                                    </button>
                                    <span class="px-2 text-xs text-gray-600 dark:text-gray-400">
                                        Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                                    </span>
                                    <button onclick="nextPage()" id="nextPageBtn"
                                        class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Next
                                    </button>
                                </div>
                            </div>
                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between p-3">
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    <span id="changedRoomsCount">0</span> room(s) changed
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="closeEditRoomModal()"
                                        class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition shadow-sm">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Cancel
                                        </span>
                                    </button>
                                    <button onclick="saveRoomData()"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition shadow-md hover:shadow-lg">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Save All
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Hardware Logs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Recent Hardware Logs</h3>
                            <a href="{{ route('hardware.logs') }}"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                Lihat Semua Log →
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Timestamp</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Zone</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Response Time</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($recentLogs as $log)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->action }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $log->speakerZone->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $log->status === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                                    {{ $log->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                                    {{ $log->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                ">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                                {{ $log->response_time_ms ? $log->response_time_ms . 'ms' : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                Belum ada log hardware
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Konfigurasi Hardware -->
            <div id="content-config" class="tab-content hidden">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Konfigurasi Hardware Modbus RS485</h3>

                        <form action="{{ route('hardware.update-config') }}" method="POST">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- COM Port -->
                                <div>
                                    <label for="com_port" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        COM Port <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="com_port" id="com_port"
                                        value="{{ old('com_port', $config->com_port ?? 'COM3') }}"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                        placeholder="COM3" required>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Port serial USB-RS485 adapter</p>
                                </div>

                                <!-- Baud Rate -->
                                <div>
                                    <label for="baud_rate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        Baud Rate <span class="text-red-500">*</span>
                                    </label>
                                    <select name="baud_rate" id="baud_rate"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                        <option value="9600" {{ old('baud_rate', $config->baud_rate ?? 9600) == 9600 ? 'selected' : '' }}>9600</option>
                                        <option value="19200" {{ old('baud_rate', $config->baud_rate ?? 9600) == 19200 ? 'selected' : '' }}>19200</option>
                                        <option value="38400" {{ old('baud_rate', $config->baud_rate ?? 9600) == 38400 ? 'selected' : '' }}>38400</option>
                                        <option value="57600" {{ old('baud_rate', $config->baud_rate ?? 9600) == 57600 ? 'selected' : '' }}>57600</option>
                                        <option value="115200" {{ old('baud_rate', $config->baud_rate ?? 9600) == 115200 ? 'selected' : '' }}>115200</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kecepatan komunikasi serial</p>
                                </div>

                                <!-- Modbus Address -->
                                <div>
                                    <label for="modbus_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        Modbus Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="modbus_address" id="modbus_address"
                                        value="{{ old('modbus_address', $config->modbus_address ?? 1) }}"
                                        min="1" max="247"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                        placeholder="1" required>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Slave address perangkat Modbus (1-247)</p>
                                </div>

                                <!-- Timeout -->
                                <div>
                                    <label for="timeout_ms" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        Timeout (ms)
                                    </label>
                                    <input type="number" name="timeout_ms" id="timeout_ms"
                                        value="{{ old('timeout_ms', $config->timeout_ms ?? 1000) }}"
                                        min="100" max="5000"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                        placeholder="1000">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Waktu tunggu response maksimal</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Simpan Konfigurasi
                                </button>
                            </div>
                        </form>

                        <!-- Info Box -->
                        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                            <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">ℹ️ Informasi Konfigurasi</h4>
                            <ul class="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-1">
                                <li>COM Port harus sesuai dengan port USB-RS485 adapter di Device Manager</li>
                                <li>Baud rate default untuk kebanyakan relay Modbus adalah 9600</li>
                                <li>Modbus address harus sama dengan setting di relay module</li>
                                <li>Timeout yang terlalu kecil dapat menyebabkan error komunikasi</li>
                            </ul>
                        </div>

                        <!-- Python Bridge Installation Guide -->
                        <div class="mt-6">
                            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-lg border border-purple-200 dark:border-purple-700 overflow-hidden">
                                <div class="p-6">
                                    <div class="flex items-center mb-4">
                                        <svg class="w-8 h-8 mr-3 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <div>
                                            <h4 class="text-lg font-bold text-purple-800 dark:text-purple-300">🐍 Instalasi Python Bridge di PC Lokal</h4>
                                            <p class="text-sm text-purple-700 dark:text-purple-400">Panduan lengkap setup bridge service untuk komunikasi dengan hardware</p>
                                        </div>
                                    </div>

                                    <!-- Step 1: Install Python -->
                                    <details class="mb-4 bg-white dark:bg-gray-800 rounded-lg border border-purple-200 dark:border-purple-700" open>
                                        <summary class="cursor-pointer p-4 font-semibold text-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                                            📥 Step 1: Install Python 3.11+
                                        </summary>
                                        <div class="p-4 border-t border-purple-200 dark:border-purple-700 space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">1. Download Python:</p>
                                                <a href="https://www.python.org/downloads/" target="_blank" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                    </svg>
                                                    Download Python 3.11+
                                                </a>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">2. Saat instalasi, centang opsi:</p>
                                                <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-2">
                                                    <li><strong>✅ Add Python to PATH</strong> (PENTING!)</li>
                                                    <li>Install pip</li>
                                                    <li>Install for all users (optional)</li>
                                                </ul>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">3. Verifikasi instalasi:</p>
                                                <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                                                    <div>C:\> python --version</div>
                                                    <div class="text-gray-400"># Output: Python 3.11.x</div>
                                                    <div class="mt-2">C:\> pip --version</div>
                                                    <div class="text-gray-400"># Output: pip 23.x.x</div>
                                                </div>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Step 2: Create Project Directory -->
                                    <details class="mb-4 bg-white dark:bg-gray-800 rounded-lg border border-purple-200 dark:border-purple-700">
                                        <summary class="cursor-pointer p-4 font-semibold text-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                                            📁 Step 2: Buat Folder Project
                                        </summary>
                                        <div class="p-4 border-t border-purple-200 dark:border-purple-700 space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">Buat folder untuk Python Bridge:</p>
                                                <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                                                    <div>C:\> mkdir C:\BelSekolahBridge</div>
                                                    <div>C:\> cd C:\BelSekolahBridge</div>
                                                </div>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Step 3: Create Virtual Environment -->
                                    <details class="mb-4 bg-white dark:bg-gray-800 rounded-lg border border-purple-200 dark:border-purple-700">
                                        <summary class="cursor-pointer p-4 font-semibold text-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                                            🔧 Step 3: Setup Virtual Environment
                                        </summary>
                                        <div class="p-4 border-t border-purple-200 dark:border-purple-700 space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">1. Buat virtual environment:</p>
                                                <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                                                    <div>C:\BelSekolahBridge> python -m venv venv</div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">2. Aktifkan virtual environment:</p>
                                                <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                                                    <div>C:\BelSekolahBridge> venv\Scripts\activate</div>
                                                    <div class="text-gray-400"># Prompt akan berubah menjadi: (venv) C:\BelSekolahBridge></div>
                                                </div>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Step 4: Install Dependencies -->
                                    <details class="mb-4 bg-white dark:bg-gray-800 rounded-lg border border-purple-200 dark:border-purple-700">
                                        <summary class="cursor-pointer p-4 font-semibold text-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                                            📦 Step 4: Install Dependencies
                                        </summary>
                                        <div class="p-4 border-t border-purple-200 dark:border-purple-700 space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">Install library yang diperlukan:</p>
                                                <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                                                    <div>(venv) C:\BelSekolahBridge> pip install pyserial</div>
                                                    <div>(venv) C:\BelSekolahBridge> pip install minimalmodbus</div>
                                                    <div>(venv) C:\BelSekolahBridge> pip install requests</div>
                                                    <div>(venv) C:\BelSekolahBridge> pip install python-dotenv</div>
                                                </div>
                                            </div>
                                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                                                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                                    <strong>💡 Tip:</strong> Atau install semua sekaligus dengan perintah:
                                                </p>
                                                <div class="bg-gray-800 text-green-400 p-2 rounded font-mono text-xs mt-2">
                                                    pip install pyserial minimalmodbus requests python-dotenv
                                                </div>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Step 5: Create Bridge Script -->
                                    <details class="mb-4 bg-white dark:bg-gray-800 rounded-lg border border-purple-200 dark:border-purple-700">
                                        <summary class="cursor-pointer p-4 font-semibold text-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                                            📝 Step 5: Buat File Bridge Script
                                        </summary>
                                        <div class="p-4 border-t border-purple-200 dark:border-purple-700 space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">1. Buat file <code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">.env</code> dengan isi:</p>
                                                <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-xs overflow-x-auto">
                                                    <div>API_BASE_URL={{ url('/') }}</div>
                                                    <div>API_TOKEN=your_secret_token_here</div>
                                                    <div>POLL_INTERVAL_SECONDS=5</div>
                                                    <div>LOG_LEVEL=INFO</div>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                    ⚠️ Ganti <code>your_secret_token_here</code> dengan token yang sama di .env Laravel (HARDWARE_BRIDGE_API_TOKEN)
                                                </p>
                                            </div>
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">2. Download script Python Bridge dari repository:</p>
                                                <a href="https://github.com/your-repo/bell-system-bridge" target="_blank" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                                    </svg>
                                                    Download dari GitHub
                                                </a>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                    Atau buat file <code>bridge.py</code> sendiri sesuai dokumentasi
                                                </p>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Step 6: Check COM Port -->
                                    <details class="mb-4 bg-white dark:bg-gray-800 rounded-lg border border-purple-200 dark:border-purple-700">
                                        <summary class="cursor-pointer p-4 font-semibold text-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                                            🔌 Step 6: Cek COM Port USB-RS485
                                        </summary>
                                        <div class="p-4 border-t border-purple-200 dark:border-purple-700 space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">1. Colokkan USB-RS485 adapter ke PC</p>
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">2. Buka Device Manager:</p>
                                                <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 ml-2 space-y-1">
                                                    <li>Tekan <kbd class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">Win + X</kbd> → Device Manager</li>
                                                    <li>Expand "Ports (COM & LPT)"</li>
                                                    <li>Cari "USB Serial Port (COMx)" atau "CH340" atau "USB-SERIAL CH340"</li>
                                                    <li>Catat nomor COM Port (contoh: COM3, COM4, dst)</li>
                                                </ul>
                                            </div>
                                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                                                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                                    <strong>⚠️ Jika tidak muncul:</strong> Install driver CH340/CH341 USB-to-Serial
                                                </p>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Step 7: Run Bridge -->
                                    <details class="mb-4 bg-white dark:bg-gray-800 rounded-lg border border-purple-200 dark:border-purple-700">
                                        <summary class="cursor-pointer p-4 font-semibold text-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                                            ▶️ Step 7: Jalankan Bridge Service
                                        </summary>
                                        <div class="p-4 border-t border-purple-200 dark:border-purple-700 space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">Jalankan bridge:</p>
                                                <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                                                    <div>(venv) C:\BelSekolahBridge> python bridge.py</div>
                                                </div>
                                            </div>
                                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3">
                                                <p class="text-sm text-green-800 dark:text-green-300">
                                                    <strong>✅ Bridge berhasil jika muncul:</strong>
                                                </p>
                                                <div class="bg-gray-800 text-green-400 p-2 rounded font-mono text-xs mt-2">
                                                    <div>[INFO] Bridge started successfully</div>
                                                    <div>[INFO] Polling server every 5 seconds...</div>
                                                    <div>[INFO] Bridge Status: Online</div>
                                                </div>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Step 8: Setup Auto-Start -->
                                    <details class="bg-white dark:bg-gray-800 rounded-lg border border-purple-200 dark:border-purple-700">
                                        <summary class="cursor-pointer p-4 font-semibold text-purple-800 dark:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition">
                                            🔄 Step 8: Setup Auto-Start (Optional)
                                        </summary>
                                        <div class="p-4 border-t border-purple-200 dark:border-purple-700 space-y-3">
                                            <div class="bg-gray-50 dark:bg-gray-900 rounded p-3">
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buat Windows Service menggunakan NSSM:</p>
                                                <ol class="list-decimal list-inside text-sm text-gray-600 dark:text-gray-400 space-y-2 ml-2">
                                                    <li>Download NSSM dari <a href="https://nssm.cc/download" target="_blank" class="text-blue-600 hover:underline">nssm.cc</a></li>
                                                    <li>Extract dan copy nssm.exe ke <code>C:\BelSekolahBridge\</code></li>
                                                    <li>Buka CMD as Administrator, jalankan:
                                                        <div class="bg-gray-800 text-green-400 p-2 rounded font-mono text-xs mt-1">
                                                            <div>C:\BelSekolahBridge> nssm install BelSekolahBridge</div>
                                                        </div>
                                                    </li>
                                                    <li>Set konfigurasi:
                                                        <ul class="list-disc list-inside ml-4 mt-1">
                                                            <li>Path: <code>C:\BelSekolahBridge\venv\Scripts\python.exe</code></li>
                                                            <li>Startup directory: <code>C:\BelSekolahBridge</code></li>
                                                            <li>Arguments: <code>bridge.py</code></li>
                                                        </ul>
                                                    </li>
                                                    <li>Start service:
                                                        <div class="bg-gray-800 text-green-400 p-2 rounded font-mono text-xs mt-1">
                                                            <div>nssm start BelSekolahBridge</div>
                                                        </div>
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Command Types Reference -->
                                    <details class="mb-4 bg-white dark:bg-gray-800 rounded-lg border border-orange-200 dark:border-orange-700">
                                        <summary class="cursor-pointer p-4 font-semibold text-orange-800 dark:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/30 transition">
                                            📋 Command Types yang Harus Dihandle Python Bridge
                                        </summary>
                                        <div class="p-4 border-t border-orange-200 dark:border-orange-700 space-y-3">
                                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded p-3">
                                                <h6 class="font-semibold text-blue-800 dark:text-blue-300 mb-2">1. activate_speaker (Manual ON - Tanpa Durasi)</h6>
                                                <p class="text-sm text-blue-700 dark:text-blue-400 mb-2">
                                                    <strong>Kapan:</strong> User klik tombol room/HORN/CTRLROOM/ON ALL di UI
                                                </p>
                                                <p class="text-sm text-blue-700 dark:text-blue-400 mb-2">
                                                    <strong>Behavior:</strong> Nyalakan relay TANPA timeout, tetap aktif sampai deactivate manual
                                                </p>
                                                <div class="bg-gray-800 text-green-400 p-2 rounded font-mono text-xs mt-2">
                                                    <div>def handle_activate_speaker(payload):</div>
                                                    <div>    hardware_address = payload['hardware_address']</div>
                                                    <div>    activate_relay(hardware_address)  # Nyalakan relay</div>
                                                    <div>    # NO auto-off timer!</div>
                                                </div>
                                            </div>

                                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded p-3">
                                                <h6 class="font-semibold text-red-800 dark:text-red-300 mb-2">2. deactivate_speaker (Manual OFF)</h6>
                                                <p class="text-sm text-red-700 dark:text-red-400 mb-2">
                                                    <strong>Kapan:</strong> User klik tombol OFF ALL di UI
                                                </p>
                                                <p class="text-sm text-red-700 dark:text-red-400 mb-2">
                                                    <strong>Behavior:</strong> Matikan relay immediately
                                                </p>
                                                <div class="bg-gray-800 text-green-400 p-2 rounded font-mono text-xs mt-2">
                                                    <div>def handle_deactivate_speaker(payload):</div>
                                                    <div>    hardware_address = payload['hardware_address']</div>
                                                    <div>    deactivate_relay(hardware_address)  # Matikan relay</div>
                                                </div>
                                            </div>

                                            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded p-3">
                                                <h6 class="font-semibold text-purple-800 dark:text-purple-300 mb-2">3. test_speaker (Bell Schedule - DENGAN Durasi)</h6>
                                                <p class="text-sm text-purple-700 dark:text-purple-400 mb-2">
                                                    <strong>Kapan:</strong> Jadwal bel otomatis (cron)
                                                </p>
                                                <p class="text-sm text-purple-700 dark:text-purple-400 mb-2">
                                                    <strong>Behavior:</strong> Nyalakan relay DENGAN timeout, auto-off setelah durasi
                                                </p>
                                                <div class="bg-gray-800 text-green-400 p-2 rounded font-mono text-xs mt-2">
                                                    <div>def handle_test_speaker(payload):</div>
                                                    <div>    hardware_address = payload['hardware_address']</div>
                                                    <div>    duration = payload.get('duration_seconds', 5)</div>
                                                    <div>    activate_relay(hardware_address)</div>
                                                    <div>    time.sleep(duration)  # Auto-off setelah durasi</div>
                                                    <div>    deactivate_relay(hardware_address)</div>
                                                </div>
                                            </div>

                                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3">
                                                <h6 class="font-semibold text-green-800 dark:text-green-300 mb-2">4. activate_parent (Legacy - Backward Compatibility)</h6>
                                                <p class="text-sm text-green-700 dark:text-green-400 mb-2">
                                                    <strong>Kapan:</strong> Bell schedule untuk parent activation
                                                </p>
                                                <p class="text-sm text-green-700 dark:text-green-400 mb-2">
                                                    <strong>Behavior:</strong> Sama seperti activate_speaker
                                                </p>
                                            </div>

                                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                                                <h6 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2">5. stop_speaker (Bell Schedule Auto-OFF)</h6>
                                                <p class="text-sm text-yellow-700 dark:text-yellow-400 mb-2">
                                                    <strong>Kapan:</strong> Setelah jadwal bel selesai (auto-off)
                                                </p>
                                                <p class="text-sm text-yellow-700 dark:text-yellow-400 mb-2">
                                                    <strong>Behavior:</strong> Sama seperti deactivate_speaker
                                                </p>
                                            </div>

                                            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 rounded p-3">
                                                <h6 class="font-semibold text-indigo-800 dark:text-indigo-300 mb-2">6. play_audio (Play Audio File)</h6>
                                                <p class="text-sm text-indigo-700 dark:text-indigo-400 mb-2">
                                                    <strong>Kapan:</strong> Jadwal bel memutar audio
                                                </p>
                                                <p class="text-sm text-indigo-700 dark:text-indigo-400 mb-2">
                                                    <strong>Behavior:</strong> Play audio file via audio output
                                                </p>
                                                <div class="bg-gray-800 text-green-400 p-2 rounded font-mono text-xs mt-2">
                                                    <div>def handle_play_audio(payload):</div>
                                                    <div>    audio_file = payload['audio_file']</div>
                                                    <div>    play_sound(audio_file)  # Contoh: pygame.mixer</div>
                                                </div>
                                            </div>

                                            <div class="bg-gray-100 dark:bg-gray-700 rounded p-3 mt-3">
                                                <p class="text-xs text-gray-700 dark:text-gray-300">
                                                    <strong>💡 Catatan Penting:</strong>
                                                </p>
                                                <ul class="list-disc list-inside text-xs text-gray-600 dark:text-gray-400 space-y-1 ml-2 mt-1">
                                                    <li><strong>activate_speaker</strong> = TANPA timeout (untuk manual control mic/pengumuman)</li>
                                                    <li><strong>deactivate_speaker</strong> = OFF manual (user klik OFF ALL)</li>
                                                    <li><strong>test_speaker</strong> = DENGAN timeout (untuk bell schedule otomatis)</li>
                                                    <li>Parent-child sequence: Parent dulu (+0s), Child kemudian (+1-2s)</li>
                                                    <li>OFF sequence: Children dulu (+0s), Parents kemudian (+1s)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </details>

                                    <!-- Final Notes -->
                                    <div class="mt-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                                        <h5 class="font-semibold text-green-800 dark:text-green-300 mb-2">🎉 Selesai!</h5>
                                        <p class="text-sm text-green-700 dark:text-green-400 mb-2">
                                            Setelah bridge berjalan, status di dashboard akan berubah menjadi <strong>"Online"</strong>
                                        </p>
                                        <p class="text-sm text-green-700 dark:text-green-400">
                                            Bridge harus bisa handle 6 command types di atas untuk sistem berfungsi optimal. Lihat dokumentasi lengkap di COMMAND_TYPES_UPDATE.md
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Kelola Speaker Zones -->
            <div id="content-zones" class="tab-content hidden">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Kelola Speaker Zones</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Konfigurasi speaker zones untuk kontrol hardware. Setiap zone merepresentasikan satu channel output Modbus.
                            </p>
                        </div>

                        <!-- Parent Channels Section -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold mb-3 text-gray-800 dark:text-gray-200">Parent Channels</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                HORN dan CTRL ROOM adalah parent channel yang mengontrol grup speaker. Edit nama dan konfigurasi di sini.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($parentChannels as $parent)
                                    <div class="border border-blue-200 dark:border-blue-700 rounded-lg p-5 bg-blue-50 dark:bg-blue-900/10">
                                        <form action="{{ route('hardware.update-parent-channel') }}" method="POST" class="space-y-4">
                                            @csrf
                                            <input type="hidden" name="room_id" value="{{ $parent->id }}">

                                            <div class="flex items-center justify-between mb-3">
                                                <h5 class="text-sm font-bold text-blue-900 dark:text-blue-200">
                                                    {{ $parent->room_type }} ({{ $parent->group_name }})
                                                </h5>
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-200 text-blue-800 dark:bg-blue-800 dark:text-blue-200">
                                                    Parent
                                                </span>
                                            </div>

                                            <!-- Room Code -->
                                            <div>
                                                <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    Code <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" name="room_code" value="{{ $parent->room_code }}" readonly
                                                    class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed"
                                                    placeholder="Code..." maxlength="10" required>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Code tidak dapat diubah (system reserved)</p>
                                            </div>

                                            <!-- Room Name -->
                                            <div>
                                                <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    Display Name <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" name="room_name" value="{{ $parent->room_name }}"
                                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                                    placeholder="Display name..." maxlength="50" required>
                                            </div>

                                            <!-- Hardware Address -->
                                            <div>
                                                <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    Hardware Address
                                                </label>
                                                <select name="hardware_address"
                                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                                    <option value="">- No Hardware -</option>
                                                    @for($i = 1; $i <= 32; $i++)
                                                        @for($j = 1; $j <= 8; $j++)
                                                            @php $hwValue = "{$i}-{$j}"; @endphp
                                                            <option value="{{ $hwValue }}" {{ $parent->hardware_address == $hwValue ? 'selected' : '' }}>
                                                                {{ $hwValue }}
                                                            </option>
                                                        @endfor
                                                    @endfor
                                                </select>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hardware address format: 1-1 to 32-8</p>
                                            </div>

                                            <button type="submit"
                                                class="w-full inline-flex justify-center items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                                Update Parent Channel
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <hr class="my-8 border-gray-200 dark:border-gray-700">

                        <!-- Regular Speaker Zones -->
                        <div class="mb-4">
                            <h4 class="text-md font-semibold mb-3 text-gray-800 dark:text-gray-200">Speaker Zones (Channel 1-8)</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Konfigurasi individual speaker zones. Setiap zone merepresentasikan satu output channel Modbus (1-8).
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
                                        Belum ada speaker zone yang dikonfigurasi. Silakan run seeder.
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
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active state from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });

            // Show selected content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Add active state to selected button
            const activeButton = document.getElementById('tab-' + tabName);
            activeButton.classList.add('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
            activeButton.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        }

        /**
         * Test individual room
         */
        function testRoom(roomId, roomName, groupName) {
            if (!confirm(`Test room ${roomName} (${groupName})?\n\nSpeaker akan berbunyi selama 5 detik.`)) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("hardware.test-room") }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';

            const roomInput = document.createElement('input');
            roomInput.type = 'hidden';
            roomInput.name = 'room_id';
            roomInput.value = roomId;

            const durationInput = document.createElement('input');
            durationInput.type = 'hidden';
            durationInput.name = 'duration';
            durationInput.value = 5; // Default 5 seconds

            form.appendChild(csrfInput);
            form.appendChild(roomInput);
            form.appendChild(durationInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Modal functions for Edit Groups
        function openEditGroupModal() {
            document.getElementById('editGroupModal').classList.remove('hidden');
        }

        function closeEditGroupModal() {
            document.getElementById('editGroupModal').classList.add('hidden');
        }

        function saveGroupLabels() {
            const updates = {};
            const groups = ['GROUP 1', 'GROUP 2', 'GROUP 3', 'GROUP 4', 'GROUP 5', 'GROUP 6', 'CUSTOM 1', 'CUSTOM 2'];

            groups.forEach(group => {
                const inputId = 'group_label_' + group.replace(/ /g, '_');
                const input = document.getElementById(inputId);
                if (input) {
                    updates[group] = input.value.trim();
                }
            });

            // Close modal immediately
            closeEditGroupModal();

            // Submit form using fetch API instead of form.submit() to avoid navigation issues
            fetch('{{ route("hardware.update-group-labels") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    labels: JSON.stringify(updates)
                })
            })
            .then(response => {
                if (response.ok) {
                    // Redirect to hardware page
                    window.location.href = '/hardware';
                } else {
                    alert('Error updating group labels');
                    console.error('Server error:', response);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                console.error('Fetch error:', error);
            });
        }

        // Modal functions for Edit Rooms
        function openEditRoomModal() {
            document.getElementById('editRoomModal').classList.remove('hidden');
        }

        function closeEditRoomModal() {
            document.getElementById('editRoomModal').classList.add('hidden');
        }

        function saveRoomNames() {
            const inputs = document.querySelectorAll('[data-room-id]');
            const updates = [];

            inputs.forEach(input => {
                const roomId = input.getAttribute('data-room-id');
                const originalName = input.getAttribute('data-original-name');
                const newName = input.value.trim();

                if (newName && newName !== originalName) {
                    updates.push({
                        room_id: roomId,
                        room_name: newName
                    });
                }
            });

            if (updates.length === 0) {
                alert('Tidak ada perubahan untuk disimpan');
                return;
            }

            if (!confirm(`Simpan ${updates.length} perubahan nama room?`)) {
                return;
            }

            // Submit form with all updates
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("hardware.bulk-update-room-names") }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const updatesInput = document.createElement('input');
            updatesInput.type = 'hidden';
            updatesInput.name = 'updates';
            updatesInput.value = JSON.stringify(updates);
            form.appendChild(updatesInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Filter rooms in modal (table version)
        function filterRooms() {
            const searchInput = document.getElementById('roomSearchInput').value.toLowerCase();
            const groupFilter = document.getElementById('groupFilterSelect').value;
            const roomRows = document.querySelectorAll('.room-item');

            roomRows.forEach(row => {
                const roomCode = row.getAttribute('data-room-code').toLowerCase();
                const roomName = row.getAttribute('data-room-name').toLowerCase();
                const groupName = row.getAttribute('data-group-name');

                const matchesSearch = roomCode.includes(searchInput) || roomName.includes(searchInput);
                const matchesGroup = !groupFilter || groupName === groupFilter;

                if (matchesSearch && matchesGroup) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Highlight changed rooms
        function highlightChangedRoom(input) {
            // Detect which field type this is
            let originalValue = '';
            let currentValue = '';

            if (input.hasAttribute('data-original-code')) {
                originalValue = input.getAttribute('data-original-code');
                currentValue = input.value.trim();
            } else if (input.hasAttribute('data-original-name')) {
                originalValue = input.getAttribute('data-original-name');
                currentValue = input.value.trim();
            } else if (input.hasAttribute('data-original-group')) {
                originalValue = input.getAttribute('data-original-group');
                currentValue = input.value;
            } else if (input.hasAttribute('data-original-hw')) {
                originalValue = input.getAttribute('data-original-hw');
                currentValue = input.value;
            }

            if (currentValue !== originalValue) {
                input.classList.add('border-yellow-400', 'bg-yellow-50', 'dark:bg-yellow-900/20');
                input.classList.remove('border-gray-300', 'dark:border-gray-600');
            } else {
                input.classList.remove('border-yellow-400', 'bg-yellow-50', 'dark:bg-yellow-900/20');
                input.classList.add('border-gray-300', 'dark:border-gray-600');
            }

            updateChangedRoomsCount();
        }

        // Update changed rooms counter
        function updateChangedRoomsCount() {
            const inputs = document.querySelectorAll('[data-room-id]');
            let count = 0;

            inputs.forEach(input => {
                const originalName = input.getAttribute('data-original-name');
                const currentValue = input.value.trim();
                if (currentValue && currentValue !== originalName) {
                    count++;
                }
            });

            const counterElement = document.getElementById('changedRoomsCount');
            if (counterElement) {
                counterElement.textContent = count;
                counterElement.parentElement.className = count > 0
                    ? 'text-sm font-semibold text-yellow-600 dark:text-yellow-400'
                    : 'text-sm text-gray-600 dark:text-gray-400';
            }
        }

        // Validate room name (max 3 words)
        function validateRoomName(input) {
            const words = input.value.trim().split(/\s+/).filter(word => word.length > 0);
            if (words.length > 3) {
                input.value = words.slice(0, 3).join(' ');
                // Show warning
                input.classList.add('border-red-400', 'bg-red-50');
                setTimeout(() => {
                    input.classList.remove('border-red-400', 'bg-red-50');
                }, 1000);
            }
        }

        // Pagination variables
        let currentPageNum = 1;
        const itemsPerPage = 10;

        function updatePagination() {
            const allRows = document.querySelectorAll('.room-item');
            const totalItems = allRows.length;
            const totalPagesCalc = Math.ceil(totalItems / itemsPerPage);

            // Update display
            document.getElementById('totalPages').textContent = totalPagesCalc;
            document.getElementById('currentPage').textContent = currentPageNum;
            document.getElementById('totalRooms').textContent = totalItems;
            document.getElementById('showingStart').textContent = ((currentPageNum - 1) * itemsPerPage) + 1;
            document.getElementById('showingEnd').textContent = Math.min(currentPageNum * itemsPerPage, totalItems);

            // Show/hide rows based on current page
            allRows.forEach((row, index) => {
                const rowPage = Math.floor(index / itemsPerPage) + 1;
                if (rowPage === currentPageNum) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });

            // Update numbering
            const visibleRows = Array.from(allRows).filter((row, index) => {
                const rowPage = Math.floor(index / itemsPerPage) + 1;
                return rowPage === currentPageNum;
            });
            visibleRows.forEach((row, index) => {
                const numberCell = row.querySelector('.room-number');
                if (numberCell) {
                    numberCell.textContent = ((currentPageNum - 1) * itemsPerPage) + index + 1;
                }
            });

            // Update button states
            document.getElementById('prevPageBtn').disabled = currentPageNum === 1;
            document.getElementById('nextPageBtn').disabled = currentPageNum === totalPagesCalc;
        }

        function nextPage() {
            const totalItems = document.querySelectorAll('.room-item').length;
            const totalPagesCalc = Math.ceil(totalItems / itemsPerPage);
            if (currentPageNum < totalPagesCalc) {
                currentPageNum++;
                updatePagination();
            }
        }

        function previousPage() {
            if (currentPageNum > 1) {
                currentPageNum--;
                updatePagination();
            }
        }

        // Open modal and initialize pagination
        const originalOpenEditRoomModal = openEditRoomModal;
        openEditRoomModal = function() {
            originalOpenEditRoomModal();
            currentPageNum = 1;
            updatePagination();
        };

        // Save all room data (code, name, group, hardware)
        function saveRoomData() {
            const updates = [];

            // Get all inputs
            const codeInputs = document.querySelectorAll('.room-code-input');
            const nameInputs = document.querySelectorAll('.room-name-input');
            const groupSelects = document.querySelectorAll('.room-group-select');
            const zoneSelects = document.querySelectorAll('.room-zone-select');
            const hwSelects = document.querySelectorAll('.room-hw-select');

            codeInputs.forEach((input, index) => {
                const roomId = input.getAttribute('data-room-id');
                const originalCode = input.getAttribute('data-original-code');
                const originalName = nameInputs[index].getAttribute('data-original-name');
                const originalGroup = groupSelects[index].getAttribute('data-original-group');
                const originalZone = zoneSelects[index].getAttribute('data-original-zone');
                const originalHw = hwSelects[index].getAttribute('data-original-hw');

                const newCode = input.value.trim();
                const newName = nameInputs[index].value.trim();
                const newGroup = groupSelects[index].value;
                const newZone = zoneSelects[index].value;
                const newHw = hwSelects[index].value;

                // Check if anything changed
                if (newCode !== originalCode || newName !== originalName ||
                    newGroup !== originalGroup || newZone !== originalZone || newHw !== originalHw) {
                    updates.push({
                        room_id: roomId,
                        room_code: newCode,
                        room_name: newName,
                        group_name: newGroup,
                        speaker_zone_id: newZone,
                        hardware_address: newHw
                    });
                }
            });

            if (updates.length === 0) {
                alert('Tidak ada perubahan untuk disimpan');
                return;
            }

            if (!confirm(`Simpan ${updates.length} perubahan data room?`)) {
                return;
            }

            // Submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("hardware.bulk-update-rooms") }}';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const updatesInput = document.createElement('input');
            updatesInput.type = 'hidden';
            updatesInput.name = 'updates';
            updatesInput.value = JSON.stringify(updates);
            form.appendChild(updatesInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const groupModal = document.getElementById('editGroupModal');
            const roomModal = document.getElementById('editRoomModal');

            if (event.target === groupModal) {
                closeEditGroupModal();
            }
            if (event.target === roomModal) {
                closeEditRoomModal();
            }
        }

        // Audio Control Functions
        // Track active buttons state
        let activeButtons = new Set();

        // Group to Room IDs mapping
        const groupToRoomButtons = {
            @foreach($allRooms->groupBy('group_name') as $groupName => $groupRooms)
                '{{ $groupName }}': [
                    @foreach($groupRooms as $room)
                        @if($room->speakerZone)
                            'btn-room-{{ $room->id }}',
                        @endif
                    @endforeach
                ],
            @endforeach
        };

        function toggleLightEffect(buttonId, forceState = null) {
            // Get dot element (convert btn-horn to dot-horn)
            const dotId = buttonId.replace('btn-', 'dot-');
            const dot = document.getElementById(dotId);
            if (!dot) return false;

            // Determine if button should be active
            const shouldBeActive = forceState !== null ? forceState : !activeButtons.has(buttonId);

            if (shouldBeActive) {
                // Activate pulse dot
                activeButtons.add(buttonId);
                dot.classList.add('active');
            } else {
                // Deactivate pulse dot
                activeButtons.delete(buttonId);
                dot.classList.remove('active');
            }

            return shouldBeActive;
        }

        function deactivateAllButtons() {
            // Turn off all active buttons
            activeButtons.forEach(buttonId => {
                const dotId = buttonId.replace('btn-', 'dot-');
                const dot = document.getElementById(dotId);
                if (dot) {
                    dot.classList.remove('active');
                }
            });
            activeButtons.clear();
        }

        function triggerParentChannel(roomType) {
            const buttonId = roomType === 'HORN' ? 'btn-horn' : 'btn-ctrlroom';

            // Toggle light effect
            const isActivating = toggleLightEffect(buttonId);

            if (isActivating) {
                // Send request to activate hardware
                fetch('{{ route("hardware.test-type") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        room_type: roomType,
                        duration: 5
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log(roomType + ' activated:', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert button state on error
                    toggleLightEffect(buttonId, false);
                });
            } else {
                // Deactivating - send stop command for this specific channel
                console.log(roomType + ' deactivated');
                // You could add a specific stop endpoint here if needed
            }
        }

        function triggerOnAll() {
            // Activate HORN and CTRL ROOM buttons
            toggleLightEffect('btn-horn', true);
            toggleLightEffect('btn-ctrlroom', true);
            toggleLightEffect('btn-on-all', true);

            // Activate all room buttons in the grid
            const allRoomButtons = document.querySelectorAll('[id^="btn-room-"]');
            allRoomButtons.forEach(button => {
                toggleLightEffect(button.id, true);
            });

            // Send request to activate all
            fetch('{{ route("hardware.test-all-zones") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    duration: 5
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('All zones activated:', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function triggerOffAll() {
            // Deactivate HORN and CTRL ROOM buttons
            toggleLightEffect('btn-horn', false);
            toggleLightEffect('btn-ctrlroom', false);

            // Deactivate all room buttons in the grid
            const allRoomButtons = document.querySelectorAll('[id^="btn-room-"]');
            allRoomButtons.forEach(button => {
                toggleLightEffect(button.id, false);
            });

            // Add temporary effect to OFF ALL button
            toggleLightEffect('btn-off-all', true);
            setTimeout(() => {
                toggleLightEffect('btn-off-all', false);
            }, 2000);

            // Send request to turn off all
            fetch('{{ route("hardware.off-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('All zones turned off:', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Group Controls Functions
        function triggerGroup(groupName) {
            const groupId = 'btn-group-' + groupName.toLowerCase().replace(/ /g, '-');

            // Toggle light effect for group button
            const isActivating = toggleLightEffect(groupId);

            // Get all room buttons in this group
            const roomButtons = groupToRoomButtons[groupName] || [];

            if (isActivating) {
                // Activate all room buttons in this group
                roomButtons.forEach(roomBtnId => {
                    toggleLightEffect(roomBtnId, true);
                });

                // Send request to activate group
                fetch('{{ route("hardware.test-group") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        group_name: groupName,
                        duration: 5
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log(groupName + ' activated with ' + roomButtons.length + ' rooms:', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert button state on error
                    toggleLightEffect(groupId, false);
                    roomButtons.forEach(roomBtnId => {
                        toggleLightEffect(roomBtnId, false);
                    });
                });
            } else {
                // Deactivate all room buttons in this group
                roomButtons.forEach(roomBtnId => {
                    toggleLightEffect(roomBtnId, false);
                });

                console.log(groupName + ' deactivated with ' + roomButtons.length + ' rooms');
            }
        }

        // Room Control Functions
        function testRoom(roomId, roomName, groupName) {
            const buttonId = 'btn-room-' + roomId;

            // Toggle light effect
            const isActivating = toggleLightEffect(buttonId);

            // Update group button status based on rooms in that group
            updateGroupButtonStatus(groupName);

            if (isActivating) {
                // Send request to test room
                fetch('{{ route("hardware.test-room") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        room_id: roomId,
                        duration: 5
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Room ' + roomName + ' activated:', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert button state on error
                    toggleLightEffect(buttonId, false);
                    updateGroupButtonStatus(groupName);
                });
            } else {
                // Deactivating
                console.log('Room ' + roomName + ' deactivated');
            }
        }

        // Helper function to update group button based on its rooms
        function updateGroupButtonStatus(groupName) {
            const groupId = 'btn-group-' + groupName.toLowerCase().replace(/ /g, '-');
            const roomButtons = groupToRoomButtons[groupName] || [];

            if (roomButtons.length === 0) return;

            // Check if all rooms in this group are active
            const allRoomsActive = roomButtons.every(roomBtnId => activeButtons.has(roomBtnId));
            const someRoomsActive = roomButtons.some(roomBtnId => activeButtons.has(roomBtnId));

            if (allRoomsActive) {
                // All rooms active, activate group button
                toggleLightEffect(groupId, true);
            } else {
                // Not all rooms active, deactivate group button
                toggleLightEffect(groupId, false);
            }
        }
    </script>

    <style>
        /* Pulse dot - hidden by default */
        .pulse-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 10px;
            height: 10px;
            background-color: #10b981; /* Green */
            border-radius: 50%;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
        }

        /* Active pulse dot */
        .pulse-dot.active {
            opacity: 1;
            transform: scale(1);
            animation: pulse-dot 1.5s ease-in-out infinite;
            box-shadow:
                0 0 0 0 rgba(16, 185, 129, 0.7),
                0 0 10px rgba(16, 185, 129, 0.5);
        }

        /* Pulse animation */
        @keyframes pulse-dot {
            0% {
                box-shadow:
                    0 0 0 0 rgba(16, 185, 129, 0.7),
                    0 0 10px rgba(16, 185, 129, 0.5);
            }
            50% {
                box-shadow:
                    0 0 0 5px rgba(16, 185, 129, 0),
                    0 0 15px rgba(16, 185, 129, 0.8);
            }
            100% {
                box-shadow:
                    0 0 0 0 rgba(16, 185, 129, 0),
                    0 0 10px rgba(16, 185, 129, 0.5);
            }
        }

        /* Button positioning for dot */
        #btn-horn, #btn-ctrlroom, #btn-on-all, #btn-off-all {
            position: relative;
            overflow: visible;
        }
    </style>
</x-app-layout>
