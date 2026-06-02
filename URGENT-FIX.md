# URGENT FIX - Masih Load dari Vite Dev Server

## ✅ SOLUSI DITEMUKAN!

**Penyebab**: File `public/hot` masih ada di server production!

File ini dibuat oleh Vite dev server (`npm run dev`) dan memberitahu Laravel untuk load assets dari dev server, bukan dari `public/build/`.

**Solusi Cepat**:
```bash
# Di server production, hapus file hot
rm -f public/hot

# Hard refresh browser
Ctrl + F5
```

Setelah file `public/hot` dihapus, Laravel akan otomatis load dari `public/build/assets/`.

---

## Masalah Saat Ini

Dari console log:
```
GET http://[::1]:5173/@vite/client [HTTP/1.1 200 OK 29ms]
GET http://[::1]:5173/resources/css/app.css [HTTP/1.1 200 OK 15ms]
GET http://[::1]:5173/resources/js/app.js [HTTP/1.1 200 OK 14ms]
```

Dan juga:
```
GET http://app1.mtsn2kotamalang.sch.id/ [HTTP/1.1 403 Forbidden 164ms]
```

## Analisa

### Masalah 1: Vite Dev Server Masih Berjalan di Production ❌

**Status 200 OK** berarti **Vite dev server sedang berjalan** di server production. Ini **SALAH BESAR!**

Di production:
- ❌ JANGAN jalankan `npm run dev`
- ✅ Hanya perlu file hasil `npm run build`

### Masalah 2: 403 Forbidden

Permission atau web server config bermasalah.

---

## SOLUSI URGENT

### LANGKAH 1: MATIKAN Vite Dev Server di Production

SSH ke server production, lalu cari dan matikan process Vite:

```bash
# Cari process vite yang sedang berjalan
ps aux | grep vite

# Hasilnya seperti ini:
# user  12345  0.0  0.1  ... node /path/to/vite
#                              ^^^^^ catat PID ini

# Kill process vite (ganti 12345 dengan PID yang muncul)
kill -9 12345

# Atau matikan semua process vite
pkill -f vite

# Atau matikan semua process node (hati-hati jika ada aplikasi node lain!)
pkill -f "npm run dev"
```

**PENTING**: Jangan jalankan `npm run dev` lagi di production!

---

### LANGKAH 2: Pastikan File .env BENAR-BENAR Production

Di server production, cek file `.env`:

```bash
cat .env | grep -E "APP_ENV|APP_DEBUG|APP_URL"
```

Harus muncul:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=http://app1.mtsn2kotamalang.sch.id
```

Jika masih `local`, edit:
```bash
nano .env
# Ubah APP_ENV=local menjadi APP_ENV=production
# Ubah APP_DEBUG=true menjadi APP_DEBUG=false
# Save: Ctrl+O, Enter, Ctrl+X
```

---

### LANGKAH 3: Hapus Cache Config dan Rebuild

```bash
# Hapus cache bootstrap
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php

# Clear cache Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Rebuild cache
php artisan config:cache

# Verifikasi environment
php artisan tinker --execute="echo app()->environment();"
# Harus muncul: production
```

---

### LANGKAH 4: Fix Permission (untuk error 403)

```bash
# Set ownership ke web server user
sudo chown -R www-data:www-data /path/to/laravel_bel_mtsn2kotamalang
# Atau untuk Nginx:
# sudo chown -R nginx:nginx /path/to/laravel_bel_mtsn2kotamalang

# Set permission
sudo chmod -R 755 /path/to/laravel_bel_mtsn2kotamalang
sudo chmod -R 775 /path/to/laravel_bel_mtsn2kotamalang/storage
sudo chmod -R 775 /path/to/laravel_bel_mtsn2kotamalang/bootstrap/cache

