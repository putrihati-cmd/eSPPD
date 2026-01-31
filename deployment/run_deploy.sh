#!/bin/bash

# Final Deployment Script
# eSPPD Production Deployment
# Server: 192.168.1.27
# User: tholib_server

APP="/var/www/esppd"
cd $APP

echo "🚀 eSPPD DEPLOYMENT START"
echo "=========================="

# 1. Git
echo "📥 Git pull..."
git pull origin main 2>&1 | tail -3

# 2. Composer
echo "📦 Composer install..."
composer install --no-dev --optimize-autoloader 2>&1 | tail -2

# 3. Migrations
echo "🗄️  Migrations..."
php artisan migrate --force 2>&1 | grep -E "Migrated|Application|error" || true

# 4. Caching
echo "⚙️  Caching..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Optimize
echo "⚡ Optimize..."
php artisan optimize

# 6. Clear
echo "🧹 Clear..."
php artisan cache:clear

echo ""
echo "✅ DEPLOYMENT SUCCESS!"
echo "=========================="
echo "App: https://esppd.infiatin.cloud"
echo "Check: php artisan about"
