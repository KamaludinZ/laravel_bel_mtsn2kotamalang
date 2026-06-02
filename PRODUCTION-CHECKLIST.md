# Production Deployment Checklist

## ✅ Masalah Terselesaikan

### Penyebab CSS Tidak Muncul

**Root Cause**: File `public/hot` ada di server production

File `public/hot` dibuat oleh `npm run dev` dan berisi URL Vite dev server. Keberadaan file ini membuat Laravel:
- ❌ Load assets dari `http://[::1]:5173` (Vite dev server)
- ✅ Seharusnya load dari `/build/assets/` (built files)

**Solusi**:
```bash
rm -f public/hot
```

---

## 📋 Checklist Sebelum Deploy ke Production

### 1. File Environment

- [ ] File `.env` di production:
  ```env
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=http://app1.mtsn2kotamalang.sch.id
  ```

### 2. Build Assets

- [ ] Di local, jalankan:
  ```bash
  npm run build
  ```

- [ ] Folder `public/build/` berisi:
  - `manifest.json`
  - `assets/app-*.css`
  - `assets/app-*.js`

### 3. File yang TIDAK Boleh Ada di Production

- [ ] `public/hot` - **HAPUS FILE INI!**
- [ ] `node_modules/` - Tidak perlu di-upload
- [ ] `.env.local` - Hanya untuk development

### 4. Upload ke Server

- [ ] Upload seluruh kode kecuali yang di `.gitignore`
- [ ] Upload folder `public/build/` (hasil npm build)
- [ ] Pastikan `public/hot` TIDAK ter-upload

### 5. Clear Cache di Server

```bash
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
```

### 6. Restart Web Server

```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

### 7. Verifikasi

- [ ] Browser console tidak ada error
- [ ] Assets load dari `/build/assets/`, BUKAN dari `[::1]:5173`
- [ ] CSS tampil normal
- [ ] JavaScript berfungsi tanpa error

---

## 🚨 Kesalahan yang Sering Terjadi

### Kesalahan #1: Jalankan `npm run dev` di Production ❌

```bash
# JANGAN lakukan ini di production!
npm run dev
```

**Mengapa salah**:
- Membuat file `public/hot`
- Laravel akan load dari dev server, bukan build files
- Dev server consume resource terus-menerus
- Tidak optimal untuk production

**Yang benar**:
```bash
# Di local/development, build sekali
npm run build

# Upload public/build/ ke server
# TIDAK PERLU npm run dev di server!
```

---

### Kesalahan #2: Upload File `public/hot` ❌

File `public/hot` dibuat oleh dev server. Jika ter-upload ke production:
- Laravel akan mencoba load dari `http://localhost:5173`
- CSS tidak muncul
- CORS error di console

**Solusi**:
```bash
# Di server production
rm -f public/hot

# Pastikan ada di .gitignore
grep "public/hot" .gitignore
```

---

### Kesalahan #3: Lupa Clear Cache ❌

Meskipun `.env` sudah diubah, Laravel masih baca cache lama.

**Solusi**:
```bash
# Hapus cache config manual
rm -f bootstrap/cache/config.php

# Clear semua cache
php artisan optimize:clear

# Rebuild cache
php artisan config:cache
```

---

### Kesalahan #4: Lupa Restart Web Server ❌

PHP masih menyimpan opcode cache meskipun file sudah diupdate.

**Solusi**:
```bash
sudo systemctl restart apache2
# atau
sudo systemctl restart nginx php8.2-fpm
```

---

## 🔄 Workflow Deploy yang Benar

### Development (Local)

```bash
# 1. Kerjakan fitur
# 2. Test dengan dev server
npm run dev
php artisan serve

# 3. Jika sudah OK, build untuk production
npm run build

# 4. Test dengan build files (optional)
php artisan serve
# Buka browser, pastikan load dari /build/assets/
```

### Production (Server)

```bash
# 1. Upload kode + public/build/
# (JANGAN upload public/hot, node_modules, .env)

# 2. Update .env
nano .env
# Set APP_ENV=production, APP_DEBUG=false

# 3. Clear cache
rm -f bootstrap/cache/*.php
php artisan optimize:clear
php artisan config:cache

# 4. Hapus public/hot jika ada
rm -f public/hot

# 5. Restart web server
sudo systemctl restart apache2

# 6. Test di browser
```

---

## 📊 Verifikasi Production

### Cek 1: Environment

```bash
php artisan tinker --execute="echo app()->environment();"
# Output: production
```

### Cek 2: File Hot Tidak Ada

```bash
ls public/hot
# Output: No such file or directory (ini yang benar!)
```

### Cek 3: Build Files Ada

```bash
ls -lh public/build/assets/
# Harus ada file .css dan .js
```

### Cek 4: Browser Console

Buka browser (F12), cek Network tab:

✅ **BENAR**:
```
GET /build/assets/app-CZi-tiVd.css  200 OK
GET /build/assets/app-LPz7da-H.js   200 OK
```

❌ **SALAH**:
```
GET http://[::1]:5173/resources/css/app.css  ERR_FAILED
```

### Cek 5: View Page Source

Klik kanan → View Page Source

✅ **BENAR**:
```html
<link rel="stylesheet" href="/build/assets/app-CZi-tiVd.css">
<script src="/build/assets/app-LPz7da-H.js"></script>
```

❌ **SALAH**:
```html
<script src="http://[::1]:5173/@vite/client"></script>
```

---

## 🔧 Troubleshooting

### Masih load dari [::1]:5173?

1. Cek file `public/hot` ada?
   ```bash
   ls public/hot
   ```
   Jika ada, hapus: `rm public/hot`

2. Cek environment production?
   ```bash
   cat .env | grep APP_ENV
   ```
   Harus: `APP_ENV=production`

3. Clear cache dan restart:
   ```bash
   rm -f bootstrap/cache/*.php
   php artisan optimize:clear
   sudo systemctl restart apache2
   ```

### CSS masih tidak muncul?

1. Cek build files ada?
   ```bash
   ls public/build/assets/
   ```

2. Jika tidak ada, build ulang di local:
   ```bash
   npm run build
   ```

3. Upload folder `public/build/` ke server

---

## 📚 File Dokumentasi

- **QUICK-FIX.md** - Solusi cepat 1 halaman
- **URGENT-FIX.md** - Penjelasan masalah public/hot
- **TROUBLESHOOTING.md** - Panduan troubleshooting lengkap
- **DEPLOYMENT.md** - Panduan deployment detail
- **PRODUCTION-CHECKLIST.md** - Checklist ini

---

## ✨ Summary

**3 Hal yang Wajib di Production**:

1. ✅ File `.env`: `APP_ENV=production`
2. ✅ Hapus file `public/hot`
3. ✅ Restart web server

**3 Hal yang Tidak Boleh di Production**:

1. ❌ Jangan jalankan `npm run dev`
2. ❌ Jangan upload `public/hot`
3. ❌ Jangan set `APP_ENV=local`

---

**Selamat! Aplikasi Anda sudah siap production! 🚀**
