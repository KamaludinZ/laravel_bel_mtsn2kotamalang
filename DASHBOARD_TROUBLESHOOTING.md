# 🔧 DASHBOARD TROUBLESHOOTING GUIDE

## ✅ FIXES YANG TELAH DILAKUKAN

### 1. **Fixed Carbon isoFormat Error** ✅
**Problem**: `isoFormat()` memerlukan package intl PHP extension atau translation files
**Solution**: Changed to `translatedFormat()` which is built-in Laravel

```php
// BEFORE (Error):
{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}

// AFTER (Fixed):
{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
```

**File**: `resources/views/dashboard.blade.php` line 8

### 2. **Fixed Missing is_active Column in bell_schedules** ✅
**Problem**: Query mencari kolom `is_active` yang tidak ada di table `bell_schedules`
**Solution**: Removed `where('is_active')` dan gunakan relasi ke `bell_types` saja

```php
// BEFORE (Error):
BellSchedule::where('is_active', true)->count()

// AFTER (Fixed):
$activeBellType ? BellSchedule::where('bell_type_id', $activeBellType->id)->count() : 0
```

**File**: `app/Http/Controllers/DashboardController.php` line 28

### 3. **Fixed Relationship Name Mismatch** ✅
**Problem**: Controller menggunakan `withCount('schedules')` tetapi relationship di model bernama `bellSchedules()`
**Solution**: Changed to use correct relationship name

```php
// BEFORE (Error):
BellType::withCount('schedules')
$bellType->schedules_count

// AFTER (Fixed):
BellType::withCount('bellSchedules')
$bellType->bell_schedules_count
```

**Files**:
- `app/Http/Controllers/DashboardController.php` lines 46, 51, 56
- `resources/views/dashboard.blade.php` line 175

### 4. **Rebuilt Vite Assets** ✅
**Command**: `npm run build`
**Result**: Assets successfully compiled
- `public/build/manifest.json` - 0.33 kB
- `public/build/assets/app-B9ixaqoo.css` - 58.91 kB
- `public/build/assets/app-BkQGjLdC.js` - 83.21 kB

### 5. **Cleared All Caches** ✅
```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 🧪 TEST ENDPOINTS

### Test Dashboard API (JSON Response)
URL: `http://localhost:8000/test-dashboard`

**Login required**

Expected Response:
```json
{
  "status": "OK",
  "users": 2,
  "audio": 5,
  "schedules": 0,
  "bell_types": 4
}
```

### Main Dashboard
URL: `http://localhost:8000/dashboard`

**Login required**

---

## 🔍 DEBUGGING STEPS

### Step 1: Check if Laravel Server Running
```bash
# Terminal 1: Start Laravel server
php artisan serve
```

### Step 2: Test Login
1. Go to: `http://localhost:8000/login`
2. Login with:
   - Email: `admin@mtsn2kotamalang.sch.id`
   - Password: `admin123`
3. Should redirect to `/dashboard`

### Step 3: Test API Endpoint
```bash
# After login, test this in browser:
http://localhost:8000/test-dashboard
```

If this returns JSON, then database queries work fine.

### Step 4: Check Browser Console
Open browser DevTools (F12) → Console tab
Look for JavaScript errors

### Step 5: Check Network Tab
Open browser DevTools (F12) → Network tab
- Look for failed requests (red)
- Check if `/dashboard` request returns 200 OK
- Check if assets load properly

---

## 🐛 COMMON ISSUES & SOLUTIONS

### Issue 1: White Screen / Loading Forever
**Possible Causes:**
1. JavaScript error blocking page render
2. Vite assets not loading
3. CSS not loading
4. PHP error in view

**Solutions:**
```bash
# Clear browser cache
Ctrl + Shift + Delete

# Hard refresh
Ctrl + Shift + R

# Rebuild assets
npm run build

# Clear Laravel cache
php artisan optimize:clear
```

### Issue 2: 500 Internal Server Error
**Check:**
```bash
# View latest logs
tail -50 storage/logs/laravel.log
```

**Common Fixes:**
```bash
# Clear config cache
php artisan config:clear

# Check .env file
# Make sure DB credentials are correct
```

### Issue 3: 404 Not Found on Dashboard
**Check Routes:**
```bash
php artisan route:list --name=dashboard
```

**Should show:**
```
GET|HEAD  dashboard  .... dashboard › DashboardController@index
```

### Issue 4: Assets Not Loading (404 on CSS/JS)
**Check:**
```bash
# Make sure build folder exists
ls public/build

# Rebuild assets
npm run build

# Check manifest
cat public/build/manifest.json
```

### Issue 5: Database Connection Error
**Check:**
```bash
# Test database connection
php artisan db:show

# Check Docker container
docker-compose ps

# Restart container if needed
docker-compose down
docker-compose up -d
```

