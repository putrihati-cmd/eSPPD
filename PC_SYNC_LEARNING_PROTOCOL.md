# 📚 PC Sync Learning Protocol

**Purpose:** Maintain zero miscommunication between PC1 and PC2 by systematically learning and tracking all changes.

## Core Principle

**Every time you pull from GitHub:**
1. ✅ Review what changed
2. ✅ Understand why it changed
3. ✅ Document the current state
4. ✅ Communicate context for next edits

## Standard Sync Workflow

### Step 1: Before Any Edit - LEARN Current State
```powershell
# 1a. Check git status
git status
git log --oneline -5

# 1b. Identify what PC2 changed
git diff origin/main -- app/Console/Commands
git diff origin/main -- .env

# 1c. Read key files
cat MULTI_PC_SYNC_IMPLEMENTATION_CHECKLIST.md
```

### Step 2: During Edit - DOCUMENT Changes
```powershell
# Before committing:
# - Describe WHAT changed
# - Explain WHY it changed
# - Note any SIDE EFFECTS

git commit -m "feat: [area] [what] because [why]"
# Example: "feat: sync-command fix encoding because pg_dump UTF-8"
```

### Step 3: After Push - NOTIFY & SUMMARIZE
```
Summary of changes:
- Modified: app/Console/Commands/SyncDbToProduction.php (line XX: reason)
- Updated: .env (AUTO_DB_SYNC config)
- Added: new documentation
- Side effects: requires cache clear, needs PostgreSQL 12+
```

---

## Key Files to Always Review

### Configuration Files
| File | Purpose | What to Check |
|------|---------|---------------|
| `.env` | Environment config | PRODUCTION_DB_PASSWORD, AUTO_DB_SYNC status |
| `.env.example` | Template | If structure changed |
| `config/app.php` | App config | If new configs added |

### Code Files
| File | Purpose | What to Check |
|------|---------|---------------|
| `app/Console/Commands/SyncDbToProduction.php` | Database sync logic | Command options, error handling |
| `app/Listeners/SyncDatabaseAfterArtisan.php` | Event listener | Triggered commands list |
| `artisan.ps1` | PowerShell wrapper | Command flow, error handling |
| `artisan-sync` | Bash wrapper | Command flow, error handling |

### Documentation Files
| File | Purpose | What to Check |
|------|---------|---------------|
| `MULTI_PC_DB_SYNC_SETUP.md` | Complete guide | New requirements, troubleshooting |
| `MULTI_PC_SYNC_QUICKREF.md` | Quick reference | Updated commands, shortcuts |
| `MULTI_PC_SYNC_IMPLEMENTATION_CHECKLIST.md` | Status & checklist | Current implementation status |

---

## Git Diff Learning Commands

### Check what changed SINCE last pull
```powershell
# What PC2 changed
git log --oneline -10
git log --name-only origin/main -5

# Detailed changes in specific file
git diff HEAD~1 HEAD -- app/Console/Commands/SyncDbToProduction.php
git diff HEAD~1 HEAD -- .env
```

### Check what YOU will change
```powershell
# Before pushing
git diff --cached
git diff --stat

# What files will be affected
git status --short
```

### Compare PC1 vs PC2 state
```powershell
# Before pull
git fetch origin main
git diff HEAD origin/main

# After pull
git status
```

---

## Baseline State Documentation

### Current Implementation (Feb 1, 2026)

**Files Created:**
- ✅ `app/Console/Commands/SyncDbToProduction.php` - Main sync logic
- ✅ `app/Listeners/SyncDatabaseAfterArtisan.php` - Event listener
- ✅ `artisan.ps1` - Windows wrapper
- ✅ `artisan-sync` - Linux wrapper
- ✅ `.env` - With production credentials

**Configuration Status:**
- ✅ `AUTO_DB_SYNC=true` (enabled)
- ✅ `PRODUCTION_HOST=192.168.1.27`
- ✅ `PRODUCTION_DB_PASSWORD=Esppd@123456`

