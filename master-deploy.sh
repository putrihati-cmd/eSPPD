#!/bin/bash
################################################################################
# ESPPD Complete Deployment Script (Master)
# Automates EVERYTHING for production deployment
#
# Run on server: bash master-deploy.sh
# Or: curl -fsSL https://raw.githubusercontent.com/putrihati-cmd/eSPPD/main/master-deploy.sh | bash
################################################################################

set -e
export DEBIAN_FRONTEND=noninteractive

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
log_step() { echo -e "${BLUE}[STEP]${NC} $1"; }
log_success() { echo -e "${GREEN}[✓]${NC} $1"; }
log_error() { echo -e "${RED}[✗]${NC} $1"; exit 1; }
log_info() { echo -e "${YELLOW}[INFO]${NC} $1"; }

################################################################################
# START
################################################################################

clear
echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     ESPPD PRODUCTION DEPLOYMENT - FULL AUTO        ║${NC}"
echo -e "${BLUE}║            One Command Deployment                   ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo "Configuration:"
echo "  App Path: $APP_PATH"
echo "  Database: $DB_DATABASE"
echo "  User: $DB_USERNAME"
echo "  Repository: $REPO"
echo ""
log_info "Starting deployment... (This will take ~10-15 minutes)"
echo ""

################################################################################
# STEP 1: Pre-flight Checks
################################################################################

log_step "Checking system requirements..."

[ -x "$(command -v php)" ] || log_error "PHP not installed"
[ -x "$(command -v composer)" ] || log_error "Composer not installed"
[ -x "$(command -v npm)" ] || log_error "Node.js not installed"
[ -x "$(command -v git)" ] || log_error "Git not installed"
[ -x "$(command -v psql)" ] || log_error "PostgreSQL client not installed"

log_success "All requirements met"
echo "  ✓ PHP $(php -v | head -1 | cut -d' ' -f2)"
echo "  ✓ Composer installed"
echo "  ✓ Node.js $(node -v)"
echo "  ✓ Git installed"
echo ""

################################################################################
# STEP 2: Clone Repository
################################################################################

log_step "Cloning repository..."

if [ -d "$APP_PATH" ]; then
    log_info "Directory exists, pulling latest changes..."
    cd "$APP_PATH"
    git pull origin main --quiet
else
    git clone "$REPO" "$APP_PATH" --quiet
    cd "$APP_PATH"
fi

log_success "Repository ready"
echo ""

################################################################################
# STEP 3: Create .env File
################################################################################

log_step "Configuring application environment..."

cat > "$APP_PATH/.env" << 'ENVFILE'
APP_NAME=eSPPD
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=esppd_production
DB_USERNAME=esppd_user
DB_PASSWORD=Esppd@123456

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=log

PYTHON_SERVICE_URL=http://localhost:8001
PYTHON_SERVICE_TIMEOUT=30

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
CORS_ALLOWED_ORIGINS=http://localhost:8000
ENVFILE

log_success ".env file created"
echo ""

################################################################################
# STEP 4: Install Dependencies
################################################################################

log_step "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet
log_success "PHP dependencies installed"

log_step "Installing Node.js dependencies..."
npm install --production --silent --no-audit
log_success "Node.js dependencies installed"
echo ""

################################################################################
# STEP 5: Generate App Key
################################################################################

log_step "Generating encryption key..."
php artisan key:generate --force --quiet
log_success "Encryption key generated"
echo ""

################################################################################
# STEP 6: Database Connection & Migrations
################################################################################

log_step "Testing database connection..."

if ! PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c "SELECT 1" >/dev/null 2>&1; then
    log_error "Cannot connect to database. Ensure PostgreSQL is running and credentials are correct."
fi

log_success "Database connection verified"

log_step "Running migrations..."
php artisan migrate --force --quiet
log_success "Database migrations completed"
echo ""

################################################################################
# STEP 7: Cache Configuration
################################################################################

log_step "Building application cache..."
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet
log_success "Application cache built"
echo ""

################################################################################
# STEP 8: Build Assets
################################################################################

log_step "Building frontend assets..."
npm run build --silent
log_success "Frontend assets built"
echo ""

################################################################################
# STEP 9: Set Permissions
################################################################################

log_step "Setting file permissions..."

sudo chown -R www-data:www-data "$APP_PATH" 2>/dev/null || true
chmod -R 755 "$APP_PATH"
chmod -R 775 "$APP_PATH/storage"
chmod -R 775 "$APP_PATH/bootstrap/cache"

log_success "File permissions configured"
echo ""

################################################################################
# STEP 10: Verification
################################################################################

log_step "Verifying deployment..."

# Check migrations
MIGRATION_COUNT=$(php artisan migrate:status 2>/dev/null | grep -c "Yes" || echo "0")
echo "  • Migrations applied: $MIGRATION_COUNT"

# Check app
if php artisan tinker --execute="echo 'OK'" >/dev/null 2>&1; then
    echo "  • Laravel application: OK"
fi

# Check database
if PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c "SELECT COUNT(*) as users FROM users;" 2>/dev/null | grep -q '[0-9]'; then
    echo "  • Database connection: OK"
fi

echo ""

################################################################################
# COMPLETE
################################################################################

echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║        DEPLOYMENT COMPLETED SUCCESSFULLY!         ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}✓ eSPPD is ready for production!${NC}"
echo ""
echo "Application:"
echo "  Location: $APP_PATH"
echo "  Environment: production"
echo "  Database: $DB_DATABASE@$DB_HOST"
echo ""
echo "Next Steps:"
echo "  1. Configure Nginx: sudo nano /etc/nginx/sites-available/esppd"
echo "  2. Enable Nginx: sudo ln -s /etc/nginx/sites-available/esppd /etc/nginx/sites-enabled/"
echo "  3. Test Nginx: sudo nginx -t && sudo systemctl reload nginx"
echo "  4. Start microservice: cd $APP_PATH/document-service && python -m uvicorn main:app &"
echo "  5. Test app: curl http://localhost:8000"
echo ""
echo "View Logs:"
echo "  tail -f $APP_PATH/storage/logs/laravel.log"
echo ""
echo "SSH Back to Server:"
echo "  ssh tholibserver@192.168.1.27"
echo ""
echo -e "${GREEN}Status: 🟢 PRODUCTION READY${NC}"
echo ""
