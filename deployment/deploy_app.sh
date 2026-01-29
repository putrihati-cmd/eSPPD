#!/bin/bash

# Deployment Script for e-SPPD
# Usage: ./deploy_app.sh

APP_DIR="/var/www/esppd"
# Use GitHub token from environment variable for security
GITHUB_TOKEN="${GITHUB_TOKEN:-}"
REPO="https://${GITHUB_TOKEN}@github.com/putrihati-cmd/eSPPD.git"

echo "🚀 Deploying Application..."
echo "ℹ️  Ensure GITHUB_TOKEN environment variable is set before running this script"

# 1. Clone or Pull
if [ -d "$APP_DIR/.git" ]; then
    echo "🔄 Pulling latest changes..."
    cd $APP_DIR
    git pull origin main
else
    echo "📥 Cloning repository..."
    git clone $REPO $APP_DIR
    cd $APP_DIR
fi

# 2. Environment
if [ ! -f .env ]; then
    cp .env.example .env
    echo "⚠️ .env created. Please configure DB credentials manually!"
else
    # Ensure Redis config AND PostgreSQL connection
    sed -i 's/CACHE_STORE=database/CACHE_STORE=redis/g' .env
    sed -i 's/QUEUE_CONNECTION=database/QUEUE_CONNECTION=redis/g' .env
    sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=pgsql/g' .env
    sed -i 's/DB_PORT=3306/DB_PORT=5432/g' .env
fi

# 3. Dependencies
echo "📦 Installing PHP Dependencies..."
composer install --optimize-autoloader --no-dev

echo "📦 Installing Node Dependencies..."
npm ci
npm run build

# 4. Migrations & Cache
echo "⚡ Running Migrations & Optimizations..."
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Permissions
# sudo chown -R www-data:www-data storage bootstrap/cache
# sudo chmod -R 775 storage bootstrap/cache
echo "⚠️ Skipping permission fix (Run manually if needed)"

# 6. Queue Workers
echo "🔄 Restarting Queue..."
php artisan queue:restart

echo "✅ App Deployment Complete!"
