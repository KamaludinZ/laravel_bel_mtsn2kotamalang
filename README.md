# Aplikasi Bel Sekolah MTsN 2 Kota Malang

Aplikasi berbasis web untuk manajemen jadwal bel sekolah otomatis.

## Fitur Utama

- Manajemen Audio (MP3/WAV)
- Jenis Bel dengan hari aktif (JSONB)
- Jadwal Bel dengan import/export Excel
- Halaman Public dengan jam digital dan auto-play
- Settings (Nama & Logo)
- Authentication (Laravel Breeze)

## Instalasi

### 1. Install Dependencies
```bash
cd temp_laravel
composer install
npm install
```

### 2. Jalankan PostgreSQL (Docker)
```bash
docker-compose up -d
```

### 3. Storage Link
```bash
php artisan storage:link
```

### 4. Development Mode (2 Terminal)

**Terminal 1 - Laravel:**
```bash
php artisan serve
```

**Terminal 2 - Vite (Hot Reload):**
```bash
npm run dev
```

Buka: http://localhost:8000

### 5. Production Build
```bash
npm run build
php artisan config:cache
```

## Login

- Email: admin@mtsn2kotamalang.sch.id
- Password: password

## Database

Database sudah dibuat otomatis dengan script SQL.
- PostgreSQL berjalan di port 5432
- Username: postgres
- Password: postgres123

## Struktur

- Audio Libraries (UUID, file upload)
- Bell Types (UUID, active_days JSONB)
- Bell Schedules (UUID, foreign keys)
- Settings (key-value)

Semua tabel menggunakan UUID primary key.

## Vite Asset Bundling

Aplikasi menggunakan Vite untuk bundling assets:

**Development:**
- Run `npm run dev` untuk hot module replacement
- Assets load dari Vite dev server (localhost:5173)
- Perubahan CSS/JS langsung terlihat tanpa refresh

**Production:**
- Run `npm run build` untuk compile & minify
- Assets disimpan di `public/build/`
- Otomatis versioned untuk cache control

Lihat `VITE_GUIDE.md` untuk detail lengkap.
