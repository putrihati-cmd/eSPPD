# 2-PC Development: Quick Start Sync Guide

**TL;DR** - Harus diingat ketika bekerja dengan 2 PC:

---

## 🎯 The 5-Step Rule (NEVER BREAK THIS!)

```powershell
# Step 1: PULL (sebelum mulai kerja)
git pull origin main

# Step 2: EDIT (modify files)
# [edit dashboard.php, routes, etc]

# Step 3: ADD (stage all changes)
git add .

# Step 4: COMMIT & PUSH (sebelum pindah PC)
git commit -m "Feat: Describe what you did"
git push origin main

# Step 5: VERIFY (confirm push success)
git log --oneline -1
```

---

## ⚡ Real Examples

### Example 1: Edit Component

```powershell
# PC SERVER
git pull origin main
notepad app/Livewire/DashboardEnhanced.php
# ... edit component logic ...
git add .
git commit -m "Feat: Add dashboard metrics calculation"
git push origin main
✅ Pushed to GitHub

# PC CLIENT
git pull origin main  # Gets latest component
# Verify in VS Code - changes appear
```

### Example 2: Create View

```powershell
# PC CLIENT
git pull origin main
notepad resources/views/livewire/dashboard.blade.php
# ... create beautiful UI ...
git add .
git commit -m "Feat: Build dashboard UI with Tailwind"
git push origin main
✅ Pushed to GitHub

# PC SERVER
git pull origin main  # Gets latest UI
# Test in browser - see beautiful interface
```

---

## ❌ What NOT To Do

```powershell
# ❌ DON'T: Switch PC without committing
git status  # Shows modified files
# [switch to other PC] ← BIG MISTAKE!

# ❌ DON'T: Push without pulling first
git push origin main  # May fail if others pushed

# ❌ DON'T: Edit same file on 2 PCs simultaneously
# PC 1: Edit dashboard.blade.php
# PC 2: Also edit dashboard.blade.php
# RESULT: MERGE CONFLICT!

# ❌ DON'T: Forget to commit before switching
# PC 1: Made changes
# [forgot to git add/commit]
# [switched to PC 2]
# RESULT: Changes stuck in PC 1!
```

---

## ✅ The Safe Workflow

```
PC Server Work
      ↓
    COMMIT ← MOST IMPORTANT!
      ↓
    PUSH ← MUST DO BEFORE SWITCHING!
      ↓
    SWITCH TO PC CLIENT
      ↓
    PULL ← Get latest code
      ↓
PC Client Work
      ↓
    COMMIT ← Again!
      ↓
    PUSH ← Before going back!
      ↓
    SWITCH TO PC SERVER
      ↓
    PULL ← Get latest code
      ↓
    [cycle repeats]
```

---

## 🚨 Emergency Fixes

### "I forgot to commit!"

```powershell
# Check status
git status

# If not committed:
git add .
git commit -m "WIP: Work in progress"
git push origin main

# NOW it's safe to switch PC
```

### "Merge conflict!"

```powershell
# This happens when 2 PCs edited same file
# Error: "CONFLICT (content merge)"

# Fix it:
git status  # Shows which files conflict
notepad [conflicted-file]
# Find <<<<<< and >>>>>
# Keep what you need, delete markers

git add .
git commit -m "Resolve conflict"
git push origin main
```

### "Pull failed!"

```powershell
# Maybe you edited something locally
git status

# If yes: commit first
git add .
git commit -m "..."

# Then pull
git pull origin main
```

---

## 📋 Status Checks

```powershell
# Good status (SAFE to switch PC)
git status
# Output: "nothing to commit, working tree clean"
# ✅ OK to switch

# Bad status (UNSAFE to switch PC)
git status
# Output: "Changes not staged for commit: modified: file.php"
# ❌ MUST commit first!
```

---

## 🔍 Verify Changes Synced

```powershell
# After push (PC 1)
git log --oneline -1
# Shows: abc1234 Feat: Dashboard component

# After pull (PC 2)
git log --oneline -1
# Shows: abc1234 Feat: Dashboard component
# ✅ Same commit = synced!
```

---

## 📊 Comparison: PC Server vs PC Client

| Action | PC Server (16) | PC Client (11) | Notes |
|--------|----------------|----------------|-------|
| Pull latest | `git pull` | `git pull` | Same always |
| Commit work | `git commit -m "..."` | `git commit -m "..."` | Same always |
| Push to GitHub | `git push` | `git push` | Same always |
| Database | esppd (local) | esppd_client (local) | DIFFERENT databases |
| App URL | 192.168.1.16:8083 | localhost:8000 | DIFFERENT URLs |
| Code | Same from GitHub | Same from GitHub | ALWAYS in sync |

---

## 🎯 Success Indicators

✅ **You're doing it right if:**
- `git status` always shows "working tree clean" before switching PC
- Latest commit appears in both PCs after pull
- Conflicts rarely happen
- Code is always in sync via GitHub

❌ **Problem indicators:**
- Frequent merge conflicts
- "Uncommitted changes" errors
- Lost work from forgotten commits
- Different code on 2 PCs

---

## 💾 QUICK COPY-PASTE

```powershell
# Always use this sequence
git pull origin main
# [make edits]
git add .
git commit -m "Feat: [description]"
git push origin main
# [switch PC]
```

**NEVER deviate from this pattern!**

---

**Created**: January 31, 2026  
**For**: 2-PC simultaneous development  
**Keep this open**: When switching between PCs!
