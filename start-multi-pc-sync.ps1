# Multi-PC Sync Starter - Run once to complete setup
# Usage: .\start-multi-pc-sync.ps1

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "   Multi-PC Database Sync - Full Implementation" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check prerequisites
Write-Host "[1/6] Checking prerequisites..." -ForegroundColor Yellow
Write-Host ""

$checks = @{
    "PostgreSQL (pg_dump)" = { Get-Command pg_dump -ErrorAction SilentlyContinue }
    "PostgreSQL (psql)" = { Get-Command psql -ErrorAction SilentlyContinue }
    "SSH client" = { Get-Command ssh -ErrorAction SilentlyContinue }
    "PHP" = { Get-Command php -ErrorAction SilentlyContinue }
}

$allChecked = $true
foreach ($check in $checks.GetEnumerator()) {
    $result = & $check.Value
    if ($result) {
        Write-Host "  ✓ $($check.Key)" -ForegroundColor Green
    } else {
        Write-Host "  ✗ $($check.Key) - MISSING" -ForegroundColor Red
        $allChecked = $false
    }
}

if (-not $allChecked) {
    Write-Host ""
    Write-Host "⚠️  Some tools missing. Please install PostgreSQL and ensure SSH is configured." -ForegroundColor Yellow
    Write-Host ""
    pause
    exit 1
}

Write-Host ""

# Step 2: Test SSH connection
Write-Host "[2/6] Testing SSH connection to production..." -ForegroundColor Yellow

$sshTest = ssh tholib_server@192.168.1.27 "echo OK" 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "  ✓ SSH connection successful" -ForegroundColor Green
} else {
    Write-Host "  ✗ SSH connection failed" -ForegroundColor Red
    Write-Host "    Error: $sshTest" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "    Generate SSH key and try again:" -ForegroundColor Cyan
    Write-Host "    ssh-keygen -t rsa -b 4096 -f `$env:USERPROFILE\.ssh\id_rsa" -ForegroundColor Cyan
    Write-Host ""
    $continueAnyway = Read-Host "Continue anyway? (y/n)"
    if ($continueAnyway -ne "y") {
        exit 1
    }
}

Write-Host ""

# Step 3: Set PRODUCTION_DB_PASSWORD
Write-Host "[3/6] Setting production database password..." -ForegroundColor Yellow

$envFile = ".env"
$currentPassword = Select-String -Path $envFile -Pattern "PRODUCTION_DB_PASSWORD=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }

if ([string]::IsNullOrWhiteSpace($currentPassword)) {
    Write-Host "  ⚠️  PRODUCTION_DB_PASSWORD is empty" -ForegroundColor Yellow
    $dbPassword = Read-Host "  Enter PostgreSQL production password (or press Enter to skip)"

    if (-not [string]::IsNullOrWhiteSpace($dbPassword)) {
        (Get-Content $envFile) -replace "PRODUCTION_DB_PASSWORD=.*", "PRODUCTION_DB_PASSWORD=$dbPassword" | Set-Content $envFile
        Write-Host "  ✓ Password updated in .env" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  Skipped (you can update later manually)" -ForegroundColor Yellow
    }
} else {
    Write-Host "  ✓ Password already set in .env" -ForegroundColor Green
}

Write-Host ""

# Step 4: Test database sync command
Write-Host "[4/6] Testing database sync command..." -ForegroundColor Yellow

$syncTest = php artisan db:sync-to-production --help 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "  ✓ Sync command available" -ForegroundColor Green
} else {
    Write-Host "  ✗ Sync command not found" -ForegroundColor Red
    Write-Host "    Run: git pull origin main" -ForegroundColor Yellow
}

Write-Host ""

# Step 5: Setup PowerShell alias
Write-Host "[5/6] Setting up PowerShell alias..." -ForegroundColor Yellow

$profilePath = $PROFILE
$profileDir = Split-Path $profilePath

if (-not (Test-Path $profileDir)) {
    New-Item -ItemType Directory -Path $profileDir -Force | Out-Null
}

$aliasLine = "Set-Alias artisan '$PWD\artisan.ps1'"

if (Test-Path $profilePath) {
    if ((Get-Content $profilePath) -notmatch "Set-Alias artisan") {
        Add-Content $profilePath "`n$aliasLine`n"
        Write-Host "  ✓ Added alias to PowerShell profile" -ForegroundColor Green
        Write-Host "    ⚠️  Restart PowerShell to use 'artisan' command" -ForegroundColor Yellow
    } else {
        Write-Host "  ✓ Alias already configured" -ForegroundColor Green
    }
} else {
    Set-Content $profilePath $aliasLine
    Write-Host "  ✓ Created PowerShell profile with alias" -ForegroundColor Green
    Write-Host "    ⚠️  Restart PowerShell to use 'artisan' command" -ForegroundColor Yellow
}

Write-Host ""

# Step 6: Summary
Write-Host "[6/6] Configuration Summary:" -ForegroundColor Yellow
Write-Host ""

$envContent = Get-Content ".env"
$config = @{
    "PRODUCTION_HOST" = ($envContent | Select-String "PRODUCTION_HOST=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
    "PRODUCTION_USER" = ($envContent | Select-String "PRODUCTION_USER=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
    "PRODUCTION_DB_NAME" = ($envContent | Select-String "PRODUCTION_DB_NAME=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
    "AUTO_DB_SYNC" = ($envContent | Select-String "AUTO_DB_SYNC=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
}

Write-Host "  Configuration:" -ForegroundColor Cyan
$config.GetEnumerator() | ForEach-Object {
    if ($_.Key -eq "AUTO_DB_SYNC") {
        $status = if ($_.Value -eq "true") { "ENABLED ✓" } else { "DISABLED (enable in .env)" }
        Write-Host "    $($_.Key) = $($_.Value)  [$status]" -ForegroundColor White
    } else {
        Write-Host "    $($_.Key) = $($_.Value)" -ForegroundColor White
    }
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

Write-Host "✅ Setup Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host ""
Write-Host "  1. Edit .env and set AUTO_DB_SYNC=true (if not enabled)" -ForegroundColor White
Write-Host "  2. Test: php artisan db:sync-to-production --dry-run" -ForegroundColor White
Write-Host "  3. Restart PowerShell" -ForegroundColor White
Write-Host "  4. Start using:" -ForegroundColor White
Write-Host ""
Write-Host "     artisan migrate              # (auto-syncs to production)" -ForegroundColor Yellow
Write-Host "     artisan db:seed" -ForegroundColor Yellow
Write-Host "     artisan make:migration table_name" -ForegroundColor Yellow
Write-Host ""
Write-Host "Both PCs will stay synchronized! 🎉" -ForegroundColor Green
Write-Host ""
