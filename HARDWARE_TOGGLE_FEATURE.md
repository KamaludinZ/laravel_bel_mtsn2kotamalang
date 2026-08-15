# 🔘 Hardware Integration Toggle Feature

## 📋 Requirement

Aplikasi harus bisa:
1. ✅ Berfungsi normal **TANPA** hardware integration (seperti sebelumnya)
2. ✅ Enable/disable hardware integration via **Settings**
3. ✅ Jika disabled: Menu hardware tidak muncul, tidak queue command ke hardware
4. ✅ Jika enabled: Full hardware integration aktif

---

## 🔧 IMPLEMENTASI

### **Step 1: Tambahkan Setting di Database**

```bash
php artisan tinker
```

```php
\App\Models\Setting::updateOrCreate(
    ['key' => 'hardware_integration_enabled'],
    ['value' => 'false']  // Default: OFF
);

\App\Models\Setting::updateOrCreate(
    ['key' => 'hardware_integration_mode'],
    ['value' => 'auto']  // auto, manual_only
);
exit
```

### **Step 2: Update SettingController**

**File:** `app/Http/Controllers/SettingController.php`

Tambahkan di method `index()`, sebelum `return view(...)`:

```php
$hardwareEnabled = \App\Models\Setting::where('key', 'hardware_integration_enabled')->value('value') === 'true';
```

Dan update compact:

```php
return view('settings.index', compact(..., 'hardwareEnabled'));
```

### **Step 3: Update Settings View**

**File:** `resources/views/settings/index.blade.php`

Tambahkan section baru (setelah section logo/nama):

```blade
<!-- Hardware Integration Toggle -->
<div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
        </svg>
        Integrasi Hardware
    </h3>

    <form action="{{ route('settings.toggle-hardware') }}" method="POST" class="space-y-4">
        @csrf

        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div>
                <label class="text-sm font-medium text-gray-900">
                    Aktifkan Integrasi Hardware (Modbus/Speaker Fisik)
                </label>
                <p class="text-xs text-gray-600 mt-1">
                    Jika diaktifkan, sistem akan mengontrol speaker fisik via Modbus relay
                </p>
            </div>

            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="hardware_enabled" value="1"
                       {{ $hardwareEnabled ? 'checked' : '' }}
                       class="sr-only peer"
                       onchange="this.form.submit()">
                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
        </div>

        @if($hardwareEnabled)
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">Hardware Integration Aktif</p>
                    <p class="text-xs text-blue-700 mt-1">
                        Menu Hardware Control dapat diakses. Pastikan Python Bridge Service running di PC sekolah.
                    </p>
                    <a href="{{ route('hardware.index') }}" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 mt-2 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka Hardware Control Dashboard
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-700">Hardware Integration Nonaktif</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Audio hanya akan diputar di browser (seperti aplikasi original). Speaker fisik tidak akan bunyi.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </form>
</div>
```

### **Step 4: Tambahkan Route untuk Toggle**

**File:** `routes/web.php`

Tambahkan di dalam `Route::middleware('auth')->group()`, di section Settings:

```php
Route::post('settings/toggle-hardware', [SettingController::class, 'toggleHardware'])->name('settings.toggle-hardware');
```

### **Step 5: Tambahkan Method di SettingController**

**File:** `app/Http/Controllers/SettingController.php`

Tambahkan method baru:

```php
/**
 * Toggle hardware integration
 */
public function toggleHardware(Request $request)
{
    $enabled = $request->has('hardware_enabled') && $request->hardware_enabled == '1';

    \App\Models\Setting::updateOrCreate(
        ['key' => 'hardware_integration_enabled'],
        ['value' => $enabled ? 'true' : 'false']
    );

    $message = $enabled
        ? 'Hardware integration diaktifkan. Speaker fisik akan ikut bunyi.'
        : 'Hardware integration dinonaktifkan. Audio hanya di browser.';

    return redirect()->back()->with('success', $message);
}
```

### **Step 6: Update Navigation Menu**

**File:** `resources/views/layouts/app.blade.php` atau navigation component

Wrap menu Hardware dengan check setting:

```blade
@php
    $hardwareEnabled = \App\Models\Setting::where('key', 'hardware_integration_enabled')->value('value') === 'true';
@endphp

@if($hardwareEnabled)
    <a href="{{ route('hardware.index') }}"
       class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('hardware.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
        </svg>
        <span class="font-medium">Hardware Control</span>
    </a>
@endif
```

