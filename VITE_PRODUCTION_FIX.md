# Fix Error: Vite Dev Mode di Production

## ❌ Penyebab Error

Console error:
```
Access to script at 'http://[::1]:5173/@vite/client' from origin 'http://app1.mtsn2kotamalang.sch.id' has been blocked by CORS policy
```

Ini berarti Laravel masih menggunakan **Vite dev mode** (port 5173) di production. Seharusnya menggunakan **file build production** dari folder `public/build/`.

---

## ✅ Solusi

### Langkah 1: Cek `.env` di Hosting

Pastikan konfigurasi production:

```env
APP_ENV=production
APP_DEBUG=false
```

Jika masih `APP_ENV=local`, ubah ke `production`.

---

### Langkah 2: Jalankan Build di Lokal

Di project lokal Anda, jalankan:

```bash
npm install
npm run build
```

**Harus menghasilkan folder `public/build/`** dengan isi:
```
public/
  build/
    assets/
      app-XXXXXX.css    ← file CSS dengan hash
      app-YYYYYY.js     ← file JS dengan hash
    manifest.json        ← file mapping
```

---

### Langkah 3: Upload Folder `public/build/`

Upload folder `public/build/` ke hosting, letakkan di:
```
/home/mtsnkot4/public_html/app1.mtsn2kotamalang.sch.id/public/build/
```

Pastikan struktur di hosting:
```
public/
  build/
    assets/
      app-XXXXXX.css
      app-YYYYYY.js
    manifest.json
  index.php
  .htaccess
```

---

### Langkah 4: Clear Config Cache

Via SSH di hosting:
```bash
cd /home/mtsnkot4/public_html/app1.mtsn2kotamalang.sch.id
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

### Langkah 5: Cek File Blade

Pastikan di file layout (misal `resources/views/layouts/app.blade.php` atau `resources/views/layouts/guest.blade.php`), menggunakan:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Direktif `@vite` ini akan otomatis:
- Mode **dev**: load dari `http://localhost:5173`
- Mode **production**: load dari `public/build/` (berdasarkan `manifest.json`)

---

## 🔍 Verifikasi

Setelah fix, cek di browser:

### Sebelum Fix (SALAH):
```html
<script type="module" src="http://[::1]:5173/@vite/client"></script>
<script type="module" src="http://[::1]:5173/resources/js/app.js"></script>
<link rel="stylesheet" href="http://[::1]:5173/resources/css/app.css" />
```

### Setelah Fix (BENAR):
```html
<link rel="stylesheet" href="/build/assets/app-XXXXXX.css" />
<script type="module" src="/build/assets/app-YYYYYY.js"></script>
```

---

## 🛠️ Troubleshooting

### Jika Build Tidak Berhasil
```bash
rm -rf node_modules
rm package-lock.json
npm install
npm run build
```

### Jika Folder `public/build/` Tidak Ada
Pastikan `vite.config.js` benar:
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

### Jika Masih Error 403 pada File Build
Cek permission folder `public/build/`:
```bash
chmod -R 755 public/build/
```

---

## ✅ Checklist

| Cek | Status |
|-----|--------|
| `APP_ENV=production` di `.env` hosting | ⬜ |
| `npm run build` sudah dijalankan di lokal | ⬜ |
| Folder `public/build/` terupload ke hosting | ⬜ |
| File `public/build/manifest.json` ada | ⬜ |
| `php artisan config:clear` sudah dijalankan | ⬜ |

---

Setelah mengikuti langkah di atas, Vite akan menggunakan file build production dan error CORS akan hilang.
