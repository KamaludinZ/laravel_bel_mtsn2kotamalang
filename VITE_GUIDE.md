# Panduan Vite - Development & Production

Aplikasi ini menggunakan **Vite** untuk bundling assets (CSS & JavaScript), bukan CDN hardcoded.

## ✅ Keuntungan Menggunakan Vite

1. **Development Mode**:
   - Hot Module Replacement (HMR) - perubahan langsung terlihat tanpa refresh
   - Fast rebuild
   - Development server cepat

2. **Production Mode**:
   - Assets di-bundle dan di-minify
   - Code splitting otomatis
   - Optimized untuk performance
   - Versioning/cache busting otomatis

## 🚀 Mode Development

### 1. Jalankan Vite Dev Server

Untuk development, Anda perlu menjalankan **2 terminal**:

**Terminal 1 - Laravel Server:**
```bash
cd temp_laravel
php artisan serve
```

**Terminal 2 - Vite Dev Server:**
```bash
cd temp_laravel
npm run dev
```

Vite akan berjalan di `http://localhost:5173` dan Laravel di `http://localhost:8000`.

### 2. Cara Kerjanya

Saat development mode:
- Blade directive `@vite(['resources/css/app.css', 'resources/js/app.js'])` akan load dari Vite dev server
- Setiap perubahan di CSS/JS langsung ter-update (HMR)
- Tidak perlu build manual

### 3. Assets yang Di-load

- **CSS**: `resources/css/app.css` (Tailwind CSS)
- **JS**: `resources/js/app.js` (Alpine.js, Axios)

## 📦 Mode Production

### 1. Build Assets untuk Production

Sebelum deploy ke production, build assets:

```bash
cd temp_laravel
npm run build
```

Ini akan:
- Compile dan minify semua CSS/JS
- Generate file di folder `public/build/`
- Membuat manifest file untuk cache busting

### 2. Hasil Build

Setelah build, file akan ada di:
```
public/build/
├── assets/
│   ├── app-[hash].css
│   ├── app-[hash].js
│   └── ...
└── manifest.json
```

### 3. Deploy ke Production

**Langkah Deploy:**

```bash
# 1. Set environment ke production
cp .env .env.production
# Edit .env: APP_ENV=production, APP_DEBUG=false

# 2. Install dependencies
composer install --optimize-autoloader --no-dev
npm ci --production=false

# 3. Build assets
npm run build

# 4. Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Set permissions
chmod -R 755 storage bootstrap/cache
```

## 🔧 File Konfigurasi

### vite.config.js

```javascript
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

### package.json scripts

```json
{
    "scripts": {
        "dev": "vite",           // Development mode
        "build": "vite build"    // Production build
    }
}
```

## 📝 Penggunaan di Blade Templates

### Admin Pages (app.blade.php, guest.blade.php)

```blade
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <!-- Content -->
</body>
</html>
```

### Public Page (public.blade.php)

```blade
<x-layouts.public>
    <div x-data="bellSystem()">
        <!-- Alpine.js component -->
    </div>
</x-layouts.public>
```

## 🎯 Deteksi Mode (Development vs Production)

Laravel otomatis mendeteksi mode dari environment:

**Development** (`APP_ENV=local`):
- `@vite()` load dari `http://localhost:5173`
- Assets tidak di-cache
- Source maps available

**Production** (`APP_ENV=production`):
- `@vite()` load dari `public/build/manifest.json`
- Assets versioned dengan hash
- Minified dan optimized

## ⚠️ Troubleshooting

### Error: "Vite manifest not found"

**Penyebab**: Belum build untuk production atau Vite dev server tidak berjalan

**Solusi Development:**
```bash
npm run dev
```

**Solusi Production:**
```bash
npm run build
```

### Error: Port 5173 already in use

**Solusi**:
```bash
# Kill process di port 5173
npx kill-port 5173

# Atau gunakan port lain di vite.config.js
export default defineConfig({
    server: {
        port: 5174
    },
    // ...
});
```

### Hot reload tidak berfungsi

**Checklist**:
1. Pastikan `npm run dev` berjalan
2. Cek tidak ada error di console browser
3. Clear browser cache
4. Restart Vite dev server

## 📊 Perbandingan CDN vs Vite

| Aspek | CDN (Lama) | Vite (Baru) |
|-------|------------|-------------|
| Speed Development | ❌ Lambat (load dari internet) | ✅ Cepat (HMR) |
| Production Size | ❌ Besar (load full library) | ✅ Kecil (tree-shaking) |
| Cache Control | ❌ Tergantung CDN | ✅ Full control |
| Offline Development | ❌ Tidak bisa | ✅ Bisa |
| Customization | ❌ Terbatas | ✅ Fully customizable |

## 🔐 Security Best Practices

1. **Jangan commit folder build/**
   - Sudah ada di `.gitignore`
   - Build di server production

2. **Minify di Production**
   - Vite otomatis minify saat build
   - Hapus console.log di production

3. **Use HTTPS di Production**
   - Vite manifest URL akan otomatis adjust

## 📚 Resources

- [Laravel Vite Documentation](https://laravel.com/docs/vite)
- [Vite Documentation](https://vitejs.dev)
- [Tailwind CSS](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)

---

**Summary**: Selalu jalankan `npm run dev` saat development, dan `npm run build` sebelum deploy ke production!
