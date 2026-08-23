# Docker Healthcheck Guide

## Status Healthcheck di Coolify

Jika Anda melihat status **UNHEALTHY** pada container di Coolify, berikut cara diagnosa dan perbaikannya.

## Healthcheck Configuration

File: `docker-compose.yml` (lines 102-107)

```yaml
healthcheck:
  test: ["CMD-SHELL", "curl -f http://localhost/health.html || curl -f http://localhost/api/health || exit 1"]
  interval: 30s
  timeout: 10s
  retries: 5
  start_period: 120s
```

### Cara Kerja Healthcheck

Healthcheck akan mengecek 2 endpoint secara berurutan:
1. **PRIMARY**: `http://localhost/health.html` (static file, super cepat)
2. **FALLBACK**: `http://localhost/api/health` (dynamic, cek database + redis)

Jika **KEDUA endpoint gagal**, status menjadi **UNHEALTHY**.

## Endpoint 1: /health.html (Static)

**File**: `public/health.html`
**Content**: `OK`

### Kenapa File Ini?
- ✅ Super cepat (tidak butuh PHP/database)
- ✅ Tidak membebani server
- ✅ Bisa langsung detect jika Nginx/web server down

### Membuat File:
```bash
echo "OK" > public/health.html
```

## Endpoint 2: /api/health (Dynamic)

**File**: `routes/api.php` (lines 17-58)

### Response Jika Healthy (200):
```json
{
  "status": "healthy",
  "timestamp": "2026-08-23T12:00:00Z",
  "services": {
    "database": "connected",
    "cache": "connected",
    "app": "running"
  }
}
```

### Response Jika Unhealthy (503):
```json
{
  "status": "unhealthy",
  "timestamp": "2026-08-23T12:00:00Z",
  "services": {
    "database": "error: SQLSTATE[HY000] [2002] Connection refused",
    "cache": "connected",
    "app": "running"
  }
}
```

### Checks yang Dilakukan:

#### 1. Database Connection
```php
\DB::connection()->getPdo();
```
- ✅ Connected: `"database": "connected"`
- ❌ Error: `"database": "error: ..."`

#### 2. Redis Connection (Optional)
```php
\Cache::driver('redis')->get('health_check');
```
- ✅ Connected: `"cache": "connected"`
- ⚠️ Not Configured: `"cache": "not_configured"`
- ❌ Error: `"cache": "error: ..."` (tapi app tetap healthy)

**Note**: Cache error TIDAK membuat status unhealthy karena aplikasi masih bisa berjalan tanpa cache.

#### 3. App Status
```php
"app": "running"
```
Selalu `running` jika PHP bisa execute.

## Kemungkinan Penyebab UNHEALTHY

### 1. File health.html Tidak Ada
**Gejala**:
```
curl http://localhost/health.html
404 Not Found
```

**Solusi**:
```bash
echo "OK" > public/health.html
git add public/health.html
git commit -m "Add health.html for healthcheck"
git push
```

Coolify akan auto-deploy dan healthcheck akan OK.

### 2. Database Connection Error
**Gejala**:
```json
{
  "status": "unhealthy",
  "services": {
    "database": "error: SQLSTATE[HY000] [2002] Connection refused"
  }
}
```

**Penyebab**:
- PostgreSQL container belum ready
- Environment variable DB_HOST/DB_PASSWORD salah
- Network issue antar container

**Solusi**:
```bash
# 1. Check env di Coolify
DB_HOST=postgres  # Harus nama service, bukan localhost
DB_DATABASE=laravel_bel
DB_USERNAME=postgres
DB_PASSWORD=<your_password>

# 2. Restart container
docker-compose restart app

# 3. Check database logs
docker logs <postgres_container_name>
```

### 3. Redis Connection Error
**Gejala**:
```json
{
  "status": "healthy",  # Masih healthy!
  "services": {
    "cache": "error: Connection refused"
  }
}
```

**Note**: Redis error TIDAK membuat unhealthy, tapi sebaiknya tetap diperbaiki.

**Solusi**:
```bash
# Check env
REDIS_HOST=redis  # Harus nama service
REDIS_PORT=6379

# Restart redis
docker-compose restart redis
```

### 4. /api/health Route Tidak Terdaftar
**Gejala**:
```
curl http://localhost/api/health
404 Not Found
```

**Solusi**:
Pastikan `routes/api.php` ada dan terdaftar. Run:
```bash
php artisan route:list | grep health
```

