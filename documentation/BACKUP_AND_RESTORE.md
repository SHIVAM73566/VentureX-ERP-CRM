# Backup and Restore Guide

Procedures for backing up and restoring VentureX ERP & CRM data and files.

---

## What to Back Up

### Critical Data

| Component | Location | Priority |
|-----------|----------|----------|
| Database | MySQL `VENTUREX_ERP` | Critical |
| Environment file | `.env` | Critical |
| User uploads | `storage/app/public` | High |
| Configuration | `config/` | High |
| Custom templates | `resources/views/` | Medium |

### Not Required in Backup

These can be regenerated:
- `vendor/` (restore via `composer install`)
- `node_modules/` (restore via `npm install`)
- `public/build/` (restore via `npm run build`)
- `storage/logs/` (logs can be regenerated)
- `bootstrap/cache/` (cache can be regenerated)

---

## Database Backup

### Manual Backup

```bash
mysqldump -u username -p --single-transaction --routines --triggers VENTUREX_ERP > backup_$(date +%Y%m%d_%H%M%S).sql
```

**Options explained:**
- `--single-transaction`: Consistent backup without locking
- `--routines`: Include stored procedures
- `--triggers`: Include triggers

### Automated Backup (Cron)

Add to crontab:

```bash
# Daily backup at 2 AM
0 2 * * * mysqldump -u username -p'password' --single-transaction --routines --triggers VENTUREX_ERP | gzip > /var/backups/VentureX-ERP/db_$(date +\%Y\%m\%d).sql.gz

# Weekly full backup on Sundays
0 3 * * 0 mysqldump -u username -p'password' --single-transaction --routines --triggers --all-databases | gzip > /var/backups/VentureX-ERP/full_$(date +\%Y\%m\%d).sql.gz
```

### Backup with mysqldump

```bash
mysqldump -u your_username -p --single-transaction --routines --triggers my_erp > backup_$(date +%Y%m%d_%H%M%S).sql
```

