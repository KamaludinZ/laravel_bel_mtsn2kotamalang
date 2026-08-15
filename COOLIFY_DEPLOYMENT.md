# 🚀 Deployment Guide - Coolify untuk Laravel Bel MTsN 2 Kota Malang

## Daftar Isi
1. [Persiapan](#persiapan)
2. [Setup di Coolify](#setup-di-coolify)
3. [Environment Variables](#environment-variables)
4. [Deploy Pertama Kali](#deploy-pertama-kali)
5. [Redeploy & Update](#redeploy--update)
6. [Troubleshooting](#troubleshooting)

---

## 📋 Persiapan

### Requirements
- **Coolify** v4.x installed
- **Server** dengan minimal 2GB RAM, 2 CPU cores
- **Docker** & **Docker Compose** installed
- **GitHub** repository access

### Repository
```
URL: https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang.git
Branch: main
```

---

## 🔧 Setup di Coolify

### 1. Login ke Coolify Dashboard

1. Buka Coolify dashboard (`https://your-coolify-domain.com`)
2. Login dengan credentials admin
3. Pilih **Server** yang akan digunakan untuk deployment

### 2. Create New Resource

1. Klik **+ Add Resource**
2. Pilih **Docker Compose**
3. Pilih **GitHub** sebagai source
4. Connect GitHub account jika belum

### 3. Configure Repository

**Project Settings:**
```
Name: Laravel Bel MTsN2
Description: Sistem Bel Sekolah MTsN 2 Kota Malang
```

**Repository:**
```
Repository: KamaludinZ/laravel_bel_mtsn2kotamalang
Branch: main
Build Pack: docker-compose
```

**Compose File:**
```
docker-compose.yml
```

### 4. Network Configuration

**Ports:**
- Main App: `8000:80` (atau port lain yang tersedia)
- PostgreSQL: `5432:5432` (internal only, tidak perlu expose)
- Redis: `6379:6379` (internal only)

**Domains (Optional):**
```
Primary: bel-mtsn2.your-domain.com
```

Centang opsi:
- ✅ HTTPS via Let's Encrypt
- ✅ Force HTTPS redirect

---

## 🔐 Environment Variables

### Required Environment Variables

Di Coolify dashboard, tambahkan environment variables berikut:

#### Application
```env
APP_NAME="Bel Sekolah MTsN 2 Kota Malang"
APP_ENV=production
APP_KEY=base64:GENERATE_WITH_php_artisan_key_generate
APP_DEBUG=false
APP_URL=https://bel-mtsn2.your-domain.com
APP_VERSION=v1.1.1
```

#### Database
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=laravel_bel
DB_USERNAME=laravel
DB_PASSWORD=GENERATE_STRONG_PASSWORD_HERE
```

#### Cache & Queue
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=GENERATE_STRONG_PASSWORD_HERE
```

#### Container Ports
```env
APP_PORT=8000
DB_PORT=5432
REDIS_PORT=6379
```

#### Hardware (Optional)
```env
HARDWARE_INTEGRATION_ENABLED=true
HARDWARE_BRIDGE_API_TOKEN=your_secure_token_here
```

### Generate APP_KEY

**Cara 1: Via Command Line**
```bash
# Clone repo
git clone https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang.git
cd laravel_bel_mtsn2kotamalang

# Install dependencies
composer install --no-dev

# Generate key
php artisan key:generate --show
```

**Cara 2: Online Generator**
```
https://generate-random.org/laravel-key-generator
```

Copy hasil generate (format: `base64:...`) ke `APP_KEY`

---

## 🎯 Deploy Pertama Kali

### 1. Start Deployment

1. Di Coolify dashboard, klik **Deploy**
2. Coolify akan:
   - Clone repository dari GitHub
   - Build Docker images
   - Start containers (app, postgres, redis, queue, scheduler)
   - Run migrations automatically

### 2. Monitor Build Process

Buka **Logs** tab untuk melihat progress:
```
✅ Cloning repository...
✅ Building Docker image...
✅ Starting PostgreSQL...
✅ Starting Redis...
✅ Running migrations...
✅ Seeding hardware data...
✅ Caching configuration...
✅ Application ready!
```

Build pertama memakan waktu ~5-10 menit.

### 3. Verify Deployment

**Check Containers:**
```bash
docker ps
# Should see:
# - laravel-bel-app
# - laravel-bel-postgres
# - laravel-bel-redis
# - laravel-bel-queue
# - laravel-bel-scheduler
```

**Check Health:**
```bash
curl https://bel-mtsn2.your-domain.com/api/health
# Response: healthy
```

### 4. Create Admin User

**Via Coolify Console:**
```bash
# Akses terminal container app
docker exec -it laravel-bel-app sh

# Buat admin user
php artisan tinker

# Jalankan di tinker:
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@mtsn2malang.sch.id';
$user->password = Hash::make('admin123'); // Ganti dengan password kuat
$user->save();

# Exit tinker
exit
```

### 5. Access Application

```
URL: https://bel-mtsn2.your-domain.com
Email: admin@mtsn2malang.sch.id
Password: admin123 (atau sesuai yang Anda set)
```

---

## 🔄 Redeploy & Update

### Automatic Redeploy (Recommended)

**Setup Webhook di GitHub:**

1. **Di Coolify:**
   - Copy webhook URL dari deployment settings
   - Format: `https://coolify.com/webhooks/...`

2. **Di GitHub Repository:**
   - Settings → Webhooks → Add webhook
   - Paste webhook URL
   - Content type: `application/json`
   - Trigger: `Just the push event`
   - Active: ✅
   - Save

**Sekarang setiap push ke branch `main` akan auto-deploy!**

### Manual Redeploy

**Via Coolify Dashboard:**
```
1. Buka deployment
2. Klik "Redeploy" button
3. Pilih "Force Rebuild" jika ada perubahan Dockerfile
4. Confirm
```

**Via Command Line:**
```bash
# SSH ke server Coolify
ssh user@your-coolify-server.com

# Redeploy
cd /path/to/coolify/projects/laravel-bel-mtsn2
docker-compose pull
docker-compose up -d --build

# Check logs
docker-compose logs -f app
```

### Zero-Downtime Deployment

Coolify secara otomatis melakukan rolling update:
```
1. Build new image
2. Start new container
3. Health check new container
4. Switch traffic to new container
5. Stop old container
```

---

## 🐛 Troubleshooting

### Problem: Build Failed - "Composer install error"

**Solusi:**
```bash
# Check composer.lock
git add composer.lock
git commit -m "Update composer.lock"
git push

# Clear Coolify build cache
# Di dashboard: Force Rebuild with Cache Clear
```

### Problem: Migration Failed

**Solusi:**
```bash
# SSH ke container
docker exec -it laravel-bel-app sh

# Run migration manually
php artisan migrate:fresh --seed --force

# Check database
php artisan tinker
DB::connection()->getPdo();
```

### Problem: Redis Connection Refused

**Check redis container:**
```bash
docker logs laravel-bel-redis

# Test connection
docker exec -it laravel-bel-app sh
redis-cli -h redis -p 6379 -a YOUR_REDIS_PASSWORD ping
# Response: PONG
```

### Problem: PostgreSQL Authentication Failed

**Fix credentials:**
```bash
# Check .env
docker exec -it laravel-bel-app sh
cat .env | grep DB_

# Test connection
psql -h postgres -U laravel -d laravel_bel
```

### Problem: 500 Error - Storage Permission

**Fix permissions:**
```bash
docker exec -it laravel-bel-app sh
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Problem: Queue Not Processing

**Check queue worker:**
```bash
docker logs laravel-bel-queue

# Restart queue
docker restart laravel-bel-queue
```

### Problem: Old Assets After Deploy

**Clear cache:**
```bash
docker exec -it laravel-bel-app sh
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

## 📊 Monitoring

### Check Application Logs

```bash
# App logs
docker logs -f laravel-bel-app

# Queue logs
docker logs -f laravel-bel-queue

# Scheduler logs
docker logs -f laravel-bel-scheduler

# Laravel logs
docker exec laravel-bel-app tail -f storage/logs/laravel.log
```

### Check Resource Usage

```bash
# Container stats
docker stats

# Disk usage
docker system df

# Clean old images
docker image prune -a
```

### Database Backup via Coolify

**Automatic Backup (Coolify built-in):**
```
1. Buka Coolify dashboard
2. Pilih PostgreSQL service
3. Enable automatic backups
4. Set schedule: Daily at 02:00 AM
5. Retention: 7 days
```

**Manual Backup:**
```bash
# Via Coolify console
docker exec laravel-bel-postgres pg_dump -U laravel laravel_bel > backup_$(date +%Y%m%d).sql

# Download backup
scp user@server:/path/backup_*.sql ./
```

---

## 🔧 Advanced Configuration

### Custom Domain SSL

**Setup di Coolify:**
```
1. Domain settings
2. Add domain: bel-mtsn2.mtsn2malang.sch.id
3. Enable SSL via Let's Encrypt
4. Auto-renew: ✅
```

**DNS Configuration:**
```
Type: A
Name: bel-mtsn2
Value: YOUR_SERVER_IP
TTL: 3600
```

### Horizontal Scaling

**Scale queue workers:**
```yaml
# Di docker-compose.yml
queue:
  deploy:
    replicas: 3  # Run 3 queue workers
```

### Custom Build Args

**Di Coolify:**
```
Build Args:
- PHP_VERSION=8.4
- NODE_VERSION=20
- COMPOSER_VERSION=2
```

---

## 📝 Checklist Deployment

### Pre-Deployment
- [ ] Repository di GitHub up-to-date
- [ ] `.env` variables configured di Coolify
- [ ] APP_KEY generated
- [ ] Database credentials set
- [ ] Redis password set
- [ ] Domain & SSL configured

### Post-Deployment
- [ ] Health check passed
- [ ] Admin user created
- [ ] Migrations completed
- [ ] Seeders run successfully
- [ ] Storage permissions correct
- [ ] Queue worker running
- [ ] Scheduler running
- [ ] Application accessible via browser
- [ ] Login test successful
- [ ] Audio upload test
- [ ] Bell schedule test
- [ ] Hardware integration test (optional)

### Maintenance
- [ ] Webhook auto-deploy configured
- [ ] Automatic backups enabled
- [ ] Monitoring setup
- [ ] Log rotation configured
- [ ] Update schedule planned

---

## 🆘 Support

**Issues:**
- GitHub Issues: https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang/issues

**Documentation:**
- Coolify Docs: https://coolify.io/docs
- Laravel Docs: https://laravel.com/docs

**Logs Location:**
```
Application: storage/logs/laravel.log
PHP-FPM: storage/logs/php-fpm.log
Nginx: /var/log/nginx/
Supervisor: /var/log/supervisor/
```

---

## 🎉 Selamat!

Aplikasi Laravel Bel MTsN 2 Kota Malang sudah ter-deploy dengan sukses di Coolify!

**Next Steps:**
1. Upload audio files
2. Buat jadwal bel
3. Configure hardware integration
4. Train user
5. Monitor production
