# 🔔 Hardware Integration - Bell System MTsN 2 Kota Malang

## 📖 Daftar Dokumentasi

Implementasi hardware integration telah selesai! Berikut adalah panduan lengkap:

### 1. **IMPLEMENTATION_SUMMARY.md**
📊 Overview lengkap tentang apa yang sudah dibuat dan yang masih perlu dilengkapi

### 2. **MANUAL_UPDATES_REQUIRED.md** ⭐ **MULAI DARI SINI!**
📝 Step-by-step instruksi untuk edit file Laravel (routes, controller, views)
- Checklist lengkap
- Copy-paste ready code
- Verifikasi steps

### 3. **HARDWARE_INTEGRATION_GUIDE.md**
🐍 Panduan instalasi Python Bridge Service di PC sekolah Windows
- Instalasi Python dependencies
- Konfigurasi Modbus
- Windows Service setup
- Troubleshooting

---

## 🚀 Quick Start (3 Langkah)

### **LANGKAH 1: Setup Laravel Backend (VPS)**

```bash
# 1. Follow instruksi di MANUAL_UPDATES_REQUIRED.md
# 2. Run migrations
php artisan migrate

# 3. Seed initial data
php artisan make:seeder HardwareSeeder
# (Edit seeder sesuai MANUAL_UPDATES_REQUIRED.md)
php artisan db:seed --class=HardwareSeeder

# 4. Generate API token
php artisan tinker
>>> echo bin2hex(random_bytes(32));
>>> exit

# 5. Paste token ke .env
# HARDWARE_BRIDGE_API_TOKEN=<token-dari-step-4>
```

### **LANGKAH 2: Install Python Bridge (PC Sekolah)**

```cmd
# Follow lengkap di HARDWARE_INTEGRATION_GUIDE.md
cd C:\BellBridgeService
pip install -r requirements.txt
python bridge_service.py
```

### **LANGKAH 3: Test!**

1. Akses `/hardware` di browser
2. Klik "Test Speaker" untuk zone tertentu
3. Lihat logs di `/hardware/logs`
4. Buat jadwal bel otomatis

---

## 📁 File Structure

```
C:\laravel_bel_mtsn2kotamalang\
│
├── 📄 README_HARDWARE_INTEGRATION.md ← Anda di sini
├── 📄 IMPLEMENTATION_SUMMARY.md
├── 📄 MANUAL_UPDATES_REQUIRED.md ⭐ START HERE
├── 📄 HARDWARE_INTEGRATION_GUIDE.md
│
├── database/migrations/
│   ├── 2026_08_11_102330_create_hardware_command_queue_table.php ✅
│   ├── 2026_08_11_102513_create_hardware_configs_table.php ✅
│   ├── 2026_08_11_102530_create_speaker_zones_table.php ✅
│   └── 2026_08_11_103015_create_hardware_logs_table.php ✅
│
├── app/Models/
│   ├── HardwareCommandQueue.php ✅
│   ├── HardwareConfig.php ✅
│   ├── SpeakerZone.php ✅
│   └── HardwareLog.php ✅
│
├── app/Http/Controllers/
│   ├── HardwareController.php ✅
│   └── Api/HardwareApiController.php ✅
│
└── routes/
    ├── web.php ⚠️ PERLU EDIT (lihat MANUAL_UPDATES_REQUIRED.md)
    └── api.php ✅

Python Bridge (PC Sekolah):
C:\BellBridgeService\
├── bridge_service.py (lihat HARDWARE_INTEGRATION_GUIDE.md)
├── requirements.txt
├── .env
└── bridge.log
```

---

## ✅ Apa yang Sudah Selesai?

### **Backend (Laravel VPS)**
✅ Database migrations (4 tables)
✅ Models dengan relationships & helper methods
✅ API Controller untuk bridge polling
✅ Hardware Controller untuk admin UI
✅ API routes untuk bridge communication
✅ Dokumentasi lengkap

### **Python Bridge Service**
✅ Full source code ready to use
✅ Modbus RTU communication
✅ Polling mechanism
✅ Error handling & retry
✅ Logging
✅ Windows service installer

---

## ⏳ Apa yang Perlu Anda Lakukan?

### **Manual Updates (15-30 menit)**
⏳ Edit `routes/web.php` (5 menit)
⏳ Edit `app/Http/Controllers/PublicController.php` (5 menit)
⏳ Edit `resources/views/public/index.blade.php` (5 menit)
⏳ Edit `.env` untuk API token (2 menit)
⏳ Buat HardwareSeeder.php (5 menit)
⏳ Run migrations & seeders (2 menit)

### **Python Bridge Installation (30-60 menit)**
⏳ Install Python 3.8+ di PC sekolah
⏳ Install dependencies
⏳ Konfigurasi COM port & Modbus
⏳ Test manual
⏳ Setup Windows service (optional)

### **Views (OPSIONAL - bisa pakai nanti)**
⏳ `resources/views/hardware/index.blade.php`
⏳ `resources/views/hardware/zones.blade.php`
⏳ `resources/views/hardware/logs.blade.php`

