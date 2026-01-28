#!/bin/bash

# Optimization Script for 500+ Users
echo "🚀 Starting Optimization for Production..."

# 1. Clear Caches
echo "🧹 Clearing Caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Optimize Autoloader
echo "📦 Optimizing Autoloader..."
composer dump-autoload --optimize

# 3. Cache Configuration & Routes
echo "⚡ Caching Config, Routes, Views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Restart Queue Workers
echo "🔄 Restarting Queue Workers..."
php artisan queue:restart

echo "✅ Optimization Complete!"
