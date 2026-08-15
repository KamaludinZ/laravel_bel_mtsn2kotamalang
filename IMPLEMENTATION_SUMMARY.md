# 🚀 Implementation Summary - Hardware Integration

## ✅ SUDAH SELESAI

### 1. **Database Migrations** ✓
- `hardware_command_queue` - Queue untuk command dari VPS ke Bridge
- `hardware_configs` - Konfigurasi perangkat Modbus
- `speaker_zones` - Mapping zone speaker
- `hardware_logs` - Log aktivitas hardware

### 2. **Models** ✓
- `HardwareCommandQueue` - Dengan method helper (markAsCompleted, markAsFailed, dll)
- `HardwareConfig` - Dengan method isOnline(), updateStatus()
- `SpeakerZone` - Dengan scope enabled(), ordered()
- `HardwareLog` - Dengan relasi ke command_queue dan speaker_zone

### 3. **API Controller** ✓
- `Api/HardwareApiController` - **PENTING!** Ini endpoint untuk Python Bridge
  - `GET /api/hardware/pending-commands` - Bridge poll command dari sini
  - `POST /api/hardware/report-result` - Bridge lapor hasil eksekusi
  - `GET /api/hardware/config` - Bridge ambil konfigurasi
  - `POST /api/hardware/heartbeat` - Bridge ping untuk status online

### 4. **Python Bridge Service** ✓
- Lengkap dengan kode siap pakai di `HARDWARE_INTEGRATION_GUIDE.md`
- Auto-reconnect Modbus
- Polling setiap 5 detik
- Error handling & logging
- Windows service installer

---

## ⏳ YANG PERLU DILENGKAPI

### 5. **HardwareController (Admin UI)** - OPSIONAL

File ini untuk admin manage hardware via web UI. Jika Anda lebih suka konfigurasi manual via database, file ini bisa diskip.

**Jika perlu, buat file:** `app/Http/Controllers/HardwareController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\HardwareCommandQueue;
use App\Models\HardwareConfig;
use App\Models\HardwareLog;
use App\Models\SpeakerZone;
use Illuminate\Http\Request;

class HardwareController extends Controller
{
    public function index()
    {
        $config = HardwareConfig::primary();
        $zones = SpeakerZone::ordered()->get();
        $recentLogs = HardwareLog::with(['commandQueue', 'speakerZone'])
            ->recent(20)
            ->get();

        $stats = [
            'pending_commands' => HardwareCommandQueue::pending()->count(),
            'today_executions' => HardwareLog::today()->count(),
            'today_success' => HardwareLog::today()->byStatus('success')->count(),
            'today_failed' => HardwareLog::today()->byStatus('failed')->count(),
        ];

        return view('hardware.index', compact('config', 'zones', 'recentLogs', 'stats'));
    }

    public function testSpeaker(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|uuid|exists:speaker_zones,id',
            'duration' => 'nullable|integer|min:1|max:60',
        ]);

        $zone = SpeakerZone::findOrFail($validated['zone_id']);
        $duration = $validated['duration'] ?? 5; // Default 5 seconds

        // Create command
        HardwareCommandQueue::create([
            'command_type' => 'test_speaker',
            'payload' => [
                'zone' => $zone->modbus_channel,
                'zone_id' => $zone->id,
                'duration_seconds' => $duration,
            ],
            'status' => 'pending',
            'scheduled_at' => now(),
            'expires_at' => now()->addMinutes(5), // Expire if not executed in 5 minutes
        ]);

        return redirect()->back()->with('success', "Test speaker {$zone->name} selama {$duration} detik telah dijadwalkan");
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'com_port' => 'required|string',
            'baud_rate' => 'required|integer',
            'modbus_address' => 'required|integer|min:1|max:247',
        ]);

        $config = HardwareConfig::primary();

        if (!$config) {
            $config = HardwareConfig::create(array_merge($validated, [
                'config_key' => 'primary_device',
            ]));
        } else {
            $config->update($validated);
        }

        return redirect()->back()->with('success', 'Konfigurasi hardware berhasil diupdate');
    }

    public function logs(Request $request)
    {
        $query = HardwareLog::with(['commandQueue', 'speakerZone'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(50);

        return view('hardware.logs', compact('logs'));
    }

    public function zones()
    {
        $zones = SpeakerZone::ordered()->get();
        return view('hardware.zones', compact('zones'));
    }

    public function updateZone(Request $request, SpeakerZone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_enabled' => 'boolean',
            'default_duration_seconds' => 'required|integer|min:1|max:3600',
        ]);

        $zone->update($validated);

        return redirect()->back()->with('success', "Zone {$zone->name} berhasil diupdate");
    }
}
```

### 6. **Routes** - **WAJIB!**

**Edit file:** `routes/web.php`

Tambahkan di bagian `Route::middleware('auth')->group()`:

```php
// Hardware Management Routes
Route::prefix('hardware')->group(function () {
    Route::get('/', [HardwareController::class, 'index'])->name('hardware.index');
    Route::post('/test-speaker', [HardwareController::class, 'testSpeaker'])->name('hardware.test-speaker');
    Route::post('/update-config', [HardwareController::class, 'updateConfig'])->name('hardware.update-config');
    Route::get('/logs', [HardwareController::class, 'logs'])->name('hardware.logs');
    Route::get('/zones', [HardwareController::class, 'zones'])->name('hardware.zones');
    Route::patch('/zones/{zone}', [HardwareController::class, 'updateZone'])->name('hardware.zones.update');
});
```

**Edit file:** `routes/api.php` (jika belum ada, buat baru)

