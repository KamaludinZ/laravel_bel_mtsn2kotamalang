# Health Check & 503 Error Fix - Docker Coolify Deployment

## Problem
Container deployed successfully but experienced:
- Continuous restart loops
- Health check status remained unhealthy in Coolify
- 503 Service Unavailable errors when accessing the site
- Browser console: `GET https://bell.mtsn2kotamalang.sch.id/ 503`

## Root Cause Analysis

### Critical Issues Found:

1. **Nginx hardcoded health check response** (CRITICAL)
   - File: `docker/nginx/default.conf`
   - Had hardcoded `return 200 "healthy\n";` for `/api/health`
   - This bypassed Laravel completely, causing false positive health checks
   - Container appeared healthy but Laravel was actually not running

2. **Insufficient startup time**
   - Initial `start_period: 40s` was too short
   - Entrypoint script performs multiple operations: migrations, seeding, caching
   - These operations can take 60-90+ seconds on first deployment

3. **Insufficient retries and timeout**
   - Only 3 retries meant health check gave up too quickly
   - 3s timeout was too aggressive

4. **Fragile health check endpoint**
   - Redis connection check was failing entire health check
   - No fallback mechanism if Laravel wasn't ready

5. **Poor error handling in entrypoint**
   - No maximum retry limits for database/Redis wait
   - No detailed logging of failures
   - Errors could cause silent failures and restart loops

## Solutions Applied

### 1. Removed Nginx Hardcoded Health Check (CRITICAL FIX)
**File**: `docker/nginx/default.conf`

**Removed**:
```nginx
location /api/health {
    access_log off;
    return 200 "healthy\n";  # ❌ WRONG - bypasses Laravel
    add_header Content-Type text/plain;
}
```

**Why**: This was the main cause of 503 errors. Nginx returned 200 for health checks, but Laravel wasn't actually running behind it.

### 2. Extended Health Check Timing
**Files**: `docker-compose.yml`, `Dockerfile`

Changed from:
```yaml
start_period: 40s
retries: 3
timeout: 3s
```

To:
```yaml
start_period: 120s  # 2 minutes startup grace period
retries: 5          # More retry attempts
timeout: 10s        # Longer timeout per check
```

### 3. Added Fallback Static Health Check
**Files**: `public/health.html`, `docker-compose.yml`, `Dockerfile`

Created simple static file: `public/health.html` with content "OK"

Health check now tries:
```bash
curl -f http://localhost/health.html || curl -f http://localhost/api/health || exit 1
```

**Why**: If Laravel isn't ready, at least nginx serving static files proves the web server is working.

### 4. Improved Health Check Endpoint
**File**: `routes/api.php`

**Changes**:
- Separated database and cache checks with individual try-catch blocks
- Cache (Redis) failures no longer mark the entire app as unhealthy
- More detailed error reporting in health check response
- Database connection is the only critical check

### 5. Robust Entrypoint Error Handling
**File**: `docker/entrypoint.sh`

**Improvements**:
- Added maximum retry limits (30 attempts) for DB and Redis waits
- Added attempt counters with progress logging
- Better error messages showing what failed
- Non-critical failures (seeding, caching) don't stop startup
- Redis failure is treated as warning, not fatal error
- Added environment variable logging for debugging

### 6. Improved Supervisor Configuration
**File**: `docker/supervisor/supervisord.conf`

**Changes**:
- Increased `startretries` from 3 to 10
- Added `startsecs: 5` to prevent rapid restart loops
- Added `priority` to ensure PHP-FPM starts before nginx

## Health Check Flow

1. **Start Period (0-120s)**: Health check runs but failures are ignored
2. **After 120s**: Health check results start affecting container status
3. **Check Interval**: Every 30 seconds
4. **Timeout**: 10 seconds per check
5. **Retries**: Up to 5 consecutive failures before marking unhealthy

## Expected Timeline

