# 🚀 DEPLOYMENT GUIDE - e-SPPD ke Production

**Server Details:**
- Host: `192.168.1.27`
- User: `tholib_server`
- Domain: `esppd.infiatin.cloud`
- OS: Linux (Ubuntu 20.04+)

---

## 📋 Pre-Deployment Checklist

- [ ] Kode sudah di-push ke GitHub
- [ ] Database backup siap
- [ ] Server sudah ter-setup (PHP 8.2+, Postgres, Redis, Nginx)
- [ ] SSL certificate siap (Let's Encrypt)
- [ ] Environment variables siap (.env production)

---

## 🔧 Deployment Steps

### 1. **SSH ke Server**
```bash
ssh tholib_server@192.168.1.27
```

### 2. **Clone Repository**
```bash
cd /var/www
git clone https://github.com/putrihati-cmd/eSPPD.git esppd
cd esppd
```

### 3. **Setup Environment**
```bash
# Copy environment file
cp .env.example .env

# Edit konfigurasi
nano .env
```

**Konfigurasi .env penting:**
```bash
APP_NAME="e-SPPD"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://esppd.infiatin.cloud

# Database PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=esppd
DB_USERNAME=esppd_user
DB_PASSWORD=your_secure_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password

# Session
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=redis
```

### 4. **Install Dependencies**
```bash
composer install --optimize-autoloader --no-dev

npm install
npm run build
```

### 5. **Database Setup**
```bash
# Generate APP_KEY
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed data (optional)
php artisan db:seed
```

### 6. **File Permissions**
```bash
sudo chown -R www-data:www-data /var/www/esppd
sudo chmod -R 755 /var/www/esppd
sudo chmod -R 775 /var/www/esppd/storage
sudo chmod -R 775 /var/www/esppd/bootstrap/cache
```

### 7. **Optimization**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 8. **Setup Supervisor untuk Queue**
```bash
sudo nano /etc/supervisor/conf.d/esppd-queue.conf
```

Tambahkan:
```ini
[program:esppd-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/esppd/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
stopwaitsecs=60
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/esppd/storage/logs/queue.log
```

Jalankan:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### 9. **Setup Cron Job**
```bash
sudo crontab -e
```

Tambahkan:
```
* * * * * cd /var/www/esppd && php artisan schedule:run >> /dev/null 2>&1
```

### 10. **Nginx Configuration**
```bash
sudo nano /etc/nginx/sites-available/esppd
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name esppd.infiatin.cloud;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name esppd.infiatin.cloud;

    # SSL Certificate
    ssl_certificate /etc/letsencrypt/live/esppd.infiatin.cloud/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/esppd.infiatin.cloud/privkey.pem;

    root /var/www/esppd/public;
    index index.php;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;
    gzip_min_length 1000;
}
```

Enable:
```bash
sudo ln -s /etc/nginx/sites-available/esppd /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 11. **SSL Certificate dengan Let's Encrypt**
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d esppd.infiatin.cloud
```

### 12. **Verify Deployment**
```bash
# Test health check
curl https://esppd.infiatin.cloud/health

# Check logs
tail -f storage/logs/laravel.log

# Check queue status
php artisan queue:failed

# Check cache
php artisan cache:status
```

---

## 📊 Post-Deployment Tasks

- [ ] Setup monitoring (New Relic, DataDog, atau similar)
- [ ] Setup backup otomatis (database + files)
- [ ] Setup log rotation
- [ ] Test email notifications
- [ ] Test file uploads/downloads
- [ ] Load testing
- [ ] Security audit

---

## 🔄 Update/Rollback Procedure

### Update (Pull latest code)
```bash
cd /var/www/esppd
git pull origin main
composer install --no-dev
npm run build
php artisan migrate --force
php artisan cache:clear
sudo systemctl restart php8.2-fpm
```

### Rollback (Revert ke commit sebelumnya)
```bash
git revert HEAD
git push origin main
# Jalankan update steps di atas
```

---

## 🆘 Troubleshooting

### Permission Denied
```bash
sudo chown -R www-data:www-data /var/www/esppd/storage
```

### 502 Bad Gateway
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Queue tidak berjalan
```bash
sudo supervisorctl restart all
tail -f storage/logs/queue.log
```

### Database connection error
```bash
php artisan db:show
# Check .env credentials
```

---

## 📞 Support

- Error log: `/var/www/esppd/storage/logs/laravel.log`
- Queue log: `/var/www/esppd/storage/logs/queue.log`
- Nginx log: `/var/log/nginx/error.log`

---

**Status: ✅ Ready for Deployment**
