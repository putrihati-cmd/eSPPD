#!/bin/bash
################################################################################
# eSPPD - Minimal Deployment to Home Directory
# For testing deployment before moving to /var/www
################################################################################

set -e
export TERM=xterm

APP_PATH="$HOME/esppd_app"
DB_HOST="localhost"
DB_DATABASE="esppd_production"
DB_USERNAME="esppd_user"
DB_PASSWORD="Esppd@123456"

echo "[*] eSPPD Deployment Started"
echo "[*] App will be installed to: $APP_PATH"

# Check requirements
echo "[STEP] Checking requirements..."
php -v | head -1
node -v
git --version | head -1
psql --version | head -1

# Clone
echo "[STEP] Setting up application..."
mkdir -p "$APP_PATH"
cd "$APP_PATH"
[ -d ".git" ] && git clean -fd . && git pull origin main --ff-only || git clone https://github.com/putrihati-cmd/eSPPD.git . --quiet

# Environment
echo "[STEP] Creating .env..."
cat > .env << 'ENVEOF'
APP_NAME=eSPPD
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.1.27:8000

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

PYTHON_SERVICE_URL=http://localhost:8001
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,192.168.1.27
CORS_ALLOWED_ORIGINS=http://192.168.1.27:8000
ENVEOF

# Dependencies
echo "[STEP] Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet 2>/dev/null || true
npm install --production --silent 2>/dev/null || npm install --production --loglevel=error

# App setup
echo "[STEP] Generating key..."
php artisan key:generate --force --quiet

# Database
echo "[STEP] Testing database..."
PGPASSWORD="$DB_PASSWORD" psql -h localhost -U "$DB_USERNAME" -d "$DB_DATABASE" -c "SELECT 1" >/dev/null 2>&1 || {
    echo "[ERROR] Cannot connect to database"
    exit 1
}

echo "[STEP] Running migrations..."
php artisan migrate --force --quiet 2>/dev/null || php artisan migrate --force

# Build
echo "[STEP] Caching config..."
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet

echo "[STEP] Building assets..."
npm run build --silent 2>/dev/null || npm run build

# Permissions
echo "[STEP] Setting permissions..."
chmod -R 755 "$APP_PATH"
chmod -R 775 "$APP_PATH/storage"
chmod -R 775 "$APP_PATH/bootstrap/cache"

# Summary
echo ""
echo "✓ DEPLOYMENT COMPLETE!"
echo ""
echo "Application installed at: $APP_PATH"
echo ""
echo "Next steps:"
echo "  1. Move to production:"
echo "     mv $APP_PATH /var/www/esppd"
echo "  2. OR run PHP server:"
echo "     php artisan serve --host=0.0.0.0 --port=8000"
echo "  3. Check logs:"
echo "     tail -f $APP_PATH/storage/logs/laravel.log"
echo ""
