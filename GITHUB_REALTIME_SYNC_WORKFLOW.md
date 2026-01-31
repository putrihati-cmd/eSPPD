# Real-Time GitHub Sync untuk 2-PC Development

**Status**: ✅ Implemented  
**Date**: January 31, 2026  
**Purpose**: Menjaga synchronization antara PC Server (192.168.1.16) dan PC Client (192.168.1.11)

---

## 🎯 Sync Strategy

### Problem Statement
- 2 developer (atau 1 developer menggunakan 2 PC) bekerja simultaneous
- Tanpa sync yang ketat → **merge conflicts** akan terjadi
- Solusi: **Pull → Edit → Commit → Push** workflow yang strict

### Solution
**Automatic Push After Every Commit** - Tidak ada uncommitted changes yang tertinggal di local.

---

## 📋 Daily Workflow - MUST FOLLOW

### ✅ Step 1: Start Session (PC Manapun)

```powershell
cd C:\laragon\www\eSPPD  # atau eSPPD_new untuk PC Client

# ALWAYS pull first - penting!
git pull origin main

# Check status
git status
# Expected: "Your branch is up to date with 'origin/main'."
```

### ✅ Step 2: Edit & Development

Edit file sesuka hati:
- Blade templates
- PHP components/controllers
- CSS/JS
- Database migrations
- Configuration

**JANGAN** push sampai semua edits selesai.

### ✅ Step 3: Before Leaving PC

```powershell
# CRITICAL: Stage semua changes
git add .

# Check apa yang di-stage
git status
# Expected: All modified files ditampilkan

# Commit dengan clear message
git commit -m "Feat: Add dashboard redesign - phase 1"

# IMMEDIATELY push ke GitHub
git push origin main

# Verify success
git log --oneline -1  # Shows latest commit hash
# Expected: Your commit should appear
```

### ✅ Step 4: Switch to Other PC

```powershell
cd C:\laragon\www\eSPPD  # atau eSPPD_new

# Pull latest changes dari GitHub
git pull origin main

# Check what changed
git diff HEAD~1  # See last changes

# Verify project status
git status
# Expected: "working tree clean"

# Continue working
```

---

## 🚨 CRITICAL RULES

### ❌ JANGAN Pernah

```powershell
# ❌ Push tanpa pull dulu
git push origin main  # (without pull first)

# ❌ Edit file, switch PC, lalu edit file sama di PC lain
# Ini akan cause CONFLICT

# ❌ Lupa commit sebelum pindah PC
# Uncommitted changes akan hilang atau stuck

# ❌ Work on 2 PC simultaneously
# Wait for pull/push cycle to complete

# ❌ Leave uncommitted changes
git status  # must show "nothing to commit"
```

### ✅ HARUS Selalu

```powershell
# ✅ Always pull first
git pull origin main

# ✅ Commit sebelum pindah PC
git add . && git commit -m "..." && git push

# ✅ Pull ketika mulai di PC baru
git pull origin main

# ✅ Check git status regularly
git status

# ✅ Wait for sync to complete
# Don't start working until: "Your branch is up to date"
```

---

## 🔄 Perfect Workflow Example

### Scenario: Edit Dashboard di PC Server, lalu Build UI di PC Client

```powershell
# ===== MORNING: PC SERVER (192.168.1.16) =====
cd C:\laragon\www\eSPPD

# 1. Pull (kosong karena baru pagi)
git pull origin main
# ✅ "Your branch is up to date with 'origin/main'."

# 2. Edit dashboard component
notepad app/Livewire/DashboardEnhanced.php
# ... add metrics loading logic ...

# 3. Before going to lunch / switching to PC Client
git add .
git commit -m "Feat: Implement dashboard metrics calculation"
git push origin main
# ✅ All changes pushed

# ===== AFTERNOON: PC CLIENT (192.168.1.11) =====
cd C:\laragon\www\eSPPD_new

# 1. Pull latest (dari PC Server pagi tadi)
git pull origin main
# ✅ Downloaded DashboardEnhanced.php updates

# 2. Build beautiful UI using that component
notepad resources/views/livewire/dashboard-enhanced.blade.php
# ... create card layouts ...

# 3. Before going home / switching back to PC Server
git add .
git commit -m "Feat: Create modern dashboard UI with card layout"
git push origin main
# ✅ All UI changes pushed

# ===== EVENING: PC SERVER =====
cd C:\laragon\www\eSPPD

# 1. Pull latest changes from PC Client
git pull origin main
# ✅ Downloaded beautiful UI from PC Client

# 2. See the complete dashboard (logic + UI combined)
php artisan serve

# 3. Verify everything working
# http://localhost:8000/dashboard
# ✅ Perfect integration!
```

---

## 📊 Real-Time Sync Checklist

### Before Switching PC

- [ ] `git status` shows "nothing to commit"
- [ ] All changes are staged with `git add .`
- [ ] Commit message is clear & descriptive
- [ ] `git push origin main` completed successfully
- [ ] `git log --oneline -1` shows YOUR commit

### After Switching PC

- [ ] `git pull origin main` completed
- [ ] `git status` shows "working tree clean"
- [ ] New files appear in explorer
- [ ] Latest changes are visible in editor
- [ ] No conflicts in console output

---

## 🔧 Commit Message Convention

Use these prefixes untuk organize commits:

