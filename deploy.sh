#!/bin/bash

# 🚀 e-SPPD Automated Deployment Script
# Usage: ./deploy.sh

set -e

echo "🚀 Starting e-SPPD Deployment..."

# Configuration
SERVER_USER="tholib_server"
SERVER_IP="192.168.1.27"
APP_PATH="/var/www/esppd"
DOMAIN="esppd.infiatin.cloud"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${GREEN}ℹ️  $1${NC}"
}

log_warn() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Step 1: Verify git status
log_info "Checking git status..."
if [[ -n $(git status -s) ]]; then
    log_error "Uncommitted changes detected. Please commit or stash changes."
    exit 1
fi

# Step 2: Push to GitHub
log_info "Pushing code to GitHub..."
git push origin main

# Step 3: SSH and pull code
log_info "Connecting to server and pulling latest code..."
ssh ${SERVER_USER}@${SERVER_IP} << 'EOF'
    set -e
    cd /var/www/esppd

    # Backup current version
    echo "Creating backup..."
    cp -r . ../esppd-backup-$(date +%Y%m%d-%H%M%S)

    # Pull latest code
    echo "Pulling latest code..."
    git pull origin main

    # Install dependencies
    echo "Installing composer dependencies..."
    composer install --optimize-autoloader --no-dev

    # Build assets
    echo "Building frontend assets..."
    npm install
    npm run build

    # Database migrations
    echo "Running database migrations..."
    php artisan migrate --force

    # Cache optimization
    echo "Optimizing cache..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize

    # Restart services
    echo "Restarting services..."
    sudo systemctl restart php8.2-fpm
    sudo systemctl restart nginx
    sudo supervisorctl restart all

    echo "✅ Deployment completed!"
EOF

log_info "Verifying deployment..."
sleep 5

# Test health check
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://${DOMAIN}/health)

if [ "$HTTP_CODE" -eq 200 ]; then
    log_info "✅ Health check passed! (HTTP $HTTP_CODE)"
    log_info "🎉 Deployment successful!"
    log_info "Application is live at: https://${DOMAIN}"
else
    log_error "Health check failed! (HTTP $HTTP_CODE)"
    log_warn "Check logs: /var/www/esppd/storage/logs/laravel.log"
    exit 1
fi