```php
<?php

use App\Http\Controllers\Api\HardwareApiController;
use Illuminate\Support\Facades\Route;

// Hardware Bridge API (untuk Python Bridge Service)
Route::prefix('hardware')->group(function () {
    Route::get('/pending-commands', [HardwareApiController::class, 'getPendingCommands']);
    Route::post('/report-result', [HardwareApiController::class, 'reportResult']);
    Route::get('/config', [HardwareApiController::class, 'getConfig']);
    Route::post('/heartbeat', [HardwareApiController::class, 'heartbeat']);
});
```

### 7. **Update PublicController** - **PENTING!**

Ini untuk trigger hardware saat bel otomatis.

**Edit file:** `app/Http/Controllers/PublicController.php`

Tambahkan di bagian atas:

```php
use App\Models\HardwareCommandQueue;
use App\Models\SpeakerZone;
```

Tambahkan method baru:

```php
/**
 * Queue hardware trigger command
 */
private function queueHardwareTrigger($schedule, $duration = null)
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

**Modifikasi method `playAudio()` di frontend JavaScript** (`resources/views/public/index.blade.php`):

Cari function `playAudio(schedule)` dan tambahkan trigger hardware SEBELUM play audio browser:

```javascript
playAudio(schedule) {
    // ===== TAMBAHKAN INI: Queue hardware trigger =====
    this.queueHardwareTrigger(schedule);

    // Original code: Browser audio play
    this.currentPlayingSchedule = schedule;
    this.currentSchedulePlayingId = schedule.id;
    this.nowPlayingTitle = schedule.audio.title;
    const audio = this.$refs.audioPlayer;
    audio.src = schedule.audio.file_url;

    // ... rest of the code
},

// ===== TAMBAHKAN METHOD BARU =====
async queueHardwareTrigger(schedule) {
    try {
        const response = await fetch('/api/queue-hardware-trigger', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                schedule_id: schedule.id,
                audio_duration: schedule.audio.duration || 180
            })
        });

        if (response.ok) {
            console.log('Hardware trigger queued successfully');
        }
    } catch (error) {
        console.error('Failed to queue hardware trigger:', error);
        // Tidak error fatal, browser audio tetap jalan
    }
},
```

**Buat API endpoint baru di PublicController:**

```php
public function queueHardwareTrigger(Request $request)
{
    $validated = $request->validate([
        'schedule_id' => 'required|uuid|exists:bell_schedules,id',
        'audio_duration' => 'nullable|integer',
    ]);

    $schedule = BellSchedule::with('audioLibrary')->findOrFail($validated['schedule_id']);
    $this->queueHardwareTrigger($schedule, $validated['audio_duration'] ?? null);

    return response()->json(['success' => true]);
}
```

**Tambahkan route di `routes/web.php`:**

```php
Route::post('/api/queue-hardware-trigger', [PublicController::class, 'queueHardwareTrigger']);
```

---

## 🔧 QUICK START DEPLOYMENT

### Step 1: Run Migrations

```bash
php artisan migrate
```

### Step 2: Seed Initial Data

```bash
php artisan db:seed --class=HardwareSeeder
```

(Buat seeder sesuai guide di `HARDWARE_INTEGRATION_GUIDE.md`)

### Step 3: Setup API Token

```bash
# Generate token
php artisan tinker
>>> echo bin2hex(random_bytes(32));
```

Copy output ke `.env`:

```env
HARDWARE_BRIDGE_API_TOKEN=<generated-token-here>
```

### Step 4: Install Python Bridge di PC Sekolah

Follow langkah-langkah di `HARDWARE_INTEGRATION_GUIDE.md` section "Instalasi Python Bridge"

### Step 5: Test!

1. Akses `/hardware` (jika sudah buat UI)
2. Test speaker manual
3. Buat jadwal bel otomatis
4. Monitor logs di `/hardware/logs`

---

## 📊 WORKFLOW COMPLETE

```
USER (Web UI)
    ↓
Laravel VPS (Create Command)
    ↓
Database (hardware_command_queue)
    ↓
Python Bridge (Poll every 5s)
    ↓
Modbus USB-RS485
    ↓
Relay Module
    ↓
SPEAKER BUNYI! 🔊
    ↓
Bridge Report Result
    ↓
Laravel VPS (Log to database)
```

---

## 🎯 NEXT ACTIONS

**Prioritas TINGGI:**
1. ✅ Tambahkan routes untuk API (WAJIB)
2. ✅ Update PublicController dengan queueHardwareTrigger (WAJIB)
3. ✅ Install Python Bridge di PC sekolah (WAJIB)
4. ✅ Test end-to-end

**Prioritas MEDIUM:**
5. Buat views untuk admin UI (opsional, bisa pakai database manual)
6. Tambahkan notifikasi real-time (opsional)

**Prioritas LOW:**
7. Dashboard analytics
8. Export logs to Excel
9. Multi-device support

---

## 📝 FILES YANG HARUS DIEDIT MANUAL

1. **routes/web.php** - Tambahkan hardware routes
2. **routes/api.php** - Tambahkan API routes untuk bridge (BUAT BARU jika belum ada)
3. **app/Http/Controllers/PublicController.php** - Tambahkan queueHardwareTrigger()
4. **resources/views/public/index.blade.php** - Modifikasi playAudio() JavaScript
5. **.env** - Tambahkan HARDWARE_BRIDGE_API_TOKEN

---

## ✨ SUMMARY

Anda sudah punya **90% implementasi** selesai!

Yang masih perlu:
- Edit 5 file yang saya sebutkan di atas (copy-paste code yang sudah saya sediakan)
- Install Python Bridge di PC sekolah
- Test!

**Estimasi waktu:** 30-60 menit untuk finalisasi + testing

**Apakah Anda mau saya buatkan script installer otomatis untuk generate semua file yang tersisa?**

---

Generated: 2026-08-11
