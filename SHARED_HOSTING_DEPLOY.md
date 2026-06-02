# Panduan Deploy ke Shared Hosting (Subdomain)

Panduan ini khusus untuk deploy aplikasi Laravel ke **shared hosting pada subdomain** dengan struktur folder root yang menjadi satu pada folder subdomain.

---

## 📁 Struktur Folder di Shared Hosting

```
/home/username/public_html/                    ← domain utama (jangan diubah)
/home/username/public_html/subdomain/          ← subdomain Anda (DOCUMENT_ROOT)
/home/username/public_html/subdomain/app/
/home/username/public_html/subdomain/config/
/home/username/public_html/subdomain/public/   ← public folder Laravel
/home/username/public_html/subdomain/vendor/
/home/username/public_html/subdomain/...
```

**Jika hosting Anda tidak mengizinkan mengubah Document Root**, Anda perlu trik khusus (lihat bagian "Jika Document Root Tidak Bisa Diubah").

---

## 🚀 Langkah Deploy

### 1. Persiapan Local

#### a) Generate APP_KEY
```bash
php artisan key:generate
```
**Simpan/copy nilai APP_KEY yang dihasilkan.**

#### b) Build Asset (Vite)
```bash
npm install
npm run build
```
Pastikan folder `public/build/` terbentuk dengan isi:
- `manifest.json`
- File CSS dan JS dengan hash

#### c) Install Dependencies (Production)
```bash
composer install --no-dev --optimize-autoloader
```
**Jangan upload folder `vendor/`**, nanti install langsung di hosting via SSH/Composer.

---

### 2. Upload File ke Hosting

#### File WAJIB Diupload:
```
app/              ← folder aplikasi
bootstrap/        ← folder bootstrap
config/           ← folder konfigurasi
database/         ← folder database & migrations
public/           ← folder public (ini yang diakses web)
resources/        ← folder views & assets
routes/           ← folder routes
storage/          ← folder storage
vendor/           ← hasil composer install (atau install di hosting)
artisan           ← file artisan
composer.json     ← untuk install dependency di hosting
composer.lock     ← lock file composer
package.json      ← (opsional, untuk reference)
.env              ← konfigurasi environment
```

#### File TIDAK PERLU Diupload:
```
node_modules/     ← besar, tidak dibutuhkan di production
.git/             ← folder git
tests/            ← file testing
.env.example      ← contoh env (sudah punya .env)
*.md              ← file dokumentasi
DASHBOARD_FEATURES.md
DEPLOYMENT.md
PROGRESS_REPORT.md
QUICK_REFERENCE.md
QUICK_START.md
VITE_GUIDE.md
docker-compose.yml
temp_laravel/     ← folder docker
template_jadwal_bel.xlsx  ← file template lokal
test_db.php       ← file test lokal
```

---

### 3. Konfigurasi .env

Edit file `.env` di hosting dengan konfigurasi production:

```env
APP_NAME="Bel Sekolah MTsN 2 Kota Malang"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://subdomain.domainanda.com

# Database (sesuaikan dengan hosting)
DB_CONNECTION=mysql      # atau pgsql jika hosting support
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username_db
DB_PASSWORD=password_db

# Cache & Session
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# File Storage
FILESYSTEM_DISK=local
```

**PENTING:**
- `APP_DEBUG=false` (WAJIB production)
- `APP_KEY` gunakan yang sudah digenerate
- Ganti `APP_URL` dengan URL subdomain Anda
- Sesuaikan konfigurasi database dengan yang diberikan hosting

---

### 4. Setup Folder Storage

