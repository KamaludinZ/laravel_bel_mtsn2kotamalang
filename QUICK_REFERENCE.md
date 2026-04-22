# 🚀 QUICK REFERENCE - Aplikasi Bel Sekolah MTsN 2 Kota Malang

## 📋 Akses Cepat

### 🌐 URLs
```
Landing Page : http://localhost:8000
Login Admin  : http://localhost:8000/login
Dashboard    : http://localhost:8000/dashboard
```

### 🔑 Login Credentials
```
Admin:
Email    : admin@mtsn2kotamalang.sch.id
Password : admin123

Operator:
Email    : operator@mtsn2kotamalang.sch.id
Password : operator123
```

---

## 🎨 Halaman Error yang Sudah Dibuat

### 1️⃣ **404 - Not Found** (Blue Gradient)
- **Path**: `resources/views/errors/404.blade.php`
- **Design**: Blue gradient background
- **Fitur**: Icon sad face, tombol kembali ke home & halaman sebelumnya

### 2️⃣ **403 - Forbidden** (Orange Gradient)
- **Path**: `resources/views/errors/403.blade.php`
- **Design**: Orange gradient background
- **Fitur**: Lock icon, tombol home & login/dashboard

### 3️⃣ **500 - Server Error** (Red Gradient)
- **Path**: `resources/views/errors/500.blade.php`
- **Design**: Red gradient background
- **Fitur**: Warning icon, error message di debug mode, tombol reload

### 4️⃣ **503 - Maintenance** (Purple Gradient)
- **Path**: `resources/views/errors/503.blade.php`
- **Design**: Purple gradient background
- **Fitur**: Gear icon dengan animasi, estimasi waktu, auto-refresh setiap 5 menit

### ✨ Semua Error Pages:
- ✅ Glassmorphism design konsisten
- ✅ Gradient background berbeda per error
- ✅ Icon SVG yang informatif
- ✅ Responsive mobile-friendly
- ✅ Action buttons yang jelas
- ✅ Animation & transitions

---

## 🛠️ Command Yang Sering Dipakai

### Database
```bash
# Fresh migration + seed
php artisan migrate:fresh --seed

# Jalankan seeder tertentu
php artisan db:seed --class=SettingSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=BellTypeSeeder
php artisan db:seed --class=AudioLibrarySeeder

# Show database info
php artisan db:show
```

### Cache
```bash
# Clear all cache
php artisan optimize:clear

# Clear specific cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Development
```bash
# Start Laravel dev server
php artisan serve

# Start Vite dev server (terminal baru)
npm run dev

# Build untuk production
npm run build
```

### Docker
```bash
# Start PostgreSQL container
docker-compose up -d

# Stop container
docker-compose down

# Check container status
docker-compose ps

# View logs
docker-compose logs postgres
```

---

## 📁 Struktur File Penting

### Controllers
```
app/Http/Controllers/
├── AudioLibraryController.php    # Kelola audio
├── BellScheduleController.php    # Kelola jadwal
├── BellTypeController.php        # Kelola jenis bel
├── PublicController.php          # Landing page & API
├── SettingController.php         # Pengaturan app
└── ProfileController.php         # User profile
```

### Models
```
app/Models/
├── AudioLibrary.php              # Model audio
├── BellSchedule.php              # Model jadwal
├── BellType.php                  # Model jenis bel
├── Setting.php                   # Model settings
└── User.php                      # Model user
```

### Views
```
resources/views/
├── auth/
│   └── login.blade.php           # Halaman login dengan eye icon
├── layouts/
│   ├── app.blade.php             # Layout admin
│   ├── guest.blade.php           # Layout auth (blue gradient)
│   └── public.blade.php          # Layout public
├── errors/
│   ├── 403.blade.php             # Forbidden (orange)
│   ├── 404.blade.php             # Not Found (blue)
│   ├── 500.blade.php             # Server Error (red)
│   └── 503.blade.php             # Maintenance (purple)
├── public/
│   └── index.blade.php           # Landing page
├── audio-libraries/              # CRUD audio
├── bell-types/                   # CRUD jenis bel
├── bell-schedules/               # CRUD jadwal
└── settings/                     # Pengaturan
```

---

## 🗄️ Database Tables

### 1. users
```sql
- id (bigint, primary)
- name (varchar)
- email (varchar, unique)
- password (varchar)
- email_verified_at (timestamp)
- created_at, updated_at
```

### 2. settings
```sql
- id (uuid, primary)
- key (varchar, unique)
- value (text, nullable)
- created_at, updated_at
```

### 3. audio_libraries
```sql
- id (uuid, primary)
- title (varchar)
- file_path (varchar)
- created_at, updated_at
```

### 4. bell_types
```sql
- id (uuid, primary)
- name (varchar)
- is_active (boolean)
- created_at, updated_at
```

### 5. bell_schedules
```sql
- id (uuid, primary)
- bell_type_id (uuid, foreign -> bell_types)
- audio_library_id (uuid, foreign -> audio_libraries)
- time (time)
- is_active (boolean)
- created_at, updated_at
```

---

## 🎯 Fitur Landing Page

### Real-time Features
- ⏰ Digital clock (update setiap detik)
- 📅 Tanggal dalam Bahasa Indonesia
- 🔔 Jenis bel aktif
- 📋 Jadwal hari ini
- 🔊 Auto-play audio (jika mode otomatis aktif)
- 🔄 Auto-refresh jadwal (setiap 5 menit)

### API Endpoints
```
GET /api/today-schedule    # Jadwal hari ini
GET /api/current-time      # Waktu saat ini
```

### JavaScript Functions (Alpine.js)
```javascript
bellSystem() {
  - updateClock()           // Update jam setiap detik
  - fetchSchedule()         // Ambil jadwal dari API
  - checkAndPlaySchedule()  // Cek & play audio otomatis
  - playAudio(schedule)     // Play audio
  - isCurrentSchedule()     // Cek jadwal sedang diputar
}
```

---

## 🎨 Design System

### Color Palette
```css
/* Landing Page & Login */
Background: bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900

