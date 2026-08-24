# Coolify Deployment Guide - Bell MTsN 2 Kota Malang

## 🚀 Auto-Migration & Seeding on Deploy

**Server:** https://bell.mtsn2kotamalang.sch.id

---

## ✨ Features

Setiap kali deploy/redeploy dari Coolify, aplikasi akan **otomatis:**

1. ✅ Wait for PostgreSQL ready
2. ✅ Run `php artisan migrate --force`
3. ✅ Check hardware tables exist and have data
4. ✅ Auto-seed missing data:
   - Speaker zones (8 Modbus channels)
   - Rooms (minimum 10 rooms)
   - Hardware config (COM port settings)
5. ✅ Seed user accounts (admin)
6. ✅ Clear and cache config/routes/views
7. ✅ Health check endpoint active

**No manual migration needed!** 🎉

---

## 📋 Coolify Configuration

### **Environment Variables (.env di Coolify)**

Pastikan environment variables berikut sudah diset:

```env
# App
APP_NAME="Bel Sekolah MTsN 2 Kota Malang"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://bell.mtsn2kotamalang.sch.id

# Locale
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=postgres  # atau IP database server
DB_PORT=5432
DB_DATABASE=bel_sekolah_mtsn2
DB_USERNAME=postgres
DB_PASSWORD=YOUR_SECURE_PASSWORD

# Hardware Bridge API Token (PENTING!)
HARDWARE_BRIDGE_API_TOKEN=a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de

# Queue & Cache
QUEUE_CONNECTION=database
CACHE_STORE=file

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Container Role
CONTAINER_ROLE=app
```

---

## 🧪 Testing After Deployment

### **Test dari PC Lokal Bell:**

```cmd
cd C:\BelSekolahBridge
python test_api_connection.py
```

Expected:
```
Passed: 4/4
✅ All tests passed! Bridge should work.
```

---

**Deployment otomatis sudah configured! Tinggal push code dan Coolify handle migrations!** 🚀
