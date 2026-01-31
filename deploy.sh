#!/bin/bash
# Production deployment to esppd.infiatin.cloud

SERVER_IP="192.168.1.27"
SERVER_USER="tholib_server"
DOMAIN="esppd.infiatin.cloud"
APP_PATH="/var/www/esppd"

echo "=========================================="
echo "Deploying to $DOMAIN ($SERVER_IP)"
echo "=========================================="
echo ""

# 1. Sync code
echo "[1/5] Syncing code..."
rsync -avz --delete \
  --exclude '.env' \
  --exclude '.env.local' \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude 'storage/logs' \
  --exclude 'storage/framework/sessions' \
  --exclude 'bootstrap/cache' \
  ./ ${SERVER_USER}@${SERVER_IP}:${APP_PATH}/
echo "OK"
echo ""

# 2. Copy production env
echo "[2/5] Setting up production environment..."
scp .env.production ${SERVER_USER}@${SERVER_IP}:${APP_PATH}/.env
echo "OK"
echo ""

# 3. Install dependencies
echo "[3/5] Installing dependencies..."
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && composer install --no-dev --optimize-autoloader -q"
echo "OK"
echo ""

# 4. Run migrations and seed production users
echo "[4/5] Running migrations and seeding data..."
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && php artisan migrate --force -q"
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && php artisan db:seed --class=RoleSeeder -q"
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && php artisan db:seed --class=PermissionSeeder -q"
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && php artisan db:seed --class=ProductionUserSeeder -q"
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && php artisan config:cache && php artisan route:cache && php artisan view:cache"
echo "OK"
echo ""

# 5. Restart services
echo "[5/5] Restarting services..."
ssh ${SERVER_USER}@${SERVER_IP} "sudo systemctl reload nginx && sudo systemctl restart php8.5-fpm"
echo "OK"
echo ""

echo "=========================================="
echo "✅ Deployment Complete!"
echo "=========================================="
echo ""
echo "Access: https://$DOMAIN"
echo "Login: admin@esppd.test / password123"
echo ""
