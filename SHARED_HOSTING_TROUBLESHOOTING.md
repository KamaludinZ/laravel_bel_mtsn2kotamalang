# Troubleshooting Deploy ke Shared Hosting

Dokumen ini berisi solusi untuk masalah umum saat deploy aplikasi Laravel ke shared hosting.

---

## ❌ Error: "Connection refused" PostgreSQL

### Penyebab:
```
SQLSTATE[08006] [7] connection to server at "127.0.0.1", port 5433 failed: Connection refused
```

Shared hosting **JARANG menyediakan PostgreSQL**. Sebagian besar shared hosting hanya menyediakan **MySQL/MariaDB**.

### Solusi: Ganti ke MySQL

#### 1. Buat Database MySQL di cPanel
1. Login cPanel → **MySQL Databases**
2. Buat database baru (contoh: `mtsnkot4_dbapp1`)
3. Buat user baru (contoh: `mtsnkot4_userdb`)
4. Tambahkan user ke database dengan **ALL PRIVILEGES**

#### 2. Update File `.env`
Edit `.env` di hosting, ubah dari PostgreSQL ke MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mtsnkot4_dbapp1
DB_USERNAME=mtsnkot4_userdb
DB_PASSWORD=password_anda
```

#### 3. Clear Config Cache
```bash
php artisan config:clear
php artisan cache:clear
```

#### 4. Jalankan Migration Ulang
```bash
php artisan migrate --force
```

---

## ✅ Kompatibilitas MySQL vs PostgreSQL

Aplikasi ini menggunakan fitur database berikut:

| Fitur | PostgreSQL | MySQL | Keterangan |
|-------|-----------|-------|------------|
| `uuid()` | ✅ Native | ✅ Laravel emulate | Laravel otomatis menyesuaikan |
| `jsonb()` | ✅ Native | ✅ `json()` | Laravel otomatis mengubah |
| `foreignUuid()` | ✅ Native | ✅ Laravel emulate | Laravel otomatis menyesuaikan |
| `boolean()` | ✅ Native | ✅ `tinyint(1)` | Laravel otomatis menyesuaikan |

**Kesimpulan:** Semua migrasi akan berjalan normal di MySQL tanpa perubahan kode!

---

## ❌ Error: "Connection refused" Meski Sudah Ganti MySQL

### Penyebab:
MySQL server tidak berjalan atau host/port salah.

### Solusi:
1. Cek di cPanel → **MySQL Databases** → lihat **hostname** yang diberikan
2. Beberapa hosting menggunakan hostname khusus, bukan `localhost`:

```env
# Coba hostname ini satu per satu:
DB_HOST=localhost
# atau
DB_HOST=127.0.0.1
# atau
DB_HOST=mysql.domainanda.com
# atau (lihat di cPanel)
DB_HOST=cpanelxx.serverhost.net
```

---

## ❌ Error: "Access denied for user"

### Penyebab:
Username/password database salah, atau user belum di-assign ke database.

### Solusi:
1. Cek ulang username dan password di cPanel → MySQL Databases
2. Pastikan user sudah ditambahkan ke database dengan privileges
3. Coba reset password user database di cPanel

---

## ❌ Error: "No application encryption key has been specified"

### Penyebab:
`APP_KEY` belum di-set di `.env`

### Solusi:
1. Generate di lokal:
   ```bash
   php artisan key:generate
   ```
2. Copy nilai `APP_KEY` dari `.env` lokal ke `.env` di hosting
3. Atau generate langsung di hosting:
   ```bash
   php artisan key:generate --force
   ```

---

## ❌ Error: "The stream or file ... could not be opened"

### Penyebab:
Folder `storage/` tidak writable.

### Solusi:
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

Atau via File Manager cPanel → Change Permissions → 775

---

## ❌ Error: "419 Page Expired" (CSRF Token Mismatch)

### Penyebab:
Session tidak tersimpan dengan benar.

### Solusi:
Pastikan di `.env`:
```env
SESSION_DRIVER=file
```

Dan folder `storage/framework/sessions/` writable:
```bash
chmod -R 775 storage/framework/sessions/
```

---

## ❌ Error: "Public assets 404 (CSS/JS tidak load)"

### Penyebab:
Folder `public/build/` tidak ada atau tidak terupload.

### Solusi:
1. Di lokal, jalankan:
   ```bash
   npm install
   npm run build
   ```
2. Upload folder `public/build/` ke hosting
3. Cek file ada di `public/build/manifest.json`

---

## ❌ Error: "Composer dependencies not found"

### Penyebab:
Folder `vendor/` tidak ada di hosting.

### Solusi:
Jika hosting tidak ada Composer:

1. Di lokal, install production dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. Upload folder `vendor/` ke hosting (besar tapi wajib)
3. Atau coba install Composer di hosting via SSH

---

## 🔍 Checklist Verifikasi Database

Setelah mengubah ke MySQL, verifikasi:

```bash
# 1. Test koneksi database
php artisan tinker
>>> DB::connection()->getPdo();
# Jika sukses, akan muncul objek PDO

# 2. Jalankan migration
php artisan migrate --force

# 3. Jalankan seeder (jika perlu data awal)
php artisan db:seed --force
```

---

## 📞 Jika Masih Bermasalah

1. **Cek log Laravel**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Cek versi PHP**:
   ```bash
   php -v
   ```
   Harus PHP 8.2+

3. **Cek extension PHP**:
   ```bash
   php -m | grep -i pdo
   ```
   Harus ada: `pdo_mysql`

4. **Kontak support hosting** untuk:
   - Hostname database yang benar
   - Port database (default 3306 untuk MySQL)
   - Apakah MySQL server berjalan

---

Semoga sukses deploy! 🚀
