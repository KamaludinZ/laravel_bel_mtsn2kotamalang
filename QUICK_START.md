# 🚀 Quick Start Guide - Aplikasi Bel Sekolah

## ⚠️ PENTING: Password Authentication Issue

Jika Anda mendapat error:
```
SQLSTATE[08006] [7] FATAL: password authentication failed for user "postgres"
```

Ini karena koneksi dari PHP Windows ke PostgreSQL Docker memerlukan setup khusus.

## ✅ SOLUSI: Gunakan Docker Lengkap

### Opsi 1: Run Semua di Docker (RECOMMENDED ⭐)

**Paling mudah dan pasti berhasil!**

```bash
# 1. Pastikan Docker Desktop running

# 2. Buka Command Prompt di folder temp_laravel
cd C:\laravel_bel_mtsn2kotamalang\temp_laravel

# 3. Jalankan semua container
docker-compose up -d

# 4. Tunggu 30 detik, lalu run migrations
timeout /t 30
docker-compose exec app php artisan migrate --force

# 5. Create storage link
docker-compose exec app php artisan storage:link

# 6. Akses aplikasi
# Buka browser: http://localhost:8000
```

**Login:**
- Email: `admin@mtsn2kotamalang.sch.id`
- Password: `password`

---

### Opsi 2: PHP di Windows + PostgreSQL di Docker (Advanced)

Jika Anda tetap ingin run PHP artisan serve di Windows:

**Install PostgreSQL Extension untuk PHP Windows:**

1. **Download php_pgsql.dll** yang sesuai versi PHP Anda
2. **Enable extension** di `php.ini`:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
3. **Restart command prompt**

**Atau gunakan psql client:**

```bash
# Install psql client untuk Windows
# Download dari: https://www.postgresql.org/download/windows/

# Connect ke database
psql -h 127.0.0.1 -p 5432 -U postgres -d bel_sekolah_mtsn2
# Password: postgres123
```

---

### Opsi 3: Install PostgreSQL Lokal (Tanpa Docker)

Jika tidak mau pakai Docker sama sekali:

```bash
# 1. Download & Install PostgreSQL untuk Windows
# https://www.postgresql.org/download/windows/

# 2. Buat database
psql -U postgres
CREATE DATABASE bel_sekolah_mtsn2;
\q

# 3. Update .env
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bel_sekolah_mtsn2
DB_USERNAME=postgres
DB_PASSWORD=YOUR_PASSWORD_HERE

# 4. Run migrations
php artisan migrate

# 5. Start server
php artisan serve

# 6. Di terminal lain, run Vite
npm run dev
```

---

## 🎯 Recommended Flow

**Untuk kemudahan, gunakan FULL DOCKER:**

```bash
cd C:\laravel_bel_mtsn2kotamalang\temp_laravel

# Start (Production)
docker-compose up -d

# Start (Development dengan Vite HMR)
docker-compose --profile development up -d

# View logs
docker-compose logs -f

# Stop
docker-compose down

# Command di dalam container
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app composer install
docker-compose exec app npm run build
```

---

## 📞 Troubleshooting

### Container tidak start

```bash
# Restart Docker Desktop
# Lalu:
docker-compose down
docker-compose up -d
```

### Port already in use

```bash
# Ganti port di docker-compose.yml
# Dari:
ports:
  - "8000:80"
# Ke:
ports:
  - "8001:80"
```

### Database error

```bash
# Reset database
docker-compose down -v
docker-compose up -d
docker-compose exec app php artisan migrate --force
```

---

## 📚 Dokumentasi Lengkap

- **[DOCKER_GUIDE.md](temp_laravel/DOCKER_GUIDE.md)** - Panduan Docker lengkap
- **[README.md](temp_laravel/README.md)** - Panduan aplikasi
- **[VITE_GUIDE.md](temp_laravel/VITE_GUIDE.md)** - Panduan Vite

---

**Kesimpulan: Untuk menghindari masalah password authentication, gunakan FULL DOCKER!** 🐳
