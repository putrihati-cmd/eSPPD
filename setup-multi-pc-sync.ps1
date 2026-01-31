#!/usr/bin/env powershell
# Quick Setup Script for Multi-PC Database Sync
# Run this once to setup everything

Write-Host "🔄 Multi-PC Database Sync Setup" -ForegroundColor Cyan
Write-Host "================================`n" -ForegroundColor Cyan

# Step 1: Check PostgreSQL tools
Write-Host "[1/5] Checking PostgreSQL tools..." -ForegroundColor Yellow
$pgDump = Get-Command pg_dump -ErrorAction SilentlyContinue
$psql = Get-Command psql -ErrorAction SilentlyContinue

if ($pgDump -and $psql) {
    Write-Host "✓ PostgreSQL tools found`n" -ForegroundColor Green
} else {
    Write-Host "⚠️  PostgreSQL tools not found in PATH" -ForegroundColor Red
    Write-Host "   Add PostgreSQL bin to PATH:" -ForegroundColor Yellow
    Write-Host "   C:\laragon\bin\postgresql\bin`n" -ForegroundColor Yellow
}

# Step 2: Check SSH
Write-Host "[2/5] Checking SSH..." -ForegroundColor Yellow
$sshTest = ssh tholib_server@192.168.1.27 "echo OK" 2>$null
if ($sshTest -eq "OK") {
    Write-Host "✓ SSH connection successful`n" -ForegroundColor Green
} else {
    Write-Host "⚠️  SSH connection failed" -ForegroundColor Red
    Write-Host "   Setup SSH key first:`n" -ForegroundColor Yellow
    Write-Host "   ssh-keygen -t rsa -b 4096 -f `$env:USERPROFILE\.ssh\id_rsa`n" -ForegroundColor Yellow
}

# Step 3: Check .env configuration
Write-Host "[3/5] Checking .env configuration..." -ForegroundColor Yellow
$envContent = Get-Content -Path ".env" -ErrorAction SilentlyContinue
$hasProduction = $envContent | Select-String "PRODUCTION_HOST"
$hasAutoSync = $envContent | Select-String "AUTO_DB_SYNC"

if ($hasProduction -and $hasAutoSync) {
    Write-Host "✓ Configuration found in .env`n" -ForegroundColor Green
} else {
    Write-Host "⚠️  Missing configuration in .env" -ForegroundColor Red
}

# Step 4: Test database sync command
Write-Host "[4/5] Testing database sync command..." -ForegroundColor Yellow
$result = php artisan db:sync-to-production --help 2>&1
if ($result -match "Sync all database tables") {
    Write-Host "✓ Sync command available`n" -ForegroundColor Green
} else {
    Write-Host "❌ Sync command not found`n" -ForegroundColor Red
}

# Step 5: Configuration check
Write-Host "[5/5] Configuration Summary:" -ForegroundColor Yellow
Write-Host ""

$envFile = Get-Content ".env" -Raw
$config = @{
    'PRODUCTION_HOST' = $envFile | Select-String "PRODUCTION_HOST=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
    'PRODUCTION_USER' = $envFile | Select-String "PRODUCTION_USER=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
    'AUTO_DB_SYNC' = $envFile | Select-String "AUTO_DB_SYNC=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
}

$config.Keys | ForEach-Object {
    $value = $config[$_]
    if ($_ -eq "AUTO_DB_SYNC") {
        $status = if ($value -eq "true") { "✓ ENABLED" } else { "⚠️  DISABLED" }
        Write-Host "   $_=$value  $status" -ForegroundColor Cyan
    } else {
        Write-Host "   $_=$value" -ForegroundColor Cyan
    }
}

Write-Host "`n"
Write-Host "═════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Green
Write-Host ""
Write-Host "1. Set PRODUCTION_DB_PASSWORD in .env"
Write-Host "2. Test: php artisan db:sync-to-production --dry-run"
Write-Host "3. Enable: Set AUTO_DB_SYNC=true in .env"
Write-Host "4. Start using: .\artisan.ps1 migrate`n"
Write-Host ""
Write-Host "For full guide: see MULTI_PC_DB_SYNC_SETUP.md`n" -ForegroundColor Cyan
