#!/bin/bash

# eSPPD Production Deployment Script
# Usage: ./deploy-production.sh

set -e

echo "=================================="
echo "  eSPPD PRODUCTION DEPLOYMENT"
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Configuration
APP_DIR="/var/www/esppd"
REPO_URL="https://github.com/putrihati-cmd/eSPPD.git"
BRANCH="main"

# Check if running as root or with sudo
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}This script must be run as root${NC}"
   exit 1
fi

# Step 1: Clone or Pull Repository
echo -e "${YELLOW}Step 1: Pulling latest code from GitHub${NC}"
if [ -d "$APP_DIR" ]; then
    cd "$APP_DIR"
    git pull origin "$BRANCH"
else
    git clone --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
    cd "$APP_DIR"
fi
echo -e "${GREEN}✅ Code pulled successfully${NC}\n"

# Step 2: Install PHP Dependencies
echo -e "${YELLOW}Step 2: Installing PHP dependencies${NC}"
composer install --no-dev --optimize-autoloader
echo -e "${GREEN}✅ PHP dependencies installed${NC}\n"

# Step 3: Setup Environment
echo -e "${YELLOW}Step 3: Checking .env file${NC}"
if [ ! -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    php artisan key:generate
    echo -e "${YELLOW}⚠️  .env created - EDIT WITH YOUR PRODUCTION VALUES${NC}"
    echo -e "${YELLOW}Then run: nano $APP_DIR/.env${NC}"
    exit 1
fi
echo -e "${GREEN}✅ .env file exists${NC}\n"

# Step 4: Database Migration
echo -e "${YELLOW}Step 4: Running database migrations${NC}"
php artisan migrate --force
echo -e "${GREEN}✅ Migrations completed${NC}\n"

# Step 5: Cache Configuration
echo -e "${YELLOW}Step 5: Caching configuration${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✅ Configuration cached${NC}\n"

# Step 6: Set File Permissions
echo -e "${YELLOW}Step 6: Setting file permissions${NC}"
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
find "$APP_DIR" -type f -exec chmod 644 {} \;
find "$APP_DIR" -type f -name "*.sh" -exec chmod +x {} \;
echo -e "${GREEN}✅ Permissions set${NC}\n"

# Step 7: Build Frontend Assets
echo -e "${YELLOW}Step 7: Building frontend assets${NC}"
npm install
npm run build
echo -e "${GREEN}✅ Frontend assets built${NC}\n"

# Step 8: Restart Services
echo -e "${YELLOW}Step 8: Restarting services${NC}"
systemctl reload nginx
systemctl restart esppd-microservice
echo -e "${GREEN}✅ Services restarted${NC}\n"

# Step 9: Verification
echo -e "${YELLOW}Step 9: Verifying deployment${NC}"

# Check app
if curl -s http://localhost:8000 > /dev/null; then
    echo -e "${GREEN}✅ Application responding${NC}"
else
    echo -e "${RED}❌ Application not responding${NC}"
fi

# Check microservice
if curl -s http://localhost:8001/health | grep -q "ok"; then
    echo -e "${GREEN}✅ Microservice healthy${NC}"
else
    echo -e "${RED}❌ Microservice not healthy${NC}"
fi

echo ""
echo -e "${GREEN}=================================="
echo "  ✅ DEPLOYMENT COMPLETED"
echo "==================================${NC}"
echo ""
echo "Application URL: https://esppd.example.com"
echo "API URL: https://esppd.example.com/api"
echo ""
echo "Check logs:"
echo "  tail -f /var/www/esppd/storage/logs/laravel.log"
echo "  tail -f /var/log/nginx/esppd-error.log"
echo "  sudo journalctl -u esppd-microservice -f"
echo ""
