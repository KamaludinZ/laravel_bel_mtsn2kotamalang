#!/bin/bash

# Script untuk deployment production
# Usage: bash deploy.sh

echo "🚀 Starting deployment process..."
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    echo "Please copy .env.production to .env and configure it."
    exit 1
fi

# Check APP_ENV
APP_ENV=$(grep "^APP_ENV=" .env | cut -d '=' -f2)
if [ "$APP_ENV" != "production" ]; then
    echo "⚠️  Warning: APP_ENV is not set to 'production'"
    echo "Current value: APP_ENV=$APP_ENV"
    echo ""
    read -p "Do you want to continue? (y/n) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo "📦 Step 1: Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader
if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi

echo ""
echo "📦 Step 2: Installing NPM dependencies..."
npm ci
if [ $? -ne 0 ]; then
    echo "❌ NPM install failed"
    exit 1
fi

echo ""
echo "🏗️  Step 3: Building assets for production..."
npm run build
if [ $? -ne 0 ]; then
    echo "❌ Build failed"
    exit 1
fi

echo ""
echo "🔗 Step 4: Creating storage link..."
php artisan storage:link

echo ""
echo "🗄️  Step 5: Running migrations..."
read -p "Do you want to run migrations? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
fi

echo ""
echo "🧹 Step 6: Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "⚡ Step 7: Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "📋 Post-deployment checklist:"
echo "  - Ensure folder public/build/ is uploaded to server"
echo "  - Verify .env has APP_ENV=production and APP_DEBUG=false"
echo "  - Test all features in browser"
echo "  - Check browser console for errors"
echo ""
