# Complete Docker Compose Deployment Guide

Deploy **Laravel App + PostgreSQL + Redis** dalam **satu kali deploy** di Coolify.

## 🎯 Overview

Setup ini menyediakan:
- ✅ **Laravel Application** (Web server)
- ✅ **PostgreSQL Database** (Built-in)
- ✅ **Redis Cache** (Built-in)
- ✅ **Queue Worker** (Background jobs)
- ✅ **Scheduler** (Cron tasks)

**Semua dalam SATU docker-compose.yml!**

---

## 📦 Services Architecture

```
┌─────────────────────────────────────────────┐
│           Docker Compose Stack              │
├─────────────────────────────────────────────┤
│                                             │
│  ┌───────────────┐    ┌──────────────┐     │
│  │  PostgreSQL   │    │    Redis     │     │
│  │   Port: 5432  │    │  Port: 6379  │     │
│  └───────┬───────┘    └──────┬───────┘     │
│          │                   │              │
│          ↓                   ↓              │
│  ┌───────────────────────────────────┐     │
│  │      Laravel App (Main)           │     │
│  │      Port: 80                     │     │
│  │      Health: /api/health          │     │
│  └───────────────────────────────────┘     │
│          ↓                                  │
│  ┌───────────────────────────────────┐     │
│  │      Queue Worker                 │     │
│  │      Processes background jobs    │     │
│  └───────────────────────────────────┘     │
│          ↓                                  │
│  ┌───────────────────────────────────┐     │
│  │      Scheduler (Cron)             │     │
│  │      Runs scheduled tasks         │     │
│  └───────────────────────────────────┘     │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🚀 Deployment Steps

### **Step 1: Prepare Coolify**

1. **Login to Coolify Dashboard**
2. **Ensure you're on latest Coolify version** (v4.0+)
3. **Have your GitHub repository ready**

### **Step 2: Create New Application**

1. Click **"+ New Resource"** → **"Application"**
2. **Source:**
   - Type: **GitHub**
   - Repository: `KamaludinZ/laravel_bel_mtsn2kotamalang`
   - Branch: `main`
3. Click **"Continue"**

### **Step 3: Configure Build**

**IMPORTANT - Select Correct Settings:**

- **Build Pack:** **Docker Compose** ← PENTING!
- **Docker Compose File:** `docker-compose.yml` (default)
- **Base Directory:** (leave empty)

### **Step 4: Configure Environment Variables**

Go to **"Environment Variables"** tab:

```bash
# ========================================
# APP CONFIGURATION
# ========================================
APP_NAME=Laravel Bel MTSN2
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bell.mtsn2kotamalang.sch.id

# Generate dengan: php artisan key:generate --show
APP_KEY=base64:wKQtz4KUYRlrfk2It/O7AqnpEgH8Kkq4uygdNuBMk6Y=

# ========================================
# DATABASE CONFIGURATION
# ========================================
DB_DATABASE=laravel_bel
DB_USERNAME=postgres
DB_PASSWORD=YourSecurePasswordHere123!

# NOTE: DB_HOST will be 'postgres' (service name in docker-compose)
# No need to set DB_HOST, docker-compose handles it

# ========================================
# CACHE & SESSION
# ========================================
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# NOTE: REDIS_HOST will be 'redis' (service name in docker-compose)

# ========================================
# LOGGING
# ========================================
LOG_CHANNEL=stack
LOG_LEVEL=error

# ========================================
# OPTIONAL
# ========================================
FILESYSTEM_DISK=local
BROADCAST_DRIVER=log
```

**⚠️ CRITICAL: Set DB_PASSWORD dengan password yang kuat!**

### **Step 5: Configure Domain**

1. Go to **"Domains"** tab
2. Add domain: `bell.mtsn2kotamalang.sch.id`
3. Enable **"Generate SSL Certificate"** (Let's Encrypt)
4. **Save**

### **Step 6: Configure Port Mapping**

Coolify biasanya auto-detect, tapi verify:
- **Container Port:** `80`
- **Public Port:** `80` (or handled by Coolify proxy)

### **Step 7: Deploy!**

1. Click **"Deploy"** button
2. Monitor deployment logs
3. Wait 5-10 minutes for complete deployment

---

## 📊 Deployment Process

```
[00:00] 🚀 Starting deployment...
[00:30] 📦 Cloning repository...
[01:00] 🔨 Building Docker images...
[02:00]    └─ Building 'app' service
[03:00]       ├─ Installing PHP dependencies (composer)
[04:00]       ├─ Installing Node dependencies (npm)
[05:00]       └─ Building frontend assets (Vite)
[06:00] ✅ Build complete!
[06:30] 🐘 Starting PostgreSQL container...
[07:00] 🔴 Starting Redis container...
[07:30] 🌐 Starting Laravel app container...
[08:00] 👷 Starting queue worker...
[08:30] ⏰ Starting scheduler...
[09:00] ✅ All services running!
[09:30] 🔍 Running health checks...
[10:00] 🎉 Deployment successful!
```

---

## ✅ Post-Deployment Tasks

### **1. Verify Services are Running**

```bash
# Check all containers
docker ps

# Should see 5 containers:
# - laravel-bel-postgres
# - laravel-bel-redis
# - laravel-bel-app
# - laravel-bel-queue
# - laravel-bel-scheduler
```

### **2. Run Database Migrations**

```bash
# Get app container ID
docker ps | grep laravel-bel-app

# Run migrations
docker exec -it laravel-bel-app php artisan migrate --force

# Verify
docker exec -it laravel-bel-app php artisan migrate:status
```

### **3. Seed Initial Data**

```bash
# Seed hardware data
docker exec -it laravel-bel-app php artisan db:seed --class=HardwareSeeder --force

