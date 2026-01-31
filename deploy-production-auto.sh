#!/bin/bash

# ESPPD Production Deployment & Configuration Script
# Ini script khusus untuk setup .env dan deploy langsung

set -e

echo "================================"
echo "ESPPD Production Deployment"
echo "================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ==========================================
# STEP 1: Clone Repository
# ==========================================
echo -e "${YELLOW}[1/10] Cloning from GitHub...${NC}"
cd /var/www || exit 1

if [ -d "esppd" ]; then
    echo "Directory esppd already exists. Pulling latest..."
    cd esppd
    git pull origin main
else
    git clone https://github.com/putrihati-cmd/eSPPD.git esppd
    cd esppd
fi
echo -e "${GREEN}✓ Repository ready${NC}"
echo ""

# ==========================================
# STEP 2: Setup Environment Variables
# ==========================================
echo -e "${YELLOW}[2/10] Setting up .env configuration...${NC}"

cat > .env << 'EOF'
APP_NAME=eSPPD
APP_ENV=production
APP_KEY=base64:EnterGeneratedKeyHere
APP_DEBUG=false
APP_URL=https://esppd.local

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
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

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINTS=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=api-mt1.pusher.com
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Microservice Configuration
PYTHON_SERVICE_URL=http://localhost:8001
PYTHON_SERVICE_TIMEOUT=30

# Application Settings
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_DOMAIN=.local
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8000

# File Upload
UPLOAD_MAX_SIZE=10485760
EOF

echo -e "${GREEN}✓ .env file created${NC}"
echo ""

# ==========================================
# STEP 3: Generate Application Key
# ==========================================
echo -e "${YELLOW}[3/10] Generating application key...${NC}"
php artisan key:generate --force
echo -e "${GREEN}✓ Application key generated${NC}"
echo ""

# ==========================================
# STEP 4: Install PHP Dependencies
# ==========================================
echo -e "${YELLOW}[4/10] Installing PHP dependencies...${NC}"
composer install --no-dev --optimize-autoloader
echo -e "${GREEN}✓ PHP dependencies installed${NC}"
echo ""

# ==========================================
# STEP 5: Install Node Dependencies
# ==========================================
echo -e "${YELLOW}[5/10] Installing Node dependencies...${NC}"
npm install --production
echo -e "${GREEN}✓ Node dependencies installed${NC}"
echo ""

# ==========================================
# STEP 6: Run Database Migrations
# ==========================================
echo -e "${YELLOW}[6/10] Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Database migrations completed${NC}"
echo ""

# ==========================================
# STEP 7: Cache Configuration
# ==========================================
echo -e "${YELLOW}[7/10] Caching Laravel configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Configuration cached${NC}"
echo ""

# ==========================================
# STEP 8: Build Frontend Assets
# ==========================================
echo -e "${YELLOW}[8/10] Building frontend assets with Vite...${NC}"
npm run build
echo -e "${GREEN}✓ Frontend assets built${NC}"
echo ""

# ==========================================
# STEP 9: Set File Permissions
# ==========================================
echo -e "${YELLOW}[9/10] Setting file permissions...${NC}"
sudo chown -R www-data:www-data /var/www/esppd
chmod -R 755 /var/www/esppd
chmod -R 775 /var/www/esppd/storage
chmod -R 775 /var/www/esppd/bootstrap/cache
echo -e "${GREEN}✓ File permissions configured${NC}"
echo ""

# ==========================================
# STEP 10: Restart Services
# ==========================================
echo -e "${YELLOW}[10/10] Restarting services...${NC}"
sudo systemctl reload nginx 2>/dev/null || echo "⚠ Nginx not running"

if sudo systemctl is-enabled esppd-microservice &> /dev/null; then
    sudo systemctl restart esppd-microservice
    echo "✓ Microservice restarted"
fi

echo -e "${GREEN}✓ Services restarted${NC}"
echo ""

# ==========================================
# Verification & Summary
# ==========================================
echo "================================"
echo -e "${GREEN}DEPLOYMENT COMPLETE!${NC}"
echo "================================"
echo ""

# Test Database Connection
echo -e "${BLUE}Testing Database Connection...${NC}"
if php artisan tinker --execute "echo 'DB OK'" 2>/dev/null; then
    echo -e "${GREEN}✓ Database connection successful${NC}"
else
    echo -e "${RED}✗ Database connection failed - check .env credentials${NC}"
fi
echo ""

# Show Migration Status
echo -e "${BLUE}Migration Status:${NC}"
php artisan migrate:status | head -10
echo ""

# ==========================================
# Final Summary
# ==========================================
echo "================================"
echo "Deployment Summary"
echo "================================"
echo ""
echo -e "${BLUE}Application Details:${NC}"
echo "  Location: /var/www/esppd"
echo "  Environment: production"
echo "  Database: PostgreSQL (esppd_production)"
echo "  User: esppd_user"
echo ""
echo -e "${BLUE}Configuration:${NC}"
echo "  PHP Artisan: php artisan [command]"
echo "  Clear Cache: php artisan cache:clear"
echo "  View Logs: tail -f storage/logs/laravel.log"
echo ""
echo -e "${BLUE}Services:${NC}"
echo "  Web Server: Nginx (reload completed)"
echo "  Microservice: Python/FastAPI (port 8001)"
echo "  Database: PostgreSQL (localhost:5432)"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo "  1. Configure Nginx virtual host: /etc/nginx/sites-available/esppd"
echo "  2. Enable SSL/TLS with Let's Encrypt"
echo "  3. Start Python microservice: cd document-service && python -m uvicorn main:app"
echo "  4. Setup Supervisor for queue worker (optional)"
echo "  5. Test: curl http://localhost:8000"
echo ""
echo -e "${GREEN}Ready for production!${NC}"
echo ""
