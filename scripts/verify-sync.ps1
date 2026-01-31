# Verify GitHub sync status
# Check if local repo is in sync with GitHub

param(
    [string]$RepoPath = "C:\laragon\www\eSPPD_new"
)

Write-Host "Verifying GitHub Sync Status..." -ForegroundColor Cyan
Write-Host ""

try {
    Push-Location $RepoPath
    
    # Get current branch
    $branch = & git rev-parse --abbrev-ref HEAD
    Write-Host "Current Branch: $branch" -ForegroundColor Yellow
    
    # Fetch latest from GitHub
    Write-Host "Fetching latest from GitHub..." -ForegroundColor Gray
    & git fetch origin 2>&1 | Out-Null
    
    # Check status
    $localCommit = & git rev-parse HEAD
    $remoteCommit = & git rev-parse origin/$branch
    
    $status = & git status --porcelain
    $aheadBehind = & git rev-list --left-right --count origin/$branch...HEAD
    
    Write-Host ""
    Write-Host "Sync Status:" -ForegroundColor Yellow
    
    if ($localCommit -eq $remoteCommit) {
        Write-Host "✅ Local branch is in sync with remote" -ForegroundColor Green
    }
    else {
        $parts = $aheadBehind -split "`t"
        $behind = $parts[0]
        $ahead = $parts[1]
        
        if ($ahead -gt 0) {
            Write-Host "⚠️  Local is $ahead commit(s) AHEAD of remote" -ForegroundColor Yellow
            Write-Host "   Run: git push origin $branch" -ForegroundColor Cyan
        }
        if ($behind -gt 0) {
            Write-Host "⚠️  Local is $behind commit(s) BEHIND remote" -ForegroundColor Yellow
            Write-Host "   Run: git pull origin $branch" -ForegroundColor Cyan
        }
    }
    
    Write-Host ""
    Write-Host "Working Tree:" -ForegroundColor Yellow
    
    if ($status) {
        Write-Host "⚠️  You have uncommitted changes:" -ForegroundColor Yellow
        Write-Host ""
        $status | ForEach-Object { Write-Host "   $_" }
        Write-Host ""
        Write-Host "Run these commands to sync:" -ForegroundColor Cyan
        Write-Host "  git add ." -ForegroundColor Gray
        Write-Host "  git commit -m 'Your commit message'" -ForegroundColor Gray
        Write-Host "  git push origin $branch" -ForegroundColor Gray
    }
    else {
        Write-Host "✅ Working tree is clean (no uncommitted changes)" -ForegroundColor Green
    }
    
    # Show last commits
    Write-Host ""
    Write-Host "Latest Commits (Last 3):" -ForegroundColor Yellow
    & git log --oneline -3 | ForEach-Object { Write-Host "   $_" -ForegroundColor Gray }
    
    Pop-Location
    Write-Host ""
    Write-Host "✅ Sync verification complete!" -ForegroundColor Green
}
catch {
    Write-Host "❌ Error: $_" -ForegroundColor Red
    Pop-Location -ErrorAction SilentlyContinue
    exit 1
}
