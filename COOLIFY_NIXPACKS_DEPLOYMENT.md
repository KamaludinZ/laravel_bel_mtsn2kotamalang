# Deployment Guide - Coolify with Nixpacks

Complete guide untuk deploy Laravel Bel MTsN 2 Kota Malang menggunakan Coolify dengan Nixpacks.

## 🎯 Keuntungan Nixpacks vs Docker Compose

| Feature | Nixpacks | Docker Compose |
|---------|----------|----------------|
| Setup | Auto-detect Laravel | Manual configuration |
| .env Issue | ✅ No issue | ❌ Directory conflict |
| Build Time | ⚡ Faster | 🐌 Slower |
| Maintenance | 🟢 Easy | 🟡 Medium |
| Complexity | 🟢 Simple | 🔴 Complex |

---

## 📋 Prerequisites

1. ✅ Coolify instance running
2. ✅ GitHub repository access
3. ✅ PostgreSQL internal service created in Coolify
4. ✅ Domain configured (bell.mtsn2kotamalang.sch.id)

---

## 🚀 Step-by-Step Deployment

### **Step 1: Create New Application in Coolify**

1. **Login ke Coolify Dashboard**
2. **Click "+ New Resource"** → **"Application"**
3. **Choose Source:**
   - Source Type: **GitHub**
   - Repository: `KamaludinZ/laravel_bel_mtsn2kotamalang`
   - Branch: `main`
4. **Click "Continue"**

### **Step 2: Configure Build Settings**

1. **Build Pack:** Select **"Nixpacks"** (IMPORTANT!)
2. **Base Directory:** Leave empty (root of repository)
3. **Build Command:** Leave empty (auto-detected from nixpacks.toml)
4. **Start Command:** Leave empty (auto-detected from Procfile)
5. **Port:** `8080` (defined in Procfile)

### **Step 3: Setup PostgreSQL Database**

#### **Option A: Create New PostgreSQL Internal Service**

1. Go to **"+ New Resource"** → **"Database"** → **"PostgreSQL"**
2. Configure:
   - Name: `laravel-bel-postgresql`
   - Version: `16`
   - Database Name: `laravel_bel`
   - Username: `laravel_user`
   - Password: (auto-generated)
