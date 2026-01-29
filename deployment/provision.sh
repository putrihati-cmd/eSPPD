#!/bin/bash

# Provisioning Script for e-SPPD Server (Ubuntu/Debian)
# Usage: sudo ./provision.sh

echo "🚀 Starting Server Provisioning..."

# Prevent interactive prompts
export DEBIAN_FRONTEND=noninteractive

# 1. Update System
sudo apt update && sudo apt upgrade -y

# 2. Install Dependencies
sudo apt install -y software-properties-common curl git unzip supervisor redis-server nginx postgresql postgresql-contrib

# 3. Add PHP Repository (if needed for older Ubuntu)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# 4. Install PHP 8.2 & Extensions
echo "🐘 Installing PHP 8.2..."
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-intl php8.2-gd php8.2-redis

# 5. Composer
echo "🎼 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 6. Configure Nginx
echo "🌐 Configuring Nginx..."
sudo tee /etc/nginx/sites-available/esppd <<EOF
server {
    listen 80;
    server_name esppd.infiatin.cloud;
    root /var/www/esppd/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

sudo ln -s /etc/nginx/sites-available/esppd /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl restart nginx

# 7. Configure Permissions
sudo mkdir -p /var/www/esppd
sudo chown -R $USER:www-data /var/www/esppd
sudo chmod -R 775 /var/www/esppd

echo "✅ Provisioning Complete!"
echo "Next: Run ./deploy_app.sh to install the application."
