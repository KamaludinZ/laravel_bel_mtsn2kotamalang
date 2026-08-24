# Deployment Guide - Production Server
**Server:** https://bell.mtsn2kotamalang.sch.id
**IP:** 187.77.117.23
**Issue:** HTTP 500 pada API endpoints hardware

---

## 🔍 Root Cause Analysis

**Test Results:**
- ✅ Health check: SUCCESS (database connected)
- ❌ Get config: HTTP 500 (Server Error)
- ❌ Get pending commands: HTTP 500
- ❌ Heartbeat: HTTP 500

**Diagnosis:**
Database tables untuk hardware system belum ada di production. Migration perlu dijalankan.

---

## 📋 Required Migrations

Tables yang dibutuhkan oleh Hardware API:

1. **speaker_zones** - Master data speaker zones (8 channels)
2. **rooms** - Master data ruangan dengan zone mapping
3. **hardware_configs** - Konfigurasi COM port dan Modbus
4. **hardware_command_queue** - Queue perintah hardware
5. **hardware_logs** - Log eksekusi perintah

**Migration files:**
```
2026_08_11_102530_create_speaker_zones_table.php
2026_08_15_022549_create_rooms_table.php
2026_08_11_102513_create_hardware_configs_table.php
2026_08_11_102330_create_hardware_command_queue_table.php
2026_08_11_103015_create_hardware_logs_table.php
2026_08_23_115646_add_parent_hardware_address_to_rooms_table.php
```

---

## 🚀 Deployment Steps (DI PRODUCTION SERVER)

### **Metode A: Via SSH/Terminal (Recommended)**

**Step 1: Login ke server**
```bash
ssh user@187.77.117.23
# atau login via panel hosting
```

**Step 2: Navigate ke folder Laravel**
```bash
cd /path/to/laravel
# contoh: cd /var/www/bell.mtsn2kotamalang.sch.id
# atau: cd /home/username/public_html
```

**Step 3: Backup database (PENTING!)**
```bash
# PostgreSQL
pg_dump -U postgres -d bel_sekolah_mtsn2 > backup_$(date +%Y%m%d_%H%M%S).sql

# MySQL (jika pakai MySQL)
mysqldump -u root -p bel_sekolah_mtsn2 > backup_$(date +%Y%m%d_%H%M%S).sql
```

**Step 4: Pull latest code dari GitHub**
```bash
git fetch origin
git status
git pull origin main
```

**Step 5: Install dependencies (jika ada update)**
```bash
composer install --no-dev --optimize-autoloader
```

**Step 6: Run migrations**
```bash
php artisan migrate --force
```

Output yang diharapkan:
```
Migrating: 2026_08_11_102530_create_speaker_zones_table
Migrated:  2026_08_11_102530_create_speaker_zones_table (123.45ms)
Migrating: 2026_08_15_022549_create_rooms_table
Migrated:  2026_08_15_022549_create_rooms_table (98.76ms)
Migrating: 2026_08_11_102513_create_hardware_configs_table
Migrated:  2026_08_11_102513_create_hardware_configs_table (87.65ms)
Migrating: 2026_08_11_102330_create_hardware_command_queue_table
Migrated:  2026_08_11_102330_create_hardware_command_queue_table (112.34ms)
Migrating: 2026_08_11_103015_create_hardware_logs_table
Migrated:  2026_08_11_103015_create_hardware_logs_table (95.43ms)
Migrating: 2026_08_23_115646_add_parent_hardware_address_to_rooms_table
Migrated:  2026_08_23_115646_add_parent_hardware_address_to_rooms_table (76.54ms)
```

**Step 7: Run seeders (populate initial data)**
```bash
php artisan db:seed --class=SpeakerZoneSeeder
php artisan db:seed --class=RoomSeeder
php artisan db:seed --class=HardwareConfigSeeder
```

**Step 8: Clear cache**
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

**Step 9: Optimize untuk production**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Step 10: Verify .env file**
```bash
nano .env
# atau: vim .env
```

**Pastikan ada:**
```env
HARDWARE_BRIDGE_API_TOKEN=a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de
```

**Step 11: Set permissions (jika perlu)**
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

**Step 12: Restart web server**
```bash
# Nginx
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm

# Apache
sudo systemctl restart apache2

# Jika Docker
docker-compose restart
```

---

### **Metode B: Via cPanel/Plesk (Web Hosting)**

**Step 1: Login ke cPanel/Plesk**

**Step 2: Open Terminal (cPanel → Terminal)**

**Step 3: Jalankan commands seperti Metode A** (Step 2-12)

Atau:

