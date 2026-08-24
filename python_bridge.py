#!/usr/bin/env python3
"""
Python Bridge for Bell System Hardware Control
Connects to Laravel server and controls Modbus RS485 hardware

Server: https://bell.mtsn2kotamalang.sch.id
"""

import serial
import time
import requests
import json
import logging
import sys
import os
from datetime import datetime
from typing import Optional, Dict, List

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)
logger = logging.getLogger(__name__)


class ModbusController:
    """Handles Modbus RTU communication over RS485"""

    def __init__(self, port: str, baud_rate: int = 9600, timeout: int = 1):
        self.port = port
        self.baud_rate = baud_rate
        self.timeout = timeout
        self.connection: Optional[serial.Serial] = None

    def connect(self) -> bool:
        """Establish serial connection"""
        try:
            self.connection = serial.Serial(
                port=self.port,
                baudrate=self.baud_rate,
                bytesize=serial.EIGHTBITS,
                parity=serial.PARITY_NONE,
                stopbits=serial.STOPBITS_ONE,
                timeout=self.timeout
            )
            logger.info(f"Connected to {self.port} at {self.baud_rate} baud")
            return True
        except Exception as e:
            logger.error(f"Failed to connect to {self.port}: {e}")
            return False

    def disconnect(self):
        """Close serial connection"""
        if self.connection and self.connection.is_open:
            self.connection.close()
            logger.info(f"Disconnected from {self.port}")

    def calculate_crc16(self, data: bytes) -> bytes:
        """Calculate Modbus CRC16"""
        crc = 0xFFFF
        for byte in data:
            crc ^= byte
            for _ in range(8):
                if crc & 0x0001:
                    crc = (crc >> 1) ^ 0xA001
                else:
                    crc >>= 1
        return crc.to_bytes(2, byteorder='little')

    def send_command(self, address: int, command: str, channel: Optional[int] = None) -> Dict:
        """
        Send Modbus command to hardware

        Args:
            address: Modbus device address (1-247)
            command: Command type ('ON', 'OFF', 'TOGGLE')
            channel: Optional channel number for multi-channel devices

        Returns:
            Dict with success status and message
        """
        if not self.connection or not self.connection.is_open:
            return {"success": False, "message": "Serial connection not open"}

        try:
            # Function code 05 (Write Single Coil)
            function_code = 0x05
            coil_address = 0x0000  # Starting address

            # Determine coil value based on command
            if command.upper() == 'ON':
                coil_value = 0xFF00
            elif command.upper() == 'OFF':
                coil_value = 0x0000
            elif command.upper() == 'TOGGLE':
                # For toggle, read current state first (not implemented in basic version)
                coil_value = 0xFF00
            else:
                return {"success": False, "message": f"Unknown command: {command}"}

            # Build Modbus RTU frame
            frame = bytearray([
                address,                        # Device address
                function_code,                  # Function code
                (coil_address >> 8) & 0xFF,    # Coil address high byte
                coil_address & 0xFF,            # Coil address low byte
                (coil_value >> 8) & 0xFF,      # Coil value high byte
                coil_value & 0xFF               # Coil value low byte
            ])

            # Add CRC
            crc = self.calculate_crc16(frame)
            frame.extend(crc)

            # Send command
            self.connection.write(frame)
            hex_frame = ' '.join(f'{b:02X}' for b in frame)
            logger.info(f"Sent to address {address:02d}: {hex_frame} ({command})")

            # Wait for response
            time.sleep(0.1)

            # Read response (8 bytes for function code 05)
            response = self.connection.read(8)

            if len(response) == 8:
                # Verify response matches request (echo)
                if response[:6] == frame[:6]:
                    hex_response = ' '.join(f'{b:02X}' for b in response)
                    logger.info(f"Response from {address:02d}: {hex_response}")
                    return {
                        "success": True,
                        "message": f"Command {command} executed successfully",
                        "sent_frame": hex_frame,
                        "response_frame": hex_response
                    }
                else:
                    return {
                        "success": False,
                        "message": "Response mismatch"
                    }
            else:
                # Some devices don't send response - still consider success if sent
                logger.warning(f"No response or incomplete response (got {len(response)} bytes)")
                return {
                    "success": True,
                    "message": f"Command {command} sent (no response)",
                    "sent_frame": hex_frame
                }

        except Exception as e:
            logger.error(f"Error sending command: {e}")
            return {"success": False, "message": str(e)}


