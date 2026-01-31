# 🔄 Multi-PC Database Sync Setup

**Purpose:** Keep database synchronized across 2 different PCs running VS Code locally, with automatic push to production.

## Overview

When 2 developers work on different PCs:
- PC1 runs artisan command → auto-syncs to production
- PC2 runs artisan command → auto-syncs to production
- Both stay synchronized with production DB

## Architecture

```
PC1 (VS Code)                PC2 (VS Code)
     ↓                            ↓
  artisan.ps1              artisan.ps1
     ↓                            ↓
php artisan command       php artisan command
     ↓                            ↓
[Command runs]           [Command runs]
     ↓                            ↓
Auto-sync? ✓             Auto-sync? ✓
     ↓                            ↓
     └────────→ Production DB ←────┘
```

## Setup Instructions

### 1. Configure Environment Variables

Edit `.env` file and set:

```env
# Production Server (already configured)
PRODUCTION_HOST=192.168.1.27
PRODUCTION_USER=tholib_server
PRODUCTION_DB_HOST=localhost
PRODUCTION_DB_NAME=esppd_production
PRODUCTION_DB_USER=postgres
PRODUCTION_DB_PASSWORD=<your_password>

# Enable auto-sync (set to true to enable)
AUTO_DB_SYNC=false
```

### 2. Install PostgreSQL Tools (Windows)

For `pg_dump` and `psql` commands to work:

```bash
# Option A: Laragon already includes PostgreSQL
# Just verify pg_dump is in PATH:
where pg_dump

# Option B: If needed, download PostgreSQL installer
# https://www.postgresql.org/download/windows/
# Add bin folder to PATH during installation
```

### 3. Test SSH Connection

Verify SSH connection to production server works:

```powershell
# Windows PowerShell
ssh tholib_server@192.168.1.27 "echo 'SSH connection OK'"

# Should output: SSH connection OK
```

### 4. Create SSH Key (if not already done)

```powershell
# Generate SSH key (one-time setup)
ssh-keygen -t rsa -b 4096 -f $env:USERPROFILE\.ssh\id_rsa

# Copy public key to production server
# Then test passwordless SSH works
```

## Usage

### Option 1: Manual Sync (Recommended for Testing)

```powershell
# Dry-run (preview without applying)
php artisan db:sync-to-production --dry-run

# Actually sync
php artisan db:sync-to-production
```

### Option 2: Auto-Sync After Artisan Commands

**Enable in .env:**
```env
AUTO_DB_SYNC=true
```

**Then use wrapper scripts:**

**Windows (PowerShell):**
```powershell
# Instead of: php artisan migrate
# Use: .\artisan.ps1 migrate

.\artisan.ps1 migrate
.\artisan.ps1 db:seed
.\artisan.ps1 make:migration create_users_table
```

**Linux/Mac:**
```bash
# Instead of: php artisan migrate
# Use: ./artisan-sync migrate

./artisan-sync migrate
./artisan-sync db:seed
```

### Option 3: Create Batch/Shell Script Alias

**Windows (add to .bashrc or PowerShell Profile):**
```powershell
# In PowerShell Profile ($PROFILE):
function artisan { & ".\artisan.ps1" @args }

# Then use: artisan migrate
```

**Linux (add to ~/.bashrc):**
```bash
alias artisan='./artisan-sync'

# Then use: artisan migrate
```

## Triggered Commands

Auto-sync triggers for these artisan commands:
- `migrate` / `migrate:rollback` / `migrate:refresh`
- `db:seed`
- `tinker`
- `make:migration`
- `make:model`
- `make:seeder`

## Command Options

```bash
# Dry-run: preview without applying changes
php artisan db:sync-to-production --dry-run

# With verbose output
php artisan db:sync-to-production -v
```

## Security Considerations

⚠️ **Important:**
1. Never commit `.env` file to git
2. SSH keys should be generated per PC
3. PostgreSQL password should be in `.env` only
4. Database dump files are temporary (auto-deleted)

## Troubleshooting

### "pg_dump not found"
```powershell
# Add PostgreSQL bin to PATH
$env:PATH += ";C:\laragon\bin\postgresql\bin"
```

### "SSH connection refused"
```powershell
# Test SSH connection
ssh -vvv tholib_server@192.168.1.27

# Verify SSH key is configured
ssh-add -l
```

### "Permission denied for database import"
```bash
# Make sure production user has correct permissions
# On production server:
sudo chown postgres:postgres /var/lib/postgresql
```

### "Disk space on production"
```bash
# Check disk space on production
ssh tholib_server@192.168.1.27 "df -h"

# If space is low, remove old dumps:
ssh tholib_server@192.168.1.27 "rm /tmp/db_sync_*.sql"
```

## Workflow Example

**PC1 Developer:**
```powershell
# Make schema changes
.\artisan.ps1 make:migration add_new_column_to_users

# Database auto-syncs to production ✓
```

**PC2 Developer (5 minutes later):**
```powershell
# Pull latest changes
git pull

# Database already synced from PC1 ✓
# Can work with latest schema immediately
```

## Monitoring

Check sync status:
```powershell
# View recent syncs in Laravel log
tail -f storage/logs/laravel.log | grep "Database sync"

# Or check command history
history | grep "db:sync-to-production"
```

## Disable/Enable Auto-Sync

To temporarily disable without editing .env:

```powershell
# Method 1: Comment out AUTO_DB_SYNC in .env
# AUTO_DB_SYNC=false

# Method 2: Use manual command only
php artisan db:sync-to-production

# Method 3: Set in PowerShell temporarily
$env:AUTO_DB_SYNC = "false"
```

## Performance Notes

- Initial sync: 2-5 seconds (depends on database size)
- Typical sync: 1-2 seconds
- Network latency: ~100-200ms to production server
- If sync is slow, use `--dry-run` to diagnose

## Next Steps

1. ✅ Install PostgreSQL tools
2. ✅ Test SSH connection
3. ✅ Set `PRODUCTION_DB_PASSWORD` in .env
4. ✅ Run manual sync: `php artisan db:sync-to-production --dry-run`
5. ✅ Enable `AUTO_DB_SYNC=true` in .env
6. ✅ Start using `artisan.ps1` or `artisan-sync` wrapper

## Support

If sync fails:
1. Check `.env` credentials are correct
2. Verify SSH access works
3. Run with `--dry-run` first
4. Check production server disk space
5. Review Laravel logs in `storage/logs/`

