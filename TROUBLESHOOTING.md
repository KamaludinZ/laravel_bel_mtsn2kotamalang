# Troubleshooting Guide

## CSS Tidak Muncul di Production

### Masalah
Ketika aplikasi di-deploy ke production di `http://app1.mtsn2kotamalang.sch.id`, CSS tidak muncul dan tampilan berantakan.

Di browser console muncul error:
```
Access to script at 'http://[::1]:5173/@vite/client' from origin 'http://app1.mtsn2kotamalang.sch.id' has been blocked by CORS policy
GET http://[::1]:5173/@vite/client net::ERR_FAILED
GET http://[::1]:5173/resources/css/app.css net::ERR_FAILED
```

### Penjelasan Masalah

Laravel menggunakan Vite untuk mengelola assets (CSS dan JavaScript). Vite memiliki 2 mode:

#### Mode Development (Local)
- Environment: `APP_ENV=local` atau `development`
- Cara kerja: Vite dev server berjalan di `http://localhost:5173` atau `http://[::1]:5173`
- Laravel load assets langsung dari Vite dev server
- Hot reload aktif (perubahan terlihat langsung)
- Command: `npm run dev` harus berjalan

#### Mode Production
- Environment: `APP_ENV=production`
- Cara kerja: Laravel load assets dari file yang sudah di-build di `public/build/assets/`
- File sudah di-minify dan di-optimize
- TIDAK butuh Vite dev server
- Command: `npm run build` cukup dijalankan sekali

### Penyebab Error

Error terjadi karena di server production:
1. File `.env` masih `APP_ENV=local` (seharusnya `production`)
2. Laravel masih mencoba akses Vite dev server di `http://[::1]:5173`
3. Vite dev server tidak berjalan (dan memang tidak boleh berjalan) di production
4. CORS error muncul karena browser memblokir akses cross-origin ke localhost

### ⚠️ MASALAH: Sudah Update .env Tapi Masih Error!

Jika Anda sudah mengubah `.env` ke `APP_ENV=production` tapi masih muncul error `http://[::1]:5173`, ini berarti **Laravel masih membaca konfigurasi yang ter-cache**.

**SOLUSI PASTI:**

Jalankan script otomatis ini di server:
```bash
php fix-production.php
```

Script ini akan:
1. ✅ Verifikasi file `.env`
2. ✅ Clear SEMUA cache (termasuk bootstrap cache)
3. ✅ Delete file cache secara manual
4. ✅ Re-cache dengan config baru
5. ✅ Verifikasi environment sudah benar

**Atau secara manual:**

```bash
# 1. Hapus cache bootstrap (PENTING!)
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php

# 2. Clear semua cache Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear

# 3. Re-cache dengan config baru
php artisan config:cache

# 4. RESTART WEB SERVER (SANGAT PENTING!)
# Pilih salah satu sesuai server Anda:
sudo systemctl restart apache2      # Untuk Apache
sudo systemctl restart nginx         # Untuk Nginx
sudo systemctl restart php8.2-fpm    # Untuk PHP-FPM
```

**PENTING**: File `bootstrap/cache/config.php` menyimpan cache konfigurasi. Jika file ini tidak dihapus, Laravel akan terus menggunakan `APP_ENV=local` yang lama meskipun `.env` sudah diubah!

---

### Solusi Lengkap (Step by Step)

#### Langkah 1: Update File `.env` di Server Production

Edit file `.env` di server production:

```env
# SEBELUM (SALAH)
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# SESUDAH (BENAR)
APP_ENV=production
APP_DEBUG=false
APP_URL=http://app1.mtsn2kotamalang.sch.id
```

**PENTING**:
- `APP_ENV` harus `production`, BUKAN `local` atau `development`
- `APP_DEBUG` harus `false` untuk keamanan
- `APP_URL` harus sesuai dengan domain production

#### Langkah 2: Build Assets di Local/Development

Di komputer lokal, jalankan:

```bash
npm run build
```

Ini akan menghasilkan:
- `public/build/manifest.json` - File mapping
- `public/build/assets/app-[hash].css` - CSS yang sudah di-minify
- `public/build/assets/app-[hash].js` - JavaScript yang sudah di-minify

#### Langkah 3: Upload ke Server Production

Upload folder `public/build/` beserta seluruh isinya ke server production.

Struktur yang harus ada di server:
```
public/
├── build/
│   ├── manifest.json
│   └── assets/
│       ├── app-CZi-tiVd.css
│       └── app-LPz7da-H.js
├── storage/
└── index.php
```

#### Langkah 4: Clear Cache di Server

SSH ke server dan jalankan:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Atau jika ingin optimize:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Langkah 5: Verifikasi

