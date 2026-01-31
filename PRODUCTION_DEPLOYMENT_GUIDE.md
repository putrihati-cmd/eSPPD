# PRODUCTION DEPLOYMENT GUIDE

**Aplikasi:** eSPPD  
**Repository:** https://github.com/putrihati-cmd/eSPPD  
**Status:** Ready for production deployment

---

## 🎯 RINGKAS DEPLOYMENT

Setelah code ada di GitHub, kita perlu:

1. ✅ Pull code dari GitHub ke server production
2. ✅ Install dependencies
3. ✅ Setup database
4. ✅ Run migrations
5. ✅ Configure web server (Nginx/Apache)
6. ✅ Start application
7. ✅ Test aplikasi berjalan

---

## 📋 PRASYARAT SERVER PRODUCTION

Server harus punya:
```
✅ PHP 8.2+ (Laravel requirement)
✅ PostgreSQL 13+ (database)
✅ Nginx atau Apache (web server)
✅ Git (untuk pull dari GitHub)
✅ Composer (PHP dependency manager)
✅ Node.js & npm (untuk asset compilation)
✅ Python 3.10+ (untuk microservice)
✅ Uvicorn (Python ASGI server)
```

---

## 🚀 STEP-BY-STEP DEPLOYMENT

### STEP 1: SSH ke Production Server

```bash
ssh user@production-server.com

# Contoh:
ssh admin@esppd.example.com
```

### STEP 2: Clone Repository dari GitHub

```bash
cd /var/www

# Clone repo (gunakan HTTPS jika tidak ada SSH key)
git clone https://github.com/putrihati-cmd/eSPPD.git esppd

cd esppd

# Verifikasi branch main
git branch -a
# Harus menunjukkan: * main
```

### STEP 3: Setup Environment

```bash
# Copy file environment
cp .env.example .env

# Edit .env dengan nilai production
nano .env
```

**Edit .env dengan nilai ini:**

```env
APP_NAME="eSPPD"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://esppd.example.com

# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=eSPPD
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password

# Python Microservice
PYTHON_DOCUMENT_SERVICE_URL=http://localhost:8001
DOCUMENT_SERVICE_TIMEOUT=60

# Mail (untuk OTP & notifikasi)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@esppd.example.com
```

### STEP 4: Install PHP Dependencies

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Publish Livewire assets
php artisan livewire:publish
```

### STEP 5: Setup Database

```bash
# Buat database (jika belum ada)
sudo -u postgres createdb eSPPD

# Run migrations
php artisan migrate --force

# Seed data (optional - untuk test data)
php artisan db:seed

# Verify migrations
php artisan migrate:status
# Harus menunjukkan: [1] Ran (untuk semua migrations)
```

### STEP 6: Compile Frontend Assets

```bash
npm install
npm run build

