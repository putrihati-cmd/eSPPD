#!/bin/bash

# setup_server.sh
# Automated Deployment Script for eSPPD on Ubuntu 22.04 LTS

set -e

# Configuration
APP_DIR="/var/www/esppd"
USER_NAME="deployer"
REPO_URL="YOUR_GIT_REPO_URL_HERE"
DOMAIN="esppd.yourdomain.com"

echo "=== Starting eSPPD Server Provisioning using $(whoami) ==="

# 1. System Update & Essentials
echo "--- Updating System ---"
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y git curl wget unzip htop ufw supervisor nginx

# 2. Security Hardening (UFW)
echo "--- Configuring Firewall ---"
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
# sudo ufw enable # Uncomment to enable immediately (Risk of lockout if SSH not allowed correctly)

# 3. Install Docker & Docker Compose
echo "--- Installing Docker ---"
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
fi

# 4. Create Directory Structure
echo "--- Setting up Application Directory ---"
sudo mkdir -p $APP_DIR
sudo chown -R $USER:$USER $APP_DIR

# 5. Application Setup (Placeholder)
echo "--- Application Setup ---"
if [ ! -d "$APP_DIR/.git" ]; then
    echo "Cloning repository..."
    # git clone $REPO_URL $APP_DIR 
    # For now, we assume files are copied or synced.
    echo "Please clone the repo to $APP_DIR or sync files manually."
else
    echo "Repository already exists."
fi

# 6. Supervisord Configuration
echo "--- Configuring Supervisor ---"
# Copy supervisor config from repo (assuming it exists in deployment/supervisord.conf)
# sudo cp $APP_DIR/deployment/supervisord.conf /etc/supervisor/conf.d/esppd.conf
# sudo supervisorctl reread
# sudo supervisorctl update

# 7. Nginx Proxy Setup
echo "--- Configuring Nginx ---"
# sudo cp $APP_DIR/deployment/nginx/app.conf /etc/nginx/sites-available/$DOMAIN
# sudo ln -s /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
# sudo nginx -t
# sudo systemctl restart nginx

echo "=== Provisioning Complete! === "
echo "Next Steps:"
echo "1. Clone your repository into $APP_DIR"
echo "2. Create .env file from .env.example"
echo "3. Run 'docker compose up -d --build'"
echo "4. Configure Supervisor and Nginx using files in 'deployment/' folder."
