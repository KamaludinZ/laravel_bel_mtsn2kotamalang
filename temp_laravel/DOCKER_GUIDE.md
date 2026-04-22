# 🐳 Panduan Docker - Aplikasi Bel Sekolah

Panduan lengkap menjalankan aplikasi menggunakan Docker Desktop.

## 📋 Prasyarat

1. **Docker Desktop** terinstall dan berjalan
   - Download: https://www.docker.com/products/docker-desktop
   - Pastikan Docker Desktop running (cek tray icon)

2. **Docker Compose** (sudah include di Docker Desktop)

## 🏗️ Arsitektur Docker

Aplikasi terdiri dari 4 container:

```
┌─────────────────────────────────────────────┐
│           Docker Network                     │
│                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │  Nginx   │  │   PHP    │  │PostgreSQL│  │
│  │  :8000   │──│   App    │──│  :5432   │  │
│  └──────────┘  └──────────┘  └──────────┘  │
│       │                                      │
│       │        ┌──────────┐                 │
│       └────────│   Vite   │ (dev only)      │
│                │  :5173   │                 │
│                └──────────┘                 │
└─────────────────────────────────────────────┘
```

### Services:

1. **postgres** - PostgreSQL 16 database
2. **app** - Laravel application (PHP 8.2-FPM)
3. **nginx** - Web server (port 8000)
4. **vite** - Development server untuk HMR (port 5173) - Optional

## 🚀 Quick Start

### Metode 1: Menggunakan Script Helper (Mudah)

**Production Mode:**
```bash
# Klik 2x atau run di terminal
docker-start.bat
```

**Development Mode (dengan Vite HMR):**
```bash
# Klik 2x atau run di terminal
docker-start-dev.bat
```

**Stop Aplikasi:**
```bash
docker-stop.bat
```

**Lihat Logs:**
```bash
docker-logs.bat
```

### Metode 2: Manual Docker Commands

**Build & Start (Production):**
```bash
# Copy environment file
copy .env.docker .env

# Build images
docker-compose build

# Start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Create storage link
docker-compose exec app php artisan storage:link
```

**Start dengan Vite (Development):**
```bash
docker-compose --profile development up -d
```

**Stop All Containers:**
```bash
docker-compose down
```

## 📱 Mengakses Aplikasi

Setelah container berjalan:

- **Web Application**: http://localhost:8000
- **Vite Dev Server**: http://localhost:5173 (hanya development mode)
- **PostgreSQL**: localhost:5432

**Login Credentials:**
- Email: `admin@mtsn2kotamalang.sch.id`
- Password: `password`

## 🔧 Command Berguna

### Container Management

```bash
# Lihat status container
docker-compose ps

# Stop specific container
docker-compose stop nginx
docker-compose stop app
docker-compose stop postgres
docker-compose stop vite

# Start specific container
docker-compose start nginx

# Restart all
docker-compose restart

# Rebuild images
docker-compose build --no-cache

# Remove all (including volumes)
docker-compose down -v
```

### Laravel Commands di Docker

```bash
# Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:list

# Composer
docker-compose exec app composer install
docker-compose exec app composer update

# NPM (jika tidak pakai vite container)
docker-compose exec app npm install
docker-compose exec app npm run build
```

### Database Commands

```bash
# Access PostgreSQL
docker-compose exec postgres psql -U postgres -d bel_sekolah_mtsn2

# Backup database
docker-compose exec postgres pg_dump -U postgres bel_sekolah_mtsn2 > backup.sql

# Restore database
docker-compose exec -T postgres psql -U postgres -d bel_sekolah_mtsn2 < backup.sql

# Run SQL file
docker-compose exec -T postgres psql -U postgres -d bel_sekolah_mtsn2 < database_manual.sql
```

### Logs & Debugging

```bash
# View all logs
docker-compose logs -f

# View specific container logs
docker-compose logs -f nginx
docker-compose logs -f app
docker-compose logs -f postgres
docker-compose logs -f vite

# Last 100 lines
docker-compose logs --tail=100 app

# Shell access
docker-compose exec app sh
docker-compose exec postgres sh
docker-compose exec nginx sh
```

## 🔄 Development Workflow

### Mode Development (dengan HMR)

1. **Start development mode:**
   ```bash
   docker-start-dev.bat
   ```

2. **Edit files** di folder project (Windows)
   - `resources/css/app.css`
   - `resources/js/app.js`
   - `resources/views/**/*.blade.php`

3. **Perubahan auto-reload** di browser

4. **Lihat console** untuk HMR messages:
   ```bash
   docker-compose logs -f vite
   ```

### Mode Production (tanpa HMR)

1. **Build assets terlebih dahulu:**
   ```bash
   docker-compose exec app npm run build
   ```

2. **Start production mode:**
   ```bash
   docker-start.bat
   ```

## 📂 Volumes & Data Persistence

### Persistent Data:

1. **postgres_data** - Database PostgreSQL
   - Location: Docker volume
   - Backup: `docker-compose exec postgres pg_dump ...`

