#!/usr/bin/env python3
"""
Quick API Connection Test Script
Tests connection to bell.mtsn2kotamalang.sch.id
"""

import requests
import json
import sys

# Configuration
BASE_URL = "https://bell.mtsn2kotamalang.sch.id"
API_TOKEN = "a46eac0b1a4bd1ebfa03607b4960c8cb98892038c9518a60b9b5d354e699e8de"

def test_endpoint(name, url, method='GET', data=None):
    """Test a single endpoint"""
    print(f"\n{'='*60}")
    print(f"Testing: {name}")
    print(f"URL: {url}")
    print(f"Method: {method}")
    print(f"{'='*60}")

    headers = {
        'Authorization': f'Bearer {API_TOKEN}',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }

    try:
        if method == 'GET':
            response = requests.get(url, headers=headers, timeout=10)
        elif method == 'POST':
            response = requests.post(url, headers=headers, json=data, timeout=10)
        else:
            print(f"❌ Unknown method: {method}")
            return False

        print(f"Status Code: {response.status_code}")
        print(f"Headers: {dict(response.headers)}")
        print(f"\nResponse Body:")

        try:
            json_data = response.json()
            print(json.dumps(json_data, indent=2))
        except:
            print(response.text)

        if response.status_code == 200:
            print(f"\n✅ {name} - SUCCESS")
            return True
        elif response.status_code == 401:
            print(f"\n❌ {name} - UNAUTHORIZED (check API token)")
            return False
        elif response.status_code == 404:
            print(f"\n❌ {name} - NOT FOUND (endpoint missing)")
            return False
        elif response.status_code == 500:
            print(f"\n❌ {name} - SERVER ERROR (check Laravel logs)")
            return False
        else:
            print(f"\n⚠️  {name} - HTTP {response.status_code}")
            return False

    except requests.exceptions.SSLError as e:
        print(f"❌ SSL Error: {e}")
        print("Try: pip install --upgrade certifi")
        return False
    except requests.exceptions.ConnectionError as e:
        print(f"❌ Connection Error: {e}")
        print("Check: Internet connection and firewall")
        return False
    except requests.exceptions.Timeout as e:
        print(f"❌ Timeout Error: {e}")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False


def main():
    print("\n" + "="*60)
    print("API Connection Test - MTsN 2 Kota Malang")
    print("="*60)
    print(f"Server: {BASE_URL}")
    print(f"Token: {API_TOKEN[:20]}...")

    results = []

    # Test 1: Health check (no auth required)
    results.append(test_endpoint(
        "Health Check",
        f"{BASE_URL}/api/health",
        method='GET'
    ))

    # Test 2: Get Config (with auth)
    results.append(test_endpoint(
        "Get Hardware Config",
        f"{BASE_URL}/api/hardware/config",
        method='GET'
    ))

    # Test 3: Get Pending Commands (with auth)
    results.append(test_endpoint(
        "Get Pending Commands",
        f"{BASE_URL}/api/hardware/pending-commands",
        method='GET'
    ))

    # Test 4: Heartbeat (with auth)
    results.append(test_endpoint(
        "Send Heartbeat",
        f"{BASE_URL}/api/hardware/heartbeat",
        method='POST',
        data={}
    ))

    # Summary
    print("\n" + "="*60)
    print("SUMMARY")
    print("="*60)

    passed = sum(results)
    total = len(results)

    print(f"Passed: {passed}/{total}")

    if passed == total:
        print("\n✅ All tests passed! Bridge should work.")
        return 0
    else:
        print(f"\n❌ {total - passed} test(s) failed. Check errors above.")
        return 1


if __name__ == '__main__':
    sys.exit(main())
