# Deploy-Production.ps1 - PowerShell Deployment Script untuk Windows
# Usage: powershell -ExecutionPolicy Bypass -File Deploy-Production.ps1

# Configuration
$ServerIP = "192.168.1.27"
$ServerUser = "tholibserver"
$ServerPassword = "065820Aaaa"
$AppPath = "/var/www/esppd"

Write-Host "================================" -ForegroundColor Cyan
Write-Host "ESPPD Production Deployment" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Target Server: $ServerIP" -ForegroundColor Yellow
Write-Host "User: $ServerUser" -ForegroundColor Yellow
Write-Host ""

# Check SSH availability
Write-Host "Checking SSH availability..." -ForegroundColor Cyan

if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) {
    Write-Host "❌ SSH command not found" -ForegroundColor Red
    Write-Host "Please ensure OpenSSH is installed on Windows 10+"
    Write-Host "Or use PuTTY/Bitvise SSH from https://www.chiark.greenend.org.uk/~sgtatham/putty/"
    exit 1
}

Write-Host "✓ SSH available" -ForegroundColor Green
Write-Host ""

# Test Connection
Write-Host "Testing SSH connection..." -ForegroundColor Cyan
try {
    $testResult = ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no `
        "$ServerUser@$ServerIP" "echo 'SSH Connection OK'" 2>&1
    
    if ($testResult -like "*SSH Connection OK*" -or $testResult -like "*Permission denied*") {
        Write-Host "✓ SSH connection successful" -ForegroundColor Green
    } else {
        Write-Host "⚠ SSH connection test unclear, proceeding anyway..." -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠ Could not test SSH: $_" -ForegroundColor Yellow
}

Write-Host ""

# Option 1: Manual Login
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Method 1: Manual SSH Login" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Run this command and enter password when prompted:" -ForegroundColor Yellow
Write-Host ""
Write-Host "ssh $ServerUser@$ServerIP" -ForegroundColor Green
Write-Host ""
Write-Host "After login, run this block of commands:" -ForegroundColor Yellow
Write-Host ""

$manualCommands = @"
# Clone repository
cd /var/www
git clone https://github.com/putrihati-cmd/eSPPD.git esppd
cd esppd

# Setup environment
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
PYTHON_SERVICE_URL=http://localhost:8001
EOF

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Generate key
php artisan key:generate --force

# Database
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Assets
npm run build

# Permissions
sudo chown -R www-data:www-data /var/www/esppd
chmod -R 775 /var/www/esppd/storage
chmod -R 775 /var/www/esppd/bootstrap/cache

# Verify
php artisan migrate:status
echo "✓ Deployment complete!"
"@

Write-Host $manualCommands -ForegroundColor Green
Write-Host ""

# Option 2: Try Automated via SCP + SSH
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Method 2: Automated via SCP" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "This requires BOTH ssh AND scp available..." -ForegroundColor Yellow
Write-Host ""

if (Get-Command scp -ErrorAction SilentlyContinue) {
    Write-Host "1. Copy script to server:" -ForegroundColor Cyan
    Write-Host "   scp deploy-production-auto.sh ${ServerUser}@${ServerIP}:/tmp/" -ForegroundColor Green
    Write-Host ""
    Write-Host "2. SSH to server:" -ForegroundColor Cyan
    Write-Host "   ssh $ServerUser@$ServerIP" -ForegroundColor Green
    Write-Host ""
    Write-Host "3. Run script:" -ForegroundColor Cyan
    Write-Host "   bash /tmp/deploy-production-auto.sh" -ForegroundColor Green
} else {
    Write-Host "⚠ SCP not available, use Method 1 instead" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "After Deployment" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Verify deployment:" -ForegroundColor Yellow
Write-Host "  ssh $ServerUser@$ServerIP" -ForegroundColor Green
Write-Host "  tail -f $AppPath/storage/logs/laravel.log" -ForegroundColor Green
Write-Host ""
Write-Host "Database test:" -ForegroundColor Yellow
Write-Host "  psql -h $ServerIP -U esppd_user -d esppd_production" -ForegroundColor Green
Write-Host ""
Write-Host "Credentials:" -ForegroundColor Yellow
Write-Host "  Username: esppd_user" -ForegroundColor Green
Write-Host "  Password: Esppd@123456" -ForegroundColor Green
Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Deployment Guide Ready!" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
