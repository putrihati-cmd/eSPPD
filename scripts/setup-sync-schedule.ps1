# Setup automatic sync task scheduler
# Runs the sync monitor on system startup
# Run this once to setup the Windows Task Scheduler

param(
    [string]$TaskName = "eSPPD-RealTimeSync",
    [string]$ScriptPath = "C:\laragon\www\eSPPD_new\scripts\sync-monitor.ps1",
    [string]$RepoPath = "C:\laragon\www\eSPPD_new"
)

# Require admin
if (-not ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "ERROR: This script requires Administrator privileges!" -ForegroundColor Red
    Write-Host "Please run PowerShell as Administrator" -ForegroundColor Yellow
    exit 1
}

Write-Host "Setting up Real-Time GitHub Sync Task Scheduler..." -ForegroundColor Cyan
Write-Host ""

# Check if task already exists
$existingTask = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue

if ($existingTask) {
    Write-Host "Task '$TaskName' already exists." -ForegroundColor Yellow
    $response = Read-Host "Do you want to recreate it? (y/n)"
    
    if ($response -eq 'y') {
        Write-Host "Removing existing task..." -ForegroundColor Yellow
        Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
        Write-Host "Task removed." -ForegroundColor Green
    }
    else {
        Write-Host "Keeping existing task." -ForegroundColor Cyan
        exit 0
    }
}

# Create task action
$action = New-ScheduledTaskAction `
    -Execute "powershell.exe" `
    -Argument "-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$ScriptPath`" -RepoPath `"$RepoPath`""

# Create trigger for system startup
$trigger = New-ScheduledTaskTrigger -AtStartup

# Create principal to run with system context
$principal = New-ScheduledTaskPrincipal `
    -UserId "SYSTEM" `
    -LogonType ServiceAccount `
    -RunLevel Highest

# Create task settings
$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -Compatibility Win8 `
    -MultipleInstances IgnoreNew `
    -StartWhenAvailable

# Register the scheduled task
Write-Host "Creating scheduled task..." -ForegroundColor Cyan
Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $action `
    -Trigger $trigger `
    -Principal $principal `
    -Settings $settings `
    -Description "Real-Time GitHub Sync Monitor for eSPPD Repository" `
    -Force | Out-Null

Write-Host "✅ Scheduled task created successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Task Details:" -ForegroundColor Yellow
Write-Host "  Task Name: $TaskName"
Write-Host "  Trigger: System Startup"
Write-Host "  User: SYSTEM"
Write-Host "  Run Level: Highest (Administrator)"
Write-Host "  Script: $ScriptPath"
Write-Host "  Repository: $RepoPath"
Write-Host ""

# Test the task
Write-Host "Testing task execution..." -ForegroundColor Cyan
Start-ScheduledTask -TaskName $TaskName

Write-Host "Task has been started." -ForegroundColor Green
Write-Host ""
Write-Host "You can:" -ForegroundColor Cyan
Write-Host "  1. View task status: Get-ScheduledTask -TaskName '$TaskName'"
Write-Host "  2. Start manually: Start-ScheduledTask -TaskName '$TaskName'"
Write-Host "  3. Stop manually: Stop-ScheduledTask -TaskName '$TaskName'"
Write-Host "  4. Remove: Unregister-ScheduledTask -TaskName '$TaskName' -Confirm:`$false"
Write-Host ""
Write-Host "✅ Real-Time GitHub Sync is now setup!" -ForegroundColor Green
