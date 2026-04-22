# 🔔 Aplikasi Bel Sekolah MTsN 2 Kota Malang

Aplikasi berbasis web untuk manajemen jadwal bel sekolah otomatis dengan fitur lengkap.

## ✨ Fitur Utama

- 🎵 **Manajemen Audio**: Upload dan kelola file audio (MP3/WAV)
- 📅 **Jenis Bel**: Buat berbagai jenis jadwal dengan hari aktif berbeda (JSONB)
- ⏰ **Jadwal Bel**: CRUD jadwal dengan import/export Excel
- 🖥️ **Halaman Public**: Display dengan jam digital real-time dan auto-play
- ⚙️ **Settings**: Konfigurasi nama aplikasi dan logo
- 🔐 **Authentication**: Laravel Breeze untuk keamanan
- 🤖 **Auto-Play**: Sistem otomatis memutar audio sesuai jadwal

## 🚀 Instalasi

Ada **2 cara** untuk menjalankan aplikasi ini:

### Opsi 1: Menggunakan Docker (Recommended ⭐)

**Paling mudah dan tidak perlu install PHP/PostgreSQL di komputer!**

#### Prasyarat:
- Docker Desktop terinstall dan berjalan

#### Quick Start:
```bash
# Klik 2x file ini, atau run di terminal:
docker-start.bat

# Atau untuk development mode (dengan Vite HMR):
docker-start-dev.bat
```

Selesai! Buka browser: **http://localhost:8000**

📖 Lihat **[DOCKER_GUIDE.md](DOCKER_GUIDE.md)** untuk dokumentasi lengkap Docker.

---

### Opsi 2: Manual Installation (Tanpa Docker)

#### Prasyarat:
- PHP 8.2+
- Composer
- Node.js & NPM
- PostgreSQL 16

#### Langkah Instalasi:

**1. Install Dependencies:**
```bash
cd temp_laravel
composer install
npm install
```

**2. Setup Database:**

Pilih salah satu:

**A. Menggunakan Docker untuk PostgreSQL saja:**
```bash
docker-compose up -d postgres
```

**B. Install PostgreSQL lokal**, lalu buat database:
```sql
CREATE DATABASE bel_sekolah_mtsn2;
```

**3. Configure Environment:**
```bash
cp .env.example .env
# Edit .env sesuai database Anda
php artisan key:generate
```

**4. Run Migrations:**
```bash
php artisan migrate
```

Atau gunakan script SQL manual:
```bash
# Jika pakai Docker PostgreSQL
docker exec -i bel_sekolah_postgres psql -U postgres -d bel_sekolah_mtsn2 < database_manual.sql
```

**5. Create Storage Link:**
```bash
php artisan storage:link
```

**6. Start Application:**

**2 Terminal:**

Terminal 1 - Laravel:
```bash
php artisan serve
```

Terminal 2 - Vite (Development):
```bash
npm run dev
```

Buka browser: **http://localhost:8000**

**Production Build:**
```bash
npm run build
```

## 🔑 Login Credentials

- **Email**: `admin@mtsn2kotamalang.sch.id`
- **Password**: `password`

## 📚 Dokumentasi

| File | Deskripsi |
|------|-----------|
| **[DOCKER_GUIDE.md](DOCKER_GUIDE.md)** | Panduan lengkap menggunakan Docker |
| **[VITE_GUIDE.md](VITE_GUIDE.md)** | Panduan Vite (Development & Production) |
| **[DEPLOYMENT.md](DEPLOYMENT.md)** | Panduan deploy ke production |

## 🗂️ Database Schema

### Tabel Utama:

1. **users** - Data pengguna/admin (UUID)
2. **audio_libraries** - Pustaka file audio (UUID)
3. **bell_types** - Jenis-jenis bel dengan active_days JSONB (UUID)
4. **bell_schedules** - Jadwal bel per hari (UUID)
5. **settings** - Pengaturan aplikasi (UUID)

Semua tabel menggunakan **UUID** sebagai primary key.

## 🎯 Cara Penggunaan