class BridgeClient:
    """Handles communication with Laravel server"""

    def __init__(self, base_url: str, api_token: str):
        self.base_url = base_url.rstrip('/')
        self.api_token = api_token
        self.session = requests.Session()
        self.session.headers.update({
            'Authorization': f'Bearer {self.api_token}',
            'Accept': 'application/json',
            'User-Agent': 'PythonBridge/1.0'
        })

    def test_connection(self) -> bool:
        """Test connection to server"""
        try:
            url = f"{self.base_url}/api/hardware/config"
            response = self.session.get(url, timeout=10)

            if response.status_code == 200:
                logger.info("✓ Server connection OK")
                data = response.json()
                logger.info(f"  Server config: {json.dumps(data, indent=2)}")
                return True
            else:
                logger.error(f"✗ Server returned status {response.status_code}")
                logger.error(f"  Response: {response.text}")
                return False

        except Exception as e:
            logger.error(f"✗ Connection failed: {e}")
            return False

    def get_pending_commands(self) -> List[Dict]:
        """Fetch pending commands from server"""
        try:
            url = f"{self.base_url}/api/hardware/pending-commands"
            response = self.session.get(url, timeout=10)

            if response.status_code == 200:
                data = response.json()
                commands = data.get('commands', [])

                if commands:
                    logger.info(f"Received {len(commands)} pending command(s)")

                return commands
            else:
                logger.warning(f"Failed to fetch commands: {response.status_code}")
                return []

        except Exception as e:
            logger.error(f"Error fetching commands: {e}")
            return []

    def report_result(self, queue_id: int, success: bool, message: str, response_data: Optional[Dict] = None):
        """Report command execution result to server"""
        try:
            url = f"{self.base_url}/api/hardware/report-result"
            payload = {
                'queue_id': queue_id,
                'success': success,
                'message': message,
                'executed_at': datetime.now().isoformat(),
                'response_data': response_data or {}
            }

            response = self.session.post(url, json=payload, timeout=10)

            if response.status_code == 200:
                logger.info(f"✓ Result reported for queue_id={queue_id}")
            else:
                logger.warning(f"Failed to report result: {response.status_code}")

        except Exception as e:
            logger.error(f"Error reporting result: {e}")

    def send_heartbeat(self):
        """Send heartbeat to server"""
        try:
            url = f"{self.base_url}/api/hardware/heartbeat"
            payload = {
                'timestamp': datetime.now().isoformat(),
                'status': 'running'
            }

            response = self.session.post(url, json=payload, timeout=5)

            if response.status_code == 200:
                logger.debug("Heartbeat sent")

        except Exception as e:
            logger.debug(f"Heartbeat failed: {e}")


