# Troubleshooting: Repository Git Tidak Ditemukan

## Problem

Saat mengklik "Cek Update" di halaman Settings, muncul error:
```
Repository git tidak ditemukan di direktori aplikasi.
```

## Penyebab

Aplikasi tidak bisa menemukan direktori `.git` di path yang dicek secara otomatis. Ini bisa terjadi karena:

1. **Deployment di shared hosting** - Path aplikasi berbeda dengan struktur standard Laravel
2. **Symlink deployment** - Direktori actual berbeda dengan `base_path()`
3. **Docker/Container** - Repository git tidak di-mount ke dalam container
4. **Permission issue** - PHP tidak punya akses ke direktori `.git`

## Solusi

### Opsi 1: Set Repository Path Manual (Recommended)

1. Login ke aplikasi sebagai admin
2. Buka menu **Settings**
3. Klik tab **Update**
4. Di bagian **"Konfigurasi Repository (Opsional)"**, isi path lengkap ke direktori aplikasi
5. Klik **Simpan Path**

**Contoh path:**
- Linux/VPS: `/var/www/html/laravel_bel_mtsn2kotamalang`
- Windows: `C:\laravel_bel_mtsn2kotamalang`
- Docker: `/app` atau `/var/www/html`

### Opsi 2: Inisialisasi Git di Deployment

Jika `.git` tidak ada di server production:

```bash
# SSH ke server
cd /path/to/aplikasi

# Clone fresh dari GitHub
git clone https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang.git temp
mv temp/.git .git
rm -rf temp

# Atau init git baru
git init
git remote add origin https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang.git
git fetch origin
git checkout main
```

### Opsi 3: Check Logs untuk Debugging

Jika masih error, check Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

Cari log dengan prefix:
- `getRepositoryPath: Path tidak valid`
- `getRepositoryPath: .git tidak ditemukan`
- `getRepositoryPath: Error git command`

Log ini akan menunjukkan path mana yang sudah dicoba.

## Verifikasi Path yang Benar

Untuk memastikan path yang benar:

```bash
# SSH ke server
cd /path/to/aplikasi

# Check apakah .git ada
ls -la .git

# Check apakah git command berfungsi
git rev-parse --is-inside-work-tree
# Output harus: true

# Lihat remote URL
git remote get-url origin
# Output: https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang.git

# Lihat absolute path
pwd
# Copy path ini untuk diisi di Settings
```

## Path Kandidat yang Dicek Otomatis

Sistem akan otomatis mencoba path berikut (berurutan):

1. **Configured path** - Path yang disimpan di Settings (`update_repo_path`)
2. **base_path()** - Direktori root Laravel (biasanya benar)
3. **base_path('..')** - Satu level di atas
4. **public_path('..')** - Dari direktori public

Jika tidak ada yang cocok, Anda harus set manual.

## Update di Environment Tanpa Git

Jika server production tidak bisa install git, gunakan cara manual:

1. Download ZIP dari GitHub: https://github.com/KamaludinZ/laravel_bel_mtsn2kotamalang/archive/refs/heads/main.zip
2. Extract dan upload via FTP/SFTP
3. Jalankan migrasi:
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Prevention

Untuk deployment future, pastikan:

1. `.git` directory included di deployment
2. Git installed di server
3. PHP punya read permission ke `.git`
4. Path aplikasi konsisten

## Need Help?

Jika masih error setelah semua solusi di atas:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs (nginx/apache)
3. Pastikan git installed: `which git` atau `git --version`
4. Contact developer dengan info:
   - Server OS & version
   - PHP version
   - Deployment method (manual/Coolify/Docker/etc)
   - Full error message dari logs
