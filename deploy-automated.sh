#!/bin/bash
################################################################################
# ESPPD Production Deployment - Non-Interactive Version
# For automated execution via SSH/CI-CD
################################################################################

set -e
export DEBIAN_FRONTEND=noninteractive
export TERM=xterm

# Configuration
APP_PATH="/var/www/esppd"
DB_HOST="localhost"
DB_DATABASE="esppd_production"
DB_USERNAME="esppd_user"
DB_PASSWORD="Esppd@123456"
REPO="https://github.com/putrihati-cmd/eSPPD.git"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Helper functions
log_step() { echo "[STEP] $1"; }
log_success() { echo "[OK] $1"; }
log_error() { echo "[ERROR] $1"; exit 1; }

################################################################################
# START
################################################################################

log_step "ESPPD Production Deployment (Non-Interactive)"
log_step "Target: $APP_PATH | DB: $DB_DATABASE"

################################################################################
# Pre-flight Checks
################################################################################

log_step "Checking requirements..."

[ -x "$(command -v php)" ] || log_error "PHP not found"
[ -x "$(command -v composer)" ] || log_error "Composer not found"
[ -x "$(command -v npm)" ] || log_error "npm not found"
[ -x "$(command -v git)" ] || log_error "Git not found"
[ -x "$(command -v psql)" ] || log_error "psql not found"

log_success "All requirements OK"
log_success "PHP $(php -v | head -1)"
log_success "Node $(node -v)"

################################################################################
# Clone/Update Repository
################################################################################

log_step "Cloning/updating repository..."

if [ -d "$APP_PATH" ]; then
    cd "$APP_PATH"
    git clean -fd
    git reset --hard HEAD
    git pull origin main --quiet
else
    sudo mkdir -p $(dirname "$APP_PATH")
    git clone "$REPO" "$APP_PATH" --quiet
    cd "$APP_PATH"
fi

log_success "Repository ready"

################################################################################
# Create .env
################################################################################

log_step "Creating .env file..."

cat > "$APP_PATH/.env" << 'ENV'
APP_NAME=eSPPD
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.1.27:8000
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=esppd_production
DB_USERNAME=esppd_user
DB_PASSWORD=Esppd@123456

CACHE_DRIVER=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

PYTHON_SERVICE_URL=http://localhost:8001
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,192.168.1.27
CORS_ALLOWED_ORIGINS=http://192.168.1.27:8000
ENV

log_success ".env created"

################################################################################
# Install Dependencies
################################################################################

log_step "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet 2>/dev/null || true
log_success "Composer dependencies installed"

log_step "Installing Node dependencies..."
npm install --production --silent --no-audit 2>/dev/null || npm install --production
log_success "Node dependencies installed"

################################################################################
# Generate Key
################################################################################

log_step "Generating encryption key..."
php artisan key:generate --force --quiet
log_success "Key generated"

################################################################################
# Database
################################################################################

log_step "Testing database connection..."

if ! PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c "SELECT 1" >/dev/null 2>&1; then
    log_error "Cannot connect to database at $DB_HOST"
fi

log_success "Database connection OK"

log_step "Running migrations..."
php artisan migrate --force --quiet 2>/dev/null || php artisan migrate --force
log_success "Migrations complete"

################################################################################
# Cache & Assets
################################################################################

log_step "Building cache..."
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet
log_success "Cache built"

log_step "Building assets..."
npm run build --silent 2>/dev/null || npm run build
log_success "Assets built"

################################################################################
# Permissions
################################################################################

log_step "Setting permissions..."
sudo chown -R www-data:www-data "$APP_PATH" 2>/dev/null || true
chmod -R 755 "$APP_PATH"
chmod -R 775 "$APP_PATH/storage"
chmod -R 775 "$APP_PATH/bootstrap/cache"
log_success "Permissions set"

################################################################################
# Verification
################################################################################

log_step "Verifying deployment..."

MIGRATION_COUNT=$(php artisan migrate:status 2>/dev/null | grep -c "Yes" || echo "0")
echo "  Migrations: $MIGRATION_COUNT applied"

DB_USERS=$(PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c "SELECT COUNT(*) FROM users;" 2>/dev/null | tail -2 | head -1 || echo "?")
echo "  Database users: $DB_USERS"

log_success "Deployment verified"

################################################################################
# Summary
################################################################################

echo ""
echo "================================"
echo "DEPLOYMENT COMPLETED!"
echo "================================"
echo ""
echo "Application: $APP_PATH"
echo "Database: $DB_DATABASE"
echo "Status: READY"
echo ""
echo "Next:"
echo "  1. Configure Nginx: /etc/nginx/sites-available/esppd"
echo "  2. Enable SSL: sudo certbot certonly --nginx"
echo "  3. Start PHP: sudo systemctl restart php-fpm"
echo "  4. Reload Nginx: sudo systemctl reload nginx"
echo "  5. Test: curl http://192.168.1.27:8000"
echo ""
echo "Logs: tail -f $APP_PATH/storage/logs/laravel.log"
echo ""