**Documentation:**
- ✅ `MULTI_PC_DB_SYNC_SETUP.md`
- ✅ `MULTI_PC_SYNC_QUICKREF.md`
- ✅ `MULTI_PC_SYNC_IMPLEMENTATION_CHECKLIST.md`

**Last Commits:**
- `config: set production database password for multi-pc sync`
- `docs: add multi-pc sync implementation checklist`
- `feat: complete multi-pc database sync implementation with setup scripts`

---

## Change Tracking Template

When you get changes from PC2, fill this out:

```
📥 CHANGES RECEIVED FROM PC2
═══════════════════════════════════════

Date: YYYY-MM-DD
Git Commits: [list commit hashes]

📝 CHANGES MADE:
─ File: [path]
  What: [describe change]
  Why: [reason for change]
  Side effects: [any impacts]

─ File: [path]
  What: [describe change]
  Why: [reason for change]
  Side effects: [any impacts]

⚠️  IMPORTANT NOTES:
- [Any breaking changes?]
- [New dependencies?]
- [Config changes?]
- [Database changes?]

✅ VERIFICATION:
- [ ] Read all changed files
- [ ] Understood the changes
- [ ] Checked for conflicts
- [ ] No side effects on my work
- [ ] Ready for next edit

📌 FOR NEXT EDIT BY OTHER PC:
- [Important context]
- [Files being worked on]
- [Expected changes]
```

---

## Pre-Edit Checklist (ALWAYS DO THIS)

Before making ANY edit to code files:

```powershell
# 1. Get latest from GitHub
git pull origin main

# 2. Check what's new
git log --oneline -3
git diff HEAD~3 HEAD --name-only

# 3. Review key changed files
git show HEAD:app/Console/Commands/SyncDbToProduction.php | head -50
git diff HEAD~1 HEAD -- .env | grep PRODUCTION

# 4. Read relevant documentation
Get-Content MULTI_PC_SYNC_IMPLEMENTATION_CHECKLIST.md

# 5. Verify current state
php artisan db:sync-to-production --help

# 6. Only THEN start editing
```

---

## Communication Protocol

### When PC2 Makes Changes

**What PC2 should say:**
```
Changed files:
- app/Console/Commands/SyncDbToProduction.php:
  * Line 50-60: Fixed encoding issue for UTF-8 dumps
  * Line 120: Added retry logic for SSH
  
- .env:
  * Added PRODUCTION_DEBUG=false for security
  
Side effects:
- Requires PostgreSQL 12+ (UTF-8 support)
- SSH timeout now 30 seconds (was 10)
- Database dump file encoding is UTF-8 only

For PC1:
- Review the encoding changes before using
- Test with --dry-run first
```

### What PC1 Should Do

```powershell
# 1. Pull & read summary
git pull origin main

# 2. Review specific changes
git diff HEAD~1 HEAD

# 3. Test before using
php artisan db:sync-to-production --dry-run

# 4. Acknowledge understanding
# (reply or create marker in git)
```

---

## Conflict Prevention Strategy

### Rule 1: Document Current State
```powershell
# Before big change, document it
git commit -m "docs: [area] current state before [change name]"
```

### Rule 2: Use Feature Branches
```powershell
# For major changes
git checkout -b feature/improve-sync-reliability
# ... make changes ...
git push -u origin feature/improve-sync-reliability

# Then PC2 can review before merging
```

### Rule 3: Explicit Commit Messages
```
# BAD
git commit -m "update sync"

# GOOD
git commit -m "fix: sync-command handle connection timeout with retry logic

- Added 3 retry attempts with exponential backoff
- Timeout increased from 10s to 30s for slow networks
- Logs connection failures for debugging

Affects: SyncDbToProduction.php
Tests: Manual --dry-run verification needed
"
```

---

## Learning Checklist

Every time you sync, check these boxes:

