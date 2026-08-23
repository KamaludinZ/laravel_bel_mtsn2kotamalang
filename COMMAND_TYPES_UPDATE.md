# Command Types Update - Toggle Activation System

## Perubahan Konsep Aktivasi

### Sebelum (OLD)
Semua aktivasi manual menggunakan **durasi/timeout**:
- testRoom: Nyala 5 detik → Mati otomatis
- testType: Nyala 5 detik → Mati otomatis
- testAllZones: Nyala 5 detik → Mati otomatis

**Masalah**:
- Speaker tidak bisa digunakan untuk panggilan mic berkelanjutan
- Tidak cocok untuk use case aktual (memanggil siswa, pengumuman, dll)

### Sesudah (NEW)
Aktivasi manual = **Toggle ON/OFF tanpa durasi**:
- Tombol room: **Nyalakan speaker** → Tetap aktif sampai dimatikan manual
- Tombol HORN/CTRLROOM: **Nyalakan speaker** → Tetap aktif sampai dimatikan manual
- Tombol ON ALL: **Nyalakan semua speaker** → Tetap aktif sampai OFF ALL
- Tombol OFF ALL: **Matikan semua speaker** → Aman

**Keuntungan**:
- Bisa digunakan untuk panggilan mic/pengumuman berkelanjutan
- Bel otomatis tetap berfungsi dengan durasi audio
- Lebih sesuai dengan use case real (sekolah)

## Command Types Baru

### 1. `activate_speaker` (MANUAL - Tanpa Durasi)
Mengaktifkan speaker tanpa timeout, tetap nyala sampai dimatikan manual.

**Digunakan untuk**:
- Aktivasi tombol individual room
- Aktivasi HORN/CTRLROOM button
- ON ALL button
- Semua aktivasi MANUAL dari UI

**Payload**:
```json
{
  "hardware_address": "10-4",
  "room_id": 123,
  "room_name": "CTRLROOM",
  "trigger_type": "MANUAL_PARENT|MANUAL_CHILD|MANUAL_STANDALONE|MANUAL_ON_ALL_PARENT|MANUAL_ON_ALL_CHILD|MANUAL_TYPE_PARENT|MANUAL_TYPE_CHILD"
}
```

**Tidak ada `duration_seconds`** - Speaker tetap ON sampai deactivate manual.

### 2. `deactivate_speaker` (MANUAL OFF)
Mematikan speaker yang aktif.

**Digunakan untuk**:
- OFF ALL button (matikan semua speaker)
- OFF individual (jika diimplementasikan toggle)

**Payload**:
```json
{
  "hardware_address": "10-4",
  "room_id": 123,
  "room_name": "CTRLROOM",
  "trigger_type": "MANUAL_OFF_ALL_PARENT|MANUAL_OFF_ALL_CHILD"
}
```

### 3. `test_speaker` (BELL SCHEDULE - Dengan Durasi)
**TETAP DIGUNAKAN** untuk jadwal bel otomatis dengan durasi tertentu.

**Digunakan untuk**:
- Jadwal bel otomatis (CheckBellSchedule command)
- Durasi = durasi audio file

**Payload**:
```json
{
  "hardware_address": "9-1",
  "room_id": 456,
  "room_name": "7C",
  "parent_address": "10-4",
  "duration_seconds": 120,
  "trigger_type": "ON_CHILD"
}
```

**Ada `duration_seconds`** - Speaker akan OFF otomatis setelah durasi habis (untuk bel schedule).

### 4. `activate_parent` (Legacy - Tetap Ada)
Khusus aktivasi parent untuk compatibility.

### 5. `stop_speaker` (BELL SCHEDULE - Auto OFF)
Mematikan speaker setelah jadwal bel selesai.

**Digunakan untuk**:
- OFF otomatis setelah audio bel selesai
- CheckBellSchedule command (step 3a dan 3b)

## Alur Aktivasi

### A. Manual Activation (Dari UI - TOGGLE System)

#### Individual Room Button (testRoom)
**Jika Child Room**:
```
1. activate_speaker (Parent) → scheduled_at: now
2. activate_speaker (Child) → scheduled_at: now + 1s
→ Tetap aktif sampai manual OFF
```

**Jika Parent Room (HORN/CTRLROOM)**:
```
1. activate_speaker (Parent) → scheduled_at: now
→ Tetap aktif sampai manual OFF
```

#### HORN/CTRLROOM Button (testType)
```
1. activate_speaker (HORN atau CTRLROOM) → scheduled_at: now
→ Tetap aktif sampai manual OFF
```

#### ON ALL Button (testAllZones)
```
1. activate_speaker (All Parents) → scheduled_at: now
2. activate_speaker (All Children) → scheduled_at: now + 2s
→ Semua tetap aktif sampai manual OFF
```

#### OFF ALL Button (offAll)
```
1. Clear all pending commands
2. deactivate_speaker (All Children) → scheduled_at: now
3. deactivate_speaker (All Parents) → scheduled_at: now + 1s
→ Semua speaker dimatikan
```

### B. Bell Schedule Activation (Otomatis - DENGAN DURASI)

Tetap menggunakan system lama dengan durasi:

```
Step 1a: activate_parent (Parents) → now
Step 1b: test_speaker (Children) → now + 2s [durasi = 2 detik untuk activation]
Step 2: play_audio → now + 4s [durasi = audio file duration]
Step 3a: stop_speaker (Children) → after audio + 2s
Step 3b: stop_speaker (Parents) → after children + 1s
```

