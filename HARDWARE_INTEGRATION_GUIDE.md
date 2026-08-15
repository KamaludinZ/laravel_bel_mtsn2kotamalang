# 🔧 Hardware Integration Guide - MTsN 2 Kota Malang Bell System

## 📋 Daftar Isi
1. [Arsitektur Sistem](#arsitektur-sistem)
2. [Instalasi Laravel Backend (VPS)](#instalasi-laravel-backend)
3. [Instalasi Python Bridge (PC Sekolah)](#instalasi-python-bridge)
4. [Konfigurasi](#konfigurasi)
5. [Testing](#testing)
6. [Troubleshooting](#troubleshooting)

---

## 🏗️ Arsitektur Sistem

```
VPS (Coolify Docker)              PC Sekolah (Windows)
┌──────────────────┐              ┌────────────────────┐
│ Laravel App      │◄─────────────┤ Python Bridge      │
│ - Queue Commands │  HTTP Poll   │ - Poll VPS         │
│ - Web UI         │  (5 detik)   │ - Execute Modbus   │
└──────────────────┘              └─────────┬──────────┘
                                            │ USB
                                            ↓
                                  ┌────────────────────┐
                                  │ USB-RS485 Modbus   │
                                  │ P/N 242411         │
                                  └─────────┬──────────┘
                                            │ RS485
                                            ↓
                                  ┌────────────────────┐
                                  │ Relay Module       │
                                  │ 8 Channel          │
                                  └─────────┬──────────┘
                                            │
                                            ↓
                                  ┌────────────────────┐
                                  │ Speaker System     │
                                  └────────────────────┘
```

---

## 📦 Instalasi Laravel Backend (VPS)

### Step 1: Run Migrations

```bash
php artisan migrate
```

Ini akan membuat tabel:
- `hardware_command_queue` - Queue command untuk bridge
- `hardware_configs` - Konfigurasi perangkat
- `speaker_zones` - Mapping zone speaker
- `hardware_logs` - Log aktivitas hardware

### Step 2: Seed Data Awal (Opsional)

Buat seeder untuk data awal:

```bash
php artisan make:seeder HardwareSeeder
```

Edit `database/seeders/HardwareSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\HardwareConfig;
use App\Models\SpeakerZone;
use Illuminate\Database\Seeder;

class HardwareSeeder extends Seeder
{
    public function run(): void
    {
        // Create default hardware config
        HardwareConfig::create([
            'config_key' => 'primary_device',
            'device_type' => 'modbus_rs485',
            'connection_type' => 'usb',
            'com_port' => 'COM3', // Sesuaikan dengan COM port Anda
            'baud_rate' => 9600,
            'data_bits' => 8,
            'parity' => 'N',
            'stop_bits' => 1,
            'modbus_address' => 1,
            'is_enabled' => true,
            'auto_reconnect' => true,
            'timeout_ms' => 1000,
        ]);

        // Create speaker zones
        $zones = [
            ['name' => 'Halaman Sekolah', 'modbus_channel' => 1, 'description' => 'Speaker area halaman utama'],
            ['name' => 'Ruang Kelas Lantai 1', 'modbus_channel' => 2, 'description' => 'Speaker kelas lt 1'],
            ['name' => 'Ruang Kelas Lantai 2', 'modbus_channel' => 3, 'description' => 'Speaker kelas lt 2'],
            ['name' => 'Masjid', 'modbus_channel' => 4, 'description' => 'Speaker masjid sekolah'],
            ['name' => 'Kantor Guru', 'modbus_channel' => 5, 'description' => 'Speaker kantor guru'],
            ['name' => 'Perpustakaan', 'modbus_channel' => 6, 'description' => 'Speaker perpustakaan'],
            ['name' => 'Laboratorium', 'modbus_channel' => 7, 'description' => 'Speaker lab komputer/IPA'],
            ['name' => 'Reserved', 'modbus_channel' => 8, 'description' => 'Channel cadangan', 'is_enabled' => false],
        ];

        foreach ($zones as $index => $zone) {
            SpeakerZone::create(array_merge($zone, [
                'sort_order' => $index + 1,
                'default_duration_seconds' => 180, // 3 menit
                'volume_level' => 100,
                'is_enabled' => $zone['is_enabled'] ?? true,
            ]));
        }
    }
}
```

Run seeder:

```bash
php artisan db:seed --class=HardwareSeeder
```

### Step 3: Generate API Token untuk Bridge

Buat environment variable untuk API token:

```env
HARDWARE_BRIDGE_API_TOKEN=your-secret-token-here-min-32-chars
```

Generate token:

```bash
php artisan tinker
```

```php
echo bin2hex(random_bytes(32));
// Copy output ke .env
```

---

## 🐍 Instalasi Python Bridge (PC Sekolah)

### Step 1: Install Python 3.8+

Download dari: https://www.python.org/downloads/

**PENTING**: Centang "Add Python to PATH" saat instalasi!

### Step 2: Buat Folder Bridge Service

```cmd
cd C:\
mkdir BellBridgeService
cd BellBridgeService
```

### Step 3: Buat File `requirements.txt`

```txt
pymodbus>=3.5.0
pyserial>=3.5
requests>=2.31.0
python-dotenv>=1.0.0
schedule>=1.2.0
```

### Step 4: Install Dependencies

```cmd
pip install -r requirements.txt
```

### Step 5: Buat File `.env`

```env
# VPS Configuration
VPS_BASE_URL=https://your-domain.com
API_TOKEN=your-secret-token-from-laravel

# Polling Configuration
POLL_INTERVAL_SECONDS=5
RECONNECT_RETRY_SECONDS=10

# Modbus Configuration (akan diambil dari VPS, ini fallback)
COM_PORT=COM3
BAUD_RATE=9600
DATA_BITS=8
PARITY=N
STOP_BITS=1
MODBUS_ADDRESS=1
TIMEOUT_MS=1000

# Logging
LOG_LEVEL=INFO
LOG_FILE=bridge.log
```

### Step 6: Buat File `bridge_service.py`

```python
import time
import json
import logging
import sys
import os
import requests
import serial
from datetime import datetime
from pymodbus.client import ModbusSerialClient
from pymodbus.exceptions import ModbusException
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Configuration
VPS_BASE_URL = os.getenv('VPS_BASE_URL')
API_TOKEN = os.getenv('API_TOKEN')
POLL_INTERVAL = int(os.getenv('POLL_INTERVAL_SECONDS', 5))
COM_PORT = os.getenv('COM_PORT', 'COM3')
BAUD_RATE = int(os.getenv('BAUD_RATE', 9600))
MODBUS_ADDRESS = int(os.getenv('MODBUS_ADDRESS', 1))
TIMEOUT_MS = int(os.getenv('TIMEOUT_MS', 1000))

# Version
BRIDGE_VERSION = "1.0.0"

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('bridge.log'),
        logging.StreamHandler(sys.stdout)
    ]
)
logger = logging.getLogger(__name__)

class BellBridgeService:
    def __init__(self):
        self.modbus_client = None
        self.is_connected = False
        self.config = {}

    def connect_modbus(self):
        """Connect to Modbus device via RS485"""
        try:
            logger.info(f"Connecting to Modbus on {COM_PORT} @ {BAUD_RATE} baud...")

            self.modbus_client = ModbusSerialClient(
                port=COM_PORT,
                baudrate=BAUD_RATE,
                bytesize=8,
                parity='N',
                stopbits=1,
                timeout=TIMEOUT_MS / 1000.0
            )

            if self.modbus_client.connect():
                self.is_connected = True
                logger.info("✓ Modbus connected successfully")
                return True
            else:
                logger.error("✗ Failed to connect to Modbus")
                return False

        except Exception as e:
            logger.error(f"✗ Modbus connection error: {e}")
            self.is_connected = False
            return False

    def disconnect_modbus(self):
        """Disconnect from Modbus device"""
        if self.modbus_client:
            self.modbus_client.close()
            self.is_connected = False
            logger.info("Modbus disconnected")

    def write_relay(self, channel, state):
        """
        Write relay state (ON/OFF)
        channel: 1-8
        state: True (ON) / False (OFF)
        """
        if not self.is_connected:
            return False, "Not connected to Modbus device"

        try:
            # Modbus coil address (0-based, so channel 1 = address 0)
            coil_address = channel - 1

            result = self.modbus_client.write_coil(
                address=coil_address,
                value=state,
                slave=MODBUS_ADDRESS
            )

            if result.isError():
                return False, f"Modbus error: {result}"

            status = "ON" if state else "OFF"
            logger.info(f"✓ Relay Channel {channel} turned {status}")
            return True, f"Channel {channel} {status}"

        except ModbusException as e:
            logger.error(f"✗ Modbus exception: {e}")
            return False, str(e)
        except Exception as e:
            logger.error(f"✗ Error writing relay: {e}")
            return False, str(e)

    def trigger_bell(self, zones, duration_seconds):
        """
        Trigger bell for specified zones
        zones: list of channel numbers [1, 2, 3]
        duration_seconds: how long to keep ON
        """
        logger.info(f"Triggering bell on zones {zones} for {duration_seconds}s")

        # Turn ON all zones
        for zone in zones:
            success, message = self.write_relay(zone, True)
            if not success:
                return False, f"Failed to turn ON zone {zone}: {message}"

        # Wait for duration
        time.sleep(duration_seconds)

        # Turn OFF all zones
        for zone in zones:
            success, message = self.write_relay(zone, False)
            if not success:
                logger.warning(f"Failed to turn OFF zone {zone}: {message}")

        return True, f"Bell triggered on zones {zones} for {duration_seconds}s"

    def fetch_pending_commands(self):
        """Fetch pending commands from VPS"""
        try:
            headers = {
                'Authorization': f'Bearer {API_TOKEN}',
                'Content-Type': 'application/json'
            }

            response = requests.get(
                f"{VPS_BASE_URL}/api/hardware/pending-commands",
                headers=headers,
                timeout=10
            )

            if response.status_code == 200:
                data = response.json()
                return data.get('commands', [])
            else:
                logger.warning(f"Failed to fetch commands: HTTP {response.status_code}")
                return []

        except requests.exceptions.RequestException as e:
            logger.error(f"Network error fetching commands: {e}")
            return []

    def report_command_result(self, command_id, success, message, execution_time_ms):
        """Report command execution result back to VPS"""
        try:
            headers = {
                'Authorization': f'Bearer {API_TOKEN}',
                'Content-Type': 'application/json'
            }

            payload = {
                'command_id': command_id,
                'success': success,
                'message': message,
                'execution_time_ms': execution_time_ms,
                'bridge_version': BRIDGE_VERSION
            }

            response = requests.post(
                f"{VPS_BASE_URL}/api/hardware/report-result",
                headers=headers,
                json=payload,
                timeout=10
            )

            if response.status_code == 200:
                logger.info(f"✓ Result reported for command {command_id}")
                return True
            else:
                logger.warning(f"Failed to report result: HTTP {response.status_code}")
                return False

        except requests.exceptions.RequestException as e:
            logger.error(f"Network error reporting result: {e}")
            return False

    def execute_command(self, command):
        """Execute a single command"""
        command_id = command.get('id')
        command_type = command.get('command_type')
        payload = command.get('payload', {})

        logger.info(f"Executing command {command_id}: {command_type}")

        start_time = time.time()
        success = False
        message = ""

        try:
            if command_type == 'trigger_bell':
                zones = payload.get('zones', [1, 2, 3, 4])  # Default: all zones
                duration = payload.get('duration_seconds', 180)  # Default: 3 minutes
                success, message = self.trigger_bell(zones, duration)

            elif command_type == 'test_speaker':
                zone = payload.get('zone', 1)
                duration = payload.get('duration_seconds', 5)
                success, message = self.trigger_bell([zone], duration)

            elif command_type == 'relay_on':
                zone = payload.get('zone', 1)
                success, message = self.write_relay(zone, True)

            elif command_type == 'relay_off':
                zone = payload.get('zone', 1)
                success, message = self.write_relay(zone, False)

            else:
                message = f"Unknown command type: {command_type}"
                success = False

        except Exception as e:
            message = f"Exception executing command: {str(e)}"
            success = False
            logger.error(message)

        execution_time_ms = int((time.time() - start_time) * 1000)

        # Report result back to VPS
        self.report_command_result(command_id, success, message, execution_time_ms)

        return success

    def run(self):
        """Main loop"""
        logger.info("=" * 60)
        logger.info("Bell Bridge Service Started")
        logger.info(f"Version: {BRIDGE_VERSION}")
        logger.info(f"VPS URL: {VPS_BASE_URL}")
        logger.info(f"Poll Interval: {POLL_INTERVAL}s")
        logger.info("=" * 60)

        # Connect to Modbus
        if not self.connect_modbus():
            logger.error("Failed to connect to Modbus. Exiting...")
            sys.exit(1)

        logger.info("Entering main loop... Press Ctrl+C to exit")

        try:
            while True:
                # Fetch pending commands
                commands = self.fetch_pending_commands()

                if commands:
                    logger.info(f"Found {len(commands)} pending command(s)")

                    for command in commands:
                        self.execute_command(command)

                # Wait before next poll
                time.sleep(POLL_INTERVAL)

        except KeyboardInterrupt:
            logger.info("\nShutdown signal received...")
        except Exception as e:
            logger.error(f"Fatal error: {e}")
        finally:
            self.disconnect_modbus()
            logger.info("Bridge service stopped")

if __name__ == "__main__":
    service = BellBridgeService()
    service.run()
```

### Step 7: Test Manual

```cmd
python bridge_service.py
```

Jika berhasil, Anda akan melihat:

```
2026-08-11 10:30:00 - INFO - ==========================================
2026-08-11 10:30:00 - INFO - Bell Bridge Service Started
2026-08-11 10:30:00 - INFO - Version: 1.0.0
2026-08-11 10:30:00 - INFO - VPS URL: https://your-domain.com
2026-08-11 10:30:00 - INFO - Poll Interval: 5s
2026-08-11 10:30:00 - INFO - ==========================================
2026-08-11 10:30:01 - INFO - Connecting to Modbus on COM3 @ 9600 baud...
2026-08-11 10:30:02 - INFO - ✓ Modbus connected successfully
2026-08-11 10:30:02 - INFO - Entering main loop... Press Ctrl+C to exit
```

### Step 8: Auto-Start saat Windows Boot

Buat file `install_service.bat`:

```batch
@echo off
echo Installing Bell Bridge Service...

:: Install NSSM (Non-Sucking Service Manager)
:: Download from: https://nssm.cc/download

:: Assuming nssm.exe is in the same folder
nssm.exe install BellBridgeService "C:\Python39\python.exe" "C:\BellBridgeService\bridge_service.py"
nssm.exe set BellBridgeService AppDirectory "C:\BellBridgeService"
nssm.exe set BellBridgeService Description "MTsN 2 Kota Malang Bell Hardware Bridge"
nssm.exe set BellBridgeService Start SERVICE_AUTO_START

echo Service installed! Starting service...
nssm.exe start BellBridgeService

echo Done! Service is now running.
pause
```

Run as Administrator:

```cmd
install_service.bat
```

---

## 🎛️ Konfigurasi

Setelah semua terinstall, akses web UI:

1. Login ke aplikasi Laravel
2. Menu: **Hardware Control**
3. Tab: **Configuration**
   - Set COM Port (auto-detect dari bridge)
   - Set Baud Rate
   - Test Connection
4. Tab: **Speaker Zones**
   - Enable/disable zones
   - Set default duration
   - Test individual speaker

---

## 🧪 Testing

### Test 1: Manual Trigger dari Web UI

1. Login sebagai admin
2. Go to **Hardware Control** → **Test Speaker**
3. Pilih zone (contoh: Halaman Sekolah)
4. Klik **Test 5 Seconds**
5. Speaker harus bunyi selama 5 detik
6. Cek di **Logs** tab untuk melihat hasil

### Test 2: Automatic Bell Schedule

1. Buat jadwal bel di **Bell Schedules**
2. Set waktu beberapa menit ke depan
3. Pastikan Bell Type **is_automatic = true**
4. Tunggu sampai waktu jadwal
5. Speaker harus otomatis bunyi
6. Cek logs untuk konfirmasi

### Test 3: Bridge Offline Scenario

1. Matikan bridge service (Stop service atau matikan PC)
2. Buat command dari web UI
3. Command akan masuk queue dengan status "pending"
4. Nyalakan kembali bridge
5. Command akan dieksekusi (jika belum expired)

---

## 🔍 Troubleshooting

### Problem 1: "COM Port not found"

**Solution:**
1. Cek Device Manager → Ports (COM & LPT)
2. Lihat port mana yang digunakan USB-RS485
3. Update `.env` dengan COM port yang benar
4. Restart bridge service

### Problem 2: "Modbus connection failed"

**Solution:**
1. Pastikan driver USB-RS485 sudah terinstall
2. Cek baud rate sama antara device dan software (9600)
3. Cek kabel RS485 (A+ dan B-)
4. Test dengan software Modbus Poll/Slave

### Problem 3: "Bridge tidak polling VPS"

**Solution:**
1. Cek koneksi internet PC sekolah
2. Ping VPS dari PC: `ping your-domain.com`
3. Cek firewall Windows tidak block Python
4. Cek API token benar di `.env`

### Problem 4: "Relay tidak switch"

**Solution:**
1. Cek Modbus address device (default: 1)
2. Test manual dengan Modbus software
3. Cek wiring relay module
4. Cek power supply relay module (biasanya 12V/24V)

---

## 📊 Monitoring

### Check Bridge Status

```cmd
nssm status BellBridgeService
```

### View Bridge Logs

```cmd
type C:\BellBridgeService\bridge.log
```

### Realtime Log Monitor

```cmd
powershell Get-Content C:\BellBridgeService\bridge.log -Wait -Tail 50
```

---

## 🎯 Next Steps

1. ✅ Migrations sudah dibuat
2. ⏳ Lengkapi Models (HardwareConfig, SpeakerZone, HardwareLog)
3. ⏳ Buat Controllers (HardwareController, HardwareApiController)
4. ⏳ Buat Routes
5. ⏳ Buat Views (UI untuk management)
6. ⏳ Update PublicController dengan hardware trigger
7. ⏳ Deploy & testing

**Apakah Anda ingin saya lanjutkan generate semua file Laravel yang dibutuhkan?**

---

Generated by: Claude Code
Date: 2026-08-11
