# Hardware Speaker Integration - Automatic Bell System

## Gambaran Sistem

Sistem ini mengintegrasikan jadwal bel dengan hardware speaker secara otomatis menggunakan:
- **Scheduler**: Mengecek jadwal bel setiap menit
- **Command Queue**: Menyimpan perintah hardware yang akan dieksekusi
- **Python Bridge**: Menjalankan perintah ke hardware Modbus RS485
- **Hardware API**: Komunikasi antara Laravel dan Python Bridge

## Alur Kerja Otomatis

### 1. Scheduler Berjalan Setiap Menit
```
Scheduler (cron) → php artisan bell:check-schedule
  ↓
Cek jadwal bel untuk hari dan waktu saat ini
  ↓
Jika ada jadwal → Buat 3 command berurutan
```

### 2. Workflow 3-Step untuk Setiap Bel

Ketika waktu bel tiba (misalnya: Senin 07:00), sistem otomatis membuat 3 perintah:

#### **Step 1: ON ALL** (Aktivasi Semua Speaker)
```
Command Type: trigger_bell
Trigger Type: ON_ALL
Duration: 2 detik
Scheduled At: 07:00:00
```
- Mengaktifkan PARENT channels (HORN + CTRLROOM)
- Mengaktifkan semua GROUP (GROUP 1-8, CUSTOM 1-8)
- Semua speaker siap menerima audio

#### **Step 2: PLAY AUDIO** (Putar Audio)
```
Command Type: play_audio
Audio: [File dari jadwal]
Duration: [Durasi audio, contoh: 60 detik]
Scheduled At: 07:00:03 (3 detik setelah ON ALL)
```
- Menunggu 3 detik setelah ON ALL
- Memutar file audio sesuai jadwal
- Durasi sesuai panjang file audio

#### **Step 3: OFF ALL** (Matikan Semua Speaker)
```
Command Type: stop_all
Trigger Type: OFF_ALL
Scheduled At: 07:01:05 (audio 60s + buffer 2s)
```
- Menunggu audio selesai + 2 detik buffer
- Mematikan semua speaker (PARENT + semua GROUP)
- Sistem kembali ke kondisi standby

### 3. Contoh Timeline Lengkap

Jadwal: **Senin 07:00** - Bel Masuk (60 detik)

```
07:00:00 → ⚡ ON ALL: Aktivasi semua speaker
07:00:03 → 🔊 PLAY: Mulai putar "Bel Masuk.mp3"
07:01:03 → 🔊 Audio selesai
07:01:05 → ⚡ OFF ALL: Matikan semua speaker
```

## Komponen Sistem

### A. Scheduler (Container: scheduler)
**File**: `routes/console.php`
```php
Schedule::command('bell:check-schedule')
    ->everyMinute()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();
```

**Container**: Berjalan di Docker container `scheduler`
**Log**: `storage/logs/bell-scheduler.log`

### B. Bell Check Command
**File**: `app/Console/Commands/CheckBellSchedule.php`

**Tugas**:
1. Cek hari dan waktu saat ini
2. Cari jadwal bel yang cocok di database
3. Cek apakah sudah di-trigger dalam 1 menit terakhir (hindari duplikasi)
4. Buat 3 command queue (ON ALL → PLAY → OFF ALL)

**Pencegahan Duplikasi**:
```php
$recentCommand = HardwareCommandQueue::where('created_at', '>=', now()->subMinute())
    ->where('command_type', 'play_audio')
    ->whereJsonContains('payload->schedule_id', $schedule->id)
    ->first();
```

### C. Hardware Command Queue
**Table**: `hardware_command_queue`

**Field Penting**:
- `command_type`: `trigger_bell`, `play_audio`, `stop_all`, `test_speaker`
- `payload`: JSON berisi detail perintah (zones, audio_id, duration, dll)
- `status`: `pending`, `processing`, `completed`, `failed`, `expired`
- `scheduled_at`: Kapan perintah harus dieksekusi
- `expires_at`: Batas waktu perintah valid

**Status Lifecycle**:
```
pending → processing → completed
                    → failed
                    → expired (jika melebihi expires_at)
```

### D. Hardware API (Bridge Polling)
**File**: `app/Http/Controllers/Api/HardwareApiController.php`

**Endpoints**:
1. `GET /api/hardware/pending-commands` - Bridge mengambil perintah
2. `POST /api/hardware/report-result` - Bridge melaporkan hasil
3. `GET /api/hardware/config` - Bridge mengambil konfigurasi
4. `POST /api/hardware/heartbeat` - Bridge ping server

**Logika Polling**:
```php
// Hanya ambil command yang:
// 1. Status = pending
// 2. scheduled_at <= sekarang (atau NULL)
// 3. Belum expired
```