Beri permission writable pada folder:

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 775 storage/app/
chmod -R 775 storage/logs/
chmod -R 775 storage/framework/
```

Jika hosting tidak ada SSH, ubah permission via **File Manager** di cPanel:
- Klik kanan folder → Change Permissions
- Isi: `755` atau `775`

---

### 5. Buat Storage Link

Jika ada SSH access:
```bash
cd /home/username/public_html/subdomain
php artisan storage:link
```

Jika **tidak ada SSH**, buat symlink manual via PHP atau upload file langsung ke `public/storage/`.

---

### 6. Setup Database

#### Opsi A: Via SSH/Terminal
```bash
php artisan migrate --force
php artisan db:seed
```

#### Opsi B: Via phpMyAdmin / Adminer
1. Buat database di cPanel
2. Import file `database_manual.sql` (jika tersedia)
3. Atau jalankan migration satu per satu

---

### 7. Konfigurasi Web Server

#### Jika Document Root Bisa Diubah (REKOMENDASI)
Arahkan Document Root ke folder `public/`:
```
/home/username/public_html/subdomain/public/
```

#### Jika Document Root TIDAK Bisa Diubah
Buat file `.htaccess` di **root subdomain** (`/subdomain/.htaccess`):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect all traffic to public folder
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Dan pastikan `.htaccess` di folder `public/` sudah ada (sudah termasuk di Laravel).

---

## 🔒 Checklist Keamanan

### WAJIB Dicek:
- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_ENV=production` di `.env`
- [ ] `.env` tidak bisa diakses dari browser (coba akses `https://subdomain.domainanda.com/.env`)
- [ ] Folder `storage/` dan `bootstrap/cache/` writable tapi tidak executable dari web
- [ ] PHP version minimal **8.2** (cek di cPanel → Select PHP Version)
- [ ] Extension PHP aktif: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`

### Cek .env Tidak Bisa Diakses:
Akses URL ini di browser:
```
https://subdomain.domainanda.com/.env
```
**Harus mendapatkan error 403 Forbidden atau 404.**  
Jika bisa terbaca, tambahkan di `.htaccess` root:

```apache
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### Cek Folder Berbahaya Tidak Bisa Diakses:
```
https://subdomain.domainanda.com/storage/logs/laravel.log
```
**Harus error 403/404.** Jika bisa dibaca, tambahkan file `storage/logs/.htaccess`:

```apache
Order deny,allow
Deny from all
```

---

## ⚙️ PHP Requirements

Pastikan hosting support:
- **PHP 8.2+** (Laravel 12 requirement)
- Extensions: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`/`imagick`

Cek dan aktifkan via cPanel → Select PHP Version.

---

## 🛠️ Troubleshooting

### Error: "The stream or file ... could not be opened"
**Solusi:** Folder `storage/logs/` dan `storage/framework/` belum writable. Ubah permission ke 775.

### Error: "No application encryption key has been specified"
**Solusi:** APP_KEY belum di-set di `.env`. Generate di lokal lalu copy ke hosting.

### Error: "419 Page Expired" (CSRF)
**Solusi:** Pastikan `SESSION_DRIVER=file` atau `database`, dan folder `storage/framework/sessions/` writable.

### Error: "Public assets 404 (CSS/JS tidak load)"
**Solusi:** Pastikan `npm run build` sudah dijalankan di lokal, dan folder `public/build/` diupload.

### Error: "Composer dependencies not found"
**Solusi:** Jika hosting tidak support Composer, install di lokal lalu upload folder `vendor/` juga.

---

## ✅ Verifikasi Setelah Deploy

Cek semua URL ini:
1. `https://subdomain.domainanda.com/` → Halaman login/public
2. `https://subdomain.domainanda.com/login` → Form login
3. `https://subdomain.domainanda.com/dashboard` → Dashboard (setelah login)
4. `https://subdomain.domainanda.com/.env` → Harus 403 Forbidden
5. `https://subdomain.domainanda.com/storage/` → Harus 403 Forbidden

Test fitur:
- [ ] Login admin
- [ ] Upload audio file
- [ ] Buat jadwal bel
- [ ] Import Excel
- [ ] Halaman public (tanpa login)

---

## 📝 Catatan Penting

1. **Backup database secara berkala** via cPanel → Backup
2. **Jangan commit `.env` ke Git** (sudah ada di `.gitignore`)
3. **Update Laravel** secara berkala untuk patch keamanan
4. **Gunakan HTTPS** (SSL) jika tersedia di hosting
5. **Jika hosting tidak support PostgreSQL**, gunakan **MySQL** dan ubah `DB_CONNECTION=mysql` di `.env`

---

Selamat deploy! 🚀
