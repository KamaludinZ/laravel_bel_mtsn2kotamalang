# Python Bridge Setup - Production Guide
**Server:** https://bell.mtsn2kotamalang.sch.id
**IP:** 187.77.117.23
**Tanggal:** 24 Agustus 2026

---

## 🎯 Ringkasan
Python Bridge adalah service yang berjalan di PC lokal yang terhubung dengan hardware Modbus RS485. Service ini berkomunikasi dengan server Laravel untuk:
- Mengambil perintah hardware yang perlu dieksekusi
- Mengirim perintah ke Modbus RS485
- Melaporkan hasil eksekusi kembali ke server

---

## 📋 Langkah 1: Install Python

### Windows:
1. **Download Python 3.11+**
   - Buka: https://www.python.org/downloads/
   - Download "Python 3.11.x" atau lebih baru

2. **Install Python**
   - **PENTING**: Centang ✅ "Add Python to PATH"
   - Klik "Install Now"

3. **Verifikasi Instalasi**
   ```cmd
   python --version
   ```
   Output: `Python 3.11.x`

---

## 📋 Langkah 2: Install Dependencies

Buka Command Prompt (CMD) dan jalankan:

```cmd
pip install pyserial requests
```

**Verifikasi:**
```cmd
pip list | findstr serial
pip list | findstr requests
```

---

## 📋 Langkah 3: Download Python Bridge Script

### Opsi A: Manual Download (dari GitHub)
1. Buka: https://github.com/YOUR_USERNAME/YOUR_REPO
2. Download file `python_bridge.py`
3. Simpan di folder, misal: `C:\BelSekolah\`

### Opsi B: Copy Script (lihat section Script di bawah)

---

## 📋 Langkah 4: Buat File Konfigurasi

Buat file `config.json` di folder yang sama dengan `python_bridge.py`:

```json
{
  "vps_base_url": "https://bell.mtsn2kotamalang.sch.id",
  "api_token": "a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de",
  "com_port": "COM3",
  "poll_interval": 2,
  "baud_rate": 9600,
  "timeout": 1
}
```

**⚠️ PENTING - Sesuaikan:**
- `com_port`: Ganti dengan COM port Anda (cek di Device Manager)
- `api_token`: Harus sama dengan `HARDWARE_BRIDGE_API_TOKEN` di server

---

## 📋 Langkah 5: Cek COM Port

### Cara Cek COM Port di Windows:
1. Tekan `Win + X` → pilih **Device Manager**
2. Expand **Ports (COM & LPT)**
3. Lihat nama device Anda, misal:
   - `USB-SERIAL CH340 (COM3)` → berarti COM port = **COM3**
   - `Prolific USB-to-Serial Comm Port (COM4)` → berarti COM port = **COM4**

**Update di `config.json`:**
```json
{
  "com_port": "COM3"  ← Ganti sesuai hasil cek
}
```

---

## 📋 Langkah 6: Test Koneksi ke Server

Sebelum test hardware, pastikan koneksi ke server OK:

```cmd
cd C:\BelSekolah
python python_bridge.py --test-connection
```

**Output yang diharapkan:**
```
✓ Koneksi ke server OK
✓ API Token valid
✓ Endpoint tersedia
```

**Jika Error:**
- Periksa internet connection
- Pastikan firewall tidak memblokir
- Cek API token sudah benar

---

## 📋 Langkah 7: Test Hardware (Modbus RS485)

```cmd
python python_bridge.py --test-hardware
```

**Output yang diharapkan:**
```
✓ COM3 tersedia
✓ Serial connection OK
✓ Test command sent to address 01
```

**Jika Error "COM port not found":**
- Periksa kabel USB terhubung
- Cek COM port di Device Manager
- Update `config.json` dengan COM port yang benar

---

## 📋 Langkah 8: Jalankan Python Bridge

```cmd
python python_bridge.py
```

**Output normal:**
```
[2026-08-24 10:00:00] Python Bridge Started
[2026-08-24 10:00:00] Polling server: https://bell.mtsn2kotamalang.sch.id
[2026-08-24 10:00:00] COM Port: COM3
[2026-08-24 10:00:02] No pending commands
[2026-08-24 10:00:04] No pending commands
```

**Ketika ada perintah:**
```
[2026-08-24 10:05:00] Command received: ON channel 01
[2026-08-24 10:05:00] Sending to Modbus: 01 05 00 00 FF 00 8C 3A
[2026-08-24 10:05:01] ✓ Success - reported to server
```

---

## 📋 Langkah 9: Setup Auto-Start (Windows)

### Buat File Batch (.bat):
Buat file `start_bridge.bat`:

```batch
@echo off
cd C:\BelSekolah
python python_bridge.py
pause
```

### Tambahkan ke Windows Startup:
1. Tekan `Win + R`
2. Ketik: `shell:startup` → Enter
3. Copy file `start_bridge.bat` ke folder yang terbuka
4. Python Bridge akan otomatis start saat Windows boot

**Alternatif - Install sebagai Windows Service:**
```cmd
pip install pywin32
python python_bridge.py --install-service
```

---

## 🔧 Troubleshooting

### 1. "python not recognized"
**Solusi:**
- Reinstall Python, centang "Add to PATH"
- Atau gunakan: `py` instead of `python`

### 2. "Serial port cannot open"
**Solusi:**
- Pastikan tidak ada aplikasi lain yang pakai COM port
- Restart PC
- Cek kabel USB terhubung

### 3. "Connection timeout"
**Solusi:**
- Cek internet connection
- Ping server: `ping 187.77.117.23`
- Periksa firewall

### 4. "API Token invalid"
**Solusi:**
- Pastikan token di `config.json` sama dengan di server `.env`
- Copy-paste token, jangan ketik manual

---

## 📊 Monitoring

### Via Web Interface:
1. Login ke: https://bell.mtsn2kotamalang.sch.id
2. Menu: **Hardware** → **Logs**
3. Lihat eksekusi command real-time

### Via Command Line:
```cmd
# Lihat log Python Bridge
python python_bridge.py --show-logs

# Test manual trigger
python python_bridge.py --manual-test --address 01 --command ON
```

---

## 🔐 Keamanan

1. **API Token** adalah rahasia - jangan share ke publik
2. **Backup config.json** ke tempat aman
3. **Update Python** secara berkala:
   ```cmd
   python -m pip install --upgrade pip
   pip install --upgrade pyserial requests
   ```

---

## 📞 Dukungan

Jika ada masalah:
1. Cek log di web interface
2. Cek output Python Bridge di CMD
3. Screenshot error dan dokumentasikan
4. Periksa dokumentasi ini step-by-step

---

**Next Steps:**
- [ ] Install Python ✓
- [ ] Install dependencies ✓
- [ ] Setup config.json ✓
- [ ] Test connection ✓
- [ ] Test hardware ✓
- [ ] Run bridge ✓
- [ ] Setup auto-start ✓
