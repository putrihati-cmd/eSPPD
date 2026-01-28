#!/bin/bash
# deploy.sh - Deploy eSPPD application

set -e

APP_DIR="/var/www/esppd"
REPO_URL="https://github.com/putrihati-cmd/eSPPD.git"
BRANCH="main"

echo "=========================================="
echo "eSPPD Deployment Script"
echo "=========================================="

# Navigate to app directory
cd $APP_DIR

# Enable maintenance mode
echo ">>> Enabling maintenance mode..."
php artisan down --render="errors::503" || true

# Pull latest code
echo ">>> Pulling latest code from $BRANCH..."
git fetch origin
git reset --hard origin/$BRANCH

# Install PHP dependencies
echo ">>> Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Install NPM dependencies and build
echo ">>> Building frontend assets..."
npm ci
npm run build

# Clear and cache config
echo ">>> Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache 2>/dev/null || true

# Run migrations
echo ">>> Running database migrations..."
php artisan migrate --force

# Clear application cache
echo ">>> Clearing cache..."
php artisan cache:clear

# Restart queue workers
echo ">>> Restarting queue workers..."
php artisan queue:restart

# Restart PHP-FPM
echo ">>> Restarting PHP-FPM..."
sudo systemctl restart php8.2-fpm

# Set correct permissions
echo ">>> Setting permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Disable maintenance mode
echo ">>> Disabling maintenance mode..."
php artisan up

echo "=========================================="
echo "Deployment complete!"
echo "=========================================="
