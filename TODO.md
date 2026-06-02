# TODO - Perbaikan Bel & Menu Update (v1.0.1)

- [x] Analisis file terkait landing page public dan settings
- [x] Update `resources/views/public/index.blade.php`
  - [x] Tambah tombol Stop Paksa pada kolom aksi
  - [x] Tambah fungsi force stop audio schedule
  - [x] Perbaiki auto-play agar jadwal hanya dipicu 1x per menit
  - [x] Perbaiki pengecekan status current schedule berbasis id
- [x] Update `resources/views/settings/index.blade.php`
  - [x] Tambah tab/menu Update
  - [x] Tambah konten informasi update dari repository GitHub
  - [x] Tampilkan versi sebelum (v1.0.0) dan sesudah perbaikan (v1.0.1)
- [x] Review akhir perubahan
- [ ] Implementasi fitur cek update + konfirmasi update 1 klik
  - [ ] Tambah endpoint cek update dari GitHub
  - [ ] Tambah endpoint proses update setelah konfirmasi
  - [ ] Tambah tombol dan alur konfirmasi di tab Update (cek -> tanya -> proses)
  - [ ] Tampilkan notifikasi jika tidak ada update / sukses / gagal
