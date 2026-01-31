# 🚀 DEPLOY KE PRODUCTION - STEP BY STEP

## Server Information
- **IP**: 192.168.1.27  
- **Domain**: esppd.infiatin.cloud
- **User**: tholib_server  
- **Password**: 065820Aaaa
- **App Dir**: /var/www/esppd

---

## METODE 1: SSH Direct Command (Paling Cepat)

Copy command ini dan jalankan di PowerShell/Terminal:

```bash
ssh tholib_server@192.168.1.27 "cd /var/www/esppd && git pull origin main && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan optimize"
```

**Password**: `065820Aaaa`

---

## METODE 2: SSH Interactive (Paling Aman)

```bash
# 1. Buka SSH connection
ssh tholib_server@192.168.1.27

# 2. Masukkan password: 065820Aaaa

# 3. Copy-paste commands berikut satu per satu:
cd /var/www/esppd
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan cache:clear

# 4. Exit SSH
exit
```

---

## METODE 3: Bash Script (Automated)

```bash
# SCP upload script ke server
scp quick_deploy.sh tholib_server@192.168.1.27:/tmp/

# SSH execute
ssh tholib_server@192.168.1.27 "bash /tmp/quick_deploy.sh"

# Password: 065820Aaaa
```

---

## METODE 4: PowerShell Script (Windows)

```powershell
# Run dari PowerShell
.\deploy_to_production.ps1

# Ikuti prompt untuk password
```

---

## Verify Deployment Success

### Test via Browser
- https://esppd.infiatin.cloud/admin/user-management
- https://esppd.infiatin.cloud/dashboard/approval-status
- dll (lihat PRODUCTION_DEPLOYMENT.md untuk daftar lengkap)

### Test via SSH
```bash
ssh tholib_server@192.168.1.27
cd /var/www/esppd

# Check git status
git log --oneline -5

# Check application
php artisan tinker
DB::connection()->getPdo() # Jika return object, DB OK
exit

# Check Laravel health
php artisan about
```

---

## Deployed Features (8 Pages)

### Admin Pages
✅ /admin/user-management
✅ /admin/role-management  
✅ /admin/organization-management
✅ /admin/delegation-management
✅ /admin/audit-logs
✅ /admin/activity-dashboard

### User Pages
✅ /dashboard/approval-status
✅ /dashboard/my-delegations

---

## Troubleshooting

**SSH tidak konek?**
```bash
# Test koneksi
ping 192.168.1.27
ssh -vv tholib_server@192.168.1.27  # Lihat detail error
```

**Git pull gagal?**
```bash
# Check git status
cd /var/www/esppd
git status
git log -1

# Jika ada conflict
git reset --hard origin/main
```

**Database error?**
```bash
# Check PostgreSQL
sudo systemctl status postgresql

# Test koneksi
psql -h localhost -U postgres -d esppd
# Password: Esppd@123456
```

**Permission denied?**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/esppd
sudo chmod -R 755 /var/www/esppd
sudo chmod -R 775 /var/www/esppd/storage
```

---

## Rollback (if needed)

```bash
ssh tholib_server@192.168.1.27
cd /var/www/esppd

# View history
git log --oneline -10

# Rollback ke commit sebelumnya
git reset --hard a986488  # ApprovalStatusPage commit
git push -f origin main

# Re-deploy
./quick_deploy.sh
```

---

**Last Updated**: February 1, 2026  
**Status**: Ready to Deploy ✅
