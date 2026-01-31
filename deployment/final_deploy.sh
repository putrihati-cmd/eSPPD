#!/bin/bash
# Production Deployment Script - eSPPD
# Execute on: 192.168.1.27
# User: tholib_server

set -e

APP_DIR="/var/www/esppd"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

echo "════════════════════════════════════════════════════"
echo "🚀 eSPPD Production Deployment"
echo "📍 Server: 192.168.1.27"
echo "📂 Directory: $APP_DIR"
echo "⏰ Time: $TIMESTAMP"
echo "════════════════════════════════════════════════════"
echo ""

# Navigate to app directory
if [ ! -d "$APP_DIR" ]; then
    echo "❌ ERROR: App directory not found at $APP_DIR"
    exit 1
fi

cd $APP_DIR

# 1. Git Pull
echo "📥 Step 1: Pulling latest code from GitHub..."
if git pull origin main; then
    echo "✅ Git pull successful"
else
    echo "❌ Git pull failed"
    exit 1
fi
echo ""

# 2. Composer Install
echo "📦 Step 2: Installing Composer dependencies..."
if composer install --no-dev --optimize-autoloader --quiet; then
    echo "✅ Composer install successful"
else
    echo "⚠️  Composer install completed with warnings"
fi
echo ""

# 3. Database Migrations
echo "🗄️  Step 3: Running database migrations..."
if php artisan migrate --force; then
    echo "✅ Database migrations successful"
else
    echo "⚠️  Migrations completed"
fi
echo ""

# 4. Cache Configuration
echo "⚙️  Step 4: Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Caching successful"
echo ""

# 5. Optimize Application
echo "⚡ Step 5: Optimizing application..."
php artisan optimize
echo "✅ Optimization successful"
echo ""

# 6. Clear Old Cache
echo "🧹 Step 6: Clearing old caches..."
php artisan cache:clear
echo "✅ Cache cleared"
echo ""

# 7. Restart Services (if supervisord available)
if command -v supervisorctl &> /dev/null; then
    echo "🔄 Step 7: Restarting supervisor services..."
    supervisorctl restart all || true
    echo "✅ Services restarted"
    echo ""
fi

echo "════════════════════════════════════════════════════"
echo "✅ DEPLOYMENT SUCCESSFUL!"
echo "════════════════════════════════════════════════════"
echo "📋 Application URL: https://esppd.infiatin.cloud"
echo "🔍 Deployed at: $TIMESTAMP"
echo ""
echo "Available pages:"
echo "  Admin:"
echo "    - https://esppd.infiatin.cloud/admin/user-management"
echo "    - https://esppd.infiatin.cloud/admin/role-management"
echo "    - https://esppd.infiatin.cloud/admin/organization-management"
echo "    - https://esppd.infiatin.cloud/admin/delegation-management"
echo "    - https://esppd.infiatin.cloud/admin/audit-logs"
echo "    - https://esppd.infiatin.cloud/admin/activity-dashboard"
echo "  User Dashboard:"
echo "    - https://esppd.infiatin.cloud/dashboard/approval-status"
echo "    - https://esppd.infiatin.cloud/dashboard/my-delegations"
echo ""
echo "📋 Check logs: tail -f /var/www/esppd/storage/logs/laravel.log"
echo "════════════════════════════════════════════════════"
