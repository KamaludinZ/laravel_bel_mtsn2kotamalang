# Panduan Deployment Production

## ⚠️ PENTING: Masalah CSS Tidak Muncul di Production

### Gejala
Di browser console muncul error:
```
Access to script at 'http://[::1]:5173/@vite/client' from origin 'http://app1.mtsn2kotamalang.sch.id' has been blocked by CORS policy
GET http://[::1]:5173/@vite/client net::ERR_FAILED
GET http://[::1]:5173/resources/css/app.css net::ERR_FAILED
```

### Penyebab
Aplikasi masih mencoba mengakses Vite dev server (`http://[::1]:5173`) padahal di production harus menggunakan file yang sudah di-build di `public/build/`.

### Solusi Cepat

**1. Update File `.env` di Server Production**
```env
APP_ENV=production   ← UBAH DARI local KE production
APP_DEBUG=false       ← UBAH DARI true KE false
APP_URL=http://app1.mtsn2kotamalang.sch.id
```

**2. Build Assets di Local**
```bash
npm run build
```

**3. Upload Folder `public/build/` ke Server**
Upload seluruh folder `public/build/` beserta isinya ke server production.

**4. Clear Cache di Server**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

**5. Refresh Browser**
Hard refresh browser dengan Ctrl+F5 atau Cmd+Shift+R

---

## 📋 Checklist Pre-Deployment

### 1. Environment Configuration

Edit `.env` untuk production:

```env
APP_NAME="Bel Sekolah MTsN 2 Kota Malang"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bel_sekolah_mtsn2
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password_here

# Ganti dengan key production yang baru
APP_KEY=

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 2. Generate Application Key

```bash
php artisan key:generate
```

### 3. Install Dependencies

```bash
# Composer (production only, no dev dependencies)
composer install --optimize-autoloader --no-dev

# NPM (install semua untuk build)
npm ci
```

### 4. Build Assets untuk Production

```bash
# Build CSS & JS dengan Vite
npm run build
```

Ini akan menghasilkan:
- `public/build/assets/app-[hash].css` (minified Tailwind)
- `public/build/assets/app-[hash].js` (minified Alpine.js)
- `public/build/manifest.json` (mapping file)

### 5. Database Migration

Jika belum dijalankan, run migration:

```bash
php artisan migrate --force
```

Atau gunakan script SQL manual:
```bash
docker exec -i postgres_container psql -U postgres -d bel_sekolah_mtsn2 < database_manual.sql
```

### 6. Storage Link

```bash
php artisan storage:link
```

### 7. Optimize Laravel

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### 8. Set File Permissions

```bash
# Linux/macOS
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Pastikan writable
chmod -R 775 storage/app/public
chmod -R 775 storage/logs
```

Windows: Pastikan IIS/Apache user memiliki write permission ke `storage/` dan `bootstrap/cache/`

### 9. Web Server Configuration

#### Apache (.htaccess)

File `.htaccess` di `public/` sudah ada dari Laravel.

Tambahkan di vhost:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/temp_laravel/public

    <Directory /path/to/temp_laravel/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/temp_laravel/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 10. PostgreSQL Production

Gunakan managed database atau setup sendiri:

```bash
# Install PostgreSQL
sudo apt install postgresql postgresql-contrib

# Create database
sudo -u postgres psql
CREATE DATABASE bel_sekolah_mtsn2;
CREATE USER bell_user WITH ENCRYPTED PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE bel_sekolah_mtsn2 TO bell_user;
\q
```

### 11. SSL Certificate

Install Let's Encrypt:

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

Atau untuk Nginx:
```bash
sudo certbot --nginx -d yourdomain.com
```

## 🚀 Deployment Steps

### Deploy ke Shared Hosting

1. **Upload Files** via FTP/SFTP:
   - Upload semua file kecuali `node_modules/`, `vendor/`, `.env`
   - Upload folder `public/build/` hasil npm build

2. **Install Dependencies** via SSH:
   ```bash
   composer install --no-dev
   ```

3. **Setup .env**:
   - Copy `.env.example` → `.env`
   - Edit dengan database credentials hosting
   - Run `php artisan key:generate`

4. **Run Migrations**:
   ```bash
   php artisan migrate --force
   ```

5. **Setup Storage**:
   ```bash
   php artisan storage:link
   ```

### Deploy ke VPS (Ubuntu)

Script lengkap:

```bash
#!/bin/bash

# Update system
sudo apt update && sudo apt upgrade -y

# Install requirements
sudo apt install -y php8.2 php8.2-fpm php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-gd
sudo apt install -y postgresql nginx composer nodejs npm

# Clone atau copy project
cd /var/www
sudo git clone your-repo.git temp_laravel
cd temp_laravel

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Setup permissions
sudo chown -R www-data:www-data /var/www/temp_laravel
sudo chmod -R 755 /var/www/temp_laravel/storage
sudo chmod -R 755 /var/www/temp_laravel/bootstrap/cache

# Setup .env
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Setup Nginx (copy config above)
sudo nano /etc/nginx/sites-available/bell-sekolah
sudo ln -s /etc/nginx/sites-available/bell-sekolah /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# Setup SSL
sudo certbot --nginx -d yourdomain.com
```

## 🔄 Update Production

Saat ada update code:

```bash
# 1. Masuk ke folder project
cd /path/to/temp_laravel

# 2. Pull latest code
git pull origin main

# 3. Install/update dependencies
composer install --no-dev --optimize-autoloader
npm ci

# 4. Build assets baru
npm run build

# 5. Run migrations baru (jika ada)
php artisan migrate --force

# 6. Clear & cache ulang
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## 🔒 Security Checklist

- [ ] `APP_DEBUG=false` di production
- [ ] `APP_ENV=production`
- [ ] Database credentials yang kuat
- [ ] SSL/HTTPS aktif
- [ ] File permissions benar (755/775)
- [ ] `.env` tidak ter-commit ke git
- [ ] Firewall aktif (UFW/iptables)
- [ ] PostgreSQL tidak expose ke public
- [ ] Regular backup database
- [ ] Rate limiting aktif

## 📊 Monitoring

### Check Application Status

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx logs
tail -f /var/log/nginx/error.log

# Check PHP-FPM
sudo systemctl status php8.2-fpm

# Check PostgreSQL
sudo systemctl status postgresql
```

### Database Backup

```bash
# Backup manual
pg_dump -U postgres bel_sekolah_mtsn2 > backup_$(date +%Y%m%d).sql

# Restore
psql -U postgres bel_sekolah_mtsn2 < backup_20260403.sql

# Automated backup (cron)
# Edit crontab: crontab -e
0 2 * * * pg_dump -U postgres bel_sekolah_mtsn2 > /backups/db_$(date +\%Y\%m\%d).sql
```

## 🎯 Performance Tips

1. **Enable OPcache** di `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=256
   opcache.max_accelerated_files=20000
   ```

2. **Use Queue** untuk heavy tasks:
   ```bash
   php artisan queue:work --daemon
   ```

3. **CDN** untuk static assets (optional)

4. **Database Indexing** - Sudah ada di migration

5. **Enable Gzip** di Nginx/Apache

---

**Setelah deployment, test semua fitur!**

✅ Login admin
✅ Upload audio
✅ Buat jenis bel
✅ Buat jadwal
✅ Import Excel
✅ Halaman public
✅ Auto-play audio
