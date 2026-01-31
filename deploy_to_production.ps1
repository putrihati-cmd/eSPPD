# PowerShell Production Deployment Script
# Usage: .\deploy_to_production.ps1
# This script deploys eSPPD to production server 192.168.1.27

param(
    [string]$Host = "192.168.1.27",
    [string]$User = "tholib_server",
    [securestring]$SecurePassword = $null
)

Write-Host "`n════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "🚀 eSPPD Production Deployment to $Host" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════`n" -ForegroundColor Cyan

# If password not provided, prompt user
if ($null -eq $SecurePassword) {
    $SecurePassword = Read-Host -AsSecureString "🔐 Enter SSH password for $User"
}

# Convert secure password to plain text (for SSH)
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($SecurePassword)
$PlainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
[System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($BSTR)

# Deployment steps
$DeploymentSteps = @(
    "cd /var/www/esppd",
    "echo '📥 Pulling latest code from GitHub...'",
    "git pull origin main",
    "echo '📦 Installing dependencies...'",
    "composer install --no-dev --optimize-autoloader",
    "echo '🗄️  Running database migrations...'",
    "php artisan migrate --force",
    "echo '⚙️  Caching configuration...'",
    "php artisan config:cache",
    "php artisan route:cache",
    "php artisan view:cache",
    "echo '⚡ Optimizing application...'",
    "php artisan optimize",
    "echo '✅ Deployment completed successfully!'",
    "echo '📋 Application: https://esppd.infiatin.cloud'"
)

$DeploymentCommand = $DeploymentSteps -join " && "

# Display deployment plan
Write-Host "📋 Deployment Plan:" -ForegroundColor Yellow
Write-Host "  1. Git pull from main branch" -ForegroundColor White
Write-Host "  2. Install Composer dependencies" -ForegroundColor White
Write-Host "  3. Run database migrations" -ForegroundColor White
Write-Host "  4. Cache configuration & routes" -ForegroundColor White
Write-Host "  5. Optimize application" -ForegroundColor White
Write-Host ""

# Confirm before deployment
$Confirm = Read-Host "Proceed with deployment? (yes/no)"
if ($Confirm -ne "yes") {
    Write-Host "❌ Deployment cancelled" -ForegroundColor Red
    exit 1
}

Write-Host "`n🚀 Starting deployment...`n" -ForegroundColor Green

try {
    # Connect via SSH and execute commands
    Write-Host "Connecting to $Host..."

    # Create temporary script file
    $TempScript = "$env:TEMP\deploy_$([guid]::NewGuid().ToString()).sh"
    $DeploymentSteps | Out-File -FilePath $TempScript -Encoding UTF8 -Force

    # Alternative: Use SSH with command string
    # This approach works better with OpenSSH on Windows
    ssh -o ConnectTimeout=10 $User@$Host $DeploymentCommand 2>&1

    $ExitCode = $LASTEXITCODE

    if ($ExitCode -eq 0) {
        Write-Host "`n════════════════════════════════════════════════════" -ForegroundColor Green
        Write-Host "✅ DEPLOYMENT SUCCESSFUL!" -ForegroundColor Green
        Write-Host "════════════════════════════════════════════════════" -ForegroundColor Green
        Write-Host "📋 Application: https://esppd.infiatin.cloud" -ForegroundColor Cyan
        Write-Host "⏰ Deployed at: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
        Write-Host ""
    } else {
        Write-Host "`n════════════════════════════════════════════════════" -ForegroundColor Red
        Write-Host "❌ DEPLOYMENT FAILED!" -ForegroundColor Red
        Write-Host "Exit Code: $ExitCode" -ForegroundColor Red
        Write-Host "════════════════════════════════════════════════════" -ForegroundColor Red
        exit $ExitCode
    }

    # Clean up temp file
    if (Test-Path $TempScript) {
        Remove-Item $TempScript -Force
    }

} catch {
    Write-Host "`n❌ Error during deployment: $_" -ForegroundColor Red
    exit 1
}
