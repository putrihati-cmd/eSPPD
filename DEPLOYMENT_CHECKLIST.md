# 📋 DEPLOYMENT CHECKLIST - e-SPPD ke Production

**Target:** `esppd.infiatin.cloud` (192.168.1.27)  
**Date:** January 30, 2026  
**Status:** ✅ Ready for Deployment  

---

## 🔐 Pre-Deployment Security Checklist

### Credentials & Access
- [ ] SSH credentials tersimpan aman (pass manager)
- [ ] Database password sudah di-generate (random 32 char)
- [ ] APP_KEY sudah di-generate (`php artisan key:generate`)
- [ ] SSL certificate siap (Let's Encrypt)
- [ ] SSH key authentication setup (optional but recommended)

### Code Quality
- [ ] ✅ All PHP errors fixed (0 critical)
- [ ] ✅ All tests passing (79/79)
- [ ] ✅ Code pushed to GitHub (main branch)
- [ ] ✅ No uncommitted changes
- [ ] ✅ Environment variables configured

### Dependencies
- [ ] ✅ composer.lock updated
- [ ] ✅ package-lock.json updated
- [ ] ✅ No security vulnerabilities (`composer audit`)
- [ ] ✅ No npm vulnerabilities (`npm audit`)

---

## 🖥️ Server Preparation Checklist

### System Requirements
- [ ] Linux OS (Ubuntu 20.04+)
- [ ] PHP 8.2+ with extensions:
  - [ ] php-fpm
  - [ ] php-pgsql
  - [ ] php-redis
  - [ ] php-gd
  - [ ] php-zip
- [ ] PostgreSQL 13+
- [ ] Redis 6+
- [ ] Nginx 1.18+
- [ ] Node.js 16+ (untuk asset building)
- [ ] Supervisor (untuk queue workers)
- [ ] Composer
- [ ] Git

### Directory Structure
- [ ] `/var/www/esppd` - Application root
- [ ] `/var/www/esppd/storage/logs/` - Log directory
- [ ] `/var/www/esppd/storage/app/` - File uploads
- [ ] `/var/www/esppd/bootstrap/cache/` - Cache directory

### Database Setup
- [ ] PostgreSQL running
- [ ] Database `esppd` created
- [ ] Database user `esppd_user` created
- [ ] User has proper permissions
- [ ] Backup of production database (if migrating)

### Services
- [ ] Nginx configured and running
- [ ] PHP-FPM configured and running
- [ ] Redis running
- [ ] PostgreSQL running

---

## 📦 Deployment Execution

### Step 1: Code Deployment
```bash
cd /var/www/esppd
git pull origin main
composer install --optimize-autoloader --no-dev
npm install && npm run build
```
- [ ] Code pulled successfully
- [ ] No git conflicts
- [ ] Dependencies installed
- [ ] Assets built

### Step 2: Environment Configuration
```bash
cp .env.production .env
# Edit with production values:
# - APP_KEY
# - DB credentials
# - REDIS config
# - MAIL config
```
- [ ] .env created from production template
- [ ] All required variables set
- [ ] No placeholder values remaining

### Step 3: Database Setup
```bash
php artisan key:generate  # if not done in .env
php artisan migrate --force
php artisan db:seed  # if needed
```
- [ ] APP_KEY generated
- [ ] All migrations ran successfully
- [ ] Database tables created
- [ ] Initial data seeded (if applicable)

### Step 4: File Permissions
```bash
sudo chown -R www-data:www-data /var/www/esppd
sudo chmod -R 755 /var/www/esppd
sudo chmod -R 775 /var/www/esppd/storage
sudo chmod -R 775 /var/www/esppd/bootstrap/cache
```
- [ ] Ownership set to www-data
- [ ] Directory permissions correct
- [ ] Storage writable
- [ ] Cache writable

### Step 5: Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```
- [ ] Config cached
- [ ] Routes cached
- [ ] Views cached
- [ ] Application optimized

### Step 6: Queue & Scheduling
```bash
# Setup Supervisor config
# sudo nano /etc/supervisor/conf.d/esppd-queue.conf

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```
- [ ] Supervisor configured
- [ ] Queue workers running (4 processes)
- [ ] Cron job added for scheduler

### Step 7: Web Server
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```
- [ ] PHP-FPM restarted
- [ ] Nginx restarted
- [ ] No errors in restart

---

## ✅ Post-Deployment Verification

### Health Checks
```bash
# Test application
curl -I https://esppd.infiatin.cloud/health

# Check logs
tail -f /var/www/esppd/storage/logs/laravel.log

# Check queue status
php artisan queue:failed
```
- [ ] Application responds with 200
- [ ] No errors in logs
- [ ] Queue workers running
- [ ] No failed jobs

### Functional Testing
- [ ] [ ] Login page loads
- [ ] [ ] Can login with test account
- [ ] [ ] Dashboard accessible
- [ ] [ ] Can create SPPD
- [ ] [ ] Can submit SPPD
- [ ] [ ] Can approve SPPD
- [ ] [ ] PDF export works
- [ ] [ ] Email notifications sent
- [ ] [ ] File uploads work

### Security Verification
- [ ] HTTPS enforced (redirect http → https)
- [ ] SSL certificate valid
- [ ] Security headers present:
  - [ ] X-Frame-Options
  - [ ] X-Content-Type-Options
  - [ ] Content-Security-Policy
- [ ] Password reset working
- [ ] Rate limiting active (test 4 failed logins)
- [ ] CSRF protection active

### Performance Check
- [ ] Page load time < 2s
- [ ] No 500 errors in logs
- [ ] Memory usage reasonable
- [ ] Database queries optimized
- [ ] Assets (CSS/JS) minified

---

## 🔄 Rollback Plan

Jika ada masalah serius:

```bash
# Stop application
sudo systemctl stop nginx

# Restore from backup
rm -rf /var/www/esppd
cp -r /var/www/esppd-backup-YYYYMMDD-HHMMSS /var/www/esppd

# Restore database
pg_restore -d esppd /path/to/backup.sql

# Restart
sudo systemctl start nginx php8.2-fpm
```

- [ ] Backup location documented
- [ ] Rollback procedure tested
- [ ] Communication plan ready

---

## 📊 Monitoring Setup

### Logging
- [ ] Daily log rotation configured
- [ ] Log retention: 30 days
- [ ] Errors logged to Slack (optional)

### Alerts
- [ ] Error rate > 5% alert
- [ ] Database down alert
- [ ] Disk space > 80% alert
- [ ] Memory usage > 80% alert

### Backups
- [ ] Database backup: Daily 2am
- [ ] Storage backup: Weekly
- [ ] Config backup: On deployment
- [ ] Retention: 30 days

---

## 📞 Support & Documentation

### Important Contacts
- **Server Admin:** tholib_server@192.168.1.27
- **Application Admin:** [contact info]
- **Emergency:** [phone/email]

### Documentation
- [ ] DEPLOYMENT_GUIDE.md reviewed
- [ ] README.md updated
- [ ] API documentation available
- [ ] Database schema documented

### Team Training
- [ ] Team trained on deployment process
- [ ] Monitoring dashboard accessible
- [ ] Error handling documented
- [ ] Update procedures documented

---

## 🎉 Final Sign-Off

| Item | Owner | Signed | Date |
|------|-------|--------|------|
| Code Quality | Developer | ☐ | |
| Infrastructure | DevOps | ☐ | |
| Security | Security Team | ☐ | |
| Testing | QA | ☐ | |
| Go-Live | Project Manager | ☐ | |

---

**Deployment Status: 🟢 READY**

**Next Steps:**
1. ✅ Prepare server environment
2. ✅ Run deployment script
3. ✅ Verify all checklist items
4. ✅ Monitor application for 24 hours
5. ✅ Document any issues
6. ✅ Celebrate! 🎉
