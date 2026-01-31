# PowerShell script to verify both PCs are identical
# Usage: .\verify-pc-sync.ps1

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "           PC1 = GitHub = PC2 Verification Tool" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Function to create checksum of important files
function Get-FileChecksum {
    param([string]$Path)
    if (Test-Path $Path) {
        $hash = Get-FileHash -Path $Path -Algorithm SHA256
        return $hash.Hash.Substring(0, 8)
    }
    return "MISSING"
}

# Function to check critical files
function Verify-CriticalFiles {
    Write-Host "🔍 Checking Critical Files..." -ForegroundColor Yellow
    Write-Host ""

    $files = @(
        "artisan.ps1",
        "artisan-sync",
        ".env",
        "app/Console/Commands/SyncDbToProduction.php",
        "app/Listeners/SyncDatabaseAfterArtisan.php",
        "MULTI_PC_DB_SYNC_SETUP.md",
        "MULTI_PC_SYNC_QUICKREF.md",
        "MULTI_PC_SYNC_IMPLEMENTATION_CHECKLIST.md"
    )

    $allPresent = $true
    foreach ($file in $files) {
        $exists = Test-Path $file
        $status = if ($exists) { "✓ Present" } else { "✗ MISSING" }
        $checksum = Get-FileChecksum $file
        Write-Host "  $status : $file [$checksum]"
        if (-not $exists) { $allPresent = $false }
    }

    Write-Host ""
    return $allPresent
}

# Function to check configuration
function Verify-Configuration {
    Write-Host "⚙️  Checking Configuration..." -ForegroundColor Yellow
    Write-Host ""

    if (-not (Test-Path ".env")) {
        Write-Host "  ✗ .env not found" -ForegroundColor Red
        return $false
    }

    $env_content = Get-Content ".env"

    # Check AUTO_DB_SYNC
    $autoSync = $env_content | Select-String "AUTO_DB_SYNC=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
    $syncStatus = if ($autoSync -eq "true") { "✓ ENABLED" } else { "✗ DISABLED (should be true)" }
    Write-Host "  $syncStatus : AUTO_DB_SYNC=$autoSync"

    # Check PRODUCTION_HOST
    $prodHost = $env_content | Select-String "PRODUCTION_HOST=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
    Write-Host "  ✓ PRODUCTION_HOST=$prodHost"

    # Check PRODUCTION_DB_PASSWORD
    $prodPass = $env_content | Select-String "PRODUCTION_DB_PASSWORD=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value }
    $passStatus = if ([string]::IsNullOrWhiteSpace($prodPass)) { "✗ NOT SET" } else { "✓ SET" }
    Write-Host "  $passStatus : PRODUCTION_DB_PASSWORD"

    Write-Host ""

    if ($autoSync -ne "true" -or [string]::IsNullOrWhiteSpace($prodPass)) {
        return $false
    }
    return $true
}

# Function to check git status
function Verify-GitStatus {
    Write-Host "📦 Checking Git Status..." -ForegroundColor Yellow
    Write-Host ""

    $branch = git rev-parse --abbrev-ref HEAD 2>$null
    $commit = git rev-parse --short HEAD 2>$null
    $status = git status --short 2>$null
    $changes = ($status | Measure-Object -Line).Lines

    Write-Host "  Branch: $branch"
    Write-Host "  Latest commit: $commit"
    Write-Host "  Local changes: $changes"

    if ($changes -gt 0) {
        Write-Host "  ⚠️  WARNING: Uncommitted changes detected" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Changes:"
        $status | ForEach-Object { Write-Host "    $_" }
    }

    Write-Host ""

    # Fetch and check against remote
    git fetch origin main --quiet 2>$null
    $localCommit = git rev-parse HEAD 2>$null
    $remoteCommit = git rev-parse origin/main 2>$null

    if ($localCommit -eq $remoteCommit) {
        Write-Host "  ✓ Local = Remote (GitHub) = SYNCHRONIZED" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Local ≠ Remote (OUT OF SYNC)" -ForegroundColor Red
        Write-Host "    Local:  $localCommit"
        Write-Host "    Remote: $remoteCommit"
    }

    Write-Host ""
    return ($localCommit -eq $remoteCommit -and $changes -eq 0)
}

