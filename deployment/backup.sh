#!/bin/bash

# backup.sh
# Automated Backup Script for eSPPD

BACKUP_DIR="/var/backups/esppd"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_CONTAINER="esppd-db"
DB_USER="postgres" # Change as needed
DB_NAME="esppd"

mkdir -p $BACKUP_DIR

echo "--- Starting Backup $TIMESTAMP ---"

# 1. Database Backup
echo "Backing up Database..."
docker exec -t $DB_CONTAINER pg_dump -U $DB_USER $DB_NAME > "$BACKUP_DIR/db_$TIMESTAMP.sql"

# 2. Compress
gzip "$BACKUP_DIR/db_$TIMESTAMP.sql"

# 3. Storage Backup (Optional, if not using S3)
# tar -czf "$BACKUP_DIR/storage_$TIMESTAMP.tar.gz" -C /var/www/esppd/storage/app/public .

# 4. Cleanup (Keep last 7 days)
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete

echo "--- Backup Complete: $BACKUP_DIR/db_$TIMESTAMP.sql.gz ---"
