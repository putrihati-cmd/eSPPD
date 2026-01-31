@echo off
REM eSPPD Production Deployment Script
REM Target: esppd.infiatin.cloud (192.168.1.27)
REM SSH: tholib_server@192.168.1.27
REM Password: 065820Aaaa

setlocal enabledelayedexpansion

set SERVER_IP=192.168.1.27
set SERVER_USER=tholib_server
set DOMAIN=esppd.infiatin.cloud
set APP_PATH=/var/www/esppd

echo.
echo ============================================================
echo eSPPD Production Deployment - esppd.infiatin.cloud
echo ============================================================
echo.
echo Server:     %SERVER_IP%
echo User:       %SERVER_USER%
echo Domain:     %DOMAIN%
echo App Path:   %APP_PATH%
echo.

REM Step 1: Test SSH connection
echo [1/6] Testing SSH connection...
ssh -o StrictHostKeyChecking=no %SERVER_USER%@%SERVER_IP% "echo SSH Connection OK" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ERROR: Cannot connect to server
    echo.
    echo Make sure:
    echo - SSH key is configured, OR
    echo - Use ssh-keygen to create key pair
    pause
    exit /b 1
)
echo OK: SSH connection successful
echo.

REM Step 2: Sync code to production
echo [2/6] Syncing code to production...
ssh %SERVER_USER%@%SERVER_IP% "mkdir -p %APP_PATH%"
REM Using scp for Windows - rsync not available by default
for /d %%D in (app bootstrap config database public resources routes storage tests) do (
    echo   Syncing %%D...
    scp -r %%D %SERVER_USER%@%SERVER_IP%:%APP_PATH%/ >nul 2>&1
)
echo Syncing files...
scp composer.json composer.lock .env* %SERVER_USER%@%SERVER_IP%:%APP_PATH%/ >nul 2>&1
echo OK: Code synced
echo.

REM Step 3: Install dependencies
echo [3/6] Installing composer dependencies...
ssh %SERVER_USER%@%SERVER_IP% "cd %APP_PATH% && composer install --no-dev --optimize-autoloader" >nul 2>&1
echo OK: Dependencies installed
echo.

REM Step 4: Deploy nginx config
echo [4/6] Deploying nginx configuration...
ssh %SERVER_USER%@%SERVER_IP% "sudo tee /etc/nginx/sites-available/esppd > /dev/null" < deployment\nginx_esppd.conf
ssh %SERVER_USER%@%SERVER_IP% "sudo ln -sf /etc/nginx/sites-available/esppd /etc/nginx/sites-enabled/esppd"
ssh %SERVER_USER%@%SERVER_IP% "sudo nginx -t" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ERROR: Nginx configuration test failed
    pause
    exit /b 1
)
echo OK: Nginx configured
echo.

REM Step 5: Run migrations and cache
echo [5/6] Running migrations and clearing cache...
ssh %SERVER_USER%@%SERVER_IP% "cd %APP_PATH% && php artisan migrate --force"
ssh %SERVER_USER%@%SERVER_IP% "cd %APP_PATH% && php artisan config:cache && php artisan route:cache && php artisan view:cache"
echo OK: Migrations and cache completed
echo.

REM Step 6: Reload services
echo [6/6] Reloading services...
ssh %SERVER_USER%@%SERVER_IP% "sudo systemctl reload nginx"
ssh %SERVER_USER%@%SERVER_IP% "sudo systemctl restart php8.5-fpm"
echo OK: Services reloaded
echo.

echo ============================================================
echo SUCCESS: Deployment Complete!
echo ============================================================
echo.
echo Access your app:
echo   https://esppd.infiatin.cloud
echo.
echo Next:
echo   1. Wait for DNS propagation (if first time)
echo   2. Test login with test accounts
echo   3. Check nginx logs: tail -f /var/log/nginx/esppd_error.log
echo.
pause
