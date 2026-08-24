# Deployment & Setup Documentation Index

## 📚 Complete Documentation Guide

---

## 🚀 **Production Deployment**

### **1. Coolify Deployment (Recommended)**
📄 **[COOLIFY_DEPLOYMENT_GUIDE.md](COOLIFY_DEPLOYMENT_GUIDE.md)**

**Auto-migration enabled!** Setiap deploy otomatis:
- Run migrations
- Check & seed hardware tables
- Clear caches
- Health check

**Quick Deploy:**
```bash
git push origin main
# Coolify auto-deploy handles everything!
```

### **2. Manual Deployment (SSH/Docker)**
📄 **[DEPLOYMENT_PRODUCTION.md](DEPLOYMENT_PRODUCTION.md)**

**For manual deployment via:**
- SSH + Laravel artisan
- cPanel/Plesk
- Docker Compose

**Quick Deploy:**
```bash
git pull origin main
php artisan migrate --force
php artisan hardware:check-migrations --seed
php artisan optimize:clear
```

---

## 🖥️ **Python Bridge Setup (PC Lokal Bell)**

### **3. Python Bridge Installation**
📄 **[PYTHON_BRIDGE_SETUP_PRODUCTION.md](PYTHON_BRIDGE_SETUP_PRODUCTION.md)**

**Complete setup guide dari nol:**
- Install Python 3.11+
- Install dependencies (pyserial, requests)
- Download scripts dari GitHub
- Setup config.json
- Test connection & hardware

**Config untuk MTsN 2:**
```json
{
  "vps_base_url": "https://bell.mtsn2kotamalang.sch.id",
  "api_token": "a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de",
  "com_port": "COM5",
  "poll_interval": 2
}
```

### **4. Quick Setup Checklist**
📄 **[SETUP_CHECKLIST_MTSN2.md](SETUP_CHECKLIST_MTSN2.md)**

**Step-by-step checklist dengan commands lengkap:**
- [x] Python 3.14 installed
- [x] COM Port: COM5
- [ ] Download python_bridge.py
- [ ] Setup config.json
- [ ] Test connection
- [ ] Run bridge

---

## 🔧 **Troubleshooting**

### **5. API Connection Issues**
📄 **[TROUBLESHOOTING_API.md](TROUBLESHOOTING_API.md)**

**Ketika test connection gagal:**
- HTTP 401 Unauthorized → API token mismatch
- HTTP 404 Not Found → Endpoint missing
- HTTP 500 Server Error → Laravel errors
- SSL/Certificate errors
- Firewall issues

**Quick Test:**
```cmd
python test_api_connection.py
```

---

## 🎯 **Quick Start - New Deployment**

### **Scenario 1: Fresh Coolify Deployment**

1. **Setup di Coolify:**
   - Create new project
   - Connect GitHub repo
   - Set environment variables (lihat COOLIFY_DEPLOYMENT_GUIDE.md)
   - Deploy!

2. **Test dari PC lokal:**
   ```cmd
   python test_api_connection.py
   ```

3. **Setup Python Bridge** (lihat PYTHON_BRIDGE_SETUP_PRODUCTION.md)

### **Scenario 2: Update Code (Redeploy)**

1. **Push code:**
   ```bash
   git push origin main
   ```

2. **Coolify auto-deploy** ✅ Migrations otomatis!

3. **Verify:**
   ```cmd
   python test_api_connection.py
   ```

### **Scenario 3: Manual Fix Production**

**Jika HTTP 500 muncul:**

```bash
# SSH ke server
ssh user@server

# Navigate to Laravel
cd /var/www/bell.mtsn2kotamalang.sch.id

# Pull latest
git pull origin main

# Run migrations
php artisan migrate --force

# Check hardware tables
php artisan hardware:check-migrations --seed

# Clear caches
php artisan optimize:clear

# Test
curl https://bell.mtsn2kotamalang.sch.id/api/health
```

---

## 📊 **File Structure**