## Perubahan Kode

### File: `app/Http/Controllers/HardwareController.php`

#### testRoom() - Lines 255-363
- Changed: `command_type` dari `test_speaker` → `activate_speaker`
- Removed: `duration_seconds` dari payload
- Message: "...tetap aktif sampai dimatikan manual"

#### testType() - Lines 420-545
- Changed: `command_type` dari `activate_parent`/`test_speaker` → `activate_speaker`
- Removed: `duration_seconds` dari payload
- Message: "...diaktifkan (tetap aktif sampai dimatikan manual)"

#### testAllZones() - Lines 171-250
- Changed: `command_type` dari `activate_parent`/`test_speaker` → `activate_speaker`
- Removed: `duration_seconds` dari payload
- Message: "...diaktifkan (tetap aktif sampai dimatikan manual)"

#### offAll() - Lines 554-637
- Changed: Complete rewrite
- Uses: `deactivate_speaker` command type
- Urutan: Children OFF → Parents OFF (reverse activation order)
- Clears pending commands first

### File: `app/Console/Commands/CheckBellSchedule.php`
**TIDAK ADA PERUBAHAN** - Tetap menggunakan:
- `activate_parent` untuk parents
- `test_speaker` untuk children (dengan duration)
- `play_audio` untuk memutar audio
- `stop_speaker` untuk mematikan

## Use Cases

### 1. Panggilan Mic untuk Memanggil Siswa
```
User: Klik tombol "7C"
System:
  - activate_speaker CTRLROOM (10-4)
  - activate_speaker 7C (9-1)
  - Speaker 7C tetap aktif
User: Bicara di mic untuk memanggil siswa
User: Klik OFF ALL atau tombol 7C lagi (jika toggle diimplementasikan)
System:
  - deactivate_speaker 7C (9-1)
  - deactivate_speaker CTRLROOM (10-4)
```

### 2. Pengumuman ke Semua Kelas
```
User: Klik ON ALL
System:
  - activate_speaker HORN + CTRLROOM
  - activate_speaker semua child rooms (32 rooms)
  - Semua speaker tetap aktif
User: Bicara pengumuman di mic
User: Klik OFF ALL
System:
  - deactivate_speaker semua children
  - deactivate_speaker HORN + CTRLROOM
```

### 3. Bel Otomatis (Jam 07:00 - Bel Masuk)
```
CheckBellSchedule (cron every minute):
  - Detect schedule: 07:00 - Bel Masuk - Audio: "bel_masuk.mp3" (60 detik)
  - activate_parent HORN + CTRLROOM (now)
  - test_speaker all children (now + 2s, duration: 2s)
  - play_audio "bel_masuk.mp3" (now + 4s, duration: 60s)
  - stop_speaker all children (now + 4s + 60s + 2s = 66s)
  - stop_speaker HORN + CTRLROOM (now + 67s)
  - Selesai, semua speaker OFF otomatis
```

## Command Type Summary

| Command Type | Durasi? | Digunakan Untuk | Auto OFF? |
|--------------|---------|-----------------|-----------|
| `activate_speaker` | ❌ No | Manual activation (UI buttons) | ❌ Sampai manual OFF |
| `deactivate_speaker` | ❌ No | Manual deactivation (OFF ALL) | ✅ Immediate |
| `test_speaker` | ✅ Yes | Bell schedule children activation | ✅ Setelah durasi |
| `activate_parent` | ❌ No | Bell schedule parent activation | ❌ Manual via stop_speaker |
| `stop_speaker` | ❌ No | Bell schedule auto OFF | ✅ Immediate |
| `play_audio` | ✅ Yes | Bell schedule audio playback | ✅ Setelah audio selesai |

## Python Bridge Implementation

Python Bridge perlu handle 3 command types utama:

### 1. `activate_speaker`
```python
def handle_activate_speaker(payload):
    hardware_address = payload['hardware_address']
    # Nyalakan relay TANPA timeout
    activate_relay(hardware_address)
    # Tidak ada timer untuk auto-off
```

### 2. `deactivate_speaker`
```python
def handle_deactivate_speaker(payload):
    hardware_address = payload['hardware_address']
    # Matikan relay
    deactivate_relay(hardware_address)
```

### 3. `test_speaker` (existing)
```python
def handle_test_speaker(payload):
    hardware_address = payload['hardware_address']
    duration = payload.get('duration_seconds', 5)
    # Nyalakan relay DENGAN timeout
    activate_relay(hardware_address)
    time.sleep(duration)
    deactivate_relay(hardware_address)
```

## Migration Notes

**Database**: Tidak perlu migration - Hanya perubahan di application logic

**Compatibility**:
- Bell schedule tetap berfungsi (menggunakan test_speaker dengan durasi)
- Manual activation sekarang tanpa durasi (lebih user-friendly)

## Testing Checklist

- [ ] Test individual room button (nyala, tetap ON)
- [ ] Test CTRLROOM button (nyala, tetap ON)
- [ ] Test HORN button (nyala, tetap ON)
- [ ] Test ON ALL (semua nyala, tetap ON)
- [ ] Test OFF ALL (semua mati)
- [ ] Test bell schedule (nyala otomatis, play audio, mati otomatis)
- [ ] Verify parent-child sequence (parent dulu, child kemudian)
- [ ] Verify OFF sequence (children dulu, parents kemudian)