# Verify:
ls -la public/build/
# Harus ada manifest.json
```

### STEP 7: Configure Web Server (Nginx)

**Buat file:** `/etc/nginx/sites-available/esppd`

```nginx
server {
    listen 80;
    server_name esppd.example.com;
    
    root /var/www/esppd/public;
    index index.php index.html;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name esppd.example.com;

    # SSL Certificates (dari Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/esppd.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/esppd.example.com/privkey.pem;

    root /var/www/esppd/public;
    index index.php index.html index.htm;

    # Logs
    access_log /var/log/nginx/esppd-access.log;
    error_log /var/log/nginx/esppd-error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/esppd /etc/nginx/sites-enabled/

# Test nginx config
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

### STEP 8: Setup SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Generate certificate
sudo certbot certonly --nginx -d esppd.example.com

# Auto-renew
sudo systemctl enable certbot.timer
```

### STEP 9: Setup Microservice (Python)

```bash
cd /var/www/esppd/document-service

# Create virtual environment
python3 -m venv venv

# Activate venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Deactivate
deactivate
```

**Buat systemd service:** `/etc/systemd/system/esppd-microservice.service`

```ini
[Unit]
Description=eSPPD Document Generation Microservice
After=network.target

[Service]
Type=notify
User=www-data
WorkingDirectory=/var/www/esppd/document-service
Environment="PATH=/var/www/esppd/document-service/venv/bin"
ExecStart=/var/www/esppd/document-service/venv/bin/python -m uvicorn main:app --host 127.0.0.1 --port 8001
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

**Enable & start service:**
```bash
sudo systemctl daemon-reload
sudo systemctl enable esppd-microservice
sudo systemctl start esppd-microservice
sudo systemctl status esppd-microservice
```

### STEP 10: Setup Laravel Worker (Optional - untuk background jobs)

**Buat systemd service:** `/etc/systemd/system/esppd-worker.service`

```ini
[Unit]
Description=eSPPD Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/esppd
ExecStart=/usr/bin/php artisan queue:work database --sleep=3 --tries=3
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

**Enable & start:**
```bash
sudo systemctl enable esppd-worker
sudo systemctl start esppd-worker
```

### STEP 11: Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize class loader
composer dump-autoload --optimize
```

### STEP 12: Setup Supervisor (Process Management)

**Buat file:** `/etc/supervisor/conf.d/esppd.conf`

```ini
[program:esppd-php]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php artisan serve --host=127.0.0.1 --port=8000
numprocs=1
directory=/var/www/esppd
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/esppd-php.log

[program:esppd-microservice]
process_name=%(program_name)s
command=/var/www/esppd/document-service/venv/bin/uvicorn main:app --host=127.0.0.1 --port=8001
directory=/var/www/esppd/document-service
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/esppd-microservice.log
environment=PATH="/var/www/esppd/document-service/venv/bin"
```

**Reload supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### STEP 13: Setup File Permissions

```bash
# Set owner
sudo chown -R www-data:www-data /var/www/esppd

# Set directories permissions
sudo chmod -R 755 /var/www/esppd
sudo chmod -R 775 /var/www/esppd/storage
sudo chmod -R 775 /var/www/esppd/bootstrap/cache

# Set file permissions
sudo find /var/www/esppd -type f -exec chmod 644 {} \;
sudo find /var/www/esppd -type f -name "*.sh" -exec chmod +x {} \;
```

### STEP 14: Setup Logs & Monitoring

```bash
# Create log directory
sudo mkdir -p /var/log/esppd
sudo chown www-data:www-data /var/log/esppd

# Rotate logs
sudo nano /etc/logrotate.d/esppd
```

**Content:**
```
/var/log/esppd/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## ✅ VERIFICATION CHECKLIST

Setelah deployment, verify:

### 1. Check Application Running

```bash
# Test HTTP request
curl http://localhost:8000

# Test with wget
wget -q -O - http://localhost:8000 | head -20
```

### 2. Check Database Connection

```bash
# Login ke app
php artisan tinker

# Test:
>>> DB::connection()->getPdo()
>>> User::count()
>>> Employee::count()
```

### 3. Check Microservice

```bash
# Test health endpoint
curl http://localhost:8001/health

# Should return: {"status": "ok"}
```

### 4. Check Web Server

```bash
# Test Nginx
sudo nginx -t

# Check status
sudo systemctl status nginx

# Check port 443
sudo ss -tlnp | grep 443
```

### 5. Test Login

```
1. Open browser: https://esppd.example.com
2. Login dengan NIP + Password
3. Verify dashboard shows
4. Click /profile
5. Verify biodata displays
```

### 6. Test Document Generation

```
1. Go to /spd/create
2. Fill form
3. Click "Generate Document"
4. Verify DOCX file downloads
```

### 7. Check Logs

```bash
# Application logs
tail -f /var/www/esppd/storage/logs/laravel.log

# Web server logs
tail -f /var/log/nginx/esppd-access.log
tail -f /var/log/nginx/esppd-error.log

# Microservice logs
sudo journalctl -u esppd-microservice -f
```

---

## 🔄 CONTINUOUS UPDATES (CI/CD)

### Update Code dari GitHub

```bash
cd /var/www/esppd

# Pull latest code
git pull origin main

# Install new dependencies
composer install --no-dev --optimize-autoloader

# Run migrations jika ada
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Re-optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart web server
sudo systemctl reload nginx

# Restart microservice
sudo systemctl restart esppd-microservice
```

### Automated Updates dengan Script

**Buat file:** `/var/www/esppd/deploy.sh`

```bash
#!/bin/bash

echo "Starting deployment..."

# Pull from GitHub
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Migrate database
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build assets
npm install
npm run build

# Restart services
sudo systemctl reload nginx
sudo systemctl restart esppd-microservice

echo "Deployment completed!"
```

**Jalankan:**
```bash
chmod +x /var/www/esppd/deploy.sh

# Manual run
/var/www/esppd/deploy.sh

# Automated with cron (setiap jam 2 pagi)
0 2 * * * /var/www/esppd/deploy.sh >> /var/log/esppd-deploy.log 2>&1
```

---

## 🚨 TROUBLESHOOTING

### Application Error 500

```bash
# Check logs
tail -f /var/www/esppd/storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan view:clear

# Check permissions
ls -la /var/www/esppd/storage/

# Fix if needed
sudo chown -R www-data:www-data /var/www/esppd/storage
```

### Database Connection Error

```bash
# Test connection
php artisan db:show

# Check credentials
cat .env | grep DB_

# Test PostgreSQL directly
psql -h localhost -U postgres -d eSPPD -c "SELECT version();"
```

### Microservice Not Available

```bash
# Check status
sudo systemctl status esppd-microservice

# Check logs
sudo journalctl -u esppd-microservice -n 50

# Restart
sudo systemctl restart esppd-microservice

# Test endpoint
curl http://localhost:8001/health
```

### Permission Denied

```bash
# Fix storage permissions
sudo chmod -R 775 /var/www/esppd/storage
sudo chmod -R 775 /var/www/esppd/bootstrap/cache

# Fix owner
sudo chown -R www-data:www-data /var/www/esppd
```

---

## 📊 MONITORING & HEALTH CHECKS

### Setup Uptime Monitoring

```bash
# Add to crontab (check every 5 minutes)
*/5 * * * * curl -s http://localhost:8000/health || mail -s "eSPPD Down" admin@example.com

# Or use monitoring tool like:
# - Datadog
# - New Relic
# - Elastic Stack (ELK)
```

### Check Service Status

```bash
#!/bin/bash
# health-check.sh

echo "=== eSPPD Health Check ==="
echo ""

echo "1. Nginx Status:"
sudo systemctl is-active nginx && echo "✅ Nginx running" || echo "❌ Nginx stopped"

echo ""
echo "2. Microservice Status:"
sudo systemctl is-active esppd-microservice && echo "✅ Microservice running" || echo "❌ Microservice stopped"

echo ""
echo "3. Database Connection:"
cd /var/www/esppd && php artisan db:show > /dev/null 2>&1 && echo "✅ Database connected" || echo "❌ Database error"

echo ""
echo "4. Web Access:"
curl -s http://localhost:8000 > /dev/null && echo "✅ App accessible" || echo "❌ App not responding"

echo ""
echo "5. Microservice Health:"
curl -s http://localhost:8001/health | grep -q "ok" && echo "✅ Microservice healthy" || echo "❌ Microservice unhealthy"
```

---

## 🎯 SUMMARY

```
Production Deployment Flow:

GitHub → Pull code to server
       → Install dependencies
       → Setup database
       → Configure web server
       → Start services
       → Test application
       → Go LIVE ✅
```

**Status:** Siap untuk production deployment!

Setelah semua steps selesai:
- ✅ Aplikasi berjalan di domain production
- ✅ Database terhubung
- ✅ Microservice aktif
- ✅ User bisa login & pakai aplikasi
- ✅ Profile dengan 13 biodata fields berfungsi
- ✅ Document generation berjalan

---

**Last Updated:** January 31, 2026  
**Server:** Production-ready  
**Status:** Ready for deployment