/* Error Pages */
404: bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900
403: bg-gradient-to-br from-orange-900 via-orange-800 to-red-900
500: bg-gradient-to-br from-red-900 via-red-800 to-orange-900
503: bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900

/* Glassmorphism Card */
bg-white/10 backdrop-blur-sm border border-white/20
```

### Typography
```css
Heading XL : text-9xl font-bold
Heading 1  : text-3xl font-bold
Heading 2  : text-2xl font-bold
Body Large : text-lg
Body Normal: text-base
Small Text : text-sm
```

### Spacing
```css
Card Padding   : p-8
Button Padding : px-6 py-3
Gap Default    : gap-3
Margin Default : mb-6
```

---

## 🔧 Troubleshooting

### Error: "Unable to locate a class or view for component"
```bash
# Clear cache
php artisan view:clear
php artisan config:clear
```

### Error: "SQLSTATE[08006] could not connect to server"
```bash
# Check Docker container
docker-compose ps

# Restart container
docker-compose down
docker-compose up -d

# Clear Laravel cache
php artisan config:clear
```

### Error: "Class not found"
```bash
# Regenerate autoload
composer dump-autoload
```

### Error: "Mix manifest not found"
```bash
# Rebuild assets
npm run build
```

### Error: "Storage not found"
```bash
# Create symlink
php artisan storage:link
```

---

## 📦 Data Seeding

### Default Settings
- **app_name**: "Bel Sekolah MTsN 2 Kota Malang"
- **app_logo**: null (bisa diupload nanti)

### Default Users (2)
1. **Admin**: admin@mtsn2kotamalang.sch.id / admin123
2. **Operator**: operator@mtsn2kotamalang.sch.id / operator123

### Default Bell Types (4)
1. Hari Senin - Kamis (inactive)
2. Hari Jumat (inactive)
3. Hari Sabtu (active) ⭐
4. Hari Libur (inactive)

### Default Audio Libraries (5)
1. Bel Masuk Sekolah
2. Bel Masuk Kelas
3. Bel Istirahat
4. Bel Pulang
5. Musik Islami

---

## 🚨 Important Notes

### ⚠️ Belum Implementasi
- ❌ Upload audio (file dummy)
- ❌ Auto-play scheduler (Laravel Scheduler)
- ❌ Excel import/export (controller ada, belum test)
- ❌ Role & Permission system
- ❌ Dashboard statistics
- ❌ Email notifications

### ✅ Sudah Berfungsi
- ✅ Login & Authentication
- ✅ CRUD semua entitas
- ✅ Landing page real-time
- ✅ API endpoints
- ✅ Error pages
- ✅ Responsive design
- ✅ Database seeding

---

## 📚 Referensi

### Documentation
- [Laravel Docs](https://laravel.com/docs)
- [TailwindCSS Docs](https://tailwindcss.com/docs)
- [Alpine.js Docs](https://alpinejs.dev)

### Stack Overflow Tags
- `laravel`
- `postgresql`
- `tailwindcss`
- `alpine.js`

---

## 📞 Untuk Developer Selanjutnya

### Prioritas Implementasi
1. 🔴 **HIGH**: Audio upload system
2. 🔴 **HIGH**: Laravel Scheduler untuk auto-play
3. 🔴 **HIGH**: Testing (Unit & Feature tests)
4. 🟡 **MEDIUM**: Dashboard statistics
5. 🟡 **MEDIUM**: Role & Permission
6. 🟢 **LOW**: Documentation lengkap

### File Penting untuk Review
1. `PROGRESS_REPORT.md` - Laporan lengkap progres
2. `routes/web.php` - Semua routes
3. `app/Http/Controllers/*` - Logic aplikasi
4. `resources/views/public/index.blade.php` - Landing page

---

**Happy Coding! 🚀**

*Last Updated: 5 April 2026*