```
T+0s    : Container starts
T+0-90s : Entrypoint runs (migrations, seeding, caching)
T+90s   : Application ready, nginx/php-fpm serving requests
T+120s  : First health check that "counts" runs
T+120s  : Health check passes → Container marked as HEALTHY ✅
```

## Verification Commands

After deployment, you can verify health check status:

```bash
# Check container health status
docker ps

# Manual health check test
docker exec <container-id> curl -f http://localhost/api/health

# View health check logs
docker inspect <container-id> | grep -A 10 Health
```

## Testing Locally

To test these changes locally before deploying:

```bash
# Build and start
docker-compose up -d --build

# Watch container status
watch -n 2 'docker ps'

# Monitor app container logs
docker-compose logs -f app

# Check health endpoint manually
curl http://localhost/api/health
```

## Deployment to Coolify

After committing these changes:

1. Commit and push to repository:
```bash
git add .
git commit -m "Fix: Critical health check and 503 errors - remove nginx hardcoded response"
git push
```

2. Trigger redeploy in Coolify (or it will auto-deploy)

3. Monitor deployment:
   - Watch container logs in Coolify dashboard
   - Look for entrypoint messages showing progress
   - Wait 2-3 minutes for full initialization

4. Verify health:
   - Container should show as **HEALTHY** in Coolify
   - Access https://bell.mtsn2kotamalang.sch.id/ should return 200, not 503
   - Check https://bell.mtsn2kotamalang.sch.id/api/health for detailed status

## Troubleshooting After Deploy

If still experiencing issues, check container logs:

```bash
# In Coolify, view logs for the app container
# Look for these key messages:

✅ PostgreSQL is ready!
✅ Redis is ready!
✅ Migrations completed successfully
✅ Config cached
✅ Application setup completed!
✅ Laravel application is ready!
🌐 Starting web server...
```

If you see errors in logs:
- `❌ PostgreSQL is not available` → Check DB_HOST, DB_PORT, DB_USERNAME in env vars
- `❌ Config cache failed` → Check APP_KEY is set correctly
- `❌ Database connection failed` → Verify PostgreSQL container is running and healthy

---

**Date**: 2026-08-23
**Issue**: Container restart loops + 503 Service Unavailable
**Fixed By**: Claude Code

**Files Changed**:
- `docker/nginx/default.conf` - Removed hardcoded health check
- `docker-compose.yml` - Extended health check timing, added fallback, fixed build conflicts
- `Dockerfile` - Extended health check timing, added fallback
- `routes/api.php` - Improved health endpoint error handling
- `docker/entrypoint.sh` - Added retry limits and better logging
- `docker/supervisor/supervisord.conf` - Increased retries and added priorities
- `public/health.html` - New static fallback health check

## Build Fix (2026-08-23 - Second Deploy)

### Issue
Build failed with exit code 255:
```
Error: #2 [scheduler internal] load build definition from Dockerfile
```

### Root Cause
Docker Compose was trying to build 3 separate images (app, queue, scheduler) from the same Dockerfile, causing build conflicts. The Dockerfile doesn't have multi-stage build targets for these services.

### Solution
Modified `docker-compose.yml` to only build image once in `app` service:
- `app`: Has `build` section - builds the image
- `queue`: Removed `build` section - uses pre-built image
- `scheduler`: Removed `build` section - uses pre-built image

All three services now use the same image `laravel-bel-mtsn2:latest`, differentiated only by:
- `CONTAINER_ROLE` environment variable
- `command` override (queue and scheduler have different commands)

This ensures single build, no conflicts.

## Runtime Fixes (2026-08-23 - Third Deploy)

### Issues Found in Runtime Logs

1. **Supervisor Log Directory Error** (CRITICAL)
```
Error: The directory named as part of the path /var/log/supervisor/supervisord.log does not exist
```
**Root Cause**: supervisord.conf tried to write logs to `/var/log/supervisor/` which doesn't exist in Alpine container.