# Seed rooms
docker exec -it laravel-bel-app php artisan db:seed --class=RoomSeeder --force
```

### **4. Create Storage Link**

```bash
docker exec -it laravel-bel-app php artisan storage:link
```

### **5. Optimize Application**

```bash
docker exec -it laravel-bel-app php artisan optimize
docker exec -it laravel-bel-app php artisan config:cache
docker exec -it laravel-bel-app php artisan route:cache
docker exec -it laravel-bel-app php artisan view:cache
```

### **6. Test Health Endpoint**

```bash
curl https://bell.mtsn2kotamalang.sch.id/api/health

# Should return:
# {
#   "status": "healthy",
#   "timestamp": "...",
#   "services": {
#     "database": "connected",
#     "cache": "connected",
#     "app": "running"
#   }
# }
```

---

## 🔍 Verification Checklist

After deployment, verify:

### **Application Level**
- [ ] Site accessible at https://bell.mtsn2kotamalang.sch.id
- [ ] SSL certificate active (green padlock)
- [ ] Homepage loads correctly
- [ ] Login works
- [ ] Dashboard accessible

### **Database Level**
- [ ] Database connection working
- [ ] Tables created (run migrations)
- [ ] Can query data

### **Cache Level**
- [ ] Redis connection working
- [ ] Session persists across requests
- [ ] Cache writes/reads working

### **Queue Level**
- [ ] Queue jobs processing
- [ ] Check `failed_jobs` table (should be empty)

### **Health Check**
- [ ] `/api/health` returns 200 OK
- [ ] All services show "connected"

---

## 🐛 Troubleshooting

### **Issue 1: Container Fails to Start**

**Check logs:**
```bash
docker logs laravel-bel-app --tail 100
docker logs laravel-bel-postgres --tail 50
docker logs laravel-bel-redis --tail 50
```

**Common causes:**
- APP_KEY not set
- Database password mismatch
- Port already in use

### **Issue 2: Database Connection Refused**

**Check:**
```bash
# Verify postgres is healthy
docker exec -it laravel-bel-postgres pg_isready

# Check if DB exists
docker exec -it laravel-bel-postgres psql -U postgres -l

# Test connection from app
docker exec -it laravel-bel-app php artisan tinker
>>> DB::connection()->getPdo();
```

**Solution:**
- Ensure `DB_PASSWORD` matches in both Coolify env vars and PostgreSQL container
- Verify `DB_DATABASE` exists

### **Issue 3: Redis Connection Failed**

**Check:**
```bash
# Test Redis
docker exec -it laravel-bel-redis redis-cli ping
# Should return: PONG

# Test from app
docker exec -it laravel-bel-app php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

### **Issue 4: Assets Not Loading**

**Check:**
```bash
# Verify build files exist
docker exec -it laravel-bel-app ls -la public/build

# Rebuild if needed
docker exec -it laravel-bel-app npm run build
```

### **Issue 5: Queue Not Processing**

**Check:**
```bash
# Check queue worker logs
docker logs laravel-bel-queue --tail 100

# Test queue
docker exec -it laravel-bel-app php artisan queue:work --once
```

---

## 🔄 Updating Application

When you push new code:

1. **Automatic (if webhook enabled):**
   - Coolify detects push
   - Auto-triggers deployment
   - Rebuilds images
   - Restarts containers

2. **Manual:**
   - Go to Coolify Dashboard
   - Click **"Redeploy"**
   - Wait for deployment to complete

**Note:** Data in volumes (postgres-data, redis-data, app-storage) persists across deployments!

---

## 💾 Data Persistence

**Volumes created:**
- `laravel-bel-postgres-data` - Database files (PERSISTENT)
- `laravel-bel-redis-data` - Redis snapshots (PERSISTENT)
- `laravel-bel-storage` - Laravel storage/logs (PERSISTENT)
- `laravel-bel-bootstrap-cache` - Bootstrap cache (PERSISTENT)

**Backup important volumes:**
```bash
# Backup database
docker exec laravel-bel-postgres pg_dump -U postgres laravel_bel > backup.sql

# Backup storage
docker run --rm -v laravel-bel-storage:/data -v $(pwd):/backup alpine tar czf /backup/storage-backup.tar.gz -C /data .
```

---

## 🎛️ Advanced Configuration

### **Enable Queue Worker Auto-restart**

Edit `docker-compose.yml`:
```yaml
queue:
  restart: always  # Change from: unless-stopped
  command: php artisan queue:work redis --tries=3 --timeout=90 --max-time=3600
```

### **Scale Queue Workers**

```bash
docker-compose up -d --scale queue=3
```

### **Add More Services**

Add to `docker-compose.yml`:
```yaml
services:
  # ... existing services

  redis-commander:
    image: rediscommander/redis-commander:latest
    environment:
      - REDIS_HOSTS=redis:redis:6379
    ports:
      - "8081:8081"
    depends_on:
      - redis
```

---

## 📈 Monitoring

### **Check Container Health**

```bash
docker ps --format "table {{.Names}}\t{{.Status}}"
```

### **Monitor Logs**

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f queue
```

### **Resource Usage**

```bash
docker stats
```

---

## 🎊 Success Indicators

If deployment successful:
- ✅ All 5 containers running (postgres, redis, app, queue, scheduler)
- ✅ Health endpoint returns 200 OK
- ✅ Application accessible via HTTPS
- ✅ SSL certificate active
- ✅ Login/auth working
- ✅ Database queries working
- ✅ Queue jobs processing
- ✅ Scheduled tasks running

---

**Generated by Claude Code** 🤖
