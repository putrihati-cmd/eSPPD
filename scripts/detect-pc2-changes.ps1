param(
    [switch]$Verbose,
    [switch]$Pull,
    [switch]$Learn
)

$repo = "c:\laragon\www\eSPPD_new"
Push-Location $repo

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║    PC 2 Change Detection & Learning System                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Step 1: Fetch
Write-Host "📡 Fetching from GitHub..." -ForegroundColor Yellow
git fetch origin 2>&1 | Out-Null
Write-Host "✅ Fetch complete" -ForegroundColor Green
Write-Host ""

# Step 2: Check diff
Write-Host "🔍 Analyzing changes..." -ForegroundColor Yellow
$diffStat = git diff main origin/main --stat
$diffNames = git diff main origin/main --name-status
$logCompare = @(git log main..origin/main --oneline)

if ($logCompare.Count -eq 0) {
    Write-Host "✅ No new changes from PC 2" -ForegroundColor Green
    Write-Host ""
    Write-Host "Current Status:" -ForegroundColor Cyan
    git log --oneline -1 main
    Write-Host ""
    exit 0
}

# Changes detected!
Write-Host "🆕 NEW CHANGES DETECTED FROM PC 2!" -ForegroundColor Yellow
Write-Host ""

# Step 3: Show summary
Write-Host "📊 Change Summary:" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host "Total commits: $($logCompare.Count)" -ForegroundColor White
Write-Host ""
Write-Host "Commits:" -ForegroundColor Cyan
$logCompare | ForEach-Object { Write-Host "  🔹 $_" -ForegroundColor White }
Write-Host ""

Write-Host "Changed files:" -ForegroundColor Cyan
Write-Host $diffStat
Write-Host ""

Write-Host "File changes by type:" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
$added = @($diffNames -match "^A")
$modified = @($diffNames -match "^M")
$deleted = @($diffNames -match "^D")
$renamed = @($diffNames -match "^R")

Write-Host "  ➕ Added: $($added.Count) files" -ForegroundColor Green
if ($added.Count -gt 0) { $added | ForEach-Object { Write-Host "     $_" } }

Write-Host "  ✏️  Modified: $($modified.Count) files" -ForegroundColor Yellow
if ($modified.Count -gt 0) { $modified | ForEach-Object { Write-Host "     $_" } }

Write-Host "  ❌ Deleted: $($deleted.Count) files" -ForegroundColor Red
if ($deleted.Count -gt 0) { $deleted | ForEach-Object { Write-Host "     $_" } }

Write-Host "  🔄 Renamed: $($renamed.Count) files" -ForegroundColor Magenta
if ($renamed.Count -gt 0) { $renamed | ForEach-Object { Write-Host "     $_" } }

Write-Host ""

if ($Verbose) {
    Write-Host "📋 Detailed diff:" -ForegroundColor Cyan
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
    git diff main origin/main
    Write-Host ""
}

# Step 4: Offer pull
if ($Pull) {
    Write-Host "🔄 Pulling changes..." -ForegroundColor Yellow
    git pull origin main
    Write-Host "✅ Pull complete" -ForegroundColor Green
    Write-Host ""
    Write-Host "Current status:" -ForegroundColor Cyan
    git log --oneline -1
    Write-Host ""
    exit 0
}

# Step 5: Offer learning
if ($Learn) {
    Write-Host "📚 LEARNING ANALYSIS NEEDED" -ForegroundColor Cyan
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor White
    Write-Host "  1. Run: git pull origin main" -ForegroundColor Yellow
    Write-Host "  2. Review changes in each file" -ForegroundColor Yellow
    Write-Host "  3. Update PC2_LEARNING_LOG.md" -ForegroundColor Yellow
    Write-Host "  4. Document new patterns & decisions" -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

Write-Host "Actions:" -ForegroundColor Cyan
Write-Host "  To pull changes:    .\detect-pc2-changes.ps1 -Pull" -ForegroundColor White
Write-Host "  For learning mode:  .\detect-pc2-changes.ps1 -Learn" -ForegroundColor White
Write-Host "  Verbose output:     .\detect-pc2-changes.ps1 -Verbose" -ForegroundColor White
Write-Host ""

Pop-Location
exit 1