Or use the backup script from the [Full Backup Script](#full-backup-script) section above for automation.

---

## File Backup

### Manual File Backup

```bash
cd /var/www
tar -czf VentureX-ERP-files-$(date +%Y%m%d).tar.gz \
  --exclude='VentureX-ERP/vendor' \
  --exclude='VentureX-ERP/node_modules' \
  --exclude='VentureX-ERP/public/build' \
  --exclude='VentureX-ERP/storage/logs' \
  --exclude='VentureX-ERP/bootstrap/cache' \
  VentureX-ERP
```

### Backup User Uploads Only

```bash
tar -czf uploads-$(date +%Y%m%d).tar.gz /var/www/VentureX-ERP/storage/app/public
```

### Automated File Backup (Cron)

```bash
# Daily file backup at 1 AM
0 1 * * * tar -czf /var/backups/VentureX-ERP/files_$(date +\%Y\%m\%d).tar.gz --exclude='vendor' --exclude='node_modules' --exclude='public/build' --exclude='storage/logs' --exclude='bootstrap/cache' /var/www/VentureX-ERP
```

---

## Full Backup Script

Create `/var/scripts/backup-VentureX-ERP.sh`:

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/var/backups/VentureX-ERP"
DB_NAME="VENTUREX_ERP"
DB_USER="username"
DB_PASS="password"
APP_DIR="/var/www/VentureX-ERP"
RETENTION_DAYS=30

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Date suffix
DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
echo "Backing up database..."
mysqldump -u "$DB_USER" -p"$DB_PASS" --single-transaction --routines --triggers "$DB_NAME" | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# File backup
echo "Backing up files..."
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" \
  --exclude="$APP_DIR/vendor" \
  --exclude="$APP_DIR/node_modules" \
  --exclude="$APP_DIR/public/build" \
  --exclude="$APP_DIR/storage/logs" \
  --exclude="$APP_DIR/bootstrap/cache" \
  "$APP_DIR"

# Uploads backup
echo "Backing up uploads..."
tar -czf "$BACKUP_DIR/uploads_$DATE.tar.gz" "$APP_DIR/storage/app/public"

# Cleanup old backups
echo "Cleaning up old backups..."
find "$BACKUP_DIR" -name "*.gz" -mtime +$RETENTION_DAYS -delete

# Log
echo "$(date): Backup completed successfully" >> "$BACKUP_DIR/backup.log"

echo "Backup completed: $DATE"
```

Make executable:

```bash
chmod +x /var/scripts/backup-VentureX-ERP.sh
```

Schedule:

```bash
# Run daily at 2 AM
0 2 * * * /var/scripts/backup-VentureX-ERP.sh
```

---

## Off-Site Backup

### AWS S3

```bash
# Install AWS CLI
sudo apt install awscli -y

# Configure
aws configure

# Sync backups to S3
aws s3 sync /var/backups/VentureX-ERP s3://your-backup-bucket/VentureX-ERP/ --delete
```

### Other Storage

Use `rclone` for cloud storage:

```bash
# Install rclone
sudo apt install rclone -y

# Configure remote
rclone config

# Copy backups
rclone copy /var/backups/VentureX-ERP remote:VentureX-ERP-backups
```

---

## Restore Procedures

### Restore Database

```bash
# Stop application first
cd /var/www/VentureX-ERP
php artisan down

# Drop and recreate database
mysql -u root -p -e "DROP DATABASE VENTUREX_ERP; CREATE DATABASE VENTUREX_ERP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Restore from backup
mysql -u username -p VENTUREX_ERP < backup_YYYYMMDD.sql

# Or from compressed backup
gunzip < backup_YYYYMMDD.sql.gz | mysql -u username -p VENTUREX_ERP

# Bring application online
php artisan up
```

### Restore Files

```bash
cd /var/www

# Restore full application
tar -xzf VentureX-ERP-files-YYYYMMDD.tar.gz

# Or restore only uploads
tar -xzf uploads-YYYYMMDD.tar.gz -C /var/www/VentureX-ERP/storage/app/public/

# Reinstall dependencies
cd VentureX-ERP
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/VentureX-ERP
sudo chmod -R 775 /var/www/VentureX-ERP/storage
sudo chmod -R 775 /var/www/VentureX-ERP/bootstrap/cache

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

### Restore from S3

```bash
# Download from S3
aws s3 cp s3://your-backup-bucket/VentureX-ERP/db_YYYYMMDD.sql.gz /tmp/

# Restore
gunzip < /tmp/db_YYYYMMDD.sql.gz | mysql -u username -p VENTUREX_ERP
```

---

## Backup Verification

### Test Database Backup

```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE VENTUREX_ERP_test;"

# Restore backup to test database
mysql -u root -p VENTUREX_ERP_test < backup_YYYYMMDD.sql

# Verify tables exist
mysql -u root -p -e "SHOW TABLES FROM VENTUREX_ERP_test;"

# Cleanup
mysql -u root -p -e "DROP DATABASE VENTUREX_ERP_test;"
```

### Test File Backup

```bash
# Extract to temporary location
mkdir /tmp/restore-test
tar -xzf VentureX-ERP-files-YYYYMMDD.tar.gz -C /tmp/restore-test

# Verify key files exist
ls -la /tmp/restore-test/VentureX-ERP/.env
ls -la /tmp/restore-test/VentureX-ERP/config/

# Cleanup
rm -rf /tmp/restore-test
```

---

## Backup Schedule Recommendations

| Data | Frequency | Retention |
|------|-----------|-----------|
| Database | Daily | 30 days |
| Full files | Weekly | 90 days |
| User uploads | Daily | 30 days |
| Off-site sync | Daily | 90 days |

---

## Disaster Recovery

### Scenario: Server Failure

1. Provision new server
2. Install dependencies (see DEPLOYMENT.md)
3. Restore database backup
4. Restore file backup
5. Configure `.env`
6. Set permissions
7. Install SSL
8. Start services
9. Verify functionality

### Scenario: Data Corruption

1. Identify when corruption occurred
2. Stop application: `php artisan down`
3. Restore database from backup before corruption
4. Verify data integrity
5. Bring application online

### Scenario: Accidental Deletion

1. If soft deletes enabled, check `deleted_at` column:
   ```sql
   SELECT * FROM table_name WHERE deleted_at IS NOT NULL;
   ```
2. Restore deleted records:
   ```sql
   UPDATE table_name SET deleted_at = NULL WHERE id = {id};
   ```
3. If hard deleted, restore from backup

---

## Monitoring Backups

### Check Backup Status

```bash
# View recent backups
ls -lh /var/backups/VentureX-ERP/

# Check backup log
tail -f /var/backups/VentureX-ERP/backup.log
```

### Backup Alerting

Add to backup script:

```bash
# Check if backup was successful
if [ $? -eq 0 ]; then
    echo "Backup successful" | mail -s "VentureX ERP & CRM Backup Success" admin@example.com
else
    echo "Backup failed" | mail -s "VentureX ERP & CRM Backup FAILED" admin@example.com
fi
```

---

## Backup Checklist

- [ ] Database backup scheduled and tested
- [ ] File backup scheduled and tested
- [ ] Off-site backup configured
- [ ] Backup retention policy set
- [ ] Restore procedure tested
- [ ] Backup monitoring in place
- [ ] Disaster recovery plan documented
