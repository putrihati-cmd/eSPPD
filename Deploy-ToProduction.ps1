# PowerShell SSH Deployment Helper
# This script facilitates deployment to production server

$Server = "192.168.1.27"
$User = "tholib_server"
$AppDir = "/var/www/esppd"
$DeployScript = "deployment/final_deploy.sh"

Write-Host ""
Write-Host "════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "🚀 eSPPD Production Deployment" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "📍 Target: $Server" -ForegroundColor Yellow
Write-Host "👤 User: $User" -ForegroundColor Yellow
Write-Host "📂 App Dir: $AppDir" -ForegroundColor Yellow
Write-Host ""

Write-Host "📋 Deployment Steps:" -ForegroundColor Cyan
Write-Host "  1. Git pull latest code from main branch" -ForegroundColor White
Write-Host "  2. Install Composer dependencies" -ForegroundColor White
Write-Host "  3. Run database migrations" -ForegroundColor White
Write-Host "  4. Cache configuration & routes" -ForegroundColor White
Write-Host "  5. Optimize application" -ForegroundColor White
Write-Host "  6. Clear old caches" -ForegroundColor White
Write-Host "  7. Restart supervisor services (if available)" -ForegroundColor White
Write-Host ""

$Confirm = Read-Host "Proceed with deployment? (yes/no)"
if ($Confirm -ne "yes") {
    Write-Host "❌ Deployment cancelled" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "🔐 Connecting to production server..." -ForegroundColor Green
Write-Host "   When prompted, enter SSH password: 065820Aaaa" -ForegroundColor Yellow
Write-Host ""

# Execute deployment script via SSH
Write-Host "⏳ Running deployment (this may take 2-3 minutes)..." -ForegroundColor Green
Write-Host ""

ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=accept-new $User@$Server "bash $AppDir/$DeployScript"

$ExitCode = $LASTEXITCODE

Write-Host ""
if ($ExitCode -eq 0) {
    Write-Host "════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host "✅ DEPLOYMENT SUCCESSFUL!" -ForegroundColor Green
    Write-Host "════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host ""
    Write-Host "📋 Access your application:" -ForegroundColor Cyan
    Write-Host "   https://esppd.infiatin.cloud" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "🔍 Check logs:" -ForegroundColor Cyan
    Write-Host "   ssh tholib_server@192.168.1.27" -ForegroundColor Cyan
    Write-Host "   tail -f /var/www/esppd/storage/logs/laravel.log" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host "════════════════════════════════════════════════════" -ForegroundColor Red
    Write-Host "❌ DEPLOYMENT FAILED" -ForegroundColor Red
    Write-Host "Exit Code: $ExitCode" -ForegroundColor Red
    Write-Host "════════════════════════════════════════════════════" -ForegroundColor Red
    Write-Host ""
    Write-Host "Troubleshooting:" -ForegroundColor Yellow
    Write-Host "  1. Check SSH connection: ssh tholib_server@192.168.1.27" -ForegroundColor White
    Write-Host "  2. Check git status: git status" -ForegroundColor White
    Write-Host "  3. Check logs: tail -f /var/www/esppd/storage/logs/laravel.log" -ForegroundColor White
    Write-Host ""
}

exit $ExitCode
