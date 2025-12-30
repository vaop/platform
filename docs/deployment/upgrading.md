# Upgrading VAOP

How to update VAOP to the latest version.

## Before You Start

1. **Back up your database** - See [Backups](backups.md)
2. **Read the release notes** - Check for any breaking changes

---

## Standard Installation

Run the upgrade command:

```bash
php artisan vaop:upgrade
```

The upgrade command will automatically:
- Check for the latest version
- Download the release
- Extract and replace files
- Run database migrations
- Clear caches

---

## Docker Installation

### 1. Update Your Image Tag

Edit your `docker-compose.yml` and update the version:

```yaml
services:
  app:
    image: quay.io/vaop/platform:X.Y.Z  # Update to new version
```

### 2. Pull and Restart

```bash
docker compose pull
docker compose down
docker compose up -d
```

Migrations run automatically on container start if `AUTO_MIGRATE=true` is set in your environment.

If you don't have auto-migrate enabled, run manually:

```bash
docker compose exec app php artisan migrate --force
```

---

## Troubleshooting

**Upgrade failed or site shows errors?**
- Restore your database backup
- Re-upload the previous version manually
- Check `storage/logs/laravel.log` for details

**Need help?**
- Open an issue on [GitHub](https://github.com/vaop/platform/issues)
