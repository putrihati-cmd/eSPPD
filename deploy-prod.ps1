$Server = "192.168.1.27"
$User = "tholib_server"
$AppDir = "/var/www/esppd"

Write-Host "eSPPD Production Deployment" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host ""

# Try with sudo first, fallback to non-sudo
$DeployCmdSudo = "cd $AppDir && sudo chown -R tholib_server:www-data . && git stash && git pull origin main && php artisan cache:clear"
$DeployCmd = "cd $AppDir && git stash && git pull origin main && php artisan cache:clear"

Write-Host "Connecting ke server..." -ForegroundColor Yellow
Write-Host "Masukkan password saat diminta" -ForegroundColor Yellow
Write-Host ""

# Try sudo first
ssh $User@$Server "$DeployCmdSudo" 2>&1 | Tee-Object -Variable output

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "Deployment BERHASIL!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Login ke: https://esppd.infiatin.cloud/login" -ForegroundColor Green
    Write-Host "NIP: 198302082015031501" -ForegroundColor Green
    Write-Host "Password: password" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "Ada error permission. Coba manual command:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "ssh $User@$Server" -ForegroundColor Cyan
    Write-Host "cd $AppDir" -ForegroundColor Cyan
    Write-Host "sudo chown -R tholib_server:www-data ." -ForegroundColor Cyan
    Write-Host "git stash" -ForegroundColor Cyan
    Write-Host "git pull origin main" -ForegroundColor Cyan
    Write-Host "php artisan cache:clear" -ForegroundColor Cyan
}
