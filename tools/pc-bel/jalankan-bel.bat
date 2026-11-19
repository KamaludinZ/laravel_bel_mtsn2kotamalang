@echo off
REM ============================================================
REM  Peluncur Bel Sekolah untuk PC khusus bel (tanpa sentuhan)
REM  - Membuka halaman bel layar penuh (kiosk)
REM  - Mengizinkan suara otomatis tanpa klik (autoplay)
REM  - Mencegah browser memperlambat timer / membekukan tab
REM  - Otomatis membuka ulang jika browser tertutup / crash
REM  Taruh shortcut file ini di folder Startup (Win+R -> shell:startup)
REM ============================================================

set "BEL_URL=https://bell.mtsn2kotamalang.sch.id/"
set "PROFILE_DIR=%LOCALAPPDATA%\BelSekolahBrowser"

set "BROWSER="
if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" set "BROWSER=%ProgramFiles%\Google\Chrome\Application\chrome.exe"
if not defined BROWSER if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" set "BROWSER=%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"
if not defined BROWSER if exist "%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe" set "BROWSER=%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe"
if not defined BROWSER if exist "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" set "BROWSER=%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe"
if not defined BROWSER if exist "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" set "BROWSER=%ProgramFiles%\Microsoft\Edge\Application\msedge.exe"

if not defined BROWSER (
    echo Chrome / Edge tidak ditemukan. Silakan install Google Chrome.
    pause
    exit /b 1
)

REM Tunggu jaringan siap setelah PC menyala
timeout /t 20 /nobreak >nul

:loop
echo [%date% %time%] Membuka halaman bel...
"%BROWSER%" ^
  --user-data-dir="%PROFILE_DIR%" ^
  --kiosk ^
  --autoplay-policy=no-user-gesture-required ^
  --disable-background-timer-throttling ^
  --disable-renderer-backgrounding ^
  --disable-backgrounding-occluded-windows ^
  --no-first-run ^
  --no-default-browser-check ^
  --disable-session-crashed-bubble ^
  --noerrdialogs ^
  "%BEL_URL%"

echo [%date% %time%] Browser tertutup, membuka ulang dalam 10 detik...
echo (Tutup jendela hitam ini dulu jika ingin menghentikan bel)
timeout /t 10 /nobreak >nul
goto loop