```
Feat:     [New feature]         git commit -m "Feat: Add dashboard component"
Fix:      [Bug fix]             git commit -m "Fix: Handle null status values"
Refactor: [Code restructure]    git commit -m "Refactor: Optimize query performance"
Docs:     [Documentation]       git commit -m "Docs: Update setup guide"
Style:    [Formatting/CSS]      git commit -m "Style: Add responsive grid layout"
Test:     [Tests]               git commit -m "Test: Add dashboard unit tests"
WIP:      [Work in progress]    git commit -m "WIP: Dashboard redesign 60% done"
Sync:     [Synchronization]     git commit -m "Sync: Merge changes from PC Client"
Hotfix:   [Emergency fix]       git commit -m "Hotfix: Critical bug in production"
```

**Good vs Bad Commits:**

```
✅ GOOD:
- "Feat: Add admin dashboard with role-based views"
- "Fix: Correct timezone calculation for SPD dates"
- "Refactor: Extract database queries to service layer"

❌ BAD:
- "update"
- "fix bug"
- "changes"
- "asdf"
```

---

## ⚠️ Conflict Resolution

### If Merge Conflict Occurs

```powershell
# When you see:
# error: Your local changes to the following files would be overwritten by merge:
#   resources/views/livewire/dashboard.blade.php

# Step 1: Stash your changes (temporary save)
git stash

# Step 2: Pull latest
git pull origin main

# Step 3: Re-apply your changes
git stash pop

# Step 4: Resolve conflicts manually
# Edit file, find <<<<<<< and >>>>>>>
# Keep what you need, delete conflict markers

# Step 5: Stage & commit
git add .
git commit -m "Resolve merge conflict in dashboard view"
git push origin main
```

### Best Practice: Prevent Conflicts

```powershell
# ✅ Better strategy: Assign different files to each PC

# PC Server handles:
# - PHP components (app/Livewire/*.php)
# - Database logic (app/Models/*, migrations)
# - API routes (routes/api.php)

# PC Client handles:
# - Blade views (resources/views/*)
# - CSS/Tailwind (resources/css/*)
# - Frontend logic (resources/js/*)

# THEN: Sync via GitHub commits
```

---

## 📈 Monitoring Real-Time Sync

### Check Latest Commits

```powershell
# View last 10 commits
git log --oneline -10

# See WHO made changes (if multiple devs)
git log --author="Developer Name" --oneline -5

# See WHEN changes were pushed
git log --pretty=format:"%h - %an, %ar : %s"

# See what changed in specific file
git log -p app/Livewire/DashboardEnhanced.php | head -50
```

### Monitor GitHub

```bash
# Via GitHub CLI
gh pr list                    # Check pull requests
gh repo view --web            # Open repo in browser

# Via terminal
git remote -v                 # Verify GitHub URL
git branch -a                 # List all branches
```

---

## 🚀 Advanced: GitHub Actions (Optional)

Untuk full automation, setup **GitHub Actions** workflow:

```yaml
# .github/workflows/sync.yml
name: Auto-Sync Check
on: [push, pull_request]

jobs:
  sync:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Check PHP Syntax
        run: php -l app/**/*.php
      - name: Check Laravel Config
        run: php artisan config:list
```

---

## 📋 Quick Reference Card

Print this & keep it visible:

```
╔═══════════════════════════════════════════════════════╗
║      REAL-TIME SYNC WORKFLOW - 2 PC SETUP            ║
╠═══════════════════════════════════════════════════════╣
║                                                       ║
║  START:    git pull origin main                      ║
║  EDIT:     [make your changes]                       ║
║  BEFORE:   git add .                                 ║
║  COMMIT:   git commit -m "Feat: ..."                 ║
║  PUSH:     git push origin main                      ║
║  VERIFY:   git log --oneline -1                      ║
║  SWITCH:   [move to other PC]                        ║
║  PULL:     git pull origin main                      ║
║                                                       ║
║  MUST: All changes pushed before switching PC        ║
║  MUST: Pull first when starting on new PC            ║
║  MUST: Status clean before switching                 ║
║                                                       ║
╚═══════════════════════════════════════════════════════╝
```

---

## 🆘 Troubleshooting

### Problem: "Working tree has uncommitted changes"

```powershell
# Check what's uncommitted
git status

# Solution 1: Commit them
git add .
git commit -m "..."
git push origin main

# Solution 2: Discard them (DANGEROUS!)
git checkout -- .
```

### Problem: "Your branch is behind 'origin/main' by X commits"

```powershell
# Simply pull
git pull origin main

# If conflict:
# - Edit conflicted files
# - git add .
# - git commit -m "Resolve conflict"
# - git push origin main
```

### Problem: Push failed after large edit

```powershell
# Check file sizes
Get-ChildItem -Recurse -File | Sort-Object Length -Descending | Select-Object FullName, Length -First 10

# If files too large (>100MB):
# - Don't commit binary files
# - Use .gitignore to exclude

# Solution:
git reset HEAD [large-file]
echo "[large-file]" >> .gitignore
git add .gitignore
git commit -m "Add large file to gitignore"
git push origin main
```

---

## ✅ Final Checklist: Real-Time Sync Ready

- [x] Both PCs have git configured
- [x] Both PCs can access GitHub
- [x] git pull works from both PCs
- [x] git push works from both PCs
- [x] Commit convention established
- [x] Team knows the workflow
- [x] No uncommitted files before switching
- [x] Conflicts handled properly
- [x] GitHub as single source of truth
- [x] Documentation available to team

---

## 📞 Support

**Problem?**
1. Check git status: `git status`
2. Review workflow: See section "Daily Workflow"
3. Check GitHub: Latest commit should be visible
4. Ask: "Is everything committed and pushed?"

**Emergency** (completely stuck):
```powershell
# Last resort: Start fresh
git fetch origin
git reset --hard origin/main
# Then pull latest
git pull origin main
```

---

**Last Updated**: January 31, 2026  
**Status**: ✅ Active & Enforced  
**Next Review**: When team size increases
