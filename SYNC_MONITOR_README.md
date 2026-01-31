# Sync Monitor - Real-Time GitHub Watch

## 📡 About This Script

Automatically monitors GitHub for changes from PC Client while you work.

## 🚀 Usage

### Basic Usage (Check every 30 seconds)

```powershell
./sync-monitor.ps1
```

### Custom Interval (Every 15 seconds)

```powershell
./sync-monitor.ps1 -IntervalSeconds 15
```

### With Verbose Output

```powershell
./sync-monitor.ps1 -Verbose
```

### Every 5 seconds (Aggressive monitoring)

```powershell
./sync-monitor.ps1 -IntervalSeconds 5
```

## 📊 What It Does

1. **Fetches from GitHub** - Checks for new commits from PC Client
2. **Compares Branches** - Local vs remote (origin/main)
3. **Alerts on Changes** - 🔔 Notification when PC Client pushes
4. **Shows Files Changed** - Lists which files were modified
5. **Continuous Monitoring** - Runs in loop until you stop (Ctrl+C)

## 📋 Output Example

```
═════════════════════════════════════════════════════════════
  2-PC Real-Time Sync Monitor
═════════════════════════════════════════════════════════════

📡 Monitoring GitHub for PC Client changes...
⏱️  Check interval: 30 seconds
🛑 Press Ctrl+C to stop

📌 Current commit: f02bffe Verified: All files synced to GitHub

[14:35:22] Check #1
  ✅ Up to date (local = remote)

[14:35:52] Check #2
  ✅ Up to date (local = remote)

[14:36:22] Check #3
  🟡 Changes detected on GitHub!
  📥 New commit: a1b2c3d CI: Add GitHub Actions workflows
  📝 Files changed:
     • .github/workflows/tests.yml
     • .github/workflows/deploy.yml

🔔 PC CLIENT HAS PUSHED NEW CHANGES!
💡 Run: git pull origin main
```

## 💡 Use Cases

### Scenario 1: While PC Client Works on Workflows

```powershell
# PC Server
./sync-monitor.ps1 -IntervalSeconds 30

# [monitor runs]
# [after 10 minutes: alerts when PC Client pushes workflows]
# [you run: git pull origin main]
# [workflow files appear locally]
```

### Scenario 2: Aggressive Monitoring (Few seconds)

```powershell
./sync-monitor.ps1 -IntervalSeconds 5

# Check every 5 seconds for changes
# Good for: Rapid back-and-forth development
```

### Scenario 3: Lazy Monitoring (Every minute)

```powershell
./sync-monitor.ps1 -IntervalSeconds 60

# Check every 60 seconds
# Good for: Long-running tasks on PC Client
```

## 🔧 How It Works

```
Loop every N seconds:
  ├─ git fetch origin main     (get latest remote info)
  ├─ Compare local HEAD vs origin/main
  └─ If different:
      ├─ Display new commits
      ├─ List changed files
      ├─ 🔔 Alert user
      └─ Suggest: git pull origin main
```

## 📌 Integration with Development

### Terminal Setup

```powershell
# Terminal 1: Run monitor
./sync-monitor.ps1

# Terminal 2: Continue working
git pull origin main
# [edit files]
git add .
git commit -m "..."
git push origin main
```

### Workflow

1. Start monitor in Terminal 1
2. Work normally in Terminal 2
3. Monitor watches GitHub
4. When PC Client pushes: **🔔 Alert appears**
5. Run `git pull origin main` in Terminal 2
6. Changes merge automatically
7. Continue development

## ⚙️ Configuration

### Default Settings

```powershell
IntervalSeconds = 30      # Check every 30 seconds
Verbose = $false          # Minimal output
```

### Recommended Settings by Scenario

| Scenario | Interval | Command |
|----------|----------|---------|
| Active collaboration | 10s | `./sync-monitor.ps1 -I 10` |
| Normal development | 30s | `./sync-monitor.ps1` |
| Background monitoring | 60s | `./sync-monitor.ps1 -I 60` |
| Aggressive watch | 5s | `./sync-monitor.ps1 -I 5` |

## 🆘 Troubleshooting

### Script won't run: "Permission denied"

```powershell
# Allow scripts to run
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Then retry
./sync-monitor.ps1
```

### "git not found" error

```powershell
# Ensure git is in PATH
git --version

# If fails, add git to PATH or use full path
C:\Program Files\Git\cmd\git.exe fetch origin main
```

### Monitor shows "Up to date" forever

```powershell
# 1. Check if PC Client actually pushed
git log -1 origin/main --oneline

# 2. Or manually pull to confirm
git pull origin main

# 3. Stop monitor (Ctrl+C) and restart
```

## 🎯 Best Practices

1. **Start monitor first** - Before PC Client starts working
2. **Keep it running** - Monitor in dedicated Terminal
3. **Act on alerts** - When 🔔 appears, pull changes
4. **Commit before leaving** - Ensure PC can pull your work
5. **Use with sync workflow** - Complements 5-step rule

## 🚀 Advanced Usage

### Run in Background Job

```powershell
$job = Start-Job -ScriptBlock { & "C:\laragon\www\eSPPD\sync-monitor.ps1" -I 30 }

# Work in main terminal
git pull origin main
# [continue development]

# Stop background job
Stop-Job $job
Remove-Job $job
```

### Log to File

```powershell
./sync-monitor.ps1 -IntervalSeconds 30 | Out-File -FilePath sync-log.txt -Append

# Monitor changes in sync-log.txt
Get-Content sync-log.txt -Tail 10 -Wait
```

---

**Created**: January 31, 2026  
**For**: 2-PC real-time development  
**Status**: ✅ Ready to use
