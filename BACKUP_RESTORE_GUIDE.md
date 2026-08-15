# 📦 Panduan Backup & Restore - MTsN 2 Kota Malang Bell System

## Daftar Isi
1. [Overview](#overview)
2. [Cara Membuat Backup](#cara-membuat-backup)
3. [Cara Restore Backup](#cara-restore-backup)
4. [Tipe Backup](#tipe-backup)
5. [Jadwal Backup yang Direkomendasikan](#jadwal-backup-yang-direkomendasikan)
6. [Troubleshooting](#troubleshooting)

---

## 📖 Overview

Fitur Backup & Restore menyediakan perlindungan data lengkap untuk aplikasi bel sekolah, termasuk:

### Yang Di-backup:
**Database (PostgreSQL/MySQL):**
- Users & authentication
- Bell schedules (jadwal bel)
- Bell types (jenis bel)
- Audio library (file audio metadata)
- Hardware configurations
- Speaker zones & rooms
- Hardware logs
- Settings

**Files:**
- `.env` (environment configuration)
- `storage/app/public/audio/*` (file audio MP3)
- `storage/app/public/logos/*` (logo aplikasi)

### Fitur:
- ✅ 3 Tipe Backup (Full, Database Only, Files Only)
- ✅ Automatic file listing dengan size & date
- ✅ One-click download
- ✅ Safe restore dengan confirmation
- ✅ Auto cache clear setelah restore
- ✅ Support PostgreSQL & MySQL

---

## 🔧 Cara Membuat Backup

### Via Web UI (Recommended)

1. **Login sebagai Admin**
2. **Buka Settings** (menu navigasi)
3. **Klik tab "Backup & Restore"**
4. **Pilih Tipe Backup:**
   - **Full Backup** - Database + Files (Direkomendasikan)
   - **Database Only** - Hanya data jadwal, users, dll
   - **Files Only** - Hanya .env, audio, logos
5. **Klik "Buat Backup Sekarang"**
6. **Tunggu proses selesai** (biasanya 10-60 detik tergantung ukuran data)
7. **Download backup** dan simpan di lokasi aman

### Via Command Line (Manual)

#### Backup Database PostgreSQL:
```bash
# SSH ke server
cd /path/to/aplikasi

# Backup database
pg_dump -h localhost -p 5432 -U username -d database_name > backup_$(date +%Y-%m-%d_%H%M%S).sql

# Download ke lokal
scp user@server:/path/backup_*.sql ./
```

#### Backup Files:
```bash
# Backup important files
tar -czf files_backup_$(date +%Y-%m-%d_%H%M%S).tar.gz \
    .env \
    storage/app/public/audio \
    storage/app/public/logos

# Download
scp user@server:/path/files_backup_*.tar.gz ./
```

---

## 🔄 Cara Restore Backup

### ⚠️ PERINGATAN PENTING!
- **Restore akan MENIMPA semua data yang ada**
- **Proses tidak dapat dibatalkan**
- **SELALU buat backup terbaru sebelum restore**

### Via Web UI

1. **Login sebagai Admin**
2. **Buka Settings → Backup & Restore**
3. **Pilih backup dari daftar**
4. **Klik tombol "Restore"** (kuning)
5. **Baca peringatan di modal dengan teliti**
6. **Centang checkbox:** "Saya mengerti dan ingin melanjutkan restore"
7. **Klik "Ya, Restore Sekarang"**
8. **Tunggu proses selesai** (20-120 detik)
9. **Logout dan login kembali** jika diperlukan

### Via Command Line (Manual)

#### Restore Database PostgreSQL:
```bash
# SSH ke server
cd /path/to/aplikasi

# Stop aplikasi (jika menggunakan queue workers)
php artisan queue:restart

# Restore database
psql -h localhost -p 5432 -U username -d database_name < backup_file.sql

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart aplikasi
systemctl restart php-fpm  # atau service yang sesuai
```

#### Restore Files:
```bash
# Extract files
tar -xzf files_backup.tar.gz -C /path/to/aplikasi

# Fix permissions
chown -R www-data:www-data storage/
chmod -R 755 storage/
```

---

## 📁 Tipe Backup

### 1. Full Backup (Recommended)
**File:** `database_YYYY-MM-DD_HHiiss.sql` + `files_YYYY-MM-DD_HHiiss.zip`

**Berisi:**
- Database lengkap (semua tabel)
- .env file
- Audio files (MP3)
- Logo aplikasi

**Kapan digunakan:**
- Backup rutin mingguan/bulanan
- Sebelum update besar
- Sebelum migrasi server
- Recovery penuh jika terjadi disaster

**Ukuran:** ~50MB - 500MB (tergantung jumlah audio)

---

### 2. Database Only
**File:** `database_YYYY-MM-DD_HHiiss.sql`

**Berisi:**
- Semua tabel database
- Jadwal bel
- Users & authentication
- Bell types
- Audio metadata (bukan file MP3)
- Hardware config
- Settings

**Kapan digunakan:**
- Backup harian untuk data yang sering berubah
- Testing restore tanpa overwrite files
- Rollback data setelah kesalahan input

**Ukuran:** ~1MB - 50MB

---

### 3. Files Only
**File:** `files_YYYY-MM-DD_HHiiss.zip`

**Berisi:**
- .env (database credentials, app keys)
- storage/app/public/audio/* (MP3 files)
- storage/app/public/logos/* (logo)

**Kapan digunakan:**
- Setelah upload audio baru
- Setelah ganti logo
- Backup files tanpa mengganggu database

**Ukuran:** ~10MB - 400MB (tergantung jumlah audio)

---

## 📅 Jadwal Backup yang Direkomendasikan

### Deployment Production

#### Backup Otomatis (via Cron)

**Harian - Database Only:**
```bash
# /etc/cron.d/backup-daily
0 2 * * * www-data cd /var/www/html && php artisan backup:run --only-db
```

**Mingguan - Full Backup:**
```bash
# /etc/cron.d/backup-weekly
0 3 * * 0 www-data cd /var/www/html && php artisan backup:run --only-files
```

#### Backup Manual

**Sebelum Update/Maintenance:**
```
Full Backup → Download → Simpan di komputer lokal
```

**Setelah Perubahan Besar:**
- Tambah banyak jadwal baru → Database Only
- Upload audio baru → Files Only
- Update pengaturan penting → Full Backup

#### Retention Policy

**Simpan:**
- Daily backup: 7 hari terakhir
- Weekly backup: 4 minggu terakhir
- Monthly backup: 12 bulan terakhir
- Pre-update backup: Selamanya (simpan eksternal)

---

## 🆘 Troubleshooting

### Problem: Backup gagal - "pg_dump: command not found"

**Solusi:**
```bash
# Install PostgreSQL client tools
sudo apt-get update
sudo apt-get install postgresql-client

# Verify
which pg_dump
```

### Problem: Backup gagal - "Permission denied"

**Solusi:**
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/app/backups

# Buat direktori jika belum ada
mkdir -p storage/app/backups
chmod 755 storage/app/backups
```

### Problem: Restore gagal - "Access denied for user"

**Solusi:**
Check kredensial database di `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Problem: File backup terlalu besar (>500MB)

**Solusi 1: Backup terpisah**
```bash
# Backup database saja dulu
Tipe: Database Only

# Backup files terpisah
Tipe: Files Only

# Download keduanya
```

**Solusi 2: Compress audio files**
```bash
# Convert audio ke bitrate lebih rendah (optional)
ffmpeg -i input.mp3 -b:a 128k output.mp3
```

### Problem: Restore database stuck/timeout

**Solusi:**
```bash
# Restore via command line lebih cepat
psql -h localhost -U username -d database < backup.sql

# Atau split file besar
split -l 10000 backup.sql backup_part_
psql -h localhost -U username -d database < backup_part_aa
psql -h localhost -U username -d database < backup_part_ab
# dst...
```

### Problem: Setelah restore, audio tidak terdengar

**Check:**
1. Files audio sudah ter-restore? Check `storage/app/public/audio/`
2. Symbolic link sudah benar? `php artisan storage:link`
3. Permissions sudah benar? `chmod -R 755 storage/`
4. Browser cache? Hard refresh (Ctrl+F5)

### Problem: Setelah restore, tidak bisa login

**Solusi:**
```bash
# Reset password admin via tinker
php artisan tinker

# Jalankan:
$user = App\Models\User::where('email', 'admin@example.com')->first();
$user->password = Hash::make('password_baru');
$user->save();
```

---

## 🔐 Best Practices

### 1. Simpan Backup di Multiple Lokasi
- ✅ Server lokal (storage/app/backups)
- ✅ Download ke komputer
- ✅ Cloud storage (Google Drive, Dropbox)
- ✅ External HDD

### 2. Test Restore Secara Berkala
```
Setiap bulan:
1. Buat backup baru
2. Restore di environment testing
3. Verifikasi semua data dan fitur berfungsi
4. Dokumentasikan hasil test
```

### 3. Enkripsi Backup Sensitive
```bash
# Encrypt backup dengan password
gpg -c backup_file.sql
# Output: backup_file.sql.gpg

# Decrypt saat perlu restore
gpg -d backup_file.sql.gpg > backup_file.sql
```

### 4. Monitor Backup Success
- Check email notification setelah cron backup
- Review backup logs: `storage/logs/laravel.log`
- Verify backup file size tidak 0 bytes

### 5. Dokumentasi
```
Catat di dokumen terpisah:
- Tanggal backup
- Tipe backup (full/database/files)
- Ukuran file
- Lokasi penyimpanan
- Alasan backup (rutin/sebelum update/dll)
```

---

## 📞 Need Help?

Jika masih ada masalah:

1. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check Web Server Logs:**
   ```bash
   # Nginx
   tail -f /var/log/nginx/error.log

   # Apache
   tail -f /var/log/apache2/error.log
   ```

3. **Test Database Connection:**
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

4. **Contact Developer:**
   - Sertakan error message lengkap
   - Sertakan Laravel version: `php artisan --version`
   - Sertakan PHP version: `php -v`
   - Sertakan database type & version
