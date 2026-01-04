# Docker Deployment

> **Note:** This guide is for advanced users familiar with Docker and container orchestration. Most users should follow the standard [installation guide](../getting-started/installation.md) for shared hosting.

VAOP provides pre-built Docker images via [quay.io/vaop/platform](https://quay.io/repository/vaop/platform).

## Quick Start

```bash
docker pull quay.io/vaop/platform:X.Y.Z
```

We recommend pinning to a specific version tag for predictable behavior. Check the [releases page](https://github.com/vaop/platform/releases) for available versions.

## Docker Compose

Create a `docker-compose.yml`:

```yaml
services:
  app:
    image: quay.io/vaop/platform:X.Y.Z
    ports:
      - "80:80"
    environment:
      APP_NAME: "Your VA Name"
      APP_URL: https://yourva.com
      APP_KEY: base64:your-key-here

      DB_HOST: db
      DB_DATABASE: vaop
      DB_USERNAME: vaop
      DB_PASSWORD: secret
    depends_on:
      - db

  db:
    image: mariadb:11.4
    environment:
      MARIADB_ROOT_PASSWORD: rootsecret
      MARIADB_DATABASE: vaop
      MARIADB_USER: vaop
      MARIADB_PASSWORD: secret
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

Start the stack:

```bash
docker compose up -d
```

## Running Migrations

After starting the container, run migrations:

```bash
docker compose exec app php artisan migrate --force
```

## Environment Variables

See [Environment Variables](../configuration/environment.md) for the complete reference.

**Generating an APP_KEY:**

```bash
# Using the container
docker run --rm quay.io/vaop/platform:X.Y.Z php artisan key:generate --show

# Or using openssl
echo "base64:$(openssl rand -base64 32)"
```

## Scheduler

For scheduled tasks, use host cron:

```
* * * * * docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

## Queue Workers

Run queue workers as separate containers. See [Queue Workers](queues.md) for detailed configuration.

Quick example:

```yaml
services:
  worker:
    image: quay.io/vaop/platform:X.Y.Z
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    environment:
      # Same environment variables as your app
      APP_KEY: ${APP_KEY}
      DB_HOST: db
      DB_DATABASE: vaop
      DB_USERNAME: vaop
      DB_PASSWORD: ${DB_PASSWORD}
    depends_on:
      - db
    restart: unless-stopped
```

Scale workers: `docker compose up -d --scale worker=3`

## Health Checks

```yaml
services:
  app:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/up"]
      interval: 30s
      timeout: 10s
      retries: 3
```

## HTTPS / SSL

The included Caddy configuration has `auto_https off` for flexibility. Options:

1. **Reverse proxy** - Use Traefik, nginx-proxy, or Cloudflare Tunnel for TLS termination
2. **Enable Caddy HTTPS** - Modify the Caddyfile to enable automatic HTTPS

## Logs

```bash
docker compose logs -f app
```

---

## Building from Source (Optional)

If you need to customize the image:

```bash
git clone https://github.com/vaop/platform.git
cd platform
docker build -t vaop:X.Y.Z -f docker/Dockerfile .
```