**Via File Manager + phpMyAdmin:**

1. **Upload kode terbaru:**
   - Download ZIP dari GitHub
   - Extract dan upload via File Manager
   - Overwrite files lama

2. **Run migrations manual via phpMyAdmin:**
   - Buka phpMyAdmin
   - Pilih database `bel_sekolah_mtsn2`
   - Import file SQL (jika tersedia)

   Atau jalankan SQL manual dari migration files

---

### **Metode C: Via Docker (Jika menggunakan Docker)**

**Step 1: SSH ke server**

**Step 2: Navigate ke folder project**
```bash
cd /path/to/project
```

**Step 3: Pull latest code**
```bash
git pull origin main
```

**Step 4: Rebuild containers (jika perlu)**
```bash
docker-compose build --no-cache
```

**Step 5: Run migrations di container**
```bash
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --class=SpeakerZoneSeeder
docker-compose exec app php artisan db:seed --class=RoomSeeder
docker-compose exec app php artisan db:seed --class=HardwareConfigSeeder
```

**Step 6: Clear cache**
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan cache:clear
```

**Step 7: Restart containers**
```bash
docker-compose restart
```

---

## ✅ Verification (SETELAH DEPLOYMENT)

### **Test 1: Via Browser**

Buka URL berikut di browser:

```
https://bell.mtsn2kotamalang.sch.id/api/health
```

**Harus return:**
```json
{
  "status": "healthy",
  "timestamp": "...",
  "services": {
    "database": "connected",
    ...
  }
}
```

### **Test 2: Via Python Test Script (DI PC LOKAL)**

```cmd
cd C:\BelSekolahBridge
python test_api_connection.py
```

**Output yang diharapkan:**
```
============================================================
SUMMARY
============================================================
Passed: 4/4

✅ All tests passed! Bridge should work.
```

### **Test 3: Via Laravel Log**

**Di server, cek log:**
```bash
tail -f storage/logs/laravel.log
```

Tidak boleh ada error saat hit API endpoints.

---

## 🔧 Troubleshooting Deployment

### **Error: "Nothing to migrate"**

**Artinya:** Migrations sudah pernah dijalankan

**Cek:**
```bash
php artisan migrate:status
```

**Jika table sudah ada tapi masih error 500:**
- Cek Laravel log: `tail -f storage/logs/laravel.log`
- Mungkin ada error di seeder atau data

### **Error: "Database connection failed"**

**Cek .env:**
```bash
cat .env | grep DB_
```

**Test connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### **Error: "Permission denied"**

**Fix permissions:**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### **Error 500 masih muncul setelah migrate**

**Clear semua cache:**
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

**Cek log detail:**
```bash
tail -n 50 storage/logs/laravel.log
```

---

## 📊 Monitoring Post-Deployment

### **1. Cek Database Tables Created**

```bash
php artisan tinker
```

```php
>>> DB::table('speaker_zones')->count();
>>> DB::table('rooms')->count();
>>> DB::table('hardware_configs')->count();
>>> DB::table('hardware_command_queue')->count();
>>> DB::table('hardware_logs')->count();
```

Should return counts (not errors).

### **2. Cek API Endpoints via curl**

```bash
# Health check
curl https://bell.mtsn2kotamalang.sch.id/api/health

# Config (dengan auth)
curl -H "Authorization: Bearer a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de" \
     https://bell.mtsn2kotamalang.sch.id/api/hardware/config
```

### **3. Monitor Laravel Logs**

```bash
tail -f storage/logs/laravel.log
```

Saat Python Bridge connect, harus muncul log API requests.

---

## 🎯 Quick Command Reference

```bash
# Update code
git pull origin main

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --class=SpeakerZoneSeeder

# Clear cache
php artisan optimize:clear

# Restart services
sudo systemctl restart nginx php8.3-fpm

# Check logs
tail -f storage/logs/laravel.log

# Check migration status
php artisan migrate:status
```

---

## 📞 Next Steps Setelah Deployment Berhasil

1. **Test Python Bridge dari PC lokal**
   ```cmd
   python test_api_connection.py
   ```

2. **Jika semua ✅, test hardware:**
   ```cmd
   python python_bridge.py --test-hardware
   ```

3. **Run Python Bridge:**
   ```cmd
   python python_bridge.py
   ```

4. **Test dari web interface:**
   - Login ke https://bell.mtsn2kotamalang.sch.id
   - Menu Hardware → Hardware Control
   - Klik ON pada salah satu room
   - Cek speaker nyala!

---

**Setelah deployment selesai, test lagi dengan `python test_api_connection.py` dan kirim hasilnya!** 🚀
