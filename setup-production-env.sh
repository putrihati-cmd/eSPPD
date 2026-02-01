#!/bin/bash
# Setup Production .env untuk e-SPPD
# Run di production server: bash /var/www/esppd/setup-production-env.sh

APP_PATH="/var/www/esppd"
ENV_FILE="$APP_PATH/.env"

echo "=========================================="
echo "Setting up Production .env"
echo "=========================================="

# Create production .env
cat > "$ENV_FILE" << 'EOF'
APP_NAME=e-SPPD
APP_ENV=production
APP_KEY=base64:kpjyIqAypooq7VWSjrKiXYso5cEmdULs/Pjs5EwyFNI=
APP_DEBUG=false
APP_URL=https://esppd.infiatin.cloud

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=esppd_production
DB_USERNAME=postgres
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.infiatin.cloud
SESSION_SECURE_COOKIE=true
LIVEWIRE_ASSET_URL=/vendor/livewire
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis

CACHE_STORE=redis
CACHE_PREFIX=esppd_prod_

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@esppd.infiatin.cloud
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=api-eu.pusher.com
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=eu

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

AUTO_DB_SYNC=true
PRODUCTION_HOST=192.168.1.27
PRODUCTION_USER=tholib_server
PRODUCTION_DB_PASSWORD=Esppd@123456
EOF

# Set proper permissions
chmod 600 "$ENV_FILE"
chown www-data:www-data "$ENV_FILE"

echo "✓ .env production created"

# Clear caches
cd "$APP_PATH"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✓ Caches cleared and rebuilt"
echo ""
echo "=========================================="
echo "Production .env Setup Complete!"
echo "=========================================="
echo ""
echo "Configuration:"
echo "  APP_ENV: production"
echo "  APP_DEBUG: false"
echo "  APP_URL: https://esppd.infiatin.cloud"
echo "  DB: esppd_production"
echo "  SESSION_SECURE_COOKIE: true"
echo "  LOG_LEVEL: error"
echo ""
