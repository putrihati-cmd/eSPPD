@echo off
REM Windows Batch Deployment Script
REM Connect to production and deploy

setlocal enabledelayedexpansion

echo.
echo ════════════════════════════════════════════════════
echo 🚀 eSPPD Production Deployment
echo ════════════════════════════════════════════════════
echo.
echo 📍 Target: 192.168.1.27
echo 👤 User: tholib_server
echo 📂 App: /var/www/esppd
echo.

REM Check SSH availability
where ssh >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ OpenSSH not found. Please ensure OpenSSH is installed.
    pause
    exit /b 1
)

echo ⏳ Connecting and deploying... (will prompt for password)
echo.

REM Execute deployment via SSH with here-document style commands
ssh tholib_server@192.168.1.27 ^
  "cd /var/www/esppd && " ^
  "git pull origin main && " ^
  "composer install --no-dev --optimize-autoloader && " ^
  "php artisan migrate --force && " ^
  "php artisan config:cache && " ^
  "php artisan route:cache && " ^
  "php artisan view:cache && " ^
  "php artisan optimize && " ^
  "php artisan cache:clear && " ^
  "echo. && " ^
  "echo ════════════════════════════════════════════════════ && " ^
  "echo ✅ DEPLOYMENT SUCCESSFUL! && " ^
  "echo ════════════════════════════════════════════════════ && " ^
  "echo. && " ^
  "echo 📋 Application: https://esppd.infiatin.cloud && " ^
  "echo."

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Deployment completed successfully!
    echo.
    pause
) else (
    echo.
    echo ❌ Deployment failed. Check error message above.
    echo.
    pause
    exit /b 1
)

endlocal
