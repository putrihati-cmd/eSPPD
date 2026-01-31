# Real-Time GitHub Sync Monitor
# Monitors for file changes dan otomatis push ke GitHub
# Usage: .\sync-monitor.ps1

param(
    [int]$CheckInterval = 30,  # Check every 30 seconds
    [string]$RepoPath = "C:\laragon\www\eSPPD_new"
)

# Color output
function Write-Status {
    param([string]$Message, [string]$Status = "Info")
    $colors = @{
        "Info"    = "Cyan"
        "Success" = "Green"
        "Warning" = "Yellow"
        "Error"   = "Red"
    }
    Write-Host "[$(Get-Date -Format 'HH:mm:ss')] [$Status] $Message" -ForegroundColor $colors[$Status]
}

Write-Status "Starting Real-Time GitHub Sync Monitor..." "Info"
Write-Status "Repository: $RepoPath" "Info"
Write-Status "Check Interval: ${CheckInterval}s" "Info"
Write-Status "Monitoring ENABLED - Press Ctrl+C to stop" "Warning"
Write-Host ""

$previousStatus = ""
$errorCount = 0
$maxErrors = 3

try {
    while ($true) {
        try {
            Push-Location $RepoPath
            
            # Get current git status
            $gitStatus = & git status --porcelain
            $gitBranch = & git rev-parse --abbrev-ref HEAD
            
            # Check if there are changes
            if ($gitStatus) {
                Write-Status "Changes detected!" "Warning"
                Write-Host $gitStatus
                
                # Auto-commit dan push
                Write-Status "Staging all changes..." "Info"
                & git add .
                
                # Get summary of changes
                $stagedStatus = & git status --porcelain
                Write-Host $stagedStatus
                
                # Create commit message with timestamp
                $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
                $commitMsg = "Auto-sync: Real-time changes from local PC [$timestamp]"
                
                Write-Status "Committing changes..." "Info"
                & git commit -m $commitMsg | Out-Null
                
                Write-Status "Pushing to GitHub (origin/$gitBranch)..." "Info"
                $pushResult = & git push origin $gitBranch 2>&1
                
                Write-Status "✅ Changes pushed successfully to GitHub!" "Success"
                Write-Host $pushResult
                Write-Host ""
                
                $errorCount = 0  # Reset error count on success
            }
            else {
                # No changes
                $currentStatus = "✓ Clean - up to date with 'origin/$gitBranch'"
                if ($currentStatus -ne $previousStatus) {
                    Write-Status $currentStatus "Success"
                    $previousStatus = $currentStatus
                }
            }
            
            Pop-Location
        }
        catch {
            $errorCount++
            Write-Status "Error during sync: $_" "Error"
            
            if ($errorCount -ge $maxErrors) {
                Write-Status "Max error attempts reached. Pausing for 60 seconds..." "Error"
                Start-Sleep -Seconds 60
                $errorCount = 0
            }
            
            Pop-Location -ErrorAction SilentlyContinue
        }
        
        # Wait before next check
        Start-Sleep -Seconds $CheckInterval
    }
}
catch {
    Write-Status "Fatal error: $_" "Error"
    exit 1
}
finally {
    Write-Status "Sync Monitor stopped" "Info"
}
