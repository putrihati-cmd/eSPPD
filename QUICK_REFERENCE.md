# QUICK REFERENCE - ESPPD PRODUCTION

## 🚀 QUICK START (Copy-Paste Ready)

### Clone dari GitHub
```bash
cd /var/www
git clone https://github.com/putrihati-cmd/eSPPD.git esppd
cd esppd
```

### Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install
php artisan key:generate
```

### Setup Database
```bash
# Create database
sudo -u postgres createdb eSPPD

# Run migrations
php artisan migrate --force

# Verify
php artisan migrate:status
```

### Configure Web Server (Nginx)
```bash
# Copy config
sudo cp esppd_nginx.conf /etc/nginx/sites-available/esppd

# Enable
sudo ln -s /etc/nginx/sites-available/esppd /etc/nginx/sites-enabled/

# Test & reload
sudo nginx -t
sudo systemctl reload nginx
```

### Start Application
```bash
# Terminal 1: PHP Laravel
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Python Microservice
cd document-service
python -m uvicorn main:app --host 0.0.0.0 --port 8001
```

### Verify
```bash
# Test app
curl http://localhost:8000

# Test microservice
curl http://localhost:8001/health
```

---

## 📋 MOST COMMON COMMANDS

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Rebuild Caches (Production)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset all
php artisan migrate:reset

# Seed data
php artisan db:seed

# Check status
php artisan migrate:status
```

### Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx access
tail -f /var/log/nginx/esppd-access.log

# Nginx error
tail -f /var/log/nginx/esppd-error.log

# Microservice
sudo journalctl -u esppd-microservice -f
```

### File Permissions
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/esppd

# Fix directory permissions
chmod -R 755 /var/www/esppd
chmod -R 775 /var/www/esppd/storage
chmod -R 775 /var/www/esppd/bootstrap/cache
```

### Services
```bash
# Nginx
sudo systemctl start/stop/restart/reload nginx
sudo systemctl status nginx

# Microservice
sudo systemctl start/stop/restart esppd-microservice
sudo systemctl status esppd-microservice

# Laravel (if using supervisor)
sudo supervisorctl restart all
sudo supervisorctl status
```

### Git Updates
```bash
# Pull latest code
git pull origin main

# Check status
git status

# See changes
git log --oneline -10
```

---

## 🔍 TROUBLESHOOTING QUICK FIXES

| Problem | Solution |
|---------|----------|
| **500 Error** | `php artisan cache:clear` + check logs |
| **DB Connection Error** | Check `.env` DB_* values + `psql -h localhost -U postgres -d eSPPD` |
| **Permission Denied** | `chmod -R 775 storage/ bootstrap/cache/` |
| **Microservice Error** | `sudo systemctl restart esppd-microservice` |
| **Nginx 502** | Check if Laravel is running on port 8000 |
| **Static Files 404** | Run `php artisan storage:link` |
| **Profile Page Error** | Check `User::with('employee')` relationship + check migrations |
| **Document Generation Fails** | Check Python service status + check port 8001 |

---

## 🔐 SECURITY CHECKLIST

- [ ] Change APP_DEBUG=false in .env
- [ ] Generate secure APP_KEY: `php artisan key:generate`
- [ ] Set strong DB password
- [ ] Enable HTTPS/SSL (Let's Encrypt)
- [ ] Set secure session cookie: `SESSION_SECURE_COOKIES=true`
- [ ] Setup firewall: Allow only 80/443/22
- [ ] Regular backups: `pg_dump -Fc eSPPD > backup.sql`
- [ ] Update dependencies: `composer update`
- [ ] Monitor logs regularly

---

## 📊 MONITORING ENDPOINTS

```bash
# App health
curl http://localhost:8000/health

# Microservice health
curl http://localhost:8001/health

# Database check
php artisan db:show

# Queue status
php artisan queue:failed

# Log tail
tail -f storage/logs/laravel.log
```

---

## 🚨 EMERGENCY PROCEDURES

### Application Down
```bash
# Step 1: Check logs
tail -f storage/logs/laravel.log

# Step 2: Restart services
php artisan cache:clear
sudo systemctl reload nginx
sudo systemctl restart esppd-microservice

# Step 3: If still down, rollback code
git revert HEAD
php artisan migrate:rollback
```

### Database Issue
```bash
# Backup data first
pg_dump -Fc eSPPD > backup.sql

# Check connection
psql -h localhost -U postgres -d eSPPD -c "SELECT 1"

# Restart PostgreSQL
sudo systemctl restart postgresql
```

### Microservice Crash
```bash
# Check status
sudo systemctl status esppd-microservice

# View errors
sudo journalctl -u esppd-microservice --no-pager -n 100

# Restart
sudo systemctl restart esppd-microservice

# Fallback: Use local document generation
# (PythonDocumentService has fallback logic)
```

---

## 📈 PERFORMANCE TIPS

1. **Cache Everything**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Optimize Database**
   ```bash
   # Check migrations have run
   php artisan migrate:status
   
   # Verify indexes exist
   \d employees  # In PostgreSQL
   ```

3. **Use CDN for Assets**
   - Put `public/build/` on CDN
   - Update `ASSET_URL` in .env

4. **Monitor File Storage**
   ```bash
   du -sh /var/www/esppd/storage/
   ```

5. **Cleanup Old Logs**
   ```bash
   find storage/logs -mtime +30 -delete
   ```

---

## 📞 SUPPORT CONTACTS

| Component | Issue | Action |
|-----------|-------|--------|
| **Laravel App** | Error 500 | Check `storage/logs/laravel.log` |
| **PostgreSQL** | Connection fail | Check DB service, credentials |
| **Nginx** | 502 Bad Gateway | Check port 8000, PHP-FPM |
| **Python Service** | Document fails | Check port 8001, logs |
| **File Upload** | Permission denied | Check `storage/` permissions |
| **SSL/HTTPS** | Certificate error | Renew: `certbot renew` |

---

## 🎯 DEPLOYMENT CHECKLIST

- [ ] Pull latest code from GitHub
- [ ] Install/update dependencies
- [ ] Run migrations
- [ ] Clear all caches
- [ ] Build frontend assets
- [ ] Set file permissions
- [ ] Restart web server
- [ ] Restart microservice
- [ ] Test application
- [ ] Check logs for errors
- [ ] Verify profile page loads
- [ ] Test document generation
- [ ] Monitor for 24 hours

---

## 📚 DOCUMENTATION

- **Full Guide:** `PRODUCTION_DEPLOYMENT_GUIDE.md`
- **Architecture:** `ARCHITECTURE_DIAGRAM.md`
- **Integration:** `SYSTEM_INTEGRATION_COMPLETE.md`
- **Deployment:** `DEPLOYMENT_PROFILE_ENHANCEMENT.md`

---

**Last Updated:** January 31, 2026  
**Repository:** https://github.com/putrihati-cmd/eSPPD  
**Status:** Production Ready