Harus muncul:
```
GET|HEAD  api/health ...........................
```

### 5. Healthcheck Timeout
**Gejala**: Container marked unhealthy setelah 5 retries

**Penyebab**: Response time > 10 detik

**Solusi**: Optimize database queries atau increase timeout di docker-compose.yml:
```yaml
healthcheck:
  timeout: 30s  # Increase dari 10s
```

## Cara Diagnosa

### 1. Test Manual dari Local
```bash
# Test health.html
curl https://bel.mtsn2kotamalang.sch.id/health.html

# Test /api/health
curl https://bel.mtsn2kotamalang.sch.id/api/health
```

### 2. Test di Container (via Coolify Console)
```bash
# Masuk ke container app
docker exec -it <app_container_name> sh

# Test healthcheck command
curl -f http://localhost/health.html || curl -f http://localhost/api/health || exit 1

# Test database
php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';"

# Test redis
php artisan tinker --execute="Cache::get('test'); echo 'Redis OK';"
```

### 3. Check Container Logs
```bash
# App logs
docker logs <app_container_name> --tail 100

# PostgreSQL logs
docker logs <postgres_container_name> --tail 50

# Redis logs
docker logs <redis_container_name> --tail 50
```

### 4. Check Docker Healthcheck Status
```bash
docker inspect <app_container_name> | grep -A 20 Health
```

Output:
```json
"Health": {
  "Status": "healthy",  # atau "unhealthy"
  "FailingStreak": 0,
  "Log": [
    {
      "Start": "2026-08-23T12:00:00Z",
      "End": "2026-08-23T12:00:01Z",
      "ExitCode": 0,  # 0 = success, 1 = failed
      "Output": "..."
    }
  ]
}
```

## Apakah UNHEALTHY Berbahaya?

### ⚠️ YA, jika:
1. **Database tidak connect**: Aplikasi tidak bisa baca/tulis data
2. **Healthcheck selalu fail**: Coolify bisa restart container terus-menerus
3. **Load balancer**: Traffic bisa di-route ke container unhealthy

### ✅ TIDAK MASALAH jika:
1. Aplikasi tetap bisa diakses dan berfungsi normal
2. Hanya warning di dashboard Coolify
3. Unhealthy karena cache/redis (optional service)

### Rekomendasi:
**SEGERA PERBAIKI** healthcheck issue karena:
- Indikator ada masalah di infrastructure
- Bisa mempengaruhi auto-scaling/restart policy
- Monitoring tool (Uptime Kuma, dll) akan report DOWN

## Quick Fix Checklist

- [ ] Pastikan `public/health.html` ada dengan content `OK`
- [ ] Test `curl https://bel.mtsn2kotamalang.sch.id/health.html`
- [ ] Test `curl https://bel.mtsn2kotamalang.sch.id/api/health`
- [ ] Check response: `"status": "healthy"` atau `"unhealthy"`
- [ ] Jika unhealthy, lihat `services` untuk tahu service mana yang error
- [ ] Fix environment variables di Coolify
- [ ] Redeploy aplikasi
- [ ] Verify healthcheck di Coolify dashboard → Status: HEALTHY ✅

## Monitoring

Setelah fix, monitor healthcheck dengan:
```bash
# Continuous monitoring (every 30s)
watch -n 30 'curl -s https://bel.mtsn2kotamalang.sch.id/api/health | jq'
```

Output should be:
```json
{
  "status": "healthy",
  "timestamp": "2026-08-23T12:00:00+07:00",
  "services": {
    "database": "connected",
    "cache": "connected",
    "app": "running"
  }
}
```

## Files Modified/Added

1. `public/health.html` - Static healthcheck file (NEW)
2. `check_health.sh` - Diagnostic script (NEW)
3. `HEALTHCHECK_GUIDE.md` - This documentation (NEW)
4. `routes/api.php` - Already has `/api/health` endpoint (EXISTING)
5. `docker-compose.yml` - Healthcheck config (EXISTING)

## Next Steps

1. Commit health.html:
   ```bash
   git add public/health.html check_health.sh HEALTHCHECK_GUIDE.md
   git commit -m "Add health.html and healthcheck documentation"
   git push
   ```

2. Wait for Coolify auto-deploy (atau manual deploy)

3. Check healthcheck status di Coolify → Harus HEALTHY ✅

4. Setup monitoring dengan Uptime Kuma atau similar tool
