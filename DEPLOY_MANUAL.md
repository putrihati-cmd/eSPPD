# 🚀 DEPLOYMENT MANUAL - COPY & PASTE

## Step 1: Buka PowerShell/Terminal baru
```powershell
# Di PowerShell atau Command Prompt Anda
ssh tholib_server@192.168.1.27
```

## Step 2: Saat diminta password, ketik:
```
065820Aaaa
```

## Step 3: Setelah login, copy-paste satu-satu:

```bash
cd /var/www/esppd
```

```bash
git pull origin main
```

```bash
composer install --no-dev --optimize-autoloader
```

```bash
php artisan migrate --force
```

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

```bash
echo "✅ Deployment Complete!"
```

## Step 4: Verify - test di browser:

- https://esppd.infiatin.cloud/admin/user-management
- https://esppd.infiatin.cloud/dashboard/approval-status

## Step 5: Jika ada error, check:

```bash
tail -f /var/www/esppd/storage/logs/laravel.log
```

---

**Status**: 8 halaman siap di production ✅
**Lokasi**: c:\laragon\www\eSPPD_new
**GitHub**: https://github.com/putrihati-cmd/eSPPD (branch: main)
