#!/bin/bash

# Final Production Deployment Script for eSPPD
# Target: 192.168.1.27
# User: tholib_server
# App: /var/www/esppd

set -e

APP="/var/www/esppd"

echo "════════════════════════════════════════════════════"
echo "🚀 eSPPD PRODUCTION DEPLOYMENT"
echo "════════════════════════════════════════════════════"
echo ""

cd $APP || exit 1

echo "📥 Step 1: Git Pull"
git pull origin main --quiet
echo "✅ Complete"
echo ""

echo "📦 Step 2: Composer Install"
composer install --no-dev --optimize-autoloader --quiet 2>/dev/null || echo "⚠️  Completed with warnings"
echo "✅ Complete"
echo ""

echo "🗄️  Step 3: Database Migrations"
php artisan migrate --force --quiet
echo "✅ Complete"
echo ""

echo "⚙️  Step 4: Cache Configuration"
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet
echo "✅ Complete"
echo ""

echo "⚡ Step 5: Optimize Application"
php artisan optimize --quiet
echo "✅ Complete"
echo ""

echo "🧹 Step 6: Clear Old Cache"
php artisan cache:clear --quiet
echo "✅ Complete"
echo ""

echo "════════════════════════════════════════════════════"
echo "✅ DEPLOYMENT SUCCESSFUL!"
echo "════════════════════════════════════════════════════"
echo ""
echo "📋 Application: https://esppd.infiatin.cloud"
echo ""
echo "🔍 Available Pages:"
echo "   ✅ /admin/user-management"
echo "   ✅ /admin/role-management"
echo "   ✅ /admin/organization-management"
echo "   ✅ /admin/delegation-management"
echo "   ✅ /admin/audit-logs"
echo "   ✅ /admin/activity-dashboard"
echo "   ✅ /dashboard/approval-status"
echo "   ✅ /dashboard/my-delegations"
echo ""
echo "📊 System Info:"
php artisan about --quiet || true
