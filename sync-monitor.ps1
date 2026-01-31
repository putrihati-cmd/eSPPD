#!/usr/bin/env powershell
# 2-PC Sync Monitor - Real-Time GitHub Watch
# Purpose: Monitor and alert when PC Client pushes changes

param(
    [int]$IntervalSeconds = 30,
    [switch]$Verbose = $false
)

Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  2-PC Real-Time Sync Monitor" -ForegroundColor Green
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "📡 Monitoring GitHub for PC Client changes..."
Write-Host "⏱️  Check interval: ${IntervalSeconds} seconds"
Write-Host "🛑 Press Ctrl+C to stop"
Write-Host ""

# Initial state
$initialCommit = git log -1 --oneline
Write-Host "📌 Current commit: $initialCommit" -ForegroundColor Yellow
Write-Host ""

$checkCount = 0
$lastCommit = $initialCommit
$changeDetected = $false

while ($true) {
    $checkCount++

    # Fetch without merging
    git fetch origin main | Out-Null

    # Get current commit hash
    $localCommit = git rev-parse HEAD
    $remoteCommit = git rev-parse origin/main

    $timestamp = Get-Date -Format "HH:mm:ss"

    # Display status
    Write-Host "[$timestamp] Check #$checkCount" -ForegroundColor Gray

    if ($localCommit -eq $remoteCommit) {
        Write-Host "  ✅ Up to date (local = remote)" -ForegroundColor Green
    } else {
        Write-Host "  🟡 Changes detected on GitHub!" -ForegroundColor Yellow

        # Show what changed
        $latestRemote = git log origin/main -1 --oneline
        Write-Host "  📥 New commit: $latestRemote" -ForegroundColor Cyan

        # Show files changed
        Write-Host "  📝 Files changed:" -ForegroundColor Cyan
        $changedFiles = git diff --name-only HEAD origin/main
        foreach ($file in $changedFiles) {
            Write-Host "     • $file" -ForegroundColor White
        }

        # Alert user
        Write-Host ""
        Write-Host "🔔 PC CLIENT HAS PUSHED NEW CHANGES!" -ForegroundColor Green -BackgroundColor Black
        Write-Host "💡 Run: git pull origin main" -ForegroundColor Yellow
        Write-Host ""

        $changeDetected = $true
    }

    # Wait before next check
    Start-Sleep -Seconds $IntervalSeconds
}
