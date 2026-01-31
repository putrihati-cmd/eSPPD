@echo off
REM Production Deployment Script for eSPPD
REM Deploy to 192.168.1.27 via SSH

setlocal enabledelayedexpansion

set HOST=192.168.1.27
set USER=tholib_server
set PASSWORD=065820Aaaa
set APP_DIR=/var/www/esppd
set DEPLOY_SCRIPT=deployment/deploy_production.sh

echo ════════════════════════════════════════════════════
echo Production Deployment - eSPPD
echo ════════════════════════════════════════════════════
echo.
echo 📍 Target: %HOST%
echo 👤 User: %USER%
echo 📂 Directory: %APP_DIR%
echo.

REM Check if OpenSSH is available
where ssh >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ OpenSSH not found. Please install Windows OpenSSH.
    exit /b 1
)

REM Execute deployment script via SSH
echo 🚀 Executing deployment...
echo.

ssh -o StrictHostKeyChecking=no %USER%@%HOST% "bash %APP_DIR%/%DEPLOY_SCRIPT%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ════════════════════════════════════════════════════
    echo ✅ Deployment completed successfully!
    echo ════════════════════════════════════════════════════
    echo 📋 Access application: https://esppd.infiatin.cloud
    pause
) else (
    echo.
    echo ❌ Deployment failed with error code: %ERRORLEVEL%
    pause
    exit /b %ERRORLEVEL%
)

endlocal