# Fix SELinux (jika ada)
sudo chcon -R -t httpd_sys_rw_content_t /path/to/laravel_bel_mtsn2kotamalang/storage
```

---

### LANGKAH 5: Cek Web Server Config

#### Untuk Apache:

Pastikan DocumentRoot mengarah ke folder `public`:

```apache
<VirtualHost *:80>
    ServerName app1.mtsn2kotamalang.sch.id
    DocumentRoot /path/to/laravel_bel_mtsn2kotamalang/public

    <Directory /path/to/laravel_bel_mtsn2kotamalang/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Cek config:
```bash
# Cek config Apache
apache2ctl -S

# Cek error log
tail -f /var/log/apache2/error.log
```

#### Untuk Nginx:

```nginx
server {
    listen 80;
    server_name app1.mtsn2kotamalang.sch.id;
    root /path/to/laravel_bel_mtsn2kotamalang/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Cek config:
```bash
# Test config Nginx
sudo nginx -t

# Cek error log
tail -f /var/log/nginx/error.log
```

---

### LANGKAH 6: Restart Web Server

```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

### LANGKAH 7: Verifikasi

**1. Cek tidak ada process vite:**
```bash
ps aux | grep vite
# Seharusnya tidak ada output (kecuali grep itu sendiri)
```

**2. Cek file build ada:**
```bash
ls -la public/build/assets/
# Harus ada file .css dan .js
```

**3. Test di browser:**
- Hard refresh: `Ctrl + F5`
- Buka console (F12)
- Seharusnya load dari `/build/assets/`, BUKAN dari `[::1]:5173`

**4. Cek akses halaman:**
```bash
curl -I http://app1.mtsn2kotamalang.sch.id
# Seharusnya HTTP/1.1 200 OK, BUKAN 403
```

---

## Penjelasan Kenapa Ini Terjadi

### Kesalahan di Production:

Sepertinya di server production, ada orang yang jalankan:
```bash
npm run dev  # ← INI SALAH! Jangan lakukan di production!
```

Command ini akan:
1. ❌ Jalankan Vite dev server di port 5173
2. ❌ Watch file changes (tidak perlu di production)
3. ❌ Consume memory dan CPU terus-menerus
4. ❌ Tidak optimal untuk production

### Yang Benar di Production:

```bash
# Cukup sekali saja (di local atau saat deploy)
npm run build

# Upload folder public/build/ ke server

# TIDAK PERLU jalankan npm run dev!
```

---

## Checklist Fix

- [ ] Vite dev server sudah dimatikan (`pkill -f vite`)
- [ ] Tidak ada process vite (`ps aux | grep vite` → kosong)
- [ ] File `.env` sudah `APP_ENV=production`
- [ ] Cache sudah di-clear dan di-rebuild
- [ ] Permission sudah benar (755/775)
- [ ] Web server DocumentRoot sudah ke `/public`
- [ ] Web server sudah restart
- [ ] Browser console tidak ada `[::1]:5173` lagi
- [ ] Browser console load dari `/build/assets/`
- [ ] Tidak ada error 403 Forbidden
- [ ] CSS muncul dengan benar

---

## Jika Masih 403 Forbidden

Cek file permission lebih detail:

```bash
# Cek permission index.php
ls -la public/index.php
# Harus readable: -rw-r--r-- atau -rwxr-xr-x

# Cek web server error log
tail -f /var/log/apache2/error.log
# atau
tail -f /var/log/nginx/error.log
```

Error log akan memberitahu penyebab 403:
- Permission denied
- Directory index forbidden
- SELinux blocking
- htaccess issue

---

## Command Ringkas (Copy-Paste)

```bash
# 1. Matikan vite
pkill -f vite

# 2. Update .env (edit manual jika perlu)
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env

# 3. Clear cache
rm -f bootstrap/cache/*.php
php artisan optimize:clear
php artisan config:cache

# 4. Fix permission
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache

# 5. Restart server
sudo systemctl restart apache2
# atau
sudo systemctl restart nginx php8.2-fpm

# 6. Verifikasi
ps aux | grep vite  # harus kosong
php artisan tinker --execute="echo app()->environment();"  # harus production
```

Setelah semua langkah, hard refresh browser dan cek console!
