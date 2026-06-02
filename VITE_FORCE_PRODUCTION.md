# Fix: Vite Masih Dev Mode Meskipun APP_ENV=production

## ❌ Penyebab

Meskipun `APP_ENV=production` sudah di-set, Laravel masih menggunakan Vite dev mode karena **config cache masih menyimpan nilai lama** atau **view cache masih menggunakan versi dev**.

---

## ✅ Solusi Lengkap

### Langkah 1: Clear SEMUA Cache

Via SSH di hosting, jalankan:

```bash
cd /home/mtsnkot4/public_html/app1.mtsn2kotamalang.sch.id

# Hapus config cache
php artisan config:clear

# Hapus view cache
php artisan view:clear

# Hapus route cache
php artisan route:clear

# Hapus application cache
php artisan cache:clear

# Hapus compiled views
php artisan optimize:clear
```

---

### Langkah 2: Verifikasi `.env` Benar-benar Terbaca

Cek nilai APP_ENV saat ini:

```bash
php artisan tinker --execute="echo env('APP_ENV');"
```

**Harus output:** `production`

Jika masih output `local`, berarti `.env` belum terbaca dengan benar.

---

### Langkah 3: Cek File `.env` Tidak Ada Spasi/Karakter Aneh

Pastikan format `.env` benar:

```env
APP_NAME="Bel Sekolah MTsN 2 Kota Malang"
APP_ENV=production
APP_KEY=base64:xxxxxxxx
APP_DEBUG=false
APP_URL=http://app1.mtsn2kotamalang.sch.id
```

**PENTING:**
- Tidak ada spasi di awal baris
- Tidak ada spasi di sekitar `=`
- Gunakan tanda petik untuk nilai dengan spasi

---

### Langkah 4: Cek File View Blade

Cek file `resources/views/layouts/app.blade.php` (atau layout utama), pastikan ada:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

**Jangan** gunakan:
```blade
@vite              ← tanpa parameter
@viteReactRefresh  ← hanya untuk React dev mode
```

---

### Langkah 5: Force Rebuild Config

Jika masih bermasalah, force rebuild:

```bash
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

---

### Langkah 6: Cek Permission Folder Storage

Pastikan cache bisa ditulis:

```bash
chmod -R 775 storage/framework/views/
chmod -R 775 storage/framework/cache/
chmod -R 775 bootstrap/cache/
```

---

## 🔍 Verifikasi

Setelah semua langkah, cek source code HTML di browser (Ctrl+U atau View Source).

### Sebelum Fix (SALAH):
```html
<script type="module" src="http://[::1]:5173/@vite/client"></script>
<script type="module" src="http://[::1]:5173/resources/js/app.js"></script>
<link rel="stylesheet" href="http://[::1]:5173/resources/css/app.css" />
```

### Setelah Fix (BENAR):
```html
<link rel="preload" as="style" href="http://app1.mtsn2kotamalang.sch.id/build/assets/app-XXXXXX.css" />
<link rel="modulepreload" href="http://app1.mtsn2kotamalang.sch.id/build/assets/app-YYYYYY.js" />
<link rel="stylesheet" href="http://app1.mtsn2kotamalang.sch.id/build/assets/app-XXXXXX.css" />
<script type="module" src="http://app1.mtsn2kotamalang.sch.id/build/assets/app-YYYYYY.js"></script>
```

---

## 🛠️ Jika Masih Tidak Berhasil

### Opsi A: Hapus Config Cache Manual

```bash
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -rf storage/framework/views/*
rm -rf storage/framework/cache/*
```

Lalu ulang:
```bash
php artisan config:cache
php artisan view:cache
```

### Opsi B: Set Environment Variable Langsung

Tambahkan di `.htaccess` root atau `public/.htaccess`:

```apache
SetEnv APP_ENV production
```

### Opsi C: Cek PHP Info

Buat file `test.php` di root:

```php
<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "APP_DEBUG: " . env('APP_DEBUG') . "\n";
```

Akses `http://app1.mtsn2kotamalang.sch.id/test.php`

---

## ✅ Checklist Akhir

| Cek | Status |
|-----|--------|
| `.env` berisi `APP_ENV=production` | ⬜ |
| `php artisan config:clear` sudah dijalankan | ⬜ |
| `php artisan view:clear` sudah dijalankan | ⬜ |
| `php artisan optimize:clear` sudah dijalankan | ⬜ |
| Folder `storage/framework/views/` writable | ⬜ |
| Source HTML browser menunjukkan `/build/assets/` | ⬜ |

---

Jalankan **Langkah 1** terlebih dahulu (`php artisan optimize:clear`), lalu refresh browser. Ini biasanya langsung memperbaiki masalah.
