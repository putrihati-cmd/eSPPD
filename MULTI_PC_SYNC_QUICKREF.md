# 📋 Multi-PC Database Sync - Quick Reference

## Current Setup Status

✅ **Files Setup:**
- `artisan.ps1` - PowerShell wrapper
- `artisan-sync` - Bash wrapper  
- `app/Console/Commands/SyncDbToProduction.php` - Sync command
- `.env` - Configuration configured
- Documentation files ready

## Step 1: Set Production DB Password

Edit `.env` and set `PRODUCTION_DB_PASSWORD`:

```powershell
# Replace with actual production PostgreSQL password
PRODUCTION_DB_PASSWORD=your_production_postgres_password
```

## Step 2: Verify Everything Works

### Test PostgreSQL Tools
```powershell
pg_dump --version
psql --version
```

### Test SSH Connection
```powershell
ssh tholib_server@192.168.1.27 "echo OK"
# Should output: OK
```

### Test Database Sync Command
```powershell
# Dry-run (no changes applied)
php artisan db:sync-to-production --dry-run

# Actually sync
php artisan db:sync-to-production
```

## Step 3: Enable Auto-Sync

Change in `.env`:
```env
AUTO_DB_SYNC=false   # Change this to true
AUTO_DB_SYNC=true    # Like this
```

## Step 4: Use Wrapper Scripts

### Windows (PowerShell)

After PowerShell restart, use:
```powershell
artisan migrate              # Will auto-sync to production
artisan db:seed
artisan make:migration name_table
```

Or without alias:
```powershell
.\artisan.ps1 migrate        # Always works
.\artisan.ps1 db:seed
```

### Linux/Mac

```bash
./artisan-sync migrate       # Auto-syncs to production
./artisan-sync db:seed
```

## How It Works

```
PC1 or PC2 runs:
  artisan migrate
         ↓
  Command executes
         ↓
  Check: AUTO_DB_SYNC=true?
         ↓ YES
  php artisan db:sync-to-production
         ↓
  Database synced to production ✓
```

## Common Commands

```powershell
# Manual sync (safe, preview first)
php artisan db:sync-to-production --dry-run   # Preview
php artisan db:sync-to-production             # Actually sync

# Auto-sync commands (if AUTO_DB_SYNC=true)
artisan migrate                  # Auto-syncs after
artisan migrate:rollback         # Auto-syncs after
artisan db:seed                  # Auto-syncs after
artisan db:seed --class=UserSeeder # Auto-syncs after
artisan make:migration table_name  # Auto-syncs after

# Laravel artisan (won't sync)
artisan tinker
artisan serve
```

## Troubleshooting

### PostgreSQL not found
```powershell
# Add to PATH
$env:PATH += ";C:\laragon\bin\postgresql\bin"

# Verify
pg_dump --version
```

### SSH connection failed
```powershell
# Check SSH works
ssh tholib_server@192.168.1.27 "whoami"

# Check SSH keys
ssh-add -l

# If no keys, generate
ssh-keygen -t rsa -b 4096 -f $env:USERPROFILE\.ssh\id_rsa
```

### Sync command not found
```powershell
# Make sure files synced from GitHub
git pull origin main

# Clear Laravel cache
php artisan optimize:clear
php artisan config:cache
```

### Disk space on production
```powershell
# Check space
ssh tholib_server@192.168.1.27 "df -h"

# Clean old dumps if needed
ssh tholib_server@192.168.1.27 "rm /tmp/db_sync_*.sql 2>/dev/null; echo 'Cleaned'"
```

## Testing Workflow

### Test 1: Manual Sync (Safe)
```powershell
# Try dry-run first
php artisan db:sync-to-production --dry-run

# If it looks good, actually sync
php artisan db:sync-to-production
```

### Test 2: Auto-Sync After Migrate
```powershell
# Enable auto-sync in .env
# AUTO_DB_SYNC=true

# Then run
.\artisan.ps1 make:migration test_table

# Watch for:
# ✓ Migration file created
# 🔄 Auto-syncing database to production...
```

### Test 3: Both PCs Synced
```powershell
# PC1: Run migration
artisan make:migration add_column_to_users

# PC2 (wait 5 seconds for cron)
# PC2: Pull latest
git pull origin main

# PC2: Database should have latest schema ✓
```

## File Permissions (if needed)

```powershell
# PowerShell (Windows)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Linux
chmod +x ./artisan-sync
chmod +x ./setup-multi-pc-sync.ps1
```

## System Requirements

- **Windows:** PowerShell 5.1+, PostgreSQL tools
- **Linux/Mac:** Bash, PostgreSQL client tools
- **Both:** SSH access to production (192.168.1.27)
- **Both:** Git configured for GitHub

## Emergency: Disable Sync

If sync is causing issues:

```powershell
# Temporary (current session only)
$env:AUTO_DB_SYNC = "false"

# Permanent (edit .env)
AUTO_DB_SYNC=false

# Then verify
php artisan config:cache
```

## Full Setup Checklist

- [ ] Set `PRODUCTION_DB_PASSWORD` in `.env`
- [ ] Test: `pg_dump --version`
- [ ] Test: `ssh tholib_server@192.168.1.27 "echo OK"`
- [ ] Test: `php artisan db:sync-to-production --dry-run`
- [ ] Set `AUTO_DB_SYNC=true` in `.env`
- [ ] PowerShell profile updated (restart PowerShell)
- [ ] Test: `artisan migrate` (or `.\artisan.ps1 migrate`)
- [ ] Verify sync completed with message: "✅ Database sync completed successfully!"
- [ ] Other PC: `git pull origin main`
- [ ] Other PC: Verify database has latest schema

## Next Steps

1. **Set the production DB password in `.env`**
2. **Run:** `php artisan db:sync-to-production --dry-run`
3. **Enable:** `AUTO_DB_SYNC=true` 
4. **Restart PowerShell**
5. **Start using:** `artisan migrate` (will auto-sync!)

---

**Both PCs will now stay synchronized with production database automatically!** 🎉