3. **Click "Create"**
4. **Copy connection details** (you'll need these)

#### **Option B: Use Existing PostgreSQL**

If you already created PostgreSQL internal service, just note down the connection details.

### **Step 4: Connect Database to Application**

1. Go back to your **Application**
2. Click **"Storages & Databases"** tab
3. Click **"Connect Existing Database"**
4. Select your PostgreSQL service
5. Coolify will **auto-inject** these environment variables:
   - `DATABASE_URL`
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`

### **Step 5: Configure Environment Variables**

Go to **"Environment Variables"** tab and add:

#### **Required Variables:**

```bash
# App Configuration
APP_NAME="Laravel Bel MTSN2"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bell.mtsn2kotamalang.sch.id

# Generate APP_KEY with: php artisan key:generate --show
APP_KEY=base64:YOUR_GENERATED_KEY_HERE

# Database (auto-injected by Coolify, verify they exist)
DB_CONNECTION=pgsql
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# should be auto-set by Coolify

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_STDERR_FORMATTER=Monolog\Formatter\JsonFormatter
```

#### **Optional Variables (if using Redis):**

```bash
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### **Step 6: Configure Domain**

1. Go to **"Domains"** tab
2. Add domain: `bell.mtsn2kotamalang.sch.id`
3. Enable **"Generate SSL"** (Let's Encrypt)
4. **Save**

### **Step 7: Deploy!**

1. Click **"Deploy"** button
2. Monitor deployment logs
3. Wait for deployment to complete (~5-10 minutes)

---

## 📊 Deployment Process Flow

```
┌─────────────────────────────────────────┐
│  1. Coolify clones GitHub repository    │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  2. Nixpacks detects Laravel project    │
│     - Reads nixpacks.toml               │
│     - Detects composer.json             │
│     - Detects package.json              │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  3. Setup Phase (nixpacks.toml)         │
│     - Install PHP 8.3                   │
│     - Install Composer                  │
│     - Install Node.js 20                │
│     - Install PostgreSQL client         │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  4. Install Phase                       │
│     - composer install (production)     │
│     - npm ci (clean install)            │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  5. Build Phase                         │
│     - npm run build (Vite)              │
│     - Clear Laravel caches              │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  6. Start Phase (Procfile)              │
│     - php artisan serve --port=8080     │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  7. Application Running! ✅             │
│     https://bell.mtsn2kotamalang.sch.id │
└─────────────────────────────────────────┘
```

---

## 🔧 Post-Deployment Tasks

### **1. Run Database Migrations**

Via Coolify **Terminal/Console** or **SSH**:

```bash
# Get container ID
docker ps | grep laravel-bel

# Execute migration
docker exec -it <container-id> php artisan migrate --force

# Seed initial data
docker exec -it <container-id> php artisan db:seed --force
```

### **2. Create Storage Link**

```bash
docker exec -it <container-id> php artisan storage:link
```

### **3. Optimize Application**

```bash
docker exec -it <container-id> php artisan optimize
docker exec -it <container-id> php artisan config:cache
docker exec -it <container-id> php artisan route:cache
docker exec -it <container-id> php artisan view:cache
```

### **4. Create Admin User (if needed)**

```bash
docker exec -it <container-id> php artisan tinker

# In tinker:
User::create([
    'name' => 'Admin',
    'email' => 'admin@mtsn2kotamalang.sch.id',
    'password' => bcrypt('password123')
]);
```

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] Application accessible at https://bell.mtsn2kotamalang.sch.id
- [ ] SSL certificate active (green padlock)
- [ ] Database connection working
- [ ] Login/authentication working
- [ ] Assets loading (CSS/JS)
- [ ] No errors in browser console
- [ ] Logs clean (check Coolify logs)

---

## 🐛 Troubleshooting

### **Issue: APP_KEY Missing Error**

**Symptom:**
```
No application encryption key has been specified.
```

**Solution:**
```bash
# Local:
php artisan key:generate --show

# Copy output, add to Coolify Environment Variables:
APP_KEY=base64:xxxxx...
```

### **Issue: Database Connection Failed**

**Symptom:**
```
SQLSTATE[08006] Connection refused
```

**Solution:**
1. Check `DB_HOST` is correct (should be PostgreSQL service hostname)
2. Verify PostgreSQL service is running
3. Check credentials match

### **Issue: Storage Permission Denied**

**Symptom:**
```
The stream or file "/var/www/html/storage/logs/laravel.log" could not be opened
```

**Solution:**
```bash
docker exec -it <container-id> chmod -R 775 storage bootstrap/cache
docker exec -it <container-id> chown -R www-data:www-data storage bootstrap/cache
```

### **Issue: Assets Not Loading (404)**

**Symptom:**
```
GET /build/assets/app-xxx.js 404 Not Found
```

**Solution:**
1. Check if `npm run build` ran successfully in logs
2. Verify `/public/build` exists in container:
   ```bash
   docker exec -it <container-id> ls -la public/build
   ```
3. Re-trigger build:
   ```bash
   docker exec -it <container-id> npm run build
   ```

---

## 🔄 Updating Application

When you push new code to GitHub:

1. Coolify auto-detects commit (if webhook enabled)
2. **OR** manually click "Redeploy" in Coolify dashboard
3. Deployment process repeats automatically
4. Zero-downtime deployment (old container runs until new one ready)

---

## 📚 Additional Resources

- **Nixpacks Docs:** https://nixpacks.com/docs
- **Coolify Docs:** https://coolify.io/docs
- **Laravel Deployment:** https://laravel.com/docs/deployment

---

## 🎊 Success!

If deployment successful, your application should be:
- ✅ Running at https://bell.mtsn2kotamalang.sch.id
- ✅ Connected to PostgreSQL database
- ✅ Auto-deploying on git push
- ✅ SSL enabled
- ✅ Production optimized

---

**Generated by Claude Code** 🤖
