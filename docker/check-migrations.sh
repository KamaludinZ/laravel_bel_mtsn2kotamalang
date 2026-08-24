#!/bin/sh
# Migration Check Script for Hardware System
# Verifies all required tables exist and have data

set -e

echo "🔍 Checking Hardware System Migrations..."
echo "=========================================="

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

check_table() {
    TABLE_NAME=$1
    MIN_ROWS=${2:-0}

    # Check if table exists
    TABLE_EXISTS=$(php artisan tinker --execute="
        try {
            \Schema::hasTable('$TABLE_NAME') ? 'yes' : 'no';
        } catch (\Exception \$e) {
            echo 'error';
        }
    " 2>/dev/null || echo "error")

    if [ "$TABLE_EXISTS" = "yes" ]; then
        # Count rows
        ROW_COUNT=$(php artisan tinker --execute="echo \DB::table('$TABLE_NAME')->count();" 2>/dev/null || echo "0")

        if [ "$ROW_COUNT" -ge "$MIN_ROWS" ]; then
            echo "${GREEN}✅ $TABLE_NAME${NC} - $ROW_COUNT rows"
            return 0
        else
            echo "${YELLOW}⚠️  $TABLE_NAME${NC} - exists but empty (expected min $MIN_ROWS rows)"
            return 1
        fi
    elif [ "$TABLE_EXISTS" = "no" ]; then
        echo "${RED}❌ $TABLE_NAME${NC} - table missing!"
        return 1
    else
        echo "${RED}❌ $TABLE_NAME${NC} - error checking table"
        return 1
    fi
}

echo ""
echo "📊 Checking Core Tables:"
check_table "users" 1
check_table "bell_schedules" 0

echo ""
echo "📡 Checking Hardware Tables:"
check_table "speaker_zones" 8
check_table "rooms" 10
check_table "hardware_configs" 1
check_table "hardware_command_queue" 0
check_table "hardware_logs" 0

echo ""
echo "=========================================="

# Count total tables
TOTAL_TABLES=$(php artisan tinker --execute="
    echo count(\DB::select('SELECT tablename FROM pg_tables WHERE schemaname = \'public\''));
" 2>/dev/null || echo "unknown")

echo "📋 Total tables in database: $TOTAL_TABLES"

# Check pending migrations
echo ""
echo "🔍 Checking for pending migrations..."
PENDING=$(php artisan migrate:status | grep -c "Pending" || echo "0")

if [ "$PENDING" -gt "0" ]; then
    echo "${YELLOW}⚠️  $PENDING pending migration(s) found:${NC}"
    php artisan migrate:status | grep "Pending"
    echo ""
    echo "Run: php artisan migrate --force"
    exit 1
else
    echo "${GREEN}✅ All migrations are up to date${NC}"
fi

echo ""
echo "=========================================="
echo "${GREEN}✅ Migration check completed!${NC}"