class PythonBridge:
    """Main bridge application"""

    def __init__(self, config_path: str = 'config.json'):
        self.config = self.load_config(config_path)
        self.modbus = ModbusController(
            port=self.config['com_port'],
            baud_rate=self.config.get('baud_rate', 9600),
            timeout=self.config.get('timeout', 1)
        )
        self.client = BridgeClient(
            base_url=self.config['vps_base_url'],
            api_token=self.config['api_token']
        )
        self.running = False
        self.last_heartbeat = 0

    def load_config(self, config_path: str) -> Dict:
        """Load configuration from JSON file"""
        try:
            with open(config_path, 'r') as f:
                config = json.load(f)

            # Validate required fields
            required = ['vps_base_url', 'api_token', 'com_port']
            for field in required:
                if field not in config:
                    raise ValueError(f"Missing required config field: {field}")

            return config

        except FileNotFoundError:
            logger.error(f"Config file not found: {config_path}")
            logger.info("Creating default config.json...")

            default_config = {
                "vps_base_url": "https://bell.mtsn2kotamalang.sch.id",
                "api_token": "YOUR_API_TOKEN_HERE",
                "com_port": "COM3",
                "poll_interval": 2,
                "baud_rate": 9600,
                "timeout": 1
            }

            with open(config_path, 'w') as f:
                json.dump(default_config, f, indent=2)

            logger.info(f"Created {config_path} - Please edit and restart")
            sys.exit(1)

        except Exception as e:
            logger.error(f"Error loading config: {e}")
            sys.exit(1)

    def process_command(self, command: Dict):
        """Process a single command"""
        queue_id = command['id']
        command_type = command['command']
        hardware_address = command['hardware_address']
        room_name = command.get('room_name', 'Unknown')

        logger.info(f"Processing command #{queue_id}: {command_type} for {room_name} (address {hardware_address})")

        # Send to Modbus
        result = self.modbus.send_command(
            address=hardware_address,
            command=command_type
        )

        # Report result to server
        self.client.report_result(
            queue_id=queue_id,
            success=result['success'],
            message=result['message'],
            response_data=result
        )

    def run(self):
        """Main loop"""
        logger.info("=" * 60)
        logger.info("Python Bridge for Bell System Hardware Control")
        logger.info("=" * 60)
        logger.info(f"Server: {self.config['vps_base_url']}")
        logger.info(f"COM Port: {self.config['com_port']}")
        logger.info(f"Poll Interval: {self.config.get('poll_interval', 2)}s")
        logger.info("=" * 60)

        # Test connection
        if not self.client.test_connection():
            logger.error("Cannot connect to server - exiting")
            return

        # Connect to Modbus
        if not self.modbus.connect():
            logger.error("Cannot connect to COM port - exiting")
            return

        logger.info("Bridge started - polling for commands...")
        logger.info("Press Ctrl+C to stop")

        self.running = True
        poll_interval = self.config.get('poll_interval', 2)
        heartbeat_interval = 30  # seconds

        try:
            while self.running:
                # Send heartbeat every 30 seconds
                now = time.time()
                if now - self.last_heartbeat > heartbeat_interval:
                    self.client.send_heartbeat()
                    self.last_heartbeat = now

                # Get pending commands
                commands = self.client.get_pending_commands()

                # Process each command
                for command in commands:
                    self.process_command(command)

                # Wait before next poll
                time.sleep(poll_interval)

        except KeyboardInterrupt:
            logger.info("\nShutdown requested...")

        finally:
            self.modbus.disconnect()
            logger.info("Bridge stopped")

    def test_hardware(self):
        """Test hardware connection"""
        logger.info("Testing hardware connection...")

        if not self.modbus.connect():
            logger.error("Cannot connect to COM port")
            return

        # Test command to address 01
        logger.info("Sending test command to address 01...")
        result = self.modbus.send_command(address=1, command='ON')

        if result['success']:
            logger.info("✓ Hardware test successful")
            logger.info(f"  {result['message']}")

            # Turn off after 2 seconds
            time.sleep(2)
            logger.info("Turning off...")
            self.modbus.send_command(address=1, command='OFF')
        else:
            logger.error(f"✗ Hardware test failed: {result['message']}")

        self.modbus.disconnect()


def main():
    """Entry point"""
    import argparse

    parser = argparse.ArgumentParser(description='Python Bridge for Bell System')
    parser.add_argument('--test-connection', action='store_true', help='Test server connection')
    parser.add_argument('--test-hardware', action='store_true', help='Test hardware connection')
    parser.add_argument('--config', default='config.json', help='Config file path')

    args = parser.parse_args()

    bridge = PythonBridge(config_path=args.config)

    if args.test_connection:
        bridge.client.test_connection()
    elif args.test_hardware:
        bridge.test_hardware()
    else:
        bridge.run()


if __name__ == '__main__':
    main()