2. **Storage Files** - Uploaded files
   - Location: `./storage` (mounted dari host)
   - Tetap ada setelah container dihapus

3. **Bootstrap Cache**
   - Location: `./bootstrap/cache` (mounted dari host)

### Reset Database:

```bash
# Stop containers
docker-compose down

# Remove volume
docker volume rm temp_laravel_postgres_data

# Start fresh
docker-compose up -d
docker-compose exec app php artisan migrate --force
```

## ⚙️ Environment Variables

File `.env.docker` berisi konfigurasi Docker:

```env
DB_HOST=postgres          # Nama service docker
DB_PORT=5432
DB_DATABASE=bel_sekolah_mtsn2
DB_USERNAME=postgres
DB_PASSWORD=postgres123
```

**Penting:**
- `DB_HOST=postgres` (bukan `127.0.0.1` atau `localhost`)
- Ini karena menggunakan Docker networking

## 🐛 Troubleshooting

### Container tidak start

```bash
# Cek logs
docker-compose logs

# Cek Docker Desktop running
# Restart Docker Desktop

# Rebuild images
docker-compose build --no-cache
docker-compose up -d
```

### Database connection error

```bash
# Cek PostgreSQL ready
docker-compose exec postgres pg_isready

# Cek credentials di .env
# DB_HOST harus "postgres" bukan "localhost"

# Restart app container
docker-compose restart app
```

### Permission errors (storage/logs)

```bash
# Fix permissions (di dalam container)
docker-compose exec app chmod -R 775 storage
docker-compose exec app chmod -R 775 bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage
```

### Port already in use

```bash
# Port 8000 sudah dipakai
# Edit docker-compose.yml:
ports:
  - "8001:80"  # Ganti 8000 ke 8001

# Atau stop service yang pakai port 8000
netstat -ano | findstr :8000
taskkill /PID [PID_NUMBER] /F
```

### Vite HMR tidak work

```bash
# Cek vite container running
docker-compose ps

# Restart vite
docker-compose restart vite

# Cek logs
docker-compose logs -f vite

# Clear browser cache
```

### Assets tidak load

**Development:**
- Pastikan vite container running
- Akses http://localhost:5173 langsung untuk test

**Production:**
- Run `docker-compose exec app npm run build`
- Cek folder `public/build/` ada

## 🔐 Security untuk Production

Jika deploy ke server production:

1. **Ganti credentials:**
   ```env
   DB_PASSWORD=strong_password_here
   APP_KEY=... # Generate new key
   ```

2. **Disable debug:**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

3. **Gunakan HTTPS:**
   - Setup reverse proxy (Nginx/Traefik)
   - Install SSL certificate

4. **Firewall:**
   - Hanya expose port 80/443
   - Jangan expose port 5432 (PostgreSQL)

## 📊 Monitoring

### Resource Usage:

```bash
# Lihat CPU/Memory usage
docker stats

# Specific container
docker stats bel_sekolah_app
```

### Container Health:

```bash
# Check health status
docker-compose ps

# PostgreSQL health check
docker-compose exec postgres pg_isready -U postgres
```

## 🔄 Update Application

Setelah pull code baru:

```bash
# Rebuild app container
docker-compose build app

# Restart with new image
docker-compose up -d app

# Run new migrations
docker-compose exec app php artisan migrate --force

# Rebuild assets
docker-compose exec app npm run build

# Clear cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

## 💾 Backup & Restore

### Backup:

```bash
# Database
docker-compose exec postgres pg_dump -U postgres bel_sekolah_mtsn2 > backup_$(date +%Y%m%d).sql

# Storage files
tar -czf storage_backup.tar.gz storage/app/public

# Complete backup
tar -czf complete_backup.tar.gz storage/ public/storage backup_*.sql
```

### Restore:

```bash
# Database
docker-compose exec -T postgres psql -U postgres -d bel_sekolah_mtsn2 < backup_20260403.sql

# Storage files
tar -xzf storage_backup.tar.gz
```

## 📚 File Structure

```
temp_laravel/
├── docker/
│   └── nginx/
│       └── nginx.conf          # Nginx configuration
├── Dockerfile                  # PHP application image
├── docker-compose.yml          # Services definition
├── .env.docker                 # Docker environment
├── docker-start.bat           # Quick start script
├── docker-start-dev.bat       # Development mode script
├── docker-stop.bat            # Stop script
└── docker-logs.bat            # Logs script
```

## 🎯 Best Practices

1. **Selalu gunakan docker-compose** untuk start/stop
2. **Jangan commit** `.env` atau `public/build/`
3. **Backup database** secara regular
4. **Monitor logs** untuk error
5. **Update images** secara berkala
6. **Use volumes** untuk persistent data
7. **Development**: Pakai profile development untuk Vite
8. **Production**: Build assets dulu sebelum deploy

---

**Pertanyaan?** Baca dokumentasi Docker di https://docs.docker.com
