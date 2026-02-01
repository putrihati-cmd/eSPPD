$Server = "192.168.1.27"
$User = "tholib_server"
$AppDir = "/var/www/esppd"
$Password = "065820Aaaa"
$DbPassword = "Esppd@123456"

Write-Host "🚀 INITIALIZING AUTO FIX FOR PRODUCTION..." -ForegroundColor Cyan

# Command block
$commands = "echo '$Password' | sudo -S rm -rf $AppDir/.git/refs/remotes/origin/main && cd $AppDir && git fetch origin && git reset --hard origin/main && sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=$DbPassword/' .env && php artisan optimize:clear"

# Execution
ssh $User@$Server $commands
