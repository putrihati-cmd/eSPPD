#!/bin/bash
# setup.sh - Install all dependencies for eSPPD on Ubuntu VPS

set -e

echo "=========================================="
echo "eSPPD Server Setup Script"
echo "=========================================="

# Update system
echo ">>> Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install essential packages
echo ">>> Installing essential packages..."
sudo apt install -y \
    software-properties-common \
    curl \
    wget \
    git \
    unzip \
    ufw \
    fail2ban

# Install PHP 8.2 and extensions
echo ">>> Installing PHP 8.2..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-gd \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-redis

# Install Nginx
echo ">>> Installing Nginx..."
sudo apt install -y nginx

# Install MySQL 8.0
echo ">>> Installing MySQL 8.0..."
sudo apt install -y mysql-server

# Install Redis
echo ">>> Installing Redis..."
sudo apt install -y redis-server

# Install Composer
echo ">>> Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20 LTS
echo ">>> Installing Node.js 20 LTS..."
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Supervisor
echo ">>> Installing Supervisor..."
sudo apt install -y supervisor

# Configure UFW Firewall
echo ">>> Configuring Firewall..."
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw --force enable

# Configure Fail2ban
echo ">>> Configuring Fail2ban..."
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Start services
echo ">>> Starting services..."
sudo systemctl enable php8.2-fpm
sudo systemctl enable nginx
sudo systemctl enable mysql
sudo systemctl enable redis-server
sudo systemctl enable supervisor

sudo systemctl start php8.2-fpm
sudo systemctl start nginx
sudo systemctl start mysql
sudo systemctl start redis-server
sudo systemctl start supervisor

echo "=========================================="
echo "Setup complete!"
echo "Next steps:"
echo "1. Configure MySQL: sudo mysql_secure_installation"
echo "2. Create database: mysql -u root -p"
echo "3. Copy nginx config to /etc/nginx/sites-available/"
echo "4. Run deploy.sh to deploy the application"
echo "=========================================="
