# 🎯 DEPLOYMENT QUICK START CARD

**Server:** 192.168.1.27 | **User:** tholibserver | **Password:** 065820Aaaa

---

## ⚡ FASTEST WAY (Copy-Paste)

### SSH Login
```bash
ssh tholibserver@192.168.1.27
# password: 065820Aaaa
```

### Clone & Deploy
```bash
cd /var/www && git clone https://github.com/putrihati-cmd/eSPPD.git esppd && cd esppd
```

### Setup .env
```bash
cat > .env << 'EOF'
APP_NAME=eSPPD
APP_ENV=production
APP_DEBUG=false
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
```

### Install & Migrate
```bash
composer install --no-dev --optimize-autoloader && npm install
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
npm run build
sudo chown -R www-data:www-data /var/www/esppd
chmod -R 775 /var/www/esppd/storage /var/www/esppd/bootstrap/cache
```

### Verify ✓
```bash
php artisan migrate:status
php artisan db:show
php artisan tinker
>>> echo 'OK'; exit;
```

---

## 📊 Deployment Status

| Component | Status | Command |
|-----------|--------|---------|
| **Code** | ✅ Ready | `git clone ...` |
| **Database** | ✅ 28 migrations | `php artisan migrate --force` |
| **Features** | ✅ Profile+13 fields | Auto deployed |
| **Integration** | ✅ 15/16 tests pass | Verified |
| **Documentation** | ✅ Complete | GitHub/QUICK_REFERENCE.md |

---

## 🔗 Database

```
Host: localhost (on server)
Database: esppd_production
User: esppd_user
Password: Esppd@123456
```

**Test Connection:**
```bash
psql -h localhost -U esppd_user -d esppd_production -c "SELECT COUNT(*) FROM users;"
```

---

## 📚 Available Docs

1. **QUICK_REFERENCE.md** ← Start here! Copy-paste commands
2. **PRODUCTION_DEPLOYMENT_README.md** ← Overview + 3 deployment methods
3. **DEPLOYMENT_INSTRUCTIONS.md** ← SSH setup + troubleshooting
4. **PRODUCTION_DEPLOYMENT_GUIDE.md** ← Detailed 14-step guide
5. **ARCHITECTURE_DIAGRAM.md** ← System design

---

## 🚨 Common Issues

| Error | Fix |
|-------|-----|
| **DB Connection** | Check .env credentials, PostgreSQL running |
| **Permission Denied** | `chmod -R 775 storage/ bootstrap/cache/` |
| **500 Error** | `tail -f storage/logs/laravel.log` |
| **Composer Error** | `composer clear-cache && composer install` |

---

## ✅ Success Checklist

- [ ] SSH connected
- [ ] Git cloned
- [ ] .env configured
- [ ] Dependencies installed
- [ ] Migrations run
- [ ] Configuration cached
- [ ] Assets built
- [ ] Permissions set
- [ ] Migration status shows all ✓
- [ ] DB connection works

---

**Total Time:** 5-10 minutes  
**Repository:** https://github.com/putrihati-cmd/eSPPD  
**Status:** 🟢 Production Ready
