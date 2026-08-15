# 🔔 Sistem Hardware Bell - MTsN 2 Kota Malang

## 📋 Daftar Isi
1. [Arsitektur Lengkap](#arsitektur-lengkap)
2. [Alur Kerja Sistem](#alur-kerja-sistem)
3. [Komponen Hardware](#komponen-hardware)
4. [Speaker Zones & Modbus Channel](#speaker-zones--modbus-channel)
5. [Room Configuration](#room-configuration)
6. [Command Queue System](#command-queue-system)
7. [Cara Kerja Trigger](#cara-kerja-trigger)
8. [Testing Manual](#testing-manual)

---

## 🏗️ Arsitektur Lengkap

```
┌─────────────────────────────────────────────────────────────────────┐
│                         VPS (Laravel App)                            │
│  ┌────────────────┐  ┌──────────────────┐  ┌──────────────────┐   │
│  │  Web Interface │→ │ Hardware Queue   │→ │  API Endpoints   │   │
│  │  - Dashboard   │  │ - Commands       │  │  /api/hardware/  │   │
│  │  - Settings    │  │ - Scheduling     │  │  pending-commands│   │
│  │  - Hardware    │  │ - Status Track   │  │  report-result   │   │
│  └────────────────┘  └──────────────────┘  └──────────────────┘   │
└───────────────────────────────────────────────────┬─────────────────┘
                                                    │ HTTPS
                                                    │ Polling setiap 5 detik
                                                    ↓
┌─────────────────────────────────────────────────────────────────────┐
│                    PC Lokal Sekolah (Windows)                        │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │ Python Bridge Service                                       │    │
│  │ - Polling API VPS                                           │    │
│  │ - Execute Modbus Commands                                   │    │
│  │ - Report Results back to VPS                                │    │
│  └────────────────────────────┬────────────────────────────────┘    │
│                               │ USB/Serial                           │
│                               ↓                                      │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │ USB-RS485 Modbus Converter (P/N 242411)                     │    │
│  │ - Baud Rate: 9600                                           │    │
│  │ - Data Bits: 8                                              │    │
│  │ - Parity: None                                              │    │
│  │ - Stop Bits: 1                                              │    │
│  └────────────────────────────┬────────────────────────────────┘    │
└────────────────────────────────┼────────────────────────────────────┘
                                 │ RS485 (A/B)
                                 ↓
┌─────────────────────────────────────────────────────────────────────┐
│                      Relay Module 8 Channel                          │
│  CH1  CH2  CH3  CH4  CH5  CH6  CH7  CH8                             │
│   │    │    │    │    │    │    │    │                              │
│   ↓    ↓    ↓    ↓    ↓    ↓    ↓    ↓                              │
│  Speaker Zones (Physical Outputs)                                   │
└─────────────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Alur Kerja Sistem

### 1. Trigger Manual dari Dashboard

```
User Click Button    →    Laravel Controller    →    Hardware Queue
(HORN/Room/Group)         (HardwareController)       (CREATE command)
                                                             ↓
Python Bridge        ←    API Endpoint           ←    Command: PENDING
(Poll /pending)           (/api/hardware/)            (Wait for bridge)
      ↓
Execute Modbus       →    COM Port               →    USB-RS485
(pymodbus)                (Serial Communication)      (Convert to RS485)
      ↓
Relay Module         →    Speaker ON/OFF         →    Audio Output
(Activate Channel)        (Physical Switch)           (Bunyi speaker)
      ↓
Report Result        →    API Endpoint           →    Update Queue
(/report-result)          (Success/Failed)            (Status: COMPLETED)
```

### 2. Trigger Otomatis dari Jadwal Bel

```
Jadwal Waktu Tiba    →    JavaScript Check       →    AJAX Request
(Browser local time)      (every second)              (/api/queue-hardware)
                                                             ↓
                          Laravel Controller      →    STEP 1: ON ALL
                          (PublicController)           CREATE trigger_bell
                                ↓                       - zones: [1,2,3,4,5,6,7,8]
                          Audio Play                   - duration: audio.duration
                          (Browser HTML5)              - trigger_type: SCHEDULE_ON_ALL
                                ↓                            ↓
                          After Audio                  STEP 2: OFF ALL
                          (duration + 2s)              CREATE stop_all
                                                       - zones: [1,2,3,4,5,6,7,8]
                                                       - scheduled_at: now() + duration + 2s
                                                       - trigger_type: SCHEDULE_OFF_ALL
                                                             ↓
                          Python Bridge          ←    Poll Commands
                          Execute ON ALL                Execute channels 1-8 ON
                                ↓
                          Wait for scheduled time
                                ↓
                          Execute OFF ALL              Execute channels 1-8 OFF
                                ↓
                          Report Results         →    Update Queue Status
```

**Catatan Penting:**
- **ON ALL** = Aktifkan SEMUA speaker zones (PARENT + semua grup)
- **OFF ALL** = Matikan SEMUA speaker zones
- Jadwal bel **SELALU** menggunakan ON ALL dulu, baru OFF ALL setelah audio selesai

---

## 🔌 Komponen Hardware

### USB-RS485 Modbus Converter
- **Part Number:** 242411
- **Connection:** USB to RS485 (A/B terminals)
- **Protocol:** Modbus RTU
- **COM Port:** Dikonfigurasi di Settings (default COM3)

### Relay Module 8 Channel
- **Type:** Modbus RTU Relay
- **Channels:** 8 (CH1-CH8)
- **Modbus Address:** Default 1 (configurable)
- **Voltage:** 12V/24V DC
- **Control:** Modbus Register Write

### Speaker System
- **Zones:** 8 zone speaker terpisah
- **Mapping:** 1 zone = 1 relay channel = 1 modbus channel
- **Control:** ON/OFF via relay module

---

## 🎵 Speaker Zones & Modbus Channel

| Zone ID | Modbus Channel | Nama Zone            | Deskripsi                |
|---------|---------------|----------------------|--------------------------|
| 1       | 1             | Kelas 7              | Semua kelas tingkat 7    |
| 2       | 2             | Kelas 8 A-E          | Kelas 8A sampai 8E       |
| 3       | 3             | Kelas 9 A-D          | Kelas 9A sampai 9D       |
| 4       | 4             | Kelas 8 F-I & 9A     | Kelas 8F-8I dan 9A       |
| 5       | 5             | Kelas 9 B-I          | Kelas 9B sampai 9I       |
| 6       | 6             | Kelas 9 E-H & Lab    | Kelas 9E-9H, 8K, Library |
| 7       | 7             | Laboratorium         | MAHAD 1                  |
| 8       | 8             | Custom/Future        | Reserved untuk ekspansi  |

**Mapping Room → Zone:**
- Setiap room memiliki `speaker_zone_id` yang menunjuk ke zone tertentu
- Setiap zone memiliki `modbus_channel` (1-8) yang merepresentasikan relay channel
- Saat trigger room, sistem akan:
  1. Ambil `speaker_zone_id` dari room
  2. Ambil `modbus_channel` dari speaker_zone
  3. Kirim Modbus command ke channel tersebut

---

## 🏠 Room Configuration

### Parent Channels (Audio Control)
| Room Code | Room Type | Group Name | Speaker Zone | Hardware Address |
|-----------|-----------|------------|--------------|------------------|
| HORN      | HORN      | PARENT     | Zone 1       | 1-1              |
| CTRLROOM  | CTRLROOM  | PARENT     | -            | 10-4             |

**Karakteristik:**
- **HORN:** Trigger zone 1 (Kelas 7)
- **CTRLROOM:** Tidak punya speaker zone (hanya control room)
- **Code tidak bisa diedit** (system reserved)

### Group Control
| Group Name | Total Rooms | Speaker Zones Used |
|------------|-------------|-------------------|
| GROUP 1    | 7           | Zone 1            |
| GROUP 2    | 6           | Zone 2            |
| GROUP 3    | 5           | Zone 3            |
| GROUP 4    | 3           | Zone 4            |
| GROUP 5    | 4           | Zone 5            |
| GROUP 6    | 4           | Zone 6            |
| CUSTOM 1   | 9 (1+8 res) | Zone 6            |
| CUSTOM 2   | 1           | Zone 7            |

**Cara Kerja:**
- Click GROUP 1 → Trigger semua room di GROUP 1 → Speaker zone 1 ON
- Click GROUP 2 → Trigger semua room di GROUP 2 → Speaker zone 2 ON
- dst...

### Room Control Grid (40 Rooms)
**Layout:**
```
Row 1: 7A 7B 7C 7D 7E 7F 7G 7H 7I 7J     (10 kelas tingkat 7)
Row 2: 8A 8B 8C 8D 8E 8F 8G 8H 8I 8J     (10 kelas tingkat 8)
Row 3: 9A 9B 9C 9D 9E 9F 9G 9H 9I 9K     (10 kelas tingkat 9)
Row 4: LIBRARY MAHAD1 RES1-RES8          (Custom + Reserved)
```

**Mapping menggunakan Room Number (`no` field):**
- Stabil, tidak berubah meski `room_code` diedit
- Room number 1-58 (dengan gap untuk numbering consistency)

---

## 📨 Command Queue System

### Command Types

#### 1. `trigger_bell`
Menyalakan speaker zone untuk durasi tertentu.

**Payload:**
```json
{
  "zones": [1, 2, 3],
  "duration_seconds": 5,
  "room_id": "uuid",
  "room_name": "7A",
  "trigger_type": "MANUAL" | "SCHEDULE_ON_ALL"
}
```

#### 2. `test_speaker`
Test individual speaker zone.

**Payload:**
```json
{
  "zone": 1,
  "zone_id": "uuid",
  "room_id": "uuid",
  "room_name": "7A",
  "duration_seconds": 5
}
```

#### 3. `stop_all`
Mematikan semua speaker zones.

**Payload:**
```json
{
  "zones": [1, 2, 3, 4, 5, 6, 7, 8],
  "action": "stop",
  "trigger_type": "MANUAL_OFF_ALL" | "SCHEDULE_OFF_ALL"
}
```

### Command Status Flow

```
PENDING → (Bridge poll) → PROCESSING → (Execute) → COMPLETED/FAILED
   ↓                                                       ↑
   └────────── (Expired) ──────────→ EXPIRED ─────────────┘
```

**Status:**
- `pending`: Menunggu bridge ambil
- `processing`: Sedang dieksekusi bridge
- `completed`: Berhasil dieksekusi
- `failed`: Gagal dieksekusi
- `expired`: Tidak diambil dalam waktu yang ditentukan

---

## 🎯 Cara Kerja Trigger

### A. Trigger Individual Room

**Contoh: Klik button "7A"**

1. **Frontend JavaScript:**
```javascript
function testRoom(roomId, roomName, groupName) {
    fetch('/hardware/test-room', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            room_id: roomId,
            duration: 5
        })
    });
}
```

2. **Backend (HardwareController@testRoom):**
```php
$room = Room::with('speakerZone')->findOrFail($roomId);
HardwareCommandQueue::create([
    'command_type' => 'test_speaker',
    'payload' => [
        'zone' => $room->speakerZone->modbus_channel, // Contoh: 1
        'zone_id' => $room->speakerZone->id,
        'duration_seconds' => 5,
    ],
    'status' => 'pending',
]);
```

3. **Python Bridge Poll:**
```python
# GET /api/hardware/pending-commands
commands = response.json()['commands']
for cmd in commands:
    if cmd['command_type'] == 'test_speaker':
        channel = cmd['payload']['zone']  # 1
        duration = cmd['payload']['duration_seconds']  # 5

        # Execute Modbus
        modbus_client.write_register(channel, 1)  # ON
        time.sleep(duration)
        modbus_client.write_register(channel, 0)  # OFF

        # Report back
        # POST /api/hardware/report-result
```

### B. Trigger Group

**Contoh: Klik button "GROUP 1"**

1. **Frontend:** Trigger semua room dalam GROUP 1
2. **Backend:**
   - Get all rooms where `group_name = 'GROUP 1'`
   - Get unique `speaker_zone.modbus_channel` (misal [1])
   - Create `trigger_bell` command dengan zones: [1]
3. **Bridge:** Execute channel 1 untuk durasi 5 detik

### C. Trigger HORN/CTRLROOM

**Contoh: Klik button "HORN"**

1. **Frontend:**
```javascript
function triggerParentChannel(type) {
    fetch('/hardware/test-type', {
        method: 'POST',
        body: JSON.stringify({ room_type: type })
    });
}
```

2. **Backend:**
   - Get rooms where `room_type = 'HORN'`
   - Get modbus_channel (1)
   - Create command

### D. ON ALL (Audio Control)

**Klik button "ON ALL"**

1. **Backend:**
   - Get ALL active rooms with speaker_zone_id
   - Get unique zones: [1,2,3,4,5,6,7,8]
   - Create `trigger_bell` dengan ALL zones
2. **Bridge:** Execute ALL channels ON

### E. OFF ALL (Audio Control)

**Klik button "OFF ALL"**

1. **Backend:**
   - Delete all pending commands (clear queue)
   - Get ALL zones
   - Create `stop_all` command
2. **Bridge:** Execute ALL channels OFF

### F. Jadwal Bel Otomatis (Paling Penting!)

**Waktu jadwal tiba (misal 07:00 - Bel Masuk)**

1. **JavaScript di Browser:**
```javascript
// Check setiap detik
if (currentTime === scheduleTime) {
    // Play audio di browser
    playAudio(audioFile);

    // Trigger hardware
    fetch('/api/queue-hardware-trigger', {
        body: JSON.stringify({
            schedule_id: scheduleId,
            audio_duration: audioDuration  // misal 30 detik
        })
    });
}
```

2. **Backend (PublicController@triggerHardware):**
```php
// STEP 1: ON ALL - Aktifkan semua speaker
HardwareCommandQueue::create([
    'command_type' => 'trigger_bell',
    'payload' => [
        'zones' => [1,2,3,4,5,6,7,8],  // SEMUA zone
        'duration_seconds' => 30,
        'trigger_type' => 'SCHEDULE_ON_ALL',
    ],
    'scheduled_at' => now(),
]);

// STEP 2: OFF ALL - Matikan setelah selesai
HardwareCommandQueue::create([
    'command_type' => 'stop_all',
    'payload' => [
        'zones' => [1,2,3,4,5,6,7,8],
        'trigger_type' => 'SCHEDULE_OFF_ALL',
    ],
    'scheduled_at' => now()->addSeconds(32),  // 30s audio + 2s buffer
]);
```

3. **Bridge Execution:**
```
T=0s    : Execute ON ALL  → Channels 1-8 ON
T=0-30s : Audio playing (dari browser + hardware)
T=32s   : Execute OFF ALL → Channels 1-8 OFF
```

**Hasil:**
- Semua speaker di semua zone menyala
- Audio bel berbunyi 30 detik
- Setelah 32 detik, semua speaker mati otomatis

---

## 🧪 Testing Manual

### 1. Test Individual Room
1. Buka menu **Hardware Management**
2. Di **Room Control Grid**, klik salah satu room (misal "7A")
3. **Expected:**
   - Pulse dot hijau muncul di button
   - Speaker zone 1 menyala 5 detik
   - Pulse dot hilang setelah selesai

### 2. Test Group
1. Di **Group Control**, klik "GROUP 1"
2. **Expected:**
   - Pulse dot di button GROUP 1 menyala
   - Pulse dot di semua room GROUP 1 menyala
   - Speaker zone 1 menyala 5 detik

### 3. Test HORN
1. Di **Audio Control**, klik "HORN"
2. **Expected:**
   - Pulse dot button HORN menyala
   - Speaker zone 1 (Kelas 7) menyala 5 detik

### 4. Test ON ALL
1. Klik button "ON ALL"
2. **Expected:**
   - Pulse dot di SEMUA button menyala (HORN, CTRLROOM, 8 groups, 40 rooms)
   - SEMUA speaker zones (1-8) menyala

### 5. Test OFF ALL
1. Trigger beberapa room/group dulu
2. Klik "OFF ALL"
3. **Expected:**
   - Semua pulse dot padam
   - Semua speaker zones mati
   - Queue dikosongkan

### 6. Test Jadwal Bel
1. Buat jadwal bel untuk 1 menit ke depan
2. Tunggu waktu jadwal tiba
3. **Expected:**
   - Audio play di browser
   - Semua speaker zones (1-8) menyala
   - Setelah audio selesai + 2 detik, semua speaker mati otomatis

---

## 🔍 Troubleshooting

### Problem: Button diklik tapi speaker tidak bunyi

**Check:**
1. Hardware integration enabled? (`Settings` → Hardware Integration ON)
2. Room punya `speaker_zone_id`? (Check di Edit Rooms)
3. Speaker zone punya `modbus_channel`? (Check di Kelola Speaker Zones)
4. Python bridge running? (Check logs)
5. COM port benar? (Check di Settings → Hardware Config)

### Problem: Jadwal bel tidak trigger hardware

**Check:**
1. `hardware_integration_enabled` = true di settings
2. Browser tab terbuka saat waktu jadwal (JavaScript check)
3. Check Hardware Logs untuk command queue
4. Check browser console untuk error

### Problem: Semua speaker menyala padahal hanya trigger 1 room

**Possible Cause:**
- Hardware address salah (banyak room pakai address sama)
- Modbus channel overlap
- Check database: distinct hardware_address per room

### Problem: Speaker tidak mati setelah durasi selesai

**Check:**
1. OFF ALL command tercreate? (Check queue)
2. Bridge execute stop command? (Check logs)
3. Relay module responding? (Check hardware connection)

---

## 📝 Kesimpulan

Sistem hardware bell ini bekerja dengan alur:
1. **User trigger** (manual/otomatis) → **Queue command** → **API** → **Bridge** → **Modbus** → **Relay** → **Speaker**
2. **Mapping:** Room → Speaker Zone → Modbus Channel → Relay Channel → Physical Speaker
3. **Jadwal bel:** SELALU gunakan ON ALL → Play → OFF ALL
4. **COM Port:** Bridge komunikasi via USB-RS485 ke relay module
5. **Status tracking:** Queue status + Hardware logs untuk monitoring

**File penting:**
- `app/Http/Controllers/HardwareController.php` - Manual trigger logic
- `app/Http/Controllers/PublicController.php` - Jadwal trigger logic (ON ALL/OFF ALL)
- `app/Http/Controllers/Api/HardwareApiController.php` - Bridge API
- `routes/api.php` - API endpoints
- Database: `hardware_command_queue`, `speaker_zones`, `rooms`, `hardware_logs`