# Function to verify artisan command works
function Verify-ArtisanCommand {
    Write-Host "🛠️  Checking Artisan Sync Command..." -ForegroundColor Yellow
    Write-Host ""

    $result = php artisan db:sync-to-production --help 2>&1

    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✓ Sync command available and working"
    } else {
        Write-Host "  ✗ Sync command failed"
        Write-Host "  Error: $($result[0])"
        return $false
    }

    Write-Host ""
    return $true
}

# Function to generate sync report
function Generate-SyncReport {
    Write-Host "📋 Generating Sync Report..." -ForegroundColor Yellow
    Write-Host ""

    $report = @{
        timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        pcName = $env:COMPUTERNAME
        branch = git rev-parse --abbrev-ref HEAD 2>$null
        commit = git rev-parse --short HEAD 2>$null
        autoSync = (Get-Content ".env" | Select-String "AUTO_DB_SYNC=(.+)" | ForEach-Object { $_.Matches[0].Groups[1].Value })
        filesOk = (Test-Path "artisan.ps1") -and (Test-Path ".env")
        gitSynced = (git rev-parse HEAD 2>$null) -eq (git rev-parse origin/main 2>$null)
    }

    # Save report
    $reportPath = "PC_SYNC_REPORT_$(Get-Date -Format 'yyyyMMdd_HHmmss').txt"
    $report | Out-String | Out-File -FilePath $reportPath -Encoding UTF8

    Write-Host "  Report saved: $reportPath"
    Write-Host ""
}

# Main execution
$filesOk = Verify-CriticalFiles
$configOk = Verify-Configuration
$gitOk = Verify-GitStatus
$commandOk = Verify-ArtisanCommand

# Summary
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "                       SUMMARY" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

Write-Host "✓ Files Status: $(if ($filesOk) { 'PASS' } else { 'FAIL' })" -ForegroundColor $(if ($filesOk) { "Green" } else { "Red" })
Write-Host "✓ Configuration: $(if ($configOk) { 'PASS' } else { 'FAIL' })" -ForegroundColor $(if ($configOk) { "Green" } else { "Red" })
Write-Host "✓ Git Sync: $(if ($gitOk) { 'PASS' } else { 'FAIL' })" -ForegroundColor $(if ($gitOk) { "Green" } else { "Red" })
Write-Host "✓ Artisan Command: $(if ($commandOk) { 'PASS' } else { 'FAIL' })" -ForegroundColor $(if ($commandOk) { "Green" } else { "Red" })

Write-Host ""

if ($filesOk -and $configOk -and $gitOk -and $commandOk) {
    Write-Host "✅ ALL CHECKS PASSED - PCs ARE SYNCHRONIZED!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Status: READY FOR COLLABORATIVE DEVELOPMENT" -ForegroundColor Green
} else {
    Write-Host "⚠️  SOME CHECKS FAILED - SYNC NEEDED" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Recommendations:" -ForegroundColor Yellow
    if (-not $gitOk) { Write-Host "  1. Run: git pull origin main" -ForegroundColor Yellow }
    if (-not $configOk) { Write-Host "  2. Edit .env - set PRODUCTION_DB_PASSWORD and AUTO_DB_SYNC=true" -ForegroundColor Yellow }
    if (-not $filesOk) { Write-Host "  3. Run setup: .\start-multi-pc-sync.ps1" -ForegroundColor Yellow }
}

Write-Host ""

# Generate report
Generate-SyncReport

Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Offer to test sync
$testSync = Read-Host "Test database sync? (y/n)"
if ($testSync -eq "y") {
    Write-Host ""
    Write-Host "Running: php artisan db:sync-to-production --dry-run" -ForegroundColor Cyan
    Write-Host ""
    php artisan db:sync-to-production --dry-run
}
