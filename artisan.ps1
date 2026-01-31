# PowerShell wrapper script to auto-sync database after artisan commands
# Usage: .\artisan.ps1 migrate
# Usage: .\artisan.ps1 db:seed

param(
    [Parameter(Mandatory=$true, Position=0)]
    [string]$Command,

    [Parameter(ValueFromRemainingArguments=$true)]
    [string[]]$Arguments
)

# Run the actual artisan command
$allArgs = @($Command) + $Arguments
& php artisan @allArgs
$commandExitCode = $LASTEXITCODE

# Check if AUTO_DB_SYNC is enabled in .env
$envContent = Get-Content -Path ".env" -ErrorAction SilentlyContinue | Select-String "AUTO_DB_SYNC=true"

# If command succeeded and AUTO_DB_SYNC is enabled, sync database
if ($commandExitCode -eq 0 -and $envContent) {
    Write-Host ""
    Write-Host "🔄 Auto-syncing database to production..." -ForegroundColor Cyan
    & php artisan db:sync-to-production
}

exit $commandExitCode
