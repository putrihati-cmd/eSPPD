# Production Deployment Script
# Deploy eSPPD to 192.168.1.27

param(
    [string]$Host = "192.168.1.27",
    [string]$User = "tholib_server",
    [string]$Password = "065820Aaaa",
    [string]$AppDir = "/var/www/esppd"
)

# Use plink (PuTTY command-line tool) if available, otherwise raw SSH
$plinkPath = "C:\Program Files\PuTTY\plink.exe"

if (Test-Path $plinkPath) {
    Write-Host "🚀 Deploying via PuTTY plink..." -ForegroundColor Green

    # Deployment commands
    $commands = @(
        "cd $AppDir",
        "git pull origin main",
        "php artisan migrate --force",
        "php artisan config:cache",
        "php artisan route:cache",
        "php artisan optimize",
        "exit"
    ) -join "`n"

    # Execute via plink with password
    $commands | & $plinkPath -ssh -l $User -pw $Password $Host 2>&1

} else {
    Write-Host "🔐 PuTTY plink not found. Using native SSH (key-based auth required)..." -ForegroundColor Yellow
    Write-Host "❌ Please set up SSH keys for passwordless authentication" -ForegroundColor Red
    Write-Host "   Or install PuTTY from: https://www.putty.org/" -ForegroundColor Yellow
    exit 1
}

Write-Host "`n✅ Deployment complete!" -ForegroundColor Green
Write-Host "📋 Verify at: https://esppd.infiatin.cloud" -ForegroundColor Cyan
