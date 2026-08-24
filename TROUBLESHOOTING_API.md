# Troubleshooting API Connection - Python Bridge

## 🔍 Gejala: "Server Error" saat test connection

**Ping ke server berhasil, tapi Python Bridge gagal connect.**

---

## ✅ Langkah Debug (DI PC LOKAL BELL):

### **Step 1: Download File Terbaru dari GitHub**

Python Bridge baru saja diperbaiki! Download versi terbaru:

1. **Download `python_bridge.py` yang baru:**
   - Buka: https://raw.githubusercontent.com/KamaludinZ/laravel_bel_mtsn2kotamalang/main/python_bridge.py
   - Save (overwrite) ke `C:\BelSekolah\python_bridge.py`

2. **Download test script:**
   - Buka: https://raw.githubusercontent.com/KamaludinZ/laravel_bel_mtsn2kotamalang/main/test_api_connection.py
   - Save ke `C:\BelSekolah\test_api_connection.py`

---

### **Step 2: Jalankan Test Script**

```cmd
cd C:\BelSekolah
python test_api_connection.py
```

**Output yang diharapkan:**

```
============================================================
API Connection Test - MTsN 2 Kota Malang
============================================================
Server: https://bell.mtsn2kotamalang.sch.id

============================================================
Testing: Health Check
URL: https://bell.mtsn2kotamalang.sch.id/api/health
Method: GET
============================================================
Status Code: 200
Response Body:
{
  "status": "healthy",
  "timestamp": "2026-08-24T10:00:00+00:00",
  "services": {
    "database": "connected",
    "cache": "not_configured",
    "app": "running"
  }
}

✅ Health Check - SUCCESS

============================================================
Testing: Get Hardware Config
URL: https://bell.mtsn2kotamalang.sch.id/api/hardware/config
Method: GET
============================================================
Status Code: 200
...

✅ Get Hardware Config - SUCCESS

============================================================
SUMMARY
============================================================
Passed: 4/4

✅ All tests passed! Bridge should work.
```

---

### **Step 3: Interpretasi Hasil**

| Error | Penyebab | Solusi |
|-------|----------|--------|
| **401 Unauthorized** | API Token salah | Cek `config.json`, pastikan token sama dengan server `.env` |
| **404 Not Found** | Endpoint belum ada di production | Server perlu update/deploy |
| **500 Server Error** | Error di Laravel (database, dll) | Cek Laravel logs di server |
| **SSL Error** | Certificate issue | Jalankan: `pip install --upgrade certifi` |
| **Connection Timeout** | Firewall blokir | Lihat Step 4 |

---

### **Step 4: Cek Firewall Windows**

**Jika ada SSL Error atau Connection Timeout:**

1. **Buka Windows Defender Firewall:**
   - Tekan `Win + R`
   - Ketik: `firewall.cpl` → Enter

2. **Klik "Allow an app or feature through Windows Defender Firewall"**

3. **Klik "Change settings"** (perlu admin)

4. **Klik "Allow another app..."**

5. **Browse → pilih:** `C:\Users\[USERNAME]\AppData\Local\Programs\Python\Python314\python.exe`

6. **Centang:**
   - ✅ Private
   - ✅ Public

7. **Klik OK**

---

### **Step 5: Test Lagi**

```cmd
python test_api_connection.py
```

Jika semua ✅ maka:

```cmd
python python_bridge.py --test-connection
```

Harus berhasil!

---

## 🔧 Error Codes Reference

### **HTTP 401 - Unauthorized**

**Penyebab:** API Token tidak valid

**Cek di PC lokal bell (`C:\BelSekolah\config.json`):**
```json
{
  "api_token": "a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de"
}
```

**Cek di server (`.env`):**
```env
HARDWARE_BRIDGE_API_TOKEN=a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de
```

**Harus IDENTIK!** (case-sensitive, no spaces)

---

### **HTTP 404 - Not Found**

**Penyebab:** Endpoint tidak ada di server

**Solusi:** Deploy versi terbaru Laravel ke production server

```bash
# Di server production
cd /path/to/laravel
git pull origin main
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

### **HTTP 500 - Server Error**

**Penyebab:** Error di Laravel (database not found, table missing, dll)

**Debug di server:**

```bash
# Lihat log Laravel
tail -f storage/logs/laravel.log

# Atau jika Docker:
docker logs -f laravel_app --tail=50
```

**Common causes:**
- Database tidak terkoneksi
- Table `hardware_configs` belum di-migrate
- `.env` salah konfigurasi

---

### **SSL Certificate Error**

**Error message:**
```
SSLError: [SSL: CERTIFICATE_VERIFY_FAILED]
```

**Solusi:**

```cmd
pip install --upgrade certifi
pip install --upgrade urllib3
```

**Jika masih error, temporary bypass (NOT RECOMMENDED for production):**

Edit `python_bridge.py`, cari baris:
```python
response = self.session.get(url, timeout=10)
```

Tambahkan `verify=False`:
```python
response = self.session.get(url, timeout=10, verify=False)
```

⚠️ **WARNING:** Ini disable SSL verification - hanya untuk testing!

---

### **Connection Timeout**

**Error message:**
```
Timeout: HTTPSConnectionPool
```

**Cek:**

1. **Internet connection:**
   ```cmd
   ping bell.mtsn2kotamalang.sch.id
   ping 8.8.8.8
   ```

2. **Firewall:**
   - Windows Firewall blokir Python?
   - Corporate firewall blokir HTTPS?

3. **Proxy:**
   - Apakah PC pakai proxy?
   - Set environment variable:
     ```cmd
     set HTTPS_PROXY=http://proxy-address:port
     python test_api_connection.py
     ```

---

## 📞 Quick Check Checklist

Sebelum lapor error, pastikan sudah cek:

- [ ] Internet connection OK (ping berhasil)
- [ ] Python 3.11+ terinstall (`python --version`)
- [ ] Dependencies terinstall (`pip list | findstr "requests serial"`)
- [ ] File `config.json` ada dan valid
- [ ] API Token di `config.json` sama dengan server `.env`
- [ ] Firewall tidak blokir Python
- [ ] File `python_bridge.py` versi terbaru (download ulang dari GitHub)

---

## 🎯 Test Final

Setelah semua OK:

```cmd
cd C:\BelSekolah

# Test 1: API connection
python test_api_connection.py

# Test 2: Bridge connection test
python python_bridge.py --test-connection

# Test 3: Hardware test
python python_bridge.py --test-hardware

# Test 4: Run bridge
python python_bridge.py
```

Jika semua ✅ maka setup berhasil! 🎉

---

## 📝 Kirim Info Jika Masih Error

Jika masih error, screenshot/copy output dari:

```cmd
python test_api_connection.py > test_output.txt
type test_output.txt
```

Dan berikan:
1. Full output dari command di atas
2. OS version: Windows berapa?
3. Python version: `python --version`
4. Apakah pakai proxy/VPN?
5. Apakah di jaringan sekolah atau dari luar?