**Keamanan**: Menggunakan Bearer Token authentication

### E. Python Bridge (External Service)
**Lokasi**: Service terpisah yang berjalan di server lokal/hardware

**Tugas**:
1. Poll Laravel API setiap 5 detik
2. Ambil pending commands
3. Eksekusi ke hardware Modbus RS485
4. Report hasil ke Laravel API

**Command Types**:
- `trigger_bell`: Aktivasi zone speaker
- `play_audio`: Putar file audio
- `stop_all`: Matikan semua speaker
- `test_speaker`: Test individual zone

## Monitoring & Logging

### 1. Hardware Logs
**Table**: `hardware_logs`

Mencatat setiap eksekusi perintah:
- Status eksekusi (success/failed)
- Waktu eksekusi (ms)
- Request & response data
- Bridge version & IP
- Error messages

**View**: `/hardware` → Tab "Riwayat Eksekusi"

### 2. Scheduler Logs
**File**: `storage/logs/bell-scheduler.log`

Mencatat:
- Waktu check scheduler berjalan
- Jadwal yang ditemukan
- Command yang dibuat
- Error (jika ada)

### 3. Laravel Logs
**File**: `storage/logs/laravel.log`

Mencatat error level aplikasi

## Konfigurasi

### 1. Environment Variables
```env
# Hardware Bridge API Token
HARDWARE_BRIDGE_API_TOKEN=your-secret-token-here

# Queue Connection
QUEUE_CONNECTION=redis

# Timezone
APP_TIMEZONE=Asia/Jakarta
```

### 2. Hardware Config (Database)
**Table**: `hardware_configs`

- COM Port (contoh: COM3, /dev/ttyUSB0)
- Baud Rate (default: 9600)
- Modbus Address (default: 1)
- Timeout (default: 2000ms)

**UI**: `/hardware` → Tab "Pengaturan Hardware"

### 3. Speaker Zones
**Table**: `speaker_zones`

8 Channel Modbus:
1. Zone 1 - GROUP 1
2. Zone 2 - GROUP 2
3. Zone 3 - GROUP 3
4. Zone 4 - GROUP 4
5. Zone 5 - CUSTOM 1-4
6. Zone 6 - CUSTOM 5-8
7. Zone 7 - HORN (PARENT)
8. Zone 8 - CTRLROOM (PARENT)

## Manual Control

### Audio Control Panel (`/hardware`)

**ON ALL Button**:
- Aktivasi semua speaker untuk testing
- Default 5 detik
- Includes PARENT + semua GROUP

**OFF ALL Button**:
- Matikan semua speaker
- Cancel semua pending commands
- Emergency stop

**Test Individual Room/Group**:
- Click pada room di grid
- Test specific group
- Test by room type

## Troubleshooting

### Problem: Bel tidak berbunyi otomatis

**Check**:
1. Container `scheduler` berjalan?
   ```bash
   docker-compose ps scheduler
   ```

2. Scheduler log ada error?
   ```bash
   tail -f storage/logs/bell-scheduler.log
   ```

3. Ada pending commands di queue?
   ```sql
   SELECT * FROM hardware_command_queue WHERE status = 'pending' ORDER BY created_at DESC;
   ```

4. Python Bridge online?
   - Check heartbeat di `/hardware`
   - Status "Bridge: Online/Offline"

5. Jadwal sudah benar?
   - Hari: `monday, tuesday, ..., sunday`
   - Waktu: Format `HH:MM` (contoh: `07:00`)
   - Bell Type aktif?
   - Audio file ada?

### Problem: Command expired sebelum dieksekusi

**Cause**: Python Bridge tidak berjalan atau terlalu lambat

**Solution**:
1. Restart Python Bridge
2. Check network connectivity
3. Increase `expires_at` duration

### Problem: Speaker tidak mati setelah audio selesai

**Check**:
1. Duration audio sudah benar?
   ```bash
   php artisan audio:calculate-durations
   ```

2. OFF ALL command ter-create?
   ```sql
   SELECT * FROM hardware_command_queue
   WHERE command_type = 'stop_all'
   AND DATE(created_at) = CURDATE();
   ```

### Problem: Duplikasi bel (berbunyi 2x di waktu yang sama)

**Cause**: Scheduler berjalan multiple times atau tidak `withoutOverlapping()`

**Solution**: Pastikan scheduler config sudah benar di `routes/console.php`

## Maintanance

### Clear Old Logs
**UI**: `/hardware/logs` → "Hapus Log Lama"

Atau via command:
```bash
php artisan tinker
>>> HardwareLog::where('created_at', '<', now()->subDays(30))->delete();
```

### Monitor Queue Health
```bash
# Check pending commands
php artisan queue:monitor hardware_command_queue

# Check queue worker status
docker-compose logs queue
```