---

## 📋 VERIFICATION CHECKLIST

Before accessing dashboard, verify:

- [ ] Laravel server running (`php artisan serve`)
- [ ] PostgreSQL Docker container running (`docker-compose ps`)
- [ ] Database seeded (2 users, 4 bell types, 5 audio)
- [ ] Assets compiled (`public/build/manifest.json` exists)
- [ ] Cache cleared (`php artisan optimize:clear`)
- [ ] Logged in as admin user
- [ ] Browser cache cleared (Ctrl + Shift + Delete)

---

## 🔄 FULL RESET PROCEDURE

If dashboard still not working, do complete reset:

```bash
# Step 1: Clear everything
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Step 2: Rebuild assets
npm run build

# Step 3: Check database
php artisan db:show

# Step 4: Verify routes
php artisan route:list --name=dashboard

# Step 5: Test basic route
curl http://localhost:8000/test-dashboard \
  -H "Cookie: YOUR_SESSION_COOKIE"

# Step 6: Check logs
tail -50 storage/logs/laravel.log

# Step 7: Restart server
# Stop: Ctrl+C
# Start: php artisan serve
```

---

## 📞 WHAT TO CHECK IN BROWSER

### Browser DevTools Checklist:

1. **Console Tab**:
   - No red errors
   - No warnings about missing files

2. **Network Tab**:
   - `/dashboard` request: Status 200
   - `app-*.css`: Status 200
   - `app-*.js`: Status 200
   - `manifest.json`: Status 200

3. **Application Tab** (Storage):
   - Cookies present
   - Session cookie present
   - XSRF token present

4. **Elements Tab**:
   - HTML fully loaded
   - CSS applied (inspect elements)
   - No inline errors

---

## 🎯 EXPECTED BEHAVIOR

### When Everything Works:

1. **Login**:
   - Redirect to `/dashboard`
   - Status code: 302 → 200

2. **Dashboard Load**:
   - Welcome banner visible
   - 4 stat cards showing numbers
   - Quick actions panel
   - Empty states for schedules (since no data yet)

3. **Browser Console**:
   - No errors
   - No 404s in network tab

4. **Performance**:
   - Page loads in < 2 seconds
   - No infinite loading spinner

---

## 🚨 IF STILL NOT WORKING

### Last Resort Checks:

1. **Check PHP Version**:
```bash
php -v
# Should be: PHP 8.2.12
```

2. **Check Composer Dependencies**:
```bash
composer install
```

3. **Check NPM Dependencies**:
```bash
npm install
```

4. **Check .env File**:
```ini
APP_ENV=local
APP_DEBUG=true  # Should be true for debugging
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=bel_sekolah_mtsn2
DB_USERNAME=postgres
DB_PASSWORD=postgres123
```

5. **Check File Permissions** (if on Linux/Mac):
```bash
chmod -R 775 storage bootstrap/cache
```

6. **Browser Compatibility**:
   - Try different browser (Chrome, Firefox, Edge)
   - Disable browser extensions
   - Try incognito/private mode

---

## 📝 REPORT TEMPLATE

If issue persists, provide this information:

```
Dashboard Loading Issue Report
==============================

1. What happens when you access /dashboard?
   [ ] White screen
   [ ] Loading spinner forever
   [ ] 500 error page
   [ ] 404 error page
   [ ] Other: ________________

2. Browser Console Errors:
   [Copy paste errors here]

3. Network Tab Failed Requests:
   [Screenshot or list]

4. Laravel Log (last 20 lines):
   [Copy paste from storage/logs/laravel.log]

5. Test Endpoint Result (/test-dashboard):
   [Copy paste JSON response or error]

6. Completed Checklist:
   [ ] Server running
   [ ] Database running
   [ ] Assets compiled
   [ ] Cache cleared
   [ ] Logged in
   [ ] Browser cache cleared
```

---

## ✅ CURRENT STATUS

**Last Updated**: 5 April 2026

**Changes Made**:
1. ✅ Fixed Carbon isoFormat → translatedFormat
2. ✅ Fixed missing is_active column query
3. ✅ Fixed relationship name mismatch (schedules → bellSchedules)
4. ✅ Rebuilt Vite assets
5. ✅ Cleared all caches
6. ✅ Added test endpoint `/test-dashboard`

**Expected Result**:
Dashboard should now load successfully without any errors.

**Next Test**:
1. Clear browser cache (Ctrl + Shift + Delete)
2. Hard refresh (Ctrl + Shift + R)
3. Access `http://localhost:8000/dashboard`

If still loading forever:
1. Open DevTools (F12)
2. Check Console tab for errors
3. Check Network tab for failed requests
4. Try test endpoint: `http://localhost:8000/test-dashboard`

---

**End of Troubleshooting Guide**