### 1. Upload Audio

1. Login → Menu **Pustaka Audio**
2. Klik **Tambah Audio**
3. Upload file MP3/WAV (max 10MB)

### 2. Buat Jenis Bel

1. Menu **Jenis Bel** → **Tambah Jenis Bel**
2. Beri nama (contoh: "Jadwal Normal", "Jadwal Ramadhan")
3. Pilih hari aktif (Senin-Minggu)
4. Centang **"Mode Otomatis"** jika ingin auto-play
5. Klik **Aktifkan** untuk menggunakan jenis bel ini

### 3. Buat Jadwal

**Manual:**
- Menu **Jadwal Bel** → **Tambah Jadwal**
- Pilih jenis bel, hari, waktu, dan audio

**Import Excel:**
- Menu **Jadwal Bel** → **Import Excel**
- Download template
- Isi data (jenis_bel, hari, waktu, nama_audio)
- Upload file

### 4. Public Display

Akses **http://localhost:8000** (atau homepage):
- Lihat jam digital real-time
- Jadwal hari ini
- Audio otomatis diputar sesuai waktu (jika mode otomatis aktif)

## 🛠️ Tech Stack

- **Backend**: Laravel 12.12.2 (PHP 8.2)
- **Frontend**: Blade, Tailwind CSS, Alpine.js
- **Database**: PostgreSQL 16
- **Auth**: Laravel Breeze
- **Excel**: Maatwebsite/Excel
- **Build Tool**: Vite 7
- **Containerization**: Docker & Docker Compose

## 📡 API Endpoints

- `GET /api/today-schedule` - Jadwal hari ini
- `GET /api/current-time` - Waktu server

## 🔧 Vite Asset Bundling

**Development Mode:**
- Run `npm run dev` untuk Hot Module Replacement
- Assets load dari Vite dev server (localhost:5173)
- Perubahan CSS/JS langsung terlihat

**Production Mode:**
- Run `npm run build` untuk compile & minify
- Assets tersimpan di `public/build/`
- Otomatis versioned dengan hash untuk cache control

📖 Detail lengkap di **[VITE_GUIDE.md](VITE_GUIDE.md)**

## 🐳 Docker Commands

Jika menggunakan Docker:

```bash
# Start aplikasi
docker-start.bat                 # Production mode
docker-start-dev.bat            # Development mode (dengan Vite)

# Stop aplikasi
docker-stop.bat

# Lihat logs
docker-logs.bat

# Manual commands
docker-compose up -d            # Start
docker-compose down             # Stop
docker-compose logs -f          # View logs
docker-compose ps               # Status containers
```

## ⚠️ Troubleshooting

### Docker Mode:

Lihat **[DOCKER_GUIDE.md](DOCKER_GUIDE.md)** section Troubleshooting

### Manual Mode:

**Database connection error:**
- Cek PostgreSQL berjalan
- Cek credentials di `.env`
- Cek `DB_HOST` (gunakan `127.0.0.1` atau `postgres` jika pakai Docker)

**Vite not found:**
```bash
npm install
npm run dev
```

**Storage link error:**
```bash
php artisan storage:link
```

**Permission error:**
```bash
chmod -R 775 storage bootstrap/cache
```

## 🔒 Security

- Password di-hash dengan bcrypt
- CSRF Protection aktif
- Middleware authentication
- File upload validation
- XSS protection

## 📝 Notes

- **Mode Otomatis**: Browser harus dibuka dan tidak muted
- **File Storage**: Audio di `storage/app/public/audio/`
- **Backup**: Gunakan `pg_dump` untuk backup database
- **Production**: Set `APP_DEBUG=false` dan `APP_ENV=production`

## 📞 Support

Untuk bantuan teknis atau pertanyaan:
- Baca dokumentasi di folder `docs/`
- Check troubleshooting section
- Lihat logs untuk error details

---

**Dibuat dengan ❤️ untuk MTsN 2 Kota Malang**

🎓 Versi: 1.0.0
📅 Update: April 2026
