# Git Workflow: Single Developer, 2 PC Setup

## 📌 Overview
- **Developer**: 1 person
- **PCs**: 2 (192.168.1.16 Server & 192.168.1.11 Client)
- **Strategy**: Direct main branch (simple), upgrade to feature branches later
- **Sync**: GitHub (pull/push)

---

## 🚀 Daily Routine

### **STEP 1: Mulai Hari (PC Pertama)**

```powershell
cd C:\laragon\www\eSPPD

# Always pull first
git pull origin main

# Verify status
git status
# Should show: "Your branch is up to date with 'origin/main'."
```

### **STEP 2: Kerja & Edit**

Edit files sesuka hati:
- Blade templates
- Livewire components
- CSS/JavaScript
- PHP models/controllers
- Database migrations

### **STEP 3: Sebelum Pindah PC**

```powershell
# Stage semua changes
git add .

# Commit dengan deskripsi jelas
git commit -m "Feat: Dashboard cards layout and styling"
# atau
git commit -m "Fix: Login form mobile responsive"
# atau  
git commit -m "WIP: User management UI (in progress)"

# Push ke GitHub
git push origin main

# Verify (optional)
git log --oneline -3
```

---

### **STEP 4: Pindah ke PC Lain**

```powershell
cd C:\laragon\www\eSPPD

# Pull latest changes dari GitHub
git pull origin main

# Check apa yang berubah
git status

# Continue working
```

---

## 📋 Commit Message Convention

Agar history clean & trackable:

```
Feat:  [New feature]
Fix:   [Bug fix]
Refactor: [Code restructuring]
Docs:  [Documentation update]
Style: [CSS/formatting]
WIP:   [Work in progress, tidak final]
Hotfix: [Urgent fix]
```

### Examples:

```
✅ git commit -m "Feat: Add dashboard card component"
✅ git commit -m "Fix: Password visibility toggle bug"
✅ git commit -m "Refactor: Optimize database queries"
✅ git commit -m "Docs: Update README with setup guide"
✅ git commit -m "WIP: Dashboard redesign (70% complete)"
```

---

## ⚠️ PENTING: Avoid Conflicts

### **Jangan**:
```powershell
# ❌ Push tanpa pull dulu
git push origin main

# ❌ Edit file yang sama di 2 PC tanpa sync
# PC 1: Edit login.blade.php
# PC 2: Edit login.blade.php (tanpa pull dulu)
# RESULT: Conflict!

# ❌ Lupa commit sebelum pindah PC
```

### **Harus**:
```powershell
# ✅ Always pull first
git pull origin main

# ✅ Commit sebelum pindah PC
git add .
git commit -m "..."
git push origin main

# ✅ Pull ketika mulai di PC baru
git pull origin main

# ✅ Check status regular
git status
```

---

## 🔧 Troubleshooting

### **Case 1: Conflict ketika pull**

```powershell
# Error: "CONFLICT (content merge)"

# Option A: Accept incoming (dari GitHub)
git checkout --theirs [file-name]

# Option B: Accept local (PC saat ini)
git checkout --ours [file-name]

# Option C: Manual merge - edit file, fix konflik, then:
git add [file-name]
git commit -m "Resolve merge conflict"
git push origin main
```

### **Case 2: Forgot to pull, made changes**

```powershell
# Situation: Edit di PC 1, lupa push. Sekarang di PC 2, mau edit file yang sama

# Solution:
git fetch origin
git status
# Will show: "Your branch is behind 'origin/main' by X commits"

git pull origin main
# May have conflicts - resolve manually

# Then continue working
```

### **Case 3: Mau undo last commit**

```powershell
# Belum push
git reset --soft HEAD~1
git status  # Changes back to staging
git add .
git commit -m "Better message"

# Sudah push (berbahaya!)
git revert HEAD
# atau
git reset --hard HEAD~1 && git push origin main --force
# ⚠️ Hanya jika urgent
```

---

## 📊 Check History

```powershell
# View last 5 commits
git log --oneline -5

# View all commits by date
git log --graph --oneline --all

# View changes in specific file
git log -p [file-name] | head -50

# Who changed what
git blame [file-name]
```

---

## 🎯 Migration Plan (Future)

Ketika tim berkembang (2+ developers), switch ke:

```
main (production)
  ↑ merge dari
develop (integration)
  ↑ merge dari
feature/dashboard-redesign
feature/user-management
feature/reports
feature/api-optimization
```

Setup saat itu:
```powershell
git checkout -b develop
git push origin develop

# Set default branch di GitHub ke 'develop'
# Settings → Default branch → develop
```

---

## 📝 Quick Reference

| Action | Command |
|--------|---------|
| Start day | `git pull origin main` |
| Check status | `git status` |
| Stage changes | `git add .` |
| Commit | `git commit -m "message"` |
| Push | `git push origin main` |
| View history | `git log --oneline -5` |
| Before switching PC | `git add . && git commit -m "..." && git push` |
| After switching PC | `git pull origin main` |

---

## ✅ Checklist: Sebelum Pindah PC

- [ ] Semua changes di-commit
- [ ] Git status clean ("working tree clean")
- [ ] Sudah push ke GitHub
- [ ] Verify: `git log --oneline -1` shows latest commit

---

**Catatan**: Workflow ini simple & fast untuk 1 developer. Jika join developer baru, upgrade ke feature branches + code review process.