### **Step 7: Update PublicController untuk Conditional Trigger**

**File:** `app/Http/Controllers/PublicController.php`

Modifikasi method `triggerHardware()`:

```php
private function triggerHardware($schedule, $duration = null)
{
    try {
        // ===== CHECK IF HARDWARE INTEGRATION IS ENABLED =====
        $hardwareEnabled = \App\Models\Setting::where('key', 'hardware_integration_enabled')
            ->value('value') === 'true';

        if (!$hardwareEnabled) {
            \Log::info('Hardware integration disabled, skipping hardware trigger');
            return; // Skip hardware trigger
        }

        // Get all enabled zones
        $zones = SpeakerZone::enabled()->pluck('modbus_channel')->toArray();

        if (empty($zones)) {
            \Log::warning('No enabled speaker zones found for hardware trigger');
            return;
        }

        // ... rest of the code tetap sama ...
    } catch (\Exception $e) {
        \Log::error('Error queueing hardware trigger: ' . $e->getMessage());
    }
}
```

### **Step 8: Update Frontend JavaScript (Optional - Visual Indicator)**

**File:** `resources/views/public/index.blade.php`

Tambahkan indicator hardware status (opsional):

```javascript
// Di bagian init() function
@php
    $hardwareEnabled = \App\Models\Setting::where('key', 'hardware_integration_enabled')->value('value') === 'true';
@endphp

const HARDWARE_ENABLED = {{ $hardwareEnabled ? 'true' : 'false' }};

// Update queueHardwareTrigger
async queueHardwareTrigger(schedule) {
    if (!HARDWARE_ENABLED) {
        console.log('ℹ️ Hardware integration disabled - browser audio only');
        return;
    }

    // ... existing code ...
}
```

---

## 🧪 TESTING

### Test 1: Default State (Hardware OFF)

```bash
php artisan tinker
>>> \App\Models\Setting::where('key', 'hardware_integration_enabled')->value('value')
# Should return: "false"
```

1. Login ke aplikasi
2. Cek navigation - Menu "Hardware Control" **TIDAK muncul**
3. Buat jadwal bel dan putar
4. Check logs - Harus ada: `Hardware integration disabled, skipping hardware trigger`
5. Audio hanya play di browser ✅

### Test 2: Enable Hardware

1. Go to Settings
2. Toggle "Aktifkan Integrasi Hardware" ke ON
3. Menu "Hardware Control" **MUNCUL** di navigation
4. Buat jadwal bel lagi
5. Check database:

```bash
php artisan tinker
>>> \App\Models\HardwareCommandQueue::count()
# Should increase when bell is triggered
```

### Test 3: Toggle Back to OFF

1. Settings → Toggle hardware OFF
2. Menu Hardware **HILANG**
3. Create schedule → Check logs
4. No hardware command queued ✅

---

## 📊 BEHAVIOR SUMMARY

| Setting | Menu Hardware | Queue Command | Speaker Fisik | Audio Browser |
|---------|--------------|---------------|---------------|---------------|
| **OFF** | ❌ Hidden | ❌ No | ❌ Mati | ✅ **Bunyi** |
| **ON**  | ✅ Visible | ✅ Yes | ✅ **Bunyi** | ✅ **Bunyi** |

---

## 🎯 BENEFITS

✅ **Backward Compatible** - Aplikasi tetap berfungsi normal tanpa hardware
✅ **Flexible** - Admin bisa enable/disable kapan saja
✅ **Safe** - Jika hardware error, bisa langsung dimatikan
✅ **Clean UI** - Menu tidak mengganggu jika fitur tidak digunakan
✅ **Easy Migration** - Gradual adoption, tidak harus langsung pakai hardware

---

## 📝 FILES TO MODIFY

```
1. Database (via tinker) - Add settings
2. app/Http/Controllers/SettingController.php - Add toggleHardware()
3. app/Http/Controllers/PublicController.php - Add hardware check
4. resources/views/settings/index.blade.php - Add toggle UI
5. resources/views/layouts/app.blade.php - Conditional menu
6. routes/web.php - Add toggle route
```

---

Apakah Anda mau saya implementasikan semua perubahan ini sekarang?
