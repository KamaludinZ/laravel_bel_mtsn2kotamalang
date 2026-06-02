# Fix Storage 404 Error di Production

## Masalah

```
GET http://app1.mtsn2kotamalang.sch.id/storage/logos/1777013507_RESMI-RE-LOGO_11zon.png
[HTTP/1.1 404 Not Found]
```

File logo (dan file upload lainnya) tidak ditemukan di production.

## Penyebab

Ada 2 kemungkinan:

1. **Storage link belum dibuat** - Symlink dari `public/storage` ke `storage/app/public` belum ada
2. **File belum di-upload** - File yang di-upload di local tidak ter-copy ke server production

---

## Solusi

### Langkah 1: Buat Storage Link di Production

SSH ke server production dan jalankan:

```bash
cd /path/to/laravel_bel_mtsn2kotamalang

# Hapus symlink lama jika ada (aman jika tidak ada)
rm -f public/storage

# Buat symlink baru
php artisan storage:link

# Verifikasi symlink berhasil dibuat
ls -la public/storage
# Output seharusnya:
# lrwxrwxrwx ... public/storage -> /path/to/storage/app/public
```

**Penjelasan:**
- `public/storage` adalah symlink (symbolic link)
- Mengarah ke `storage/app/public`
- Laravel menyimpan upload file di `storage/app/public/`
- Browser mengakses via URL `/storage/` yang di-map ke `public/storage`

---

### Langkah 2: Upload File dari Local ke Production

Ada 2 opsi:

#### Opsi A: Upload Folder Storage (Termasuk File yang Sudah Ada)

Dari komputer local, upload folder:
- `storage/app/public/logos/`
- `storage/app/public/audios/` (jika ada)
- Semua subfolder di `storage/app/public/`

**Via FTP/SFTP:**
1. Buka FileZilla / WinSCP
2. Upload folder `storage/app/public/` dari local
3. Paste ke `storage/app/public/` di server

**Via SCP (command line):**
```bash
# Dari komputer local
scp -r storage/app/public/* user@server:/path/to/laravel/storage/app/public/
```

**Via rsync (lebih efisien):**
```bash
# Dari komputer local
rsync -avz storage/app/public/ user@server:/path/to/laravel/storage/app/public/
```

#### Opsi B: Re-upload Logo di Production

Jika file tidak banyak, upload ulang via web interface:

1. Login ke `/settings` di production
2. Upload logo baru
3. File akan otomatis tersimpan di `storage/app/public/logos/`

---

### Langkah 3: Set Permission yang Benar

Di server production:

```bash
# Set ownership ke web server user
sudo chown -R www-data:www-data storage/
# Atau untuk Nginx:
# sudo chown -R nginx:nginx storage/

# Set permission
sudo chmod -R 775 storage/
sudo chmod -R 775 storage/app/public/

# Pastikan public/storage readable
sudo chmod 755 public/storage
```

---

### Langkah 4: Verifikasi

#### Cek 1: Symlink Ada?

```bash
ls -la public/storage
# Output:
# lrwxrwxrwx ... public/storage -> /path/to/storage/app/public
```

#### Cek 2: File Ada di Storage?

```bash
ls -la storage/app/public/logos/
# Seharusnya muncul file logo
```

#### Cek 3: File Bisa Diakses via Web?

```bash
curl -I http://app1.mtsn2kotamalang.sch.id/storage/logos/1777013507_RESMI-RE-LOGO_11zon.png
# Seharusnya: HTTP/1.1 200 OK
```

#### Cek 4: Browser

Refresh browser dan cek console, error 404 seharusnya hilang.

---

## Troubleshooting

### Error: "The [public/storage] link already exists"

Hapus symlink lama dulu:

```bash
rm public/storage
php artisan storage:link
```

### Error: "symlink(): No such file or directory"

Folder `storage/app/public` belum ada, buat dulu:

```bash
mkdir -p storage/app/public/logos
mkdir -p storage/app/public/audios
php artisan storage:link
```

### File Masih 404 Meskipun Sudah Ada

Cek permission:

```bash
# Cek permission file
ls -la storage/app/public/logos/1777013507_RESMI-RE-LOGO_11zon.png
# Harus readable (minimal 644)

# Fix permission
chmod 644 storage/app/public/logos/*
chmod 755 storage/app/public/logos/
```

### Symlink Tidak Bisa Dibuat (Shared Hosting)

Beberapa shared hosting disable `symlink()`. Solusi:

