# Setup PC Khusus Bel (Tanpa Sentuhan)

Tujuan: PC menyala → bel berjalan otomatis seharian, **tanpa perlu klik apa pun**,
termasuk setelah listrik padam / PC restart.

## 1. Izinkan suara otomatis
Klik kanan `izinkan-autoplay.reg` → **Merge** (sebagai Administrator).
Cek di Chrome: buka `chrome://policy` → `AutoplayAllowed` harus bernilai `true`.

## 2. Jalankan bel otomatis saat Windows menyala
1. Tekan `Win + R`, ketik `shell:startup`, Enter.
2. Buat **shortcut** dari `jalankan-bel.bat` ke folder tersebut.

Script membuka halaman bel layar penuh, mengizinkan suara tanpa klik,
dan membuka ulang browser bila tertutup/crash.
Untuk keluar dari layar penuh: `Alt + F4` setelah menutup jendela hitam (cmd).

## 3. Pengaturan Windows (wajib)
- **Login otomatis**: `Win + R` → `netplwiz` → hilangkan centang
  *"Users must enter a user name and password"* (agar setelah listrik padam tidak berhenti di layar login).
- **Jangan tidur**: Settings → System → Power → Screen & Sleep → semua **Never**.
- **BIOS**: aktifkan *"Restore on AC Power Loss" = Power On* agar PC menyala sendiri saat listrik kembali.
- **Windows Update**: atur *Active hours* sesuai jam sekolah agar tidak restart saat jam pelajaran.
- **Volume**: pastikan speaker tidak mute dan volume Windows cukup.

## Cara cek berhasil
Di halaman bel (pengguna Mode Otomatis) tampil **🔊 Suara bel aktif** tanpa diklik.
Jika tampil **🔇 Suara belum aktif**, langkah 1 atau 2 belum berlaku.
