# SSH Configuration & Deployment Instructions
# File: DEPLOYMENT_INSTRUCTIONS.md

## 🔐 SSH Setup (Jika Password-Based Auth)

Jika server menggunakan password authentication, gunakan salah satu cara berikut:

### Option 1: Manual SSH Login (Rekomendasi untuk Pertama Kali)

```bash
# 1. Connect via SSH
ssh tholibserver@192.168.1.27

# Masukkan password: 065820Aaaa

# 2. Verify di server
pwd
whoami

# 3. Run deployment script (copy-paste dari step berikutnya)
```

### Option 2: Copy Script & Execute

```bash
# Dari local machine (Windows/Mac/Linux):
scp deploy-production-auto.sh tholibserver@192.168.1.27:/tmp/

# Kemudian SSH login:
ssh tholibserver@192.168.1.27

# Di server, jalankan:
bash /tmp/deploy-production-auto.sh
```

### Option 3: One-Liner dengan Heredoc (Linux/Mac)

```bash
ssh tholibserver@192.168.1.27 << 'ENDSSH'
cd /tmp
git clone https://github.com/putrihati-cmd/eSPPD.git esppd-deploy
cd esppd-deploy
bash deploy-production-auto.sh
ENDSSH
```

---

## 🚀 Manual Deployment Steps (Jika Automation Tidak Bisa)

Jika script tidak bisa dijalankan, lakukan manual ini di server:

```bash
# 1. Login ke Server
ssh tholibserver@192.168.1.27
# Password: 065820Aaaa

# 2. Clone Repository
cd /var/www
git clone https://github.com/putrihati-cmd/eSPPD.git esppd
cd esppd

# 3. Setup .env (Copy-paste ke file .env)
# Gunakan database credentials:
# DB_HOST=localhost
# DB_DATABASE=esppd_production
# DB_USERNAME=esppd_user
# DB_PASSWORD=Esppd@123456

cat > .env << 'EOF'
APP_NAME=eSPPD
APP_ENV=production
APP_DEBUG=false
APP_URL=https://esppd.local

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=esppd_production
DB_USERNAME=esppd_user
DB_PASSWORD=Esppd@123456

CACHE_DRIVER=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database
EOF

# 4. Install Dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# 5. Generate Key
php artisan key:generate

# 6. Database
php artisan migrate --force

# 7. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Assets
npm run build

# 9. Permissions
sudo chown -R www-data:www-data /var/www/esppd
chmod -R 775 /var/www/esppd/storage
chmod -R 775 /var/www/esppd/bootstrap/cache

# 10. Verify
php artisan migrate:status
php artisan db:show
```

---

## ✅ Verification Checklist

Setelah deployment, verify:

```bash
# 1. Check Laravel
php artisan tinker
>>> echo 'OK';
>>> exit;

# 2. Check Database
psql -h localhost -U esppd_user -d esppd_production -c "SELECT 1"
# Password: Esppd@123456

# 3. Check Migrations
php artisan migrate:status

# 4. Check Logs
tail -f storage/logs/laravel.log

# 5. Check Permissions
ls -la /var/www/esppd/storage/
ls -la /var/www/esppd/bootstrap/cache/
```

---

## 🔗 Network Requirements

Pastikan server memiliki:

- [x] Internet connection (untuk git clone, composer, npm)
- [x] PHP 8.2+ 
- [x] PostgreSQL 13+ running
- [x] Node.js 18+ (untuk npm)
- [x] Port 8000 (Laravel)
- [x] Port 8001 (Python Microservice)
- [x] Port 5432 (PostgreSQL)

---

## 🚨 Troubleshooting

### Database Connection Error
```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Check if user exists
psql -h localhost -U postgres -c "\du"

# Create user if not exists
psql -h localhost -U postgres << 'EOF'
CREATE USER esppd_user WITH PASSWORD 'Esppd@123456';
CREATE DATABASE esppd_production OWNER esppd_user;
GRANT ALL PRIVILEGES ON DATABASE esppd_production TO esppd_user;
EOF
```

### Composer Error
```bash
# Clear composer cache
composer clear-cache
composer install --no-dev
```

### Permission Error
```bash
# Reset permissions
sudo chown -R www-data:www-data /var/www/esppd
chmod -R 755 /var/www/esppd
chmod -R 775 /var/www/esppd/storage
chmod -R 775 /var/www/esppd/bootstrap/cache
```

### Server Info
```bash
# Check PHP version
php -v

# Check Node version
node -v
npm -v

# Check PostgreSQL
psql --version
```

---

## 📊 After Deployment

### Configure Nginx Virtual Host

```bash
sudo nano /etc/nginx/sites-available/esppd
```

Copy dari repo: `esppd_nginx.conf` atau `esppd_nginx_prod.conf`

### Setup Python Microservice

```bash
cd /var/www/esppd/document-service

# Install Python dependencies
pip install -r requirements.txt

# Start microservice
python -m uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

### Setup Systemd Service (Optional)

```bash
sudo nano /etc/systemd/system/esppd-microservice.service
```

### Enable Supervisor Queue Worker (Optional)

```bash
sudo nano /etc/supervisor/conf.d/esppd-worker.conf
```

---

## 🔐 Security Checklist

- [ ] Change APP_KEY (php artisan key:generate)
- [ ] Set APP_DEBUG=false
- [ ] Change database password
- [ ] Enable HTTPS/SSL
- [ ] Setup firewall rules
- [ ] Configure session cookies secure
- [ ] Regular backups: `pg_dump -Fc eSPPD > backup.sql`

---

## 📞 Support

Jika ada masalah:
1. Check logs: `tail -f storage/logs/laravel.log`
2. Check database: `psql -U esppd_user -d esppd_production`
3. Check services: `sudo systemctl status nginx`
4. Review QUICK_REFERENCE.md for common issues

---

**Server:** 192.168.1.27  
**User:** tholibserver  
**Database:** esppd_production  
**Status:** Ready for Deployment
