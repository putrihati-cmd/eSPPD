#!/bin/bash
# Quick deployment script
# Requires SSH access: ssh tholib_server@192.168.1.27 'bash /tmp/deploy.sh'

set -e

APP_DIR="/var/www/esppd"
echo "🚀 eSPPD Production Deployment"
echo "════════════════════════════════════════════════════"

cd $APP_DIR

# 1. Git Pull
echo "📥 Pulling latest changes..."
git pull origin main --quiet

# 2. Composer Install
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --quiet 2>/dev/null || true

# 3. Database Migrations
echo "🗄️  Running migrations..."
php artisan migrate --force --quiet

# 4. Cache Config
echo "⚙️  Caching configuration..."
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet

# 5. Optimize
echo "⚡ Optimizing..."
php artisan optimize --quiet

# 6. Clear cache
echo "🧹 Clearing old caches..."
php artisan cache:clear --quiet

echo ""
echo "✅ Deployment successful!"
echo "📋 https://esppd.infiatin.cloud"
echo "════════════════════════════════════════════════════"