```powershell
# Files changed
- [ ] Read all modified .php files
- [ ] Understood logic changes
- [ ] Checked for new dependencies
- [ ] Verified error handling

# Configuration
- [ ] Read .env changes
- [ ] Understood new settings
- [ ] Checked for breaking changes
- [ ] Updated local .env if needed

# Documentation
- [ ] Read updated docs
- [ ] Understood new procedures
- [ ] Checked for outdated info
- [ ] Noted new troubleshooting tips

# Database
- [ ] Checked for migrations
- [ ] Understood schema changes
- [ ] Verified seeders if changed
- [ ] No conflicts with local DB

# Verification
- [ ] Code runs without errors
- [ ] Tests pass (if any)
- [ ] Dry-run test successful
- [ ] No console warnings
```

---

## Zero Miscommunication Guarantees

### What I Will Do (Agent)
1. ✅ Always `git pull` before suggesting changes
2. ✅ Read all recently changed files
3. ✅ Understand context from commits & docs
4. ✅ Never assume - verify current state
5. ✅ Provide detailed change explanations
6. ✅ Test changes before committing

### What You Should Do (User)
1. ✅ Read change summaries from other PC
2. ✅ Push with clear commit messages
3. ✅ Document why changes were made
4. ✅ Note any side effects
5. ✅ Flag breaking changes early
6. ✅ Test before accepting changes

### Sync Ritual (Do This Every Time)
```
FROM PC2 → GitHub
  ↓
[REVIEW CHANGES]
  ↓
Pull + Read Diffs
  ↓
Understand Context
  ↓
Test Locally
  ↓
Safe to Use ✓
```

---

## Quick Reference

### When PC2 Pushes Changes
**My Response:**
```powershell
# 1. Fetch all changes
git fetch origin main

# 2. Review what changed
git log origin/main..HEAD
git diff HEAD origin/main

# 3. Read and understand
# (Read all changed files carefully)

# 4. Summarize for user
# "PC2 changed X because Y, affects Z"

# 5. Merge safely
git pull origin main
```

### When I Make Changes
**My Protocol:**
```powershell
# 1. Get latest first
git pull origin main

# 2. Review PC2's recent work
git log -5 --oneline

# 3. Make isolated changes
# (One feature at a time)

# 4. Write clear commit message
git commit -m "area: what because why"

# 5. Push with summary
# (Explain changes + side effects)

# 6. Document for PC2
# (Create context for their next edit)
```

---

## Example: Learning from PC2 Changes

**Scenario:** PC2 updates SyncDbToProduction.php

**My Learning Process:**
```powershell
# 1. Check what changed
git diff HEAD~1 HEAD -- app/Console/Commands/SyncDbToProduction.php

# 2. Read the file
cat app/Console/Commands/SyncDbToProduction.php | head -100

# 3. Check commit message
git log -1 --format=%B

# 4. Test the change
php artisan db:sync-to-production --help
php artisan db:sync-to-production --dry-run

# 5. Read related documentation
cat MULTI_PC_DB_SYNC_SETUP.md | grep -A 10 "SyncDbToProduction"

# 6. Note impact
# "PC2 added X parameter. Affects workflow because Y. 
#  Need to update docs Z accordingly."
```

---

## Documentation Standard

All commits must include:
1. **What:** What changed
2. **Why:** Reason for change
3. **How:** How it works
4. **Effects:** Side effects
5. **Test:** How to verify

```git
feat: add retry logic to database sync

Changes:
- Added exponential backoff retry (3 attempts)
- Increased SSH timeout from 10s to 30s
- New config: DB_SYNC_MAX_RETRIES

Why:
- Improved reliability on slow networks
- Production server sometimes slow during peak hours

How:
- Retries with 2s, 4s, 8s delays
- Logs each attempt for debugging

Side Effects:
- Sync now takes longer on failures (up to 30s)
- Requires SSH connectivity verification

Testing:
- Manual: php artisan db:sync-to-production --dry-run
- Check logs for retry messages in storage/logs/
```

---

## Status: LEARNING PROTOCOL ACTIVE ✅

From now on, every sync will include:
- ✅ Complete file review
- ✅ Change understanding
- ✅ Context documentation
- ✅ Side effect analysis
- ✅ Zero miscommunication guarantee

**Both PCs will always be synchronized in knowledge, not just code!** 📚

