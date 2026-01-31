@echo off
REM Multi-PC Database Sync Setup Batch
REM Usage: setup-multi-pc-sync.bat

setlocal enabledelayedexpansion

echo.
echo ========================================
echo  Multi-PC Database Sync Setup
echo ========================================
echo.

REM Step 1: Check PostgreSQL
echo [1/5] Checking PostgreSQL tools...
where pg_dump >nul 2>&1
if %errorlevel% equ 0 (
    echo  [OK] pg_dump found
) else (
    echo  [FAIL] pg_dump NOT found
    echo  Add C:\laragon\bin\postgresql\bin to PATH
    echo.
)

REM Step 2: Check SSH
echo [2/5] Checking SSH connection...
ssh tholib_server@192.168.1.27 "echo OK" >nul 2>&1
if %errorlevel% equ 0 (
    echo  [OK] SSH connection works
) else (
    echo  [FAIL] SSH connection failed
    echo  Run: ssh-keygen -t rsa -b 4096 -f %%USERPROFILE%%\.ssh\id_rsa
    echo.
)

REM Step 3: Check .env
echo [3/5] Checking .env configuration...
findstr /M "PRODUCTION_HOST" .env >nul 2>&1
if %errorlevel% equ 0 (
    echo  [OK] .env configured
) else (
    echo  [FAIL] .env not configured properly
    echo.
)

REM Step 4: Check artisan command
echo [4/5] Checking database sync command...
php artisan db:sync-to-production --help >nul 2>&1
if %errorlevel% equ 0 (
    echo  [OK] Sync command available
) else (
    echo  [FAIL] Sync command not found
    echo  Run: git pull origin main
    echo.
)

REM Step 5: Show current config
echo [5/5] Current configuration:
echo.
for /f "tokens=2 delims==" %%i in ('findstr "PRODUCTION_HOST=" .env') do echo  PRODUCTION_HOST=%%i
for /f "tokens=2 delims==" %%i in ('findstr "AUTO_DB_SYNC=" .env') do echo  AUTO_DB_SYNC=%%i
echo.

echo ========================================
echo  Setup Complete!
echo ========================================
echo.
echo Next steps:
echo  1. Edit .env and set PRODUCTION_DB_PASSWORD
echo  2. Test: php artisan db:sync-to-production --dry-run
echo  3. Enable: Set AUTO_DB_SYNC=true in .env
echo  4. Use: .\artisan.ps1 migrate
echo.
pause
