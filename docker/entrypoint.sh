#!/bin/sh
set -e

echo "🚀 Starting Laravel Bel MTsN 2 Kota Malang..."

# Wait for database to be ready
echo "⏳ Waiting for PostgreSQL..."
until pg_isready -h ${DB_HOST} -p ${DB_PORT} -U ${DB_USERNAME}; do
    echo "PostgreSQL is unavailable - sleeping"
    sleep 2
done
echo "✅ PostgreSQL is ready!"

# Wait for Redis to be ready
echo "⏳ Waiting for Redis..."
until redis-cli -h ${REDIS_HOST} -p ${REDIS_PORT} ping > /dev/null 2>&1; do
    echo "Redis is unavailable - sleeping"
    sleep 2
done
echo "✅ Redis is ready!"

# Set proper permissions
echo "🔧 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run migrations (only on app container, not on queue/scheduler)
if [ "${CONTAINER_ROLE:-app}" = "app" ]; then
    echo "📦 Running database migrations..."
    php artisan migrate --force --no-interaction || echo "⚠️  Migration failed, continuing..."

    # Seed hardware data if not exists
    echo "🌱 Seeding hardware data..."
    php artisan db:seed --class=HardwareSeeder --force --no-interaction || echo "⚠️  Seeding failed, continuing..."
    php artisan db:seed --class=RoomSeeder --force --no-interaction || echo "⚠️  Seeding failed, continuing..."

    # Create storage link
    echo "🔗 Creating storage link..."
    php artisan storage:link || echo "⚠️  Storage link already exists"

    # Clear and cache config
    echo "🗑️  Clearing caches..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    echo "📝 Caching config..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Optimize for production
    if [ "$APP_ENV" = "production" ]; then
        echo "⚡ Optimizing for production..."
        php artisan optimize
    fi
fi

echo "✅ Laravel application is ready!"
echo "🌐 Starting web server..."

# Execute the main container command
exec "$@"
