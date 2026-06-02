# QUICK FIX - CSS Tidak Muncul di Production

## Masalah
```
GET http://[::1]:5173/resources/css/app.css net::ERR_FAILED
GET http://[::1]:5173/@vite/client net::ERR_FAILED
```

## Penyebab
Laravel masih membaca **config yang ter-cache** dengan `APP_ENV=local`, meskipun file `.env` sudah diubah ke `production`.

## Solusi (Pilih salah satu)

### Opsi 1: Script Otomatis (RECOMMENDED)

Upload file `fix-production.php` ke server, lalu jalankan:

```bash
php fix-production.php
```

Kemudian **RESTART web server**:
```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

### Opsi 2: Manual Command

Jalankan perintah ini **SATU PER SATU** di server production:

```bash
# 1. HAPUS cache bootstrap (INI KUNCI NYA!)
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php

# 2. Clear cache Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 3. Re-cache config baru
php artisan config:cache

# 4. RESTART web server
sudo systemctl restart apache2
# ATAU
sudo systemctl restart nginx && sudo systemctl restart php8.2-fpm
```

---

### Opsi 3: Windows Server

Jika di Windows dengan IIS/XAMPP:

```powershell
# 1. Delete cache files
del bootstrap\cache\config.php
del bootstrap\cache\services.php

# 2. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Re-cache
php artisan config:cache

# 4. Restart IIS atau XAMPP
iisreset
# ATAU restart Apache/PHP dari XAMPP Control Panel
```

---

## Verifikasi

Setelah restart server, cek di browser:

1. **Hard refresh**: `Ctrl + F5`
2. **Buka Console** (F12)
3. **Cek Network tab**

✅ **BENAR** (seharusnya):
```
GET /build/assets/app-CZi-tiVd.css  200 OK
GET /build/assets/app-LPz7da-H.js   200 OK
```

❌ **SALAH** (jika masih seperti ini):
```
GET http://[::1]:5173/resources/css/app.css  ERR_FAILED
```

---

## Jika Masih Gagal

### Cek 1: File .env benar-benar production?

```bash
cat .env | grep APP_ENV
```

Harus muncul:
```
APP_ENV=production
```

Jika masih `local`, edit file `.env`:
```bash
nano .env
# Ubah APP_ENV=local menjadi APP_ENV=production
# Save: Ctrl+O, Enter, Ctrl+X
```

### Cek 2: File build sudah ada?

```bash
ls -la public/build/assets/
```

Harus ada file `.css` dan `.js`. Jika tidak ada:
1. Di local: `npm run build`
2. Upload folder `public/build/` ke server

### Cek 3: Permission benar?

```bash
chmod -R 755 public/build
```

### Cek 4: Web server sudah restart?

**INI SERING TERLUPA!**

Meskipun cache sudah di-clear, PHP masih menyimpan opcode cache. Harus restart web server:

```bash
# Apache
sudo systemctl status apache2
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl status php8.2-fpm
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

## Root Cause Analysis

```
┌─────────────────────────────────────┐
│ .env file                           │
│ APP_ENV=production                  │
└─────────────────┬───────────────────┘
                  │
                  ▼
          Tapi Laravel baca dari
                  │
                  ▼
┌─────────────────────────────────────┐
│ bootstrap/cache/config.php          │◄─── FILE INI YANG BERMASALAH!
│ (cached config dengan APP_ENV=local)│     Harus dihapus manual!
└─────────────────────────────────────┘
```

**Jadi:**
- ✅ Hapus `bootstrap/cache/config.php`
- ✅ Run `php artisan config:cache` (generate cache baru)
- ✅ Restart web server
- ✅ Hard refresh browser

---

## Checklist

- [ ] File `.env` sudah `APP_ENV=production`
- [ ] File `bootstrap/cache/config.php` sudah dihapus
- [ ] Sudah run `php artisan config:clear`
- [ ] Sudah run `php artisan config:cache`
- [ ] Web server sudah di-restart
- [ ] Browser sudah hard refresh (Ctrl+F5)
- [ ] Folder `public/build/` sudah ada di server
- [ ] File `public/build/assets/app-*.css` ada
- [ ] File `public/build/assets/app-*.js` ada

---

**Jika semua sudah dilakukan tapi masih error, hubungi developer dengan screenshot:**
1. Output dari: `php artisan config:show app.env`
2. Output dari: `ls -la public/build/assets/`
3. Screenshot browser console (F12)
