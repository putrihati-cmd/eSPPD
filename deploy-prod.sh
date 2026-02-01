#!/bin/bash
# Deploy to production and rebuild routes

cd /var/www/esppd || exit 1

echo "Pulling latest code..."
git pull origin main

echo "Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Verifying routes..."
php artisan route:list | grep -E "about|guide"

echo "Done!"
