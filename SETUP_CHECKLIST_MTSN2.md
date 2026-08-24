# Setup Checklist - MTsN 2 Kota Malang
**Tanggal:** 24 Agustus 2026
**Server:** https://bell.mtsn2kotamalang.sch.id
**IP:** 187.77.117.23

---

## ✅ Informasi PC Lokal Bell

- **Python:** 3.14 ✓
- **OS:** Windows 11 ✓
- **COM Port:** COM5 ✓
- **Server:** https://bell.mtsn2kotamalang.sch.id ✓

---

## 📝 Langkah-Langkah Setup (IKUTI URUTAN INI)

### **Step 1: Setup Folder di PC Lokal Bell** ✅

**Di PC lokal bell, buka Command Prompt (CMD) atau PowerShell:**

```cmd
mkdir C:\BelSekolah
cd C:\BelSekolah
```

---

### **Step 2: Download Files dari GitHub** ⬅️ **MULAI DI SINI**

**Opsi A - Manual Download (Mudah):**

1. Buka browser, buka: https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang

2. Download 3 file ini (klik file → klik "Raw" → Ctrl+S Save):
   - `python_bridge.py` → Save ke `C:\BelSekolah\python_bridge.py`
   - `config.json.example` → Save ke `C:\BelSekolah\config.json.example`
   - `start_bridge.bat` → Save ke `C:\BelSekolah\start_bridge.bat`

**Opsi B - Git Clone (Jika Git terinstall):**

```cmd
cd C:\
git clone https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang.git BelSekolah
cd BelSekolah
```

---

### **Step 3: Install Dependencies Python**

**Di Command Prompt, jalankan:**

```cmd
pip install pyserial requests
```

**Verifikasi instalasi:**

```cmd
pip list | findstr serial
pip list | findstr requests
```

**Output yang diharapkan:**
```
pyserial        3.5
requests        2.31.0
```

---

### **Step 4: Buat File config.json**

