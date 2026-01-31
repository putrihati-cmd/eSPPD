#!/bin/bash

# eSPPD Production Deployment with Domain Setup
# This script deploys to production server with esppd.infiatin.cloud domain

set -e

echo "=================================================="
echo "eSPPD Production Deployment - Domain Setup"
echo "=================================================="

# Configuration
SERVER_IP="192.168.1.27"
SERVER_USER="root"
DOMAIN="esppd.infiatin.cloud"
APP_PATH="/var/www/esppd"
NGINX_CONF="/etc/nginx/sites-enabled/esppd"

echo ""
echo "📋 Configuration:"
echo "   Server: $SERVER_IP"
echo "   Domain: $DOMAIN"
echo "   App Path: $APP_PATH"
echo "   Nginx Config: $NGINX_CONF"
echo ""

# Step 1: Sync code to production
echo "🔄 Step 1: Syncing code to production server..."
rsync -avz --delete \
  --exclude '.env.local' \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude 'storage/logs/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'bootstrap/cache/*' \
  ./ ${SERVER_USER}@${SERVER_IP}:${APP_PATH}/
echo "✅ Code synced"

# Step 2: Install dependencies on server
echo ""
echo "📦 Step 2: Installing dependencies on server..."
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && composer install --no-dev --optimize-autoloader"
echo "✅ Dependencies installed"

# Step 3: Deploy nginx configuration for domain
echo ""
echo "🌐 Step 3: Deploying nginx configuration..."
ssh ${SERVER_USER}@${SERVER_IP} "cat > ${NGINX_CONF}" << 'NGINX_CONFIG'
# eSPPD Production Configuration
# Domain: esppd.infiatin.cloud

# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name esppd.infiatin.cloud www.esppd.infiatin.cloud;
    return 301 https://$server_name$request_uri;
}

# HTTPS Server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name esppd.infiatin.cloud www.esppd.infiatin.cloud;

    # SSL Certificate (Let's Encrypt or self-signed)
    ssl_certificate /etc/letsencrypt/live/esppd.infiatin.cloud/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/esppd.infiatin.cloud/privkey.pem;

    # SSL Best Practices
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    root /var/www/esppd/public;
    index index.php;
    charset utf-8;

    # Logging
    access_log /var/log/nginx/esppd_access.log;
    error_log /var/log/nginx/esppd_error.log;

    # Main Location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Static Files Cache
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP Handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Hide hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX_CONFIG
echo "✅ Nginx configuration deployed"

# Step 4: Run migrations on server
echo ""
echo "🗄️  Step 4: Running database migrations..."
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && php artisan migrate --force"
echo "✅ Migrations completed"

# Step 5: Clear cache on server
echo ""
echo "🧹 Step 5: Clearing caches..."
ssh ${SERVER_USER}@${SERVER_IP} "cd ${APP_PATH} && php artisan config:cache && php artisan route:cache && php artisan view:cache"
echo "✅ Caches cleared"

# Step 6: Reload nginx
echo ""
echo "🔄 Step 6: Reloading nginx..."
ssh ${SERVER_USER}@${SERVER_IP} "nginx -t && systemctl reload nginx"
echo "✅ Nginx reloaded"

echo ""
echo "=================================================="
echo "✅ DEPLOYMENT COMPLETE!"
echo "=================================================="
echo ""
echo "📍 Access your application at:"
echo "   🌐 https://esppd.infiatin.cloud"
echo ""
echo "📝 Next steps:"
echo "   1. Verify DNS is pointing domain to 192.168.1.27"
echo "   2. If using Let's Encrypt, run: certbot certonly --webroot -d esppd.infiatin.cloud"
echo "   3. Test login with test accounts (see RBAC_QUICK_REFERENCE.md)"
echo ""
