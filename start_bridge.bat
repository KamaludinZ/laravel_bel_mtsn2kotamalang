@echo off
REM Python Bridge Startup Script
REM Server: https://bell.mtsn2kotamalang.sch.id

echo ============================================================
echo Python Bridge for Bell System - MTsN 2 Kota Malang
echo ============================================================
echo.

REM Get the directory where this batch file is located
cd /d "%~dp0"

echo Current directory: %CD%
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python from https://www.python.org/downloads/
    echo Make sure to check "Add Python to PATH" during installation
    pause
    exit /b 1
)

echo Python version:
python --version
echo.

REM Check if config.json exists
if not exist config.json (
    echo ERROR: config.json not found
    echo.
    echo Creating config.json from example...
    copy config.json.example config.json
    echo.
    echo Please edit config.json with your settings:
    echo - COM port (check Device Manager)
    echo - API token (from server .env)
    echo.
    echo Then run this script again.
    pause
    exit /b 1
)

echo Config file: OK
echo.

REM Check if required modules are installed
python -c "import serial" >nul 2>&1
if errorlevel 1 (
    echo Installing required module: pyserial...
    pip install pyserial
    echo.
)

python -c "import requests" >nul 2>&1
if errorlevel 1 (
    echo Installing required module: requests...
    pip install requests
    echo.
)

echo Dependencies: OK
echo.

echo Starting Python Bridge...
echo Press Ctrl+C to stop
echo.

REM Run the bridge
python python_bridge.py

echo.
echo Bridge stopped
pause
