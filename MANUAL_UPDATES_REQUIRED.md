# 📝 Manual Updates Required

Berikut adalah file-file yang perlu Anda edit secara manual untuk menyelesaikan integrasi hardware.

---

## 1. **routes/web.php**

**Lokasi:** `C:\laravel_bel_mtsn2kotamalang\routes\web.php`

**TAMBAHKAN** di dalam `Route::middleware('auth')->group(function () {`:

```php
// Hardware Management Routes (tambahkan SEBELUM closing brace dari middleware auth)
Route::prefix('hardware')->name('hardware.')->group(function () {
    Route::get('/', [HardwareController::class, 'index'])->name('index');
    Route::post('/test-speaker', [HardwareController::class, 'testSpeaker'])->name('test-speaker');
    Route::post('/test-all-zones', [HardwareController::class, 'testAllZones'])->name('test-all-zones');
    Route::post('/update-config', [HardwareController::class, 'updateConfig'])->name('update-config');
    Route::get('/logs', [HardwareController::class, 'logs'])->name('logs');
    Route::post('/logs/clear', [HardwareController::class, 'clearOldLogs'])->name('logs.clear');
    Route::get('/zones', [HardwareController::class, 'zones'])->name('zones');
    Route::patch('/zones/{zone}', [HardwareController::class, 'updateZone'])->name('zones.update');
});
```

**TAMBAHKAN** import di bagian atas file:

```php
use App\Http\Controllers\HardwareController;
```

**TAMBAHKAN** route untuk queue hardware trigger (di luar middleware auth, setelah route public):

```php
// API untuk queue hardware trigger (dipanggil dari JavaScript)
Route::post('/api/queue-hardware-trigger', [PublicController::class, 'queueHardwareTrigger'])->name('api.queue-hardware-trigger');
```

---

## 2. **app/Http/Controllers/PublicController.php**

**Lokasi:** `C:\laravel_bel_mtsn2kotamalang\app\Http\Controllers\PublicController.php`

### 2.1 **TAMBAHKAN** import di bagian atas:

```php
use App\Models\HardwareCommandQueue;
use App\Models\SpeakerZone;
```

### 2.2 **TAMBAHKAN** method baru (di akhir class, sebelum closing brace):

```php
/**
 * Queue hardware trigger from frontend
 */
public function queueHardwareTrigger(Request $request)
{
    $validated = $request->validate([
        'schedule_id' => 'required|uuid|exists:bell_schedules,id',
        'audio_duration' => 'nullable|integer',
    ]);

    $schedule = BellSchedule::with('audioLibrary')->findOrFail($validated['schedule_id']);
    $this->triggerHardware($schedule, $validated['audio_duration'] ?? null);

    return response()->json(['success' => true]);
}

/**
 * Queue hardware trigger command
 */
private function triggerHardware($schedule, $duration = null)
{
    try {
        // Get all enabled zones
        $zones = SpeakerZone::enabled()->pluck('modbus_channel')->toArray();

        if (empty($zones)) {
            \Log::warning('No enabled speaker zones found for hardware trigger');
            return;
        }

        // Calculate duration from audio or use default
        $audioDuration = $schedule->audioLibrary->duration ?? 180; // 3 minutes default
        $triggerDuration = $duration ?? $audioDuration;

        // Create command for bridge
        HardwareCommandQueue::create([
            'command_type' => 'trigger_bell',
            'payload' => [
                'zones' => $zones,
                'duration_seconds' => (int) $triggerDuration,
                'schedule_id' => $schedule->id,
                'audio_id' => $schedule->audioLibrary->id,
                'audio_title' => $schedule->audioLibrary->title,
            ],
            'status' => 'pending',
            'scheduled_at' => now(),
            'expires_at' => now()->addMinutes(10), // Expire after 10 minutes
        ]);

        \Log::info("Hardware trigger queued for schedule {$schedule->id}, zones: " . implode(',', $zones));

    } catch (\Exception $e) {
        \Log::error('Error queueing hardware trigger: ' . $e->getMessage());
    }
}
```

