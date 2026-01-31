#!/bin/bash

# Production Deployment Script for eSPPD
# This script will be executed on production server 192.168.1.27

APP_DIR="/var/www/esppd"

echo "════════════════════════════════════════════════════"
echo "🚀 eSPPD Production Deployment"
echo "════════════════════════════════════════════════════"

# Navigate to app directory
cd $APP_DIR || { echo "❌ App directory not found"; exit 1; }

# 1. Pull latest changes from GitHub
echo "📥 Pulling latest changes from GitHub..."
git pull origin main
if [ $? -ne 0 ]; then
    echo "❌ Git pull failed"
    exit 1
fi

# 2. Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader
if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi

# 3. Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo "❌ Migrations failed"
    exit 1
fi

# 4. Cache configuration
echo "⚙️  Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Optimize application
echo "⚡ Optimizing application..."
php artisan optimize

# 6. Clear old caches
echo "🧹 Clearing old caches..."
php artisan cache:clear

# 7. Restart supervisord (if available)
if command -v supervisorctl &> /dev/null; then
    echo "🔄 Restarting Supervisord services..."
    supervisorctl restart all
fi

echo ""
echo "════════════════════════════════════════════════════"
echo "✅ Deployment successful!"
echo "════════════════════════════════════════════════════"
echo "📋 Application: https://esppd.infiatin.cloud"
echo "⏰ Deployed at: $(date)"