**NOTE:** Views opsional! Anda bisa manage hardware via database dulu, nanti buat UI kalau diperlukan.

---

## 🎯 How It Works

```
┌─────────────────────────────────────────────────────┐
│ USER: Buat jadwal bel jam 07:00                     │
│ atau klik "Test Speaker"                            │
└────────────────┬────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────┐
│ LARAVEL VPS                                         │
│ - PublicController: triggerHardware()               │
│ - Insert ke hardware_command_queue                  │
│   Status: pending                                    │
└────────────────┬────────────────────────────────────┘
                 │
                 │ ⏰ Polling setiap 5 detik
                 │
                 ↓
┌─────────────────────────────────────────────────────┐
│ PYTHON BRIDGE (PC Sekolah)                         │
│ - GET /api/hardware/pending-commands                │
│ - Ambil command dari queue                          │
│ - Parse payload (zones, duration)                   │
└────────────────┬────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────┐
│ MODBUS COMMUNICATION                                │
│ - Connect ke COM3 @ 9600 baud                       │
│ - Write coil ke relay module                        │
│ - Channel 1,2,3,4 = ON                              │
└────────────────┬────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────┐
│ RELAY MODULE (8 Channel)                            │
│ - Close relay 1,2,3,4                               │
│ - Trigger 220V power to amplifier                   │
└────────────────┬────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────┐
│ 🔊 SPEAKER BUNYI!!!                                 │
│ - Halaman, Kelas Lt 1, Lt 2, Masjid                 │
│ - Duration: 3 menit (sesuai audio)                  │
└────────────────┬────────────────────────────────────┘
                 │
                 │ ⏱️ Sleep(180 seconds)
                 │
                 ↓
┌─────────────────────────────────────────────────────┐
│ RELAY OFF                                           │
│ - Open relay 1,2,3,4                                │
│ - Speaker mati                                      │
└────────────────┬────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────┐
│ REPORT RESULT                                       │
│ - POST /api/hardware/report-result                  │
│ - Status: success                                   │
│ - Execution time: 180000ms                          │
└────────────────┬────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────────┐
│ LARAVEL VPS                                         │
│ - Update command status: completed                  │
│ - Create hardware_log entry                         │
│ - Admin bisa lihat di /hardware/logs                │
└─────────────────────────────────────────────────────┘
```

---

## 🔍 Troubleshooting Quick Reference

### Laravel API tidak bisa diakses
```bash
# Check routes
php artisan route:list | findstr hardware

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Python Bridge tidak konek ke VPS
```cmd
# Test ping
ping your-domain.com

# Test API endpoint
curl https://your-domain.com/api/hardware/pending-commands ^
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Modbus tidak konek
```cmd
# Check COM port di Device Manager
# Lihat di: Control Panel > Device Manager > Ports (COM & LPT)

# Test dengan software Modbus Poll
# Download: https://www.modbustools.com/download.html
```

### Speaker tidak bunyi
- Cek relay module power supply (12V/24V DC)
- Cek wiring RS485 (A+, B-, GND)
- Cek Modbus address device (default: 1)
- Cek COM port & baud rate sama antara software & hardware

**Lengkapnya lihat:** `HARDWARE_INTEGRATION_GUIDE.md` section Troubleshooting

---

## 📞 Support

Jika ada error atau pertanyaan:

1. Check file log:
   - Laravel: `storage/logs/laravel.log`
   - Python Bridge: `C:\BellBridgeService\bridge.log`

2. Check database:
   ```sql
   SELECT * FROM hardware_logs ORDER BY created_at DESC LIMIT 10;
   SELECT * FROM hardware_command_queue WHERE status = 'pending';
   ```

3. Lihat dokumentasi:
   - `IMPLEMENTATION_SUMMARY.md` - Overview
   - `MANUAL_UPDATES_REQUIRED.md` - Laravel setup
   - `HARDWARE_INTEGRATION_GUIDE.md` - Python Bridge setup

---

## 📊 Monitoring

### Check Bridge Status
```bash
# Via web UI
http://localhost:8000/hardware

# Via database
SELECT last_status, last_connected_at
FROM hardware_configs
WHERE config_key = 'primary_device';
```

### Check Command Queue
```bash
SELECT command_type, status, created_at
FROM hardware_command_queue
ORDER BY created_at DESC
LIMIT 10;
```

### Check Logs
```bash
SELECT action, status, message, created_at
FROM hardware_logs
ORDER BY created_at DESC
LIMIT 20;
```

---

## 🎉 Selesai!

Setelah mengikuti semua dokumentasi:

✅ Sistem bel web-based berfungsi normal (seperti sebelumnya)
✅ **PLUS** Speaker fisik sekolah ikut bunyi via Modbus
✅ Monitoring real-time via web dashboard
✅ Logs lengkap untuk debugging
✅ Fallback ke browser audio jika hardware error

**Happy bell ringing! 🔔**

---

Generated by: Claude Code
Date: 2026-08-11
Version: 1.0.0