**Di `C:\BelSekolah\`, copy file example:**

```cmd
cd C:\BelSekolah
copy config.json.example config.json
```

**Edit `config.json` dengan Notepad:**

```cmd
notepad config.json
```

**Isi dengan konfigurasi ini:**

```json
{
  "vps_base_url": "https://bell.mtsn2kotamalang.sch.id",
  "api_token": "a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de",
  "com_port": "COM5",
  "poll_interval": 2,
  "baud_rate": 9600,
  "timeout": 1
}
```

**Save file (Ctrl+S) dan tutup Notepad.**

---

### **Step 5: Cek File Sudah Lengkap**

```cmd
cd C:\BelSekolah
dir
```

**Harus ada 4 file:**
```
python_bridge.py
config.json
config.json.example
start_bridge.bat
```

---

### **Step 6: Test Koneksi ke Server**

**Pastikan PC lokal bell bisa akses internet, lalu test:**

```cmd
python python_bridge.py --test-connection
```

**Output yang diharapkan:**
```
[2026-08-24 10:00:00] INFO: ✓ Server connection OK
[2026-08-24 10:00:00] INFO:   Server config: {...}
```

**Jika error "Connection failed":**
- Cek internet connection
- Pastikan firewall tidak blokir Python
- Ping server: `ping bell.mtsn2kotamalang.sch.id`

---

### **Step 7: Test Hardware (Modbus RS485)**

**⚠️ PASTIKAN kabel USB ke Modbus RS485 sudah terhubung!**

```cmd
python python_bridge.py --test-hardware
```

**Output yang diharapkan:**
```
[2026-08-24 10:00:00] INFO: Testing hardware connection...
[2026-08-24 10:00:00] INFO: Connected to COM5 at 9600 baud
[2026-08-24 10:00:00] INFO: Sending test command to address 01...
[2026-08-24 10:00:00] INFO: Sent to address 01: 01 05 00 00 FF 00 8C 3A (ON)
[2026-08-24 10:00:00] INFO: Response from 01: 01 05 00 00 FF 00 8C 3A
[2026-08-24 10:00:00] INFO: ✓ Hardware test successful
[2026-08-24 10:00:02] INFO: Turning off...
[2026-08-24 10:00:02] INFO: Sent to address 01: 01 05 00 00 00 00 CD CA (OFF)
```

**Jika error "Serial port cannot open":**
- Pastikan kabel USB terhubung
- Cek di Device Manager: COM5 ada?
- Tutup aplikasi lain yang pakai COM5

---

### **Step 8: Jalankan Python Bridge**

**Cara 1 - Manual (untuk testing):**

```cmd
python python_bridge.py
```

**Cara 2 - Pakai Batch File (Recommended):**

```cmd
start_bridge.bat
```

**Output normal:**
```
============================================================
Python Bridge for Bell System Hardware Control
============================================================
Server: https://bell.mtsn2kotamalang.sch.id
COM Port: COM5
Poll Interval: 2s
============================================================
[2026-08-24 10:00:00] INFO: ✓ Server connection OK
[2026-08-24 10:00:00] INFO: Connected to COM5 at 9600 baud
[2026-08-24 10:00:00] INFO: Bridge started - polling for commands...
[2026-08-24 10:00:00] INFO: Press Ctrl+C to stop
[2026-08-24 10:00:02] No pending commands
[2026-08-24 10:00:04] No pending commands
```

**Biarkan jendela CMD terbuka! Bridge akan terus berjalan.**

---

### **Step 9: Test dari Web Interface**

1. **Buka browser di laptop/PC mana saja**
2. **Login ke:** https://bell.mtsn2kotamalang.sch.id
3. **Masuk menu:** Hardware → Hardware Control
4. **Klik tombol ON** pada salah satu room
5. **Lihat di CMD Python Bridge** - harus muncul:

```
[2026-08-24 10:05:00] INFO: Received 1 pending command(s)
[2026-08-24 10:05:00] INFO: Processing command #123: ON for Ruang Kelas 7A (address 01)
[2026-08-24 10:05:00] INFO: Sent to address 01: 01 05 00 00 FF 00 8C 3A (ON)
[2026-08-24 10:05:00] INFO: ✓ Result reported for queue_id=123
```

6. **Speaker di Ruang Kelas 7A harus NYALA!** ✓

---

### **Step 10: Setup Auto-Start**

**Agar Python Bridge otomatis start saat Windows boot:**

1. **Tekan `Win + R`**
2. **Ketik:** `shell:startup` → Enter
3. **Copy file** `C:\BelSekolah\start_bridge.bat` ke folder yang terbuka
4. **Restart PC** - Python Bridge akan otomatis start

**Untuk test tanpa restart:**
- Tutup Python Bridge (Ctrl+C)
- Double-click file `start_bridge.bat` di folder Startup
- Harus langsung jalan

---

## 🎯 Checklist Final

- [x] Python 3.14 terinstall
- [x] Folder `C:\BelSekolah` dibuat
- [ ] File `python_bridge.py` downloaded
- [ ] File `config.json.example` downloaded
- [ ] File `start_bridge.bat` downloaded
- [ ] Dependencies installed (pyserial, requests)
- [ ] File `config.json` dibuat dan diisi
- [ ] Test koneksi server (--test-connection) ✓
- [ ] Test hardware (--test-hardware) ✓
- [ ] Bridge berjalan normal
- [ ] Test dari web interface ✓
- [ ] Auto-start configured

---

## 🔧 Troubleshooting Quick Reference

| Error | Solusi |
|-------|--------|
| "python not recognized" | Reinstall Python, centang "Add to PATH" |
| "No module named serial" | Jalankan: `pip install pyserial` |
| "No module named requests" | Jalankan: `pip install requests` |
| "Serial port cannot open" | Cek kabel USB, tutup aplikasi lain yang pakai COM5 |
| "Connection timeout" | Cek internet, ping server |
| "API Token invalid" | Cek token di config.json sama dengan server .env |
| "No response from device" | Cek wiring Modbus RS485, cek baud rate |

---

## 📞 Kontak

**Server Production:**
- URL: https://bell.mtsn2kotamalang.sch.id
- IP: 187.77.117.23

**Hardware:**
- COM Port: COM5
- Baud Rate: 9600
- Protocol: Modbus RTU (RS485)

**API Token (Rahasia):**
```
a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de
```

---

**Selamat! Setup Python Bridge selesai! 🎉**
