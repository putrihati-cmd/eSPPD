#!/bin/bash

# Setup e-SPPD Server - Direktori dan Nginx Configuration
# Usage: bash setup_server_nginx.sh

set -e

echo "=========================================="
echo "Setup e-SPPD Server - Fase 1"
echo "=========================================="

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fungsi untuk print status
print_status() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Step 1: Buat direktori yang diperlukan
echo ""
echo "Step 1: Membuat direktori..."
sudo mkdir -p /var/www/esppd
sudo mkdir -p /var/www/esppd/storage/logs
sudo mkdir -p /var/www/esppd/storage/app
sudo mkdir -p /var/www/esppd/bootstrap/cache
sudo mkdir -p /home/tholib_server/esppd_production

print_status "Direktori dibuat"

# Step 2: Set permissions
echo ""
echo "Step 2: Mengatur permissions..."
sudo chown -R tholib_server:www-data /var/www/esppd
sudo chmod -R 755 /var/www/esppd
sudo chmod -R 775 /var/www/esppd/storage
sudo chmod -R 775 /var/www/esppd/bootstrap/cache
sudo chown -R tholib_server:tholib_server /home/tholib_server/esppd_production
sudo chmod -R 755 /home/tholib_server/esppd_production

print_status "Permissions dikonfigurasi"

# Step 3: Check PHP version
echo ""
echo "Step 3: Checking PHP version..."
PHP_VERSION=$(php -v | head -n 1)
print_status "PHP installed: $PHP_VERSION"

# Step 4: Check FPM socket
echo ""
echo "Step 4: Checking PHP-FPM socket..."
if [ -S /var/run/php/php8.2-fpm.sock ]; then
    print_status "PHP 8.2 FPM socket found: /var/run/php/php8.2-fpm.sock"
    FPM_SOCKET="/var/run/php/php8.2-fpm.sock"
elif [ -S /var/run/php/php8.1-fpm.sock ]; then
    print_warning "PHP 8.2 socket not found, using 8.1"
    FPM_SOCKET="/var/run/php/php8.1-fpm.sock"
else
    print_error "PHP-FPM socket tidak ditemukan!"
    echo "Available sockets:"
    ls -la /var/run/php/ 2>/dev/null || echo "  (No sockets found)"
    exit 1
fi

# Step 5: Verify directories were created
echo ""
echo "Step 5: Verifying directory structure..."
echo "Directory listing /var/www/esppd:"
ls -la /var/www/esppd | head -10
print_status "Direktori /var/www/esppd exists"

# Step 6: Show Nginx config location
echo ""
echo "Step 6: Next steps..."
echo "=================================="
echo "Nginx config yang sudah diperbaiki tersimpan di:"
echo "  Local:  deployment/nginx_esppd_fixed.conf"
echo "  Server: /etc/nginx/sites-enabled/esppd"
echo ""
echo "Untuk upload config ke server, jalankan:"
echo "  scp deployment/nginx_esppd_fixed.conf tholib_server@192.168.1.27:/tmp/"
echo "  ssh tholib_server@192.168.1.27 'sudo mv /tmp/nginx_esppd_fixed.conf /etc/nginx/sites-enabled/esppd'"
echo ""
echo "Atau manual copy-paste ke:"
echo "  sudo nano /etc/nginx/sites-enabled/esppd"
echo ""
echo "Setelah itu, test config dengan:"
echo "  sudo nginx -t"
echo "  sudo systemctl restart nginx"
echo "=================================="

print_status "Setup tahap 1 selesai!"