---

## 3. **resources/views/public/index.blade.php**

**Lokasi:** `C:\laravel_bel_mtsn2kotamalang\resources\views\public\index.blade.php`

### 3.1 **TAMBAHKAN** CSRF token meta tag di bagian `<head>` (jika belum ada):

Cari section `<head>` di layout, atau tambahkan di file ini:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 3.2 **MODIFIKASI** function `playAudio(schedule)` di dalam `<script>`:

**CARI** function ini (sekitar baris 493):

```javascript
playAudio(schedule) {
    this.currentPlayingSchedule = schedule;
    this.currentSchedulePlayingId = schedule.id;
    this.nowPlayingTitle = schedule.audio.title;
    const audio = this.$refs.audioPlayer;
    audio.src = schedule.audio.file_url;
```

**GANTI** dengan:

```javascript
playAudio(schedule) {
    // ===== TRIGGER HARDWARE FIRST =====
    this.queueHardwareTrigger(schedule);

    // Then play browser audio as fallback
    this.currentPlayingSchedule = schedule;
    this.currentSchedulePlayingId = schedule.id;
    this.nowPlayingTitle = schedule.audio.title;
    const audio = this.$refs.audioPlayer;
    audio.src = schedule.audio.file_url;
```

### 3.3 **TAMBAHKAN** method baru `queueHardwareTrigger` di dalam object `bellSystem()`:

**CARI** bagian akhir dari function `bellSystem()` (sekitar baris 709, sebelum closing brace `}`):

**TAMBAHKAN** method ini:

```javascript
,

// Queue hardware trigger via API
async queueHardwareTrigger(schedule) {
    if (!schedule || !schedule.audio) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');

        const response = await fetch('/api/queue-hardware-trigger', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
            },
            body: JSON.stringify({
                schedule_id: schedule.id,
                audio_duration: schedule.audio.duration || 180
            })
        });

        if (response.ok) {
            console.log('✓ Hardware trigger queued successfully');
        } else {
            console.warn('✗ Failed to queue hardware trigger:', response.status);
        }
    } catch (error) {
        console.error('✗ Error queueing hardware trigger:', error);
        // Don't block browser audio playback
    }
}
```

**PERHATIAN:** Pastikan ada koma (`,`) setelah method sebelumnya!

---

## 4. **.env**

**Lokasi:** `C:\laravel_bel_mtsn2kotamalang\.env`

**TAMBAHKAN** di bagian bawah:

```env
# Hardware Bridge API Token
HARDWARE_BRIDGE_API_TOKEN=your-secret-token-here-min-32-characters-long
```

**Generate token:**

```bash
php artisan tinker
```

Lalu jalankan:

```php
echo bin2hex(random_bytes(32));
exit
```

Copy hasil output, paste ke `.env` menggantikan `your-secret-token-here-min-32-characters-long`

---

## 5. **database/seeders/HardwareSeeder.php**

**BUAT FILE BARU:**

```bash
php artisan make:seeder HardwareSeeder
```

