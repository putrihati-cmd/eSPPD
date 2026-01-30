#!/bin/bash

# ===================================================
# e-SPPD Production Deployment Script
# Repository: https://github.com/putrihati-cmd/eSPPD.git
# Server: 192.168.1.27 (esppd.infiatin.cloud)
# ===================================================

set -e

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Konfigurasi
REPO_URL="https://github.com/putrihati-cmd/eSPPD.git"
DEPLOY_PATH="/var/www/esppd"
BACKUP_PATH="/home/tholib_server/esppd_production/backups"
DB_NAME="esppd_production"
APP_ENV="production"
APP_DEBUG="false"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}e-SPPD Production Deployment${NC}"
echo -e "${BLUE}========================================${NC}"

# Fungsi helper
log_info() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
    exit 1
}

log_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

log_step() {
    echo -e "${BLUE}[→]${NC} $1"
}

# ===================================================
# STEP 1: Prepare Directories
# ===================================================
log_step "Step 1: Preparing directories..."
sudo mkdir -p "$DEPLOY_PATH"/{storage,bootstrap/cache}
sudo mkdir -p "$DEPLOY_PATH"/storage/{app,framework,logs}
sudo mkdir -p "$DEPLOY_PATH"/storage/framework/{sessions,views,cache}
sudo mkdir -p "$BACKUP_PATH"
log_info "Directories created"

# ===================================================
# STEP 2: Clone/Update Repository
# ===================================================
log_step "Step 2: Cloning repository..."

if [ -d "$DEPLOY_PATH/.git" ]; then
    log_warning "Repository already exists, pulling latest changes..."
    cd "$DEPLOY_PATH"
    sudo git pull origin main
else
    log_info "Cloning repository..."
    sudo git clone "$REPO_URL" "$DEPLOY_PATH"
    cd "$DEPLOY_PATH"
fi

log_info "Repository ready"

# ===================================================
# STEP 3: Check Dependencies
# ===================================================
log_step "Step 3: Checking dependencies..."

# Check PHP
if ! command -v php &> /dev/null; then
    log_error "PHP not installed!"
fi
PHP_VERSION=$(php -v | head -n 1)
log_info "PHP: $PHP_VERSION"

# Check Composer
if ! command -v composer &> /dev/null; then
    log_error "Composer not installed!"
fi
log_info "Composer installed"

# Check npm
if command -v npm &> /dev/null; then
    log_info "Node.js/npm installed"
else
    log_warning "Node.js/npm not installed (optional)"
fi

# ===================================================
# STEP 4: Install PHP Dependencies
# ===================================================
log_step "Step 4: Installing PHP dependencies..."
cd "$DEPLOY_PATH"
sudo composer install --no-dev --optimize-autoloader --working-dir="$DEPLOY_PATH"
log_info "PHP dependencies installed"

# ===================================================
# STEP 5: Install Node Dependencies & Build Assets
# ===================================================
log_step "Step 5: Building frontend assets..."
if command -v npm &> /dev/null; then
    sudo npm ci --working-dir="$DEPLOY_PATH"
    sudo npm run build --working-dir="$DEPLOY_PATH"
    log_info "Frontend assets built"
else
    log_warning "npm not available, skipping frontend build"
fi

# ===================================================
# STEP 6: Setup Environment File
# ===================================================
log_step "Step 6: Setting up environment..."
if [ ! -f "$DEPLOY_PATH/.env" ]; then
    log_warning ".env file not found, creating from template..."
    cp "$DEPLOY_PATH/.env.example" "$DEPLOY_PATH/.env" 2>/dev/null || log_error ".env.example not found!"

    # Generate app key
    php "$DEPLOY_PATH/artisan" key:generate

    log_warning "Please update .env with your database credentials:"
    log_warning "  DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    log_warning "  REDIS_HOST, REDIS_PASSWORD"
    log_warning "File: $DEPLOY_PATH/.env"
else
    log_info ".env file already exists"
fi

# ===================================================
# STEP 7: Set Permissions
# ===================================================
log_step "Step 7: Setting file permissions..."
sudo chown -R tholib_server:www-data "$DEPLOY_PATH"
sudo chmod -R 755 "$DEPLOY_PATH"
sudo chmod -R 775 "$DEPLOY_PATH"/storage
sudo chmod -R 775 "$DEPLOY_PATH"/bootstrap/cache
log_info "Permissions set correctly"

# ===================================================
# STEP 8: Run Database Migrations (if DB is ready)
# ===================================================
log_step "Step 8: Running database migrations..."
cd "$DEPLOY_PATH"

# Check if we can connect to database
if php artisan migrate --dry-run 2>/dev/null; then
    php artisan migrate --force
    log_info "Database migrations completed"
else
    log_warning "Database connection not ready yet"
    log_warning "Run manually later: php artisan migrate --force"
fi

# ===================================================
# STEP 9: Cache Optimization
# ===================================================
log_step "Step 9: Optimizing application cache..."
cd "$DEPLOY_PATH"
php artisan config:cache
php artisan route:cache
php artisan view:cache
log_info "Application cache optimized"

# ===================================================
# STEP 10: Restart Services
# ===================================================
log_step "Step 10: Restarting services..."

# Restart PHP-FPM
if sudo systemctl is-active --quiet php8.2-fpm; then
    sudo systemctl restart php8.2-fpm
    log_info "PHP-FPM restarted"
elif sudo systemctl is-active --quiet php8.1-fpm; then
    sudo systemctl restart php8.1-fpm
    log_info "PHP-FPM (8.1) restarted"
fi

# Restart Nginx
sudo nginx -t && sudo systemctl restart nginx
log_info "Nginx restarted"

# ===================================================
# STEP 11: Health Check
# ===================================================
log_step "Step 11: Health check..."
sleep 3

if [ -f "$DEPLOY_PATH/public/index.php" ]; then
    log_info "Application files present"
else
    log_error "Application files not found!"
fi

# Check if we can access the health endpoint
HEALTH_CHECK=$(curl -s http://localhost/health || echo "failed")
if [[ "$HEALTH_CHECK" == *"success"* ]]; then
    log_info "Application health check passed"
else
    log_warning "Health check inconclusive (may need database setup)"
fi

# ===================================================
# FINAL SUMMARY
# ===================================================
echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✓ Deployment Completed!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo "Application Path: $DEPLOY_PATH"
echo "Backup Path: $BACKUP_PATH"
echo "URL: https://esppd.infiatin.cloud"
echo ""
echo "Next Steps:"
echo "1. Verify .env configuration:"
echo "   sudo nano $DEPLOY_PATH/.env"
echo ""
echo "2. If database is not ready, run migrations:"
echo "   cd $DEPLOY_PATH && php artisan migrate --force"
echo ""
echo "3. Monitor logs:"
echo "   tail -f $DEPLOY_PATH/storage/logs/laravel.log"
echo ""
echo "4. Setup queue workers (Supervisor):"
echo "   sudo nano /etc/supervisor/conf.d/esppd.conf"
echo "   sudo supervisorctl reread && sudo supervisorctl update"
echo ""
echo -e "${BLUE}========================================${NC}"
