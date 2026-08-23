#!/bin/sh
set -e

echo "🚀 Starting Laravel Bel MTsN 2 Kota Malang..."
echo "📋 Environment: APP_ENV=${APP_ENV}, CONTAINER_ROLE=${CONTAINER_ROLE}"

# Wait for database to be ready
echo "⏳ Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
MAX_TRIES=30
TRIES=0
until pg_isready -h ${DB_HOST:-postgres} -p ${DB_PORT:-5432} -U ${DB_USERNAME:-postgres} 2>/dev/null; do
    TRIES=$((TRIES+1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "❌ PostgreSQL is not available after ${MAX_TRIES} attempts"
        exit 1
    fi
    echo "PostgreSQL is unavailable - attempt ${TRIES}/${MAX_TRIES}"
    sleep 2
done
echo "✅ PostgreSQL is ready!"

# Wait for Redis to be ready (optional - skip if Redis not configured)
if [ ! -z "${REDIS_HOST}" ] && [ "${REDIS_HOST}" != "null" ]; then
    echo "⏳ Waiting for Redis at ${REDIS_HOST}:${REDIS_PORT:-6379}..."
    MAX_TRIES=30
    TRIES=0
    until nc -z ${REDIS_HOST} ${REDIS_PORT:-6379} 2>/dev/null; do
        TRIES=$((TRIES+1))
        if [ $TRIES -ge $MAX_TRIES ]; then
            echo "⚠️  Redis is not available after ${MAX_TRIES} attempts - continuing anyway..."
            break
        fi
        echo "Redis is unavailable - attempt ${TRIES}/${MAX_TRIES}"
        sleep 2
    done
    if [ $TRIES -lt $MAX_TRIES ]; then
        echo "✅ Redis is ready!"
    fi
else
    echo "ℹ️  Redis not configured, skipping..."
fi

# Set proper permissions
echo "🔧 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations (only on app container, not on queue/scheduler)
if [ "${CONTAINER_ROLE:-app}" = "app" ]; then
    echo "📦 Running database migrations..."
    if php artisan migrate --force --no-interaction; then
        echo "✅ Migrations completed successfully"
    else
        echo "⚠️  Migration failed - checking if database is accessible..."
        php artisan db:show || echo "❌ Database connection failed"
    fi

    # Seed hardware data if not exists
    echo "🌱 Seeding hardware data..."
    php artisan db:seed --class=HardwareSeeder --force --no-interaction || echo "⚠️  HardwareSeeder failed (might already exist)"
    php artisan db:seed --class=RoomSeeder --force --no-interaction || echo "⚠️  RoomSeeder failed (might already exist)"

    # Create storage link
    echo "🔗 Creating storage link..."
    php artisan storage:link || echo "ℹ️  Storage link already exists"

    # Clear and cache config
    echo "🗑️  Clearing caches..."
    php artisan config:clear || echo "⚠️  Config clear failed"
    php artisan route:clear || echo "⚠️  Route clear failed"
    php artisan view:clear || echo "⚠️  View clear failed"

    echo "📝 Caching configuration..."
    if php artisan config:cache; then
        echo "✅ Config cached"
    else
        echo "❌ Config cache failed - this might cause issues!"
    fi

    php artisan route:cache || echo "⚠️  Route cache failed"
    php artisan view:cache || echo "⚠️  View cache failed"

    # Optimize for production
    if [ "$APP_ENV" = "production" ]; then
        echo "⚡ Optimizing for production..."
        php artisan optimize || echo "⚠️  Optimization failed"
    fi

    echo "✅ Application setup completed!"
fi

echo "✅ Laravel application is ready!"
echo "🌐 Starting web server..."

# Execute the main container command
exec "$@"