### Backup Important Tables
```sql
-- Hardware configs
SELECT * FROM hardware_configs;

-- Speaker zones
SELECT * FROM speaker_zones;

-- Recent logs (last 7 days)
SELECT * FROM hardware_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

## Development & Testing

### Test Bell Schedule Manually
```bash
# Trigger check scheduler manually
docker-compose exec app php artisan bell:check-schedule

# Or from outside container
php artisan bell:check-schedule
```

### Create Test Schedule
```php
BellSchedule::create([
    'bell_type_id' => BellType::first()->id,
    'day' => strtolower(now()->englishDayOfWeek),
    'time' => now()->addMinutes(2)->format('H:i'), // 2 minutes from now
    'audio_library_id' => AudioLibrary::first()->id,
]);
```

### Monitor Command Queue Real-time
```bash
watch -n 1 'docker-compose exec -T postgres psql -U postgres -d laravel_bel -c "SELECT id, command_type, status, scheduled_at, created_at FROM hardware_command_queue WHERE DATE(created_at) = CURRENT_DATE ORDER BY created_at DESC LIMIT 10;"'
```

## Arsitektur Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      Laravel Application                     │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────┐      ┌─────────────────┐                 │
│  │  Scheduler   │──────>│ CheckBellSchedule│                 │
│  │ (every min)  │      │     Command     │                 │
│  └──────────────┘      └────────┬────────┘                 │
│                                  │                           │
│                                  v                           │
│              ┌───────────────────────────────┐              │
│              │  BellSchedule (Database)      │              │
│              │  - Find matching schedule     │              │
│              └───────────┬───────────────────┘              │
│                          │                                   │
│                          v                                   │
│         ┌────────────────────────────────────┐              │
│         │   Create 3 Commands:               │              │
│         │   1. ON ALL  (t+0s)                │              │
│         │   2. PLAY    (t+3s)                │              │
│         │   3. OFF ALL (t+duration+5s)       │              │
│         └────────────┬───────────────────────┘              │
│                      │                                       │
│                      v                                       │
│         ┌────────────────────────────┐                      │
│         │ HardwareCommandQueue       │                      │
│         │ (Database Table)           │                      │
│         │ - pending commands         │                      │
│         │ - scheduled_at timing      │                      │
│         └───────────┬────────────────┘                      │
│                     │                                        │
└─────────────────────┼────────────────────────────────────────┘
                      │
                      │ API Polling (every 5s)
                      │
            ┌─────────v──────────┐
            │  Python Bridge     │
            │  - Poll pending    │
            │  - Execute Modbus  │
            │  - Report result   │
            └─────────┬──────────┘
                      │
                      │ Modbus RS485
                      │
            ┌─────────v──────────┐
            │  Hardware Speaker  │
            │  8 Channel Relay   │
            │  - PARENT (2 ch)   │
            │  - GROUPS (6 ch)   │
            └────────────────────┘
```

## File Penting

| File | Deskripsi |
|------|-----------|
| `routes/console.php` | Scheduler configuration |
| `app/Console/Commands/CheckBellSchedule.php` | Bell checker command |
| `app/Http/Controllers/HardwareController.php` | UI controller |
| `app/Http/Controllers/Api/HardwareApiController.php` | API for bridge |
| `app/Models/HardwareCommandQueue.php` | Command queue model |
| `app/Models/HardwareLog.php` | Log model |
| `docker-compose.yml` | Scheduler container config |

## API Documentation (Python Bridge)

### 1. Get Pending Commands
```http
GET /api/hardware/pending-commands
Authorization: Bearer {HARDWARE_BRIDGE_API_TOKEN}
```

**Response**:
```json
{
  "success": true,
  "count": 2,
  "commands": [
    {
      "id": "uuid",
      "command_type": "trigger_bell",
      "payload": {
        "zones": [1, 2, 3, 4, 5, 6, 7, 8],
        "duration_seconds": 2,
        "trigger_type": "ON_ALL"
      },
      "scheduled_at": "2026-08-23T07:00:00+07:00"
    }
  ],
  "server_time": "2026-08-23T07:00:01+07:00"
}
```

### 2. Report Result
```http
POST /api/hardware/report-result
Authorization: Bearer {HARDWARE_BRIDGE_API_TOKEN}
Content-Type: application/json
```

**Body**:
```json
{
  "command_id": "uuid",
  "success": true,
  "message": "Command executed successfully",
  "execution_time_ms": 145,
  "bridge_version": "1.0.0",
  "response_data": {
    "zones_activated": [1, 2, 3, 4, 5, 6, 7, 8]
  }
}
```

---

**Last Updated**: 2026-08-23
**Version**: 1.0.0
