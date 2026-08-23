#!/bin/bash

# Script untuk diagnosa healthcheck issue di Coolify
# Run: bash check_health.sh

echo "=== HEALTHCHECK DIAGNOSIS ==="
echo ""

# 1. Check if /api/health endpoint exists and responds
echo "1. Testing /api/health endpoint..."
curl -v https://bel.mtsn2kotamalang.sch.id/api/health 2>&1 | grep -E "HTTP|status|database|cache"
echo ""

# 2. Check container logs
echo "2. Checking app container logs for errors..."
docker logs $(docker ps -q -f name=app) --tail 50 | grep -i "error\|fail\|exception" || echo "No errors found in recent logs"
echo ""

# 3. Check healthcheck command inside container
echo "3. Testing healthcheck command inside container..."
docker exec $(docker ps -q -f name=app) sh -c "curl -f http://localhost/api/health || echo 'Health endpoint failed'"
echo ""

# 4. Check if health.html exists
echo "4. Checking if health.html exists..."
docker exec $(docker ps -q -f name=app) sh -c "ls -la /var/www/html/public/health.html 2>&1 || echo 'health.html not found'"
echo ""

# 5. Check database connection
echo "5. Testing database connection..."
docker exec $(docker ps -q -f name=app) php artisan tinker --execute="try { \DB::connection()->getPdo(); echo 'Database: CONNECTED\n'; } catch (\Exception \$e) { echo 'Database ERROR: ' . \$e->getMessage() . '\n'; }"
echo ""

# 6. Check Redis connection
echo "6. Testing Redis connection..."
docker exec $(docker ps -q -f name=app) php artisan tinker --execute="try { \Cache::get('test'); echo 'Redis: CONNECTED\n'; } catch (\Exception \$e) { echo 'Redis ERROR: ' . \$e->getMessage() . '\n'; }"
echo ""

echo "=== DIAGNOSIS COMPLETE ==="