**Opsi 1: Manual Symlink via cPanel/SSH**

```bash
ln -s /home/user/laravel/storage/app/public /home/user/public_html/storage
```

**Opsi 2: Ubah Filesystem Config**

Edit `config/filesystems.php`:

```php
'public' => [
    'driver' => 'local',
    'root' => public_path('uploads'), // Ubah ke public/uploads
    'url' => env('APP_URL').'/uploads',
    'visibility' => 'public',
],
```

Kemudian buat folder `public/uploads/` dan set permission 775.

**Opsi 3: Ubah Disk di Model**

Jika tidak bisa ubah config, ubah di controller upload:

```php
// Dari:
Storage::disk('public')->put('logos', $file);

// Menjadi:
$file->move(public_path('uploads/logos'), $filename);
```

---

## Struktur Folder Storage yang Benar

```
laravel/
├── public/
│   ├── storage/  ← SYMLINK mengarah ke ../../storage/app/public
│   ├── build/
│   └── index.php
├── storage/
│   ├── app/
│   │   ├── public/  ← File upload disimpan di sini
│   │   │   ├── logos/
│   │   │   │   └── 1777013507_RESMI-RE-LOGO_11zon.png
│   │   │   └── audios/
│   │   │       └── 1775366098_5is.wav
│   │   └── private/
│   ├── logs/
│   └── framework/
```

**URL Mapping:**
```
http://app1.mtsn2kotamalang.sch.id/storage/logos/xxx.png
                                   ↓
                            public/storage/logos/xxx.png (symlink)
                                   ↓
                            storage/app/public/logos/xxx.png (actual file)
```

---

## Command Ringkas (Copy-Paste)

```bash
# Di server production

# 1. Buat storage link
php artisan storage:link

# 2. Buat folder jika belum ada
mkdir -p storage/app/public/logos
mkdir -p storage/app/public/audios

# 3. Set permission
sudo chmod -R 775 storage/app/public
sudo chown -R www-data:www-data storage/

# 4. Verifikasi
ls -la public/storage
ls -la storage/app/public/logos/

# 5. Test akses file
curl -I http://app1.mtsn2kotamalang.sch.id/storage/logos/1777013507_RESMI-RE-LOGO_11zon.png
```

---

## Cara Upload File dari Local ke Production

### Metode 1: SCP (Recommended)

```bash
# Dari komputer local
scp -r storage/app/public/logos user@server:/path/to/laravel/storage/app/public/
scp -r storage/app/public/audios user@server:/path/to/laravel/storage/app/public/
```

### Metode 2: Rsync (Lebih Cepat)

```bash
# Dari komputer local
rsync -avz --progress storage/app/public/ user@server:/path/to/laravel/storage/app/public/
```

### Metode 3: FTP/SFTP (FileZilla/WinSCP)

1. Connect ke server
2. Navigate ke `storage/app/public/`
3. Upload folder `logos/` dan `audios/`

### Metode 4: ZIP Upload

```bash
# Di local
cd storage/app
zip -r public.zip public/

# Upload public.zip ke server via FTP

# Di server
cd storage/app
unzip public.zip
rm public.zip
chmod -R 775 public/
```

---

## Pencegahan untuk Deployment Berikutnya

### 1. Tambahkan ke Deploy Script

Edit `deploy.sh`:

```bash
echo "🔗 Creating storage link..."
php artisan storage:link

echo "📁 Setting storage permissions..."
chmod -R 775 storage/app/public
```

### 2. Backup Storage Sebelum Deploy

```bash
# Di server production, backup storage
tar -czf storage-backup-$(date +%Y%m%d).tar.gz storage/app/public/
```

### 3. Jangan Commit File Upload ke Git

Pastikan `.gitignore` sudah ada:

```
/storage/app/public/*
!/storage/app/public/.gitignore
```

---

## Checklist Storage

- [ ] `php artisan storage:link` sudah dijalankan
- [ ] `public/storage` adalah symlink (bukan folder biasa)
- [ ] Folder `storage/app/public/logos/` ada
- [ ] Folder `storage/app/public/audios/` ada
- [ ] File upload sudah di-copy dari local ke server
- [ ] Permission storage adalah 775
- [ ] Owner storage adalah www-data/nginx
- [ ] File bisa diakses via browser (200 OK)
- [ ] Tidak ada error 404 di console

---

**Setelah semua langkah di atas, refresh browser dan logo seharusnya muncul! 🎨**