**Edit file:** `database/seeders/HardwareSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\HardwareConfig;
use App\Models\SpeakerZone;
use Illuminate\Database\Seeder;

class HardwareSeeder extends Seeder
{
    public function run(): void
    {
        // Create default hardware config
        HardwareConfig::updateOrCreate(
            ['config_key' => 'primary_device'],
            [
                'device_type' => 'modbus_rs485',
                'connection_type' => 'usb',
                'com_port' => 'COM3', // Sesuaikan dengan COM port Anda
                'baud_rate' => 9600,
                'data_bits' => 8,
                'parity' => 'N',
                'stop_bits' => 1,
                'modbus_address' => 1,
                'is_enabled' => true,
                'auto_reconnect' => true,
                'timeout_ms' => 1000,
            ]
        );

        // Create speaker zones
        $zones = [
            ['name' => 'Halaman Sekolah', 'modbus_channel' => 1, 'description' => 'Speaker area halaman utama'],
            ['name' => 'Ruang Kelas Lantai 1', 'modbus_channel' => 2, 'description' => 'Speaker kelas lt 1'],
            ['name' => 'Ruang Kelas Lantai 2', 'modbus_channel' => 3, 'description' => 'Speaker kelas lt 2'],
            ['name' => 'Masjid', 'modbus_channel' => 4, 'description' => 'Speaker masjid sekolah'],
            ['name' => 'Kantor Guru', 'modbus_channel' => 5, 'description' => 'Speaker kantor guru'],
            ['name' => 'Perpustakaan', 'modbus_channel' => 6, 'description' => 'Speaker perpustakaan'],
            ['name' => 'Laboratorium', 'modbus_channel' => 7, 'description' => 'Speaker lab komputer/IPA'],
            ['name' => 'Reserved', 'modbus_channel' => 8, 'description' => 'Channel cadangan', 'is_enabled' => false],
        ];

        foreach ($zones as $index => $zone) {
            SpeakerZone::updateOrCreate(
                ['modbus_channel' => $zone['modbus_channel']],
                array_merge($zone, [
                    'sort_order' => $index + 1,
                    'default_duration_seconds' => 180, // 3 menit
                    'volume_level' => 100,
                    'is_enabled' => $zone['is_enabled'] ?? true,
                ])
            );
        }

        $this->command->info('Hardware configuration seeded successfully!');
    }
}
```

---

## 6. **Run Migrations & Seeders**

Setelah semua file di-update, jalankan:

```bash
# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed --class=HardwareSeeder

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

## 7. **Verifikasi**

### Cek routes berhasil dibuat:

```bash
php artisan route:list | findstr hardware
```

Harus muncul:

```
GET|HEAD  hardware ..................................................... hardware.index › HardwareController@index
POST      hardware/test-speaker ............................... hardware.test-speaker › HardwareController@testSpeaker
GET|HEAD  api/hardware/pending-commands ........................ HardwareApiController@getPendingCommands
POST      api/hardware/report-result ............................ HardwareApiController@reportResult
...
```

### Cek database:

```bash
php artisan tinker
```

```php
\App\Models\HardwareConfig::count(); // Harus return 1
\App\Models\SpeakerZone::count(); // Harus return 8
exit
```

---

## 8. **Test Access**

1. Login ke aplikasi
2. Akses: `http://localhost:8000/hardware`
3. Jika error "View not found", itu normal karena view belum dibuat
4. Test API endpoint:

```bash
curl http://localhost:8000/api/hardware/pending-commands ^
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Harus return JSON dengan `success: true`

---

## ✅ **CHECKLIST**

- [ ] Edit `routes/web.php` - Tambahkan hardware routes
- [ ] Edit `routes/web.php` - Tambahkan API route queue-hardware-trigger
- [ ] Edit `PublicController.php` - Tambahkan import
- [ ] Edit `PublicController.php` - Tambahkan method queueHardwareTrigger & triggerHardware
- [ ] Edit `public/index.blade.php` - Tambahkan CSRF token meta tag
- [ ] Edit `public/index.blade.php` - Modifikasi playAudio()
- [ ] Edit `public/index.blade.php` - Tambahkan queueHardwareTrigger() method
- [ ] Edit `.env` - Tambahkan HARDWARE_BRIDGE_API_TOKEN
- [ ] Buat `HardwareSeeder.php`
- [ ] Run `php artisan migrate`
- [ ] Run `php artisan db:seed --class=HardwareSeeder`
- [ ] Verifikasi routes & database
- [ ] Install Python Bridge di PC sekolah (lihat HARDWARE_INTEGRATION_GUIDE.md)
- [ ] Test end-to-end!

---

**Setelah checklist selesai, sistem siap digunakan!** 🎉

Lanjut ke `HARDWARE_INTEGRATION_GUIDE.md` untuk instalasi Python Bridge.
