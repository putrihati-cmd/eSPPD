# Immediately start the real-time sync monitor
# Usage: .\start-sync-now.ps1

param(
    [int]$CheckInterval = 30,
    [string]$RepoPath = "C:\laragon\www\eSPPD_new"
)

Write-Host "Starting Real-Time GitHub Sync Monitor..." -ForegroundColor Cyan
Write-Host "Repository: $RepoPath" -ForegroundColor Yellow
Write-Host "Check Interval: ${CheckInterval}s" -ForegroundColor Yellow
Write-Host ""
Write-Host "Monitor will auto-commit and push changes every $CheckInterval seconds" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop the monitor" -ForegroundColor Yellow
Write-Host ""

# Run the monitor
& "C:\laragon\www\eSPPD_new\scripts\sync-monitor.ps1" -CheckInterval $CheckInterval -RepoPath $RepoPath
