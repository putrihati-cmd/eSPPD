#!/bin/bash
# backup.sh - Backup database and uploads

set -e

# Configuration
BACKUP_DIR="/var/backups/esppd"
APP_DIR="/var/www/esppd"
DB_NAME="esppd"
DB_USER="esppd_user"
DB_PASS="${DB_PASSWORD:-password}"
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "=========================================="
echo "eSPPD Backup Script - $TIMESTAMP"
echo "=========================================="

# Database backup
echo ">>> Backing up database..."
DB_BACKUP_FILE="$BACKUP_DIR/db_backup_$TIMESTAMP.sql.gz"
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $DB_BACKUP_FILE
echo "Database backup: $DB_BACKUP_FILE"

# Storage backup (uploads)
echo ">>> Backing up uploads..."
STORAGE_BACKUP_FILE="$BACKUP_DIR/storage_backup_$TIMESTAMP.tar.gz"
tar -czf $STORAGE_BACKUP_FILE -C $APP_DIR storage/app/public
echo "Storage backup: $STORAGE_BACKUP_FILE"

# Encrypt backups (optional - requires GPG key)
if [ -n "$BACKUP_GPG_KEY" ]; then
    echo ">>> Encrypting backups..."
    gpg --encrypt --recipient $BACKUP_GPG_KEY $DB_BACKUP_FILE
    gpg --encrypt --recipient $BACKUP_GPG_KEY $STORAGE_BACKUP_FILE
    rm $DB_BACKUP_FILE $STORAGE_BACKUP_FILE
fi

# Clean old backups
echo ">>> Cleaning old backups (older than $RETENTION_DAYS days)..."
find $BACKUP_DIR -type f -mtime +$RETENTION_DAYS -delete

# Calculate backup size
BACKUP_SIZE=$(du -sh $BACKUP_DIR | cut -f1)
echo ">>> Total backup size: $BACKUP_SIZE"

echo "=========================================="
echo "Backup complete!"
echo "=========================================="
