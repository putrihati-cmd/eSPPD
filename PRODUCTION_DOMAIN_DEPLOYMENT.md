# 🚀 Production Deployment Guide - esppd.infiatin.cloud

## Quick Start

```bash
# Deploy to production with domain
bash deploy-production-domain.sh
```

---

## Current Status

| Component | Status | Details |
|-----------|--------|---------|
| **Server** | ✅ Running | 192.168.1.27 (tholib_server) |
| **App Path** | ✅ Deployed | /var/www/esppd |
| **Database** | ✅ Running | PostgreSQL esppd_production (474 users) |
| **Nginx** | ✅ Running | Port 8083 (HTTP) |
| **PHP-FPM** | ✅ Running | PHP 8.5 |
| **Redis** | ✅ Running | Cache backend ready |
| **Domain** | ⏳ Needs Setup | esppd.infiatin.cloud |

---

## Prerequisites

Before deploying, ensure:

### 1. Domain Registration ✅/❌
- [ ] Domain `esppd.infiatin.cloud` registered
- [ ] Domain registrar accessible
- [ ] Admin credentials available

### 2. DNS Configuration (Critical!)
Domain must point to production server IP:

```
esppd.infiatin.cloud  A  192.168.1.27
www.esppd.infiatin.cloud  CNAME  esppd.infiatin.cloud
```

**How to verify DNS:**
```bash
nslookup esppd.infiatin.cloud
# Should return: 192.168.1.27
```

### 3. SSL Certificate Options

#### Option A: Let's Encrypt (Recommended - FREE)
```bash
# On production server:
apt-get install certbot python3-certbot-nginx
certbot certonly --webroot -d esppd.infiatin.cloud -d www.esppd.infiatin.cloud
```

#### Option B: Self-Signed Certificate (Development)
```bash
# On production server:
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/nginx/esppd.key \
  -out /etc/nginx/esppd.crt
```

---

## Deployment Steps

### Step 1: Update DNS Records

Contact your domain registrar and set:
```
esppd.infiatin.cloud  A  192.168.1.27
```

**Wait 24-48 hours for DNS propagation** (or verify with nslookup)

### Step 2: Install SSL Certificate

```bash
# SSH to production server
ssh root@192.168.1.27

# For Let's Encrypt (recommended):
certbot certonly --webroot -d esppd.infiatin.cloud -d www.esppd.infiatin.cloud

# Certificate will be at:
# /etc/letsencrypt/live/esppd.infiatin.cloud/fullchain.pem
# /etc/letsencrypt/live/esppd.infiatin.cloud/privkey.pem
```

### Step 3: Deploy Application

From your local machine:

```bash
# Option A: Use automated script
bash deploy-production-domain.sh

# Option B: Manual deployment
rsync -avz --delete ./ root@192.168.1.27:/var/www/esppd/
ssh root@192.168.1.27 "cd /var/www/esppd && composer install && php artisan migrate"
```

### Step 4: Configure Nginx

The script automatically deploys correct config. If manual:

```bash
# SSH to server
ssh root@192.168.1.27

# Deploy config
cat > /etc/nginx/sites-enabled/esppd << 'EOF'
# HTTP to HTTPS redirect
server {
    listen 80;
    listen [::]:80;
    server_name esppd.infiatin.cloud www.esppd.infiatin.cloud;
    return 301 https://$server_name$request_uri;
}

# HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name esppd.infiatin.cloud www.esppd.infiatin.cloud;

    ssl_certificate /etc/letsencrypt/live/esppd.infiatin.cloud/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/esppd.infiatin.cloud/privkey.pem;

    root /var/www/esppd/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF

# Test and reload
nginx -t
systemctl reload nginx
```

### Step 5: Verify Deployment

```bash
# Test HTTPS
curl -k https://esppd.infiatin.cloud/login

# Should return HTML login page

# Check logs
tail -f /var/log/nginx/esppd_error.log
tail -f /var/log/nginx/esppd_access.log
```

---

## Testing Access

### 1. Browser Test
```
https://esppd.infiatin.cloud
```
Should show login page.

### 2. Login Test

Use test accounts (from RBAC_QUICK_REFERENCE.md):

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@esppd.test | password123 |
| Rektor | rektor@esppd.test | password123 |
| Warek | warek@esppd.test | password123 |
| Dekan | dekan@esppd.test | password123 |
| Wadek | wadek@esppd.test | password123 |
| Kaprodi | kaprodi@esppd.test | password123 |
| Dosen | dosen@esppd.test | password123 |

### 3. Dashboard Access
After login, you should see:
- Role-based dashboard
- SPD management
- Approval queue (if role >= 2)
- Admin panel (if admin)

---

## Troubleshooting

### Domain Not Resolving
```bash
# Check DNS
nslookup esppd.infiatin.cloud
dig esppd.infiatin.cloud

# If fails, update DNS at your registrar
```

### SSL Certificate Error
```bash
# Check certificate
openssl s_client -connect esppd.infiatin.cloud:443

# Renew Let's Encrypt
certbot renew --dry-run
certbot renew
```

### Nginx Configuration Error
```bash
# Test config
nginx -t

# View error log
tail /var/log/nginx/esppd_error.log
```

### PHP-FPM Socket Error
```bash
# Check PHP-FPM running
systemctl status php8.5-fpm

# Check socket exists
ls -la /var/run/php/php8.5-fpm.sock
```

### Database Connection Error
```bash
# SSH to server
ssh root@192.168.1.27

# Check PostgreSQL
psql -U postgres -d esppd_production -c "SELECT COUNT(*) FROM users;"

# Should return: 474
```

---

## Post-Deployment

### 1. Backup Database
```bash
ssh root@192.168.1.27 "pg_dump -U postgres esppd_production > /var/backups/esppd_$(date +%Y%m%d).sql"
```

### 2. Monitor Logs
```bash
ssh root@192.168.1.27 "tail -f /var/log/nginx/esppd_access.log"
```

### 3. SSL Auto-Renewal
```bash
# Let's Encrypt auto-renewal (usually pre-configured)
systemctl status certbot.timer
```

### 4. Performance Monitoring
```bash
ssh root@192.168.1.27 "top -b -n 1 | head -20"
```

---

## Environment Configuration

`.env` on production should have:
```
APP_NAME=eSPPD
APP_ENV=production
APP_DEBUG=false
APP_URL=https://esppd.infiatin.cloud

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=esppd_production
DB_USERNAME=postgres
DB_PASSWORD=<your_password>

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_DRIVER=smtp
MAIL_HOST=<your_smtp>
MAIL_PORT=587
MAIL_FROM_ADDRESS=noreply@esppd.infiatin.cloud
```

---

## Rollback

If something breaks:

```bash
# Keep backup of working version
rsync -avz --delete /var/www/esppd-backup/ root@192.168.1.27:/var/www/esppd/

# Reload nginx
ssh root@192.168.1.27 "systemctl reload nginx"
```

---

## Support

For issues:
1. Check logs: `/var/log/nginx/esppd_error.log`
2. Check DB: `psql -d esppd_production`
3. Check app: `php artisan tinker` on server
4. Review: RBAC_QUICK_REFERENCE.md for feature details

---

**Status: READY FOR DEPLOYMENT** ✅

Run: `bash deploy-production-domain.sh`
