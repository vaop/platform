# Queue Workers

VAOP uses a queue system for background job processing. Queue workers handle tasks like sending emails, processing uploads, and generating reports.

## Setup

Add this cron entry to your server (via cPanel, Plesk, or SSH):

```
* * * * * cd /path/to/vaop && php artisan schedule:run >> /dev/null 2>&1
```

That's it! VAOP will now process background jobs automatically.

---

## Configuration

You can fine-tune the queue workers with these optional settings:

| Variable | Default | Description |
|----------|---------|-------------|
| `QUEUE_EMBEDDED_WORKERS` | `true` | Enable queue workers |
| `QUEUE_WORKERS` | `2` | Number of workers to run |
| `QUEUE_MAX_TIME` | `55` | Seconds before worker exits (current job completes first) |
| `QUEUE_MAX_JOBS` | `50` | Jobs per worker before restart |
| `QUEUE_MEMORY` | `128` | Memory limit in MB |
| `QUEUE_REST` | `1` | Pause between jobs (seconds) |
| `QUEUE_QUEUES` | `default` | Queue names to process |

### Recommended Settings

**Basic shared hosting** (limited resources):
```env
QUEUE_WORKERS=1
QUEUE_MAX_TIME=25
QUEUE_MAX_JOBS=20
QUEUE_MEMORY=64
```

**Standard hosting** (default):
```env
QUEUE_WORKERS=2
QUEUE_MAX_TIME=55
QUEUE_MAX_JOBS=50
QUEUE_MEMORY=128
```

**Premium hosting**:
```env
QUEUE_WORKERS=3
QUEUE_MAX_TIME=55
QUEUE_MAX_JOBS=100
QUEUE_MEMORY=256
```

---

## Troubleshooting

### Jobs Not Processing

1. Check cron is running - look for entries in your hosting control panel
2. Test manually: `php artisan schedule:run`
3. Check logs: `storage/logs/laravel.log`

### Host Complaining About Processes

If your host limits concurrent processes:

1. Reduce `QUEUE_WORKERS` to 1
2. Increase `QUEUE_REST` to 2-3
3. Reduce `QUEUE_MAX_JOBS` to 10-20

### View Failed Jobs

```bash
php artisan queue:failed
php artisan queue:retry all
```

---

## Docker Deployment

For Docker deployments, disable embedded workers and run queue workers as separate containers:

```env
QUEUE_EMBEDDED_WORKERS=false
```

Add a worker service to your `docker-compose.yml`:

```yaml
services:
  app:
    image: quay.io/vaop/platform:X.Y.Z
    # ... your existing app configuration

  worker:
    image: quay.io/vaop/platform:X.Y.Z
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    environment:
      APP_KEY: ${APP_KEY}
      DB_HOST: db
      DB_DATABASE: vaop
      DB_USERNAME: vaop
      DB_PASSWORD: ${DB_PASSWORD}
    depends_on:
      - db
    restart: unless-stopped
```

Scale workers based on load:

```bash
docker compose up -d --scale worker=3
```

### Multiple Queues

Process different queues with dedicated workers:

```yaml
services:
  worker-default:
    image: quay.io/vaop/platform:X.Y.Z
    command: php artisan queue:work --queue=default --sleep=3 --tries=3
```