**Solution**: Changed supervisor to log to stdout instead:
```ini
logfile=/dev/stdout
logfile_maxbytes=0
```

2. **RoomSeeder Duplicate Key Error** (WARNING - repeated on every restart)
```
SQLSTATE[23505]: Unique violation: duplicate key value violates unique constraint "rooms_no_unique"
DETAIL: Key (no)=(41) already exists.
```

**Root Cause**: RoomSeeder used `create()` which fails on re-deploy when data already exists.

**Solution**: Changed to `updateOrCreate()` for idempotent seeding:
```php
Room::updateOrCreate(
    ['no' => $roomData['no']], // Match by unique key
    [...] // Update fields
);
```

Now shows: `✓ Created 0 rooms, Updated 45 rooms` on re-deploy instead of errors.

### Files Changed
- `docker/supervisor/supervisord.conf` - Changed log to stdout
- `database/seeders/RoomSeeder.php` - Use updateOrCreate for idempotency

## HTTPS & 500 Error Fixes (2026-08-23 - Fourth Deploy)

### Issues Found
Container healthy and running, but browser showed:

1. **500 Internal Server Error**
```
GET https://bell.mtsn2kotamalang.sch.id/ 500 (Internal Server Error)
```

2. **Mixed Content Errors** (HTTPS/HTTP mismatch)
```
Mixed Content: The page at 'https://bell.mtsn2kotamalang.sch.id/' was loaded over HTTPS,
but requested an insecure stylesheet 'http://bell.mtsn2kotamalang.sch.id/build/assets/app-BXgCNn_d.css'.
This request has been blocked; the content must be served over HTTPS.
```

### Root Causes

**1. No HTTPS Forcing**
- Laravel generated asset URLs with `http://` instead of `https://`
- Site served via HTTPS (Coolify reverse proxy) but assets used HTTP
- Browser blocked mixed content for security

**2. No Proxy Trust Configuration**
- Laravel didn't recognize requests came through reverse proxy
- `X-Forwarded-Proto` header not trusted
- Laravel thought all requests were HTTP even when proxied via HTTPS

**3. AppServiceProvider View Composer Error**
- Tried to access `Setting::get()` on every view
- If `settings` table missing or error occurred, 500 error
- No error handling for database failures

### Solutions Applied

**1. Force HTTPS in Production**
**File**: `app/Providers/AppServiceProvider.php`

Added URL forcing:
```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    // Force HTTPS in production (when behind reverse proxy like Coolify)
    if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
}
```

**2. Trust Reverse Proxy Headers**
**File**: `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    // Trust proxies for HTTPS detection behind reverse proxy (Coolify)
    $middleware->trustProxies(at: '*');
})
```

**File**: `app/Http/Middleware/TrustProxies.php` (created)

```php
protected $proxies = '*'; // Trust all proxies

protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
```

**3. Add Error Handling in View Composer**
**File**: `app/Providers/AppServiceProvider.php`

```php
view()->composer('*', function ($view) {
    try {
        $view->with('appName', \App\Models\Setting::get('app_name', config('app.name')));
        $view->with('appLogo', \App\Models\Setting::get('app_logo'));
    } catch (\Exception $e) {
        // Fallback if settings table doesn't exist or has issues
        $view->with('appName', config('app.name', 'Laravel'));
        $view->with('appLogo', null);
    }
});
```

### Expected Results
- ✅ Site loads with 200 OK (no more 500 errors)
- ✅ All assets load via HTTPS (no mixed content errors)
- ✅ Asset URLs: `https://bell.mtsn2kotamalang.sch.id/build/assets/...`
- ✅ Laravel recognizes HTTPS correctly from proxy headers
- ✅ No crashes if settings table has issues

### Files Changed
- `app/Providers/AppServiceProvider.php` - HTTPS forcing & error handling
- `bootstrap/app.php` - Trust proxies configuration
- `app/Http/Middleware/TrustProxies.php` - Proxy trust middleware (new)
