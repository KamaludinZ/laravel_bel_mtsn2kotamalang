# Health Check Fix - Docker Coolify Deployment

## Problem
Container deployed successfully but health check status remained unhealthy in Coolify.

## Root Cause Analysis
1. **Insufficient startup time**: Initial `start_period: 40s` was too short
   - Entrypoint script performs multiple operations: database migrations, seeding, cache clearing/warming
   - These operations can take 60-90 seconds or more on first deployment

2. **Insufficient retries**: Only 3 retries meant the health check gave up too quickly

3. **Fragile health check endpoint**: Redis connection check was failing the entire health check even when Redis was temporarily unavailable

## Solutions Applied

### 1. Extended Health Check Timing
**Files**: `docker-compose.yml` (line 104-109), `Dockerfile` (line 96-97)

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

**Reasoning**: This gives the application 2 minutes to complete initialization before health checks begin failing.

### 2. Improved Health Check Endpoint
**File**: `routes/api.php` (line 17-58)

**Changes**:
- Separated database and cache checks with individual try-catch blocks
- Cache (Redis) failures no longer mark the entire app as unhealthy
- More detailed error reporting in health check response
- Database connection is the only critical check

**Why**: Application can still function without cache, but needs database. This makes the health check more resilient to temporary Redis connection issues.

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

1. Push to repository
2. Trigger redeploy in Coolify
3. Wait 2-3 minutes for full initialization
4. Container should show as **HEALTHY** in Coolify dashboard

---

**Date**: 2026-08-23
**Fixed By**: Claude Code
**Related Files**:
- `docker-compose.yml`
- `Dockerfile`
- `routes/api.php`
