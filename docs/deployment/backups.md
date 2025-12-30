# Backups

Strategies for backing up your VAOP installation.

## What to Back Up

1. **Database** - All application data
2. **Uploaded files** - User uploads in `storage/app/`
3. **Environment file** - `.env` configuration (keep secure)

## Database Backups

### MariaDB / MySQL

```bash
# Full backup
mysqldump -u root -p vaop > backup_$(date +%Y%m%d_%H%M%S).sql

# Compressed backup
mysqldump -u root -p vaop | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Docker

```bash
docker compose exec db mysqldump -u root -p vaop > backup.sql
```

### Automated Backups

Add to crontab:

```cron
# Daily backup at 2 AM
0 2 * * * mysqldump -u vaop -p'password' vaop | gzip > /backups/vaop_$(date +\%Y\%m\%d).sql.gz
```

Retention policy (keep last 7 days):

```cron
0 3 * * * find /backups -name "vaop_*.sql.gz" -mtime +7 -delete
```

## File Storage Backups

### Local Storage

```bash
# Backup uploaded files
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/
```

### S3 / Object Storage

If using S3, configure bucket versioning and cross-region replication for redundancy. Most S3-compatible services offer automated backup features.

## Restore Procedures

### Database Restore

```bash
# From SQL file
mysql -u root -p vaop < backup.sql

# From compressed file
gunzip < backup.sql.gz | mysql -u root -p vaop

# Docker
docker compose exec -T db mysql -u root -p vaop < backup.sql
```

### File Storage Restore

```bash
# Extract to storage directory
tar -xzf storage_backup.tar.gz -C /path/to/vaop/
```

## Backup Verification

Regularly test your backups:

1. Restore to a test environment
2. Verify application functionality
3. Check data integrity

```bash
# Quick verification - check backup file is valid SQL
head -n 50 backup.sql

# Check compressed file integrity
gzip -t backup.sql.gz && echo "OK"
```

## Disaster Recovery

For critical deployments, consider:

1. **Offsite backups** - Store copies in a different location/provider
2. **Point-in-time recovery** - Enable binary logging for MySQL/MariaDB
3. **Replication** - Set up database replication for high availability
4. **Documented procedures** - Keep runbooks for recovery scenarios

## Environment File

Back up your `.env` file separately and securely:

```bash
# Encrypt with GPG
gpg -c .env -o env_backup.gpg

# Store in a secure location (password manager, secrets vault)
```

Never commit `.env` to version control or store it with regular backups.