```
📁 Project Root
├── 📄 COOLIFY_DEPLOYMENT_GUIDE.md       ← Coolify auto-deploy
├── 📄 DEPLOYMENT_PRODUCTION.md          ← Manual deployment
├── 📄 PYTHON_BRIDGE_SETUP_PRODUCTION.md ← PC lokal bell setup
├── 📄 SETUP_CHECKLIST_MTSN2.md          ← Quick checklist
├── 📄 TROUBLESHOOTING_API.md            ← Debug guide
├── 📄 DEPLOYMENT_README.md              ← This file (index)
│
├── 📁 docker/
│   ├── entrypoint.sh                    ← Auto-migration script
│   ├── check-migrations.sh              ← Verify migrations
│   └── ...
│
├── 📁 app/Console/Commands/
│   └── CheckHardwareMigrations.php      ← Laravel command
│
├── python_bridge.py                     ← Python Bridge script
├── config.json.example                  ← Config template
├── test_api_connection.py               ← API test script
└── start_bridge.bat                     ← Windows startup script
```

---

## 🧪 **Testing Checklist**

### **After Production Deployment:**

- [ ] Health check OK:
  ```bash
  curl https://bell.mtsn2kotamalang.sch.id/api/health
  ```

- [ ] Hardware API OK (dari PC lokal):
  ```cmd
  python test_api_connection.py
  ```

- [ ] Web interface accessible:
  ```
  https://bell.mtsn2kotamalang.sch.id
  ```

- [ ] Login works (admin account)

- [ ] Hardware menu accessible

### **After Python Bridge Setup:**

- [ ] Bridge connects to server:
  ```cmd
  python python_bridge.py --test-connection
  ```

- [ ] Hardware test OK:
  ```cmd
  python python_bridge.py --test-hardware
  ```

- [ ] Bridge runs continuously:
  ```cmd
  python python_bridge.py
  ```

- [ ] Test from web: Click ON button → Speaker nyala!

---

## 🔐 **Important Credentials**

**Server:**
- URL: https://bell.mtsn2kotamalang.sch.id
- IP: 187.77.117.23

**API Token:**
```
a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de
```

**PC Lokal Bell:**
- Python: 3.14
- OS: Windows 11
- COM Port: COM5
- Baud Rate: 9600

---

## 📞 **Support Flow**

1. **Issue saat deploy?** → COOLIFY_DEPLOYMENT_GUIDE.md
2. **HTTP 500 error?** → DEPLOYMENT_PRODUCTION.md (manual fix)
3. **Bridge can't connect?** → TROUBLESHOOTING_API.md
4. **Need fresh setup?** → SETUP_CHECKLIST_MTSN2.md
5. **Hardware not responding?** → PYTHON_BRIDGE_SETUP_PRODUCTION.md

---

## 🎯 **Current Status**

### **✅ Completed:**
- [x] Docker auto-migration configured
- [x] Hardware check command created
- [x] Python Bridge scripts ready
- [x] Test tools available
- [x] Complete documentation

### **⏳ Pending:**
- [ ] Deploy migrations to production (via Coolify or manual)
- [ ] Test Python Bridge connection
- [ ] Test hardware Modbus RS485
- [ ] Setup auto-start Python Bridge

---

## 🚀 **Next Steps**

### **Step 1: Deploy to Production**

**Via Coolify:**
```bash
git push origin main
# Wait for auto-deploy
```

**Manual:**
```bash
ssh user@187.77.117.23
cd /var/www/bell.mtsn2kotamalang.sch.id
git pull origin main
php artisan migrate --force
php artisan hardware:check-migrations --seed
```

### **Step 2: Test API**

```cmd
cd C:\BelSekolahBridge
python test_api_connection.py
```

**Expected:** `Passed: 4/4`

### **Step 3: Run Python Bridge**

```cmd
python python_bridge.py
```

**Expected:** Polling server, no errors

### **Step 4: Test End-to-End**

1. Login ke https://bell.mtsn2kotamalang.sch.id
2. Menu Hardware → Hardware Control
3. Klik ON button pada room
4. Speaker harus nyala! 🔊

---

**All documentation complete! Ready for deployment!** 🎉