1. Refresh browser dengan hard reload: `Ctrl + F5` (Windows) atau `Cmd + Shift + R` (Mac)
2. Buka browser console (F12)
3. Cek Network tab, seharusnya load dari `/build/assets/app-xxx.css`
4. TIDAK ada error ke `http://[::1]:5173`

### Cara Mengecek Mode Saat Ini

#### Cek di Blade Template

Tambahkan kode ini sementara di blade file untuk debug:

```blade
<!-- Debug Mode -->
<div style="background: yellow; padding: 10px;">
    Environment: {{ app()->environment() }}<br>
    Debug: {{ config('app.debug') ? 'true' : 'false' }}<br>
    URL: {{ config('app.url') }}
</div>
```

Jika muncul:
- `Environment: production` → ✅ BENAR
- `Environment: local` → ❌ SALAH, ubah .env

#### Cek di Source Code Browser

1. Buka halaman di browser
2. Klik kanan → View Page Source
3. Cari tag `<script>` dan `<link>`

**Mode Development (SALAH untuk production)**:
```html
<script type="module" src="http://[::1]:5173/@vite/client"></script>
<link rel="stylesheet" href="http://[::1]:5173/resources/css/app.css">
```

**Mode Production (BENAR)**:
```html
<link rel="stylesheet" href="http://app1.mtsn2kotamalang.sch.id/build/assets/app-CZi-tiVd.css">
<script type="module" src="http://app1.mtsn2kotamalang.sch.id/build/assets/app-LPz7da-H.js"></script>
```

### Penjelasan Teknis

#### Bagaimana Laravel Tahu Mode Mana yang Digunakan?

File: `resources/views/layouts/app.blade.php` dan `resources/views/layouts/public.blade.php`

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Directive `@vite()` ini secara otomatis:

1. **Jika `APP_ENV=local` atau `development`**:
   - Cek apakah Vite dev server berjalan di `http://[::1]:5173`
   - Jika ya, load langsung dari dev server
   - Jika tidak, fallback ke file build

2. **Jika `APP_ENV=production`**:
   - Langsung load dari `public/build/manifest.json`
   - Baca mapping file hash
   - Load file CSS/JS yang sudah di-build

#### Kenapa Perlu Build?

Vite melakukan:
- **Minification**: Menghapus whitespace, comment, shorten variable names
- **Tree Shaking**: Menghapus kode yang tidak terpakai
- **Code Splitting**: Memisahkan kode menjadi chunk optimal
- **CSS Purging**: Tailwind hanya include class yang dipakai (72KB dari ~3MB)
- **Hashing**: Filename dengan hash untuk cache busting

Hasil:
- Development: `~3MB` Tailwind CSS
- Production: `~72KB` minified CSS (96% lebih kecil!)

### Kesalahan Umum

#### ❌ Menjalankan `npm run dev` di Production
```bash
# JANGAN lakukan ini di production!
npm run dev
```
Vite dev server hanya untuk development, bukan production.

#### ❌ Lupa Build Sebelum Upload
Upload code tanpa build = CSS tidak ada.

#### ❌ Upload `node_modules/`
Folder ini tidak perlu di-upload (berat dan tidak efisien).

#### ❌ `.env` Production Sama dengan Local
Production harus `APP_ENV=production`, bukan `local`.

### Checklist Deploy

Sebelum deploy, pastikan:

- [ ] File `.env` di production: `APP_ENV=production`
- [ ] File `.env` di production: `APP_DEBUG=false`
- [ ] File `.env` di production: `APP_URL` sesuai domain
- [ ] Sudah run `npm run build` di local
- [ ] Folder `public/build/` sudah di-upload
- [ ] Sudah run `php artisan config:clear` di server
- [ ] Tidak ada error di browser console
- [ ] CSS muncul dengan benar

### Jika Masih Bermasalah

1. **Hard delete browser cache**:
   ```
   Chrome: Ctrl+Shift+Del → Clear All
   Firefox: Ctrl+Shift+Del → Clear All
   ```

2. **Cek file benar-benar ada**:
   ```bash
   ls -la public/build/assets/
   # Harus ada file .css dan .js
   ```

3. **Cek permissions**:
   ```bash
   chmod -R 755 public/build
   ```

4. **Rebuild ulang**:
   ```bash
   rm -rf public/build
   npm run build
   ```

5. **Clear semua cache Laravel**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   php artisan route:clear
   php artisan optimize:clear
   ```

### Resource Tambahan

- [Laravel Vite Documentation](https://laravel.com/docs/vite)
- [Vite Documentation](https://vite.dev/)
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Panduan lengkap deployment

---

**Catatan**: Setelah perbaikan, JANGAN lupa test semua fitur untuk memastikan semuanya berfungsi!
