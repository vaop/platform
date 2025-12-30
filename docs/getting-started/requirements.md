# System Requirements

## PHP

- **Version:** 8.4 or higher
- **Extensions:** pdo_mysql, gd, zip, bcmath, intl, exif, pcntl

Most hosting providers include these extensions by default. If you're using Docker, everything is pre-configured.

## Database

- **MariaDB:** 10 or higher, or
- **MySQL:** 8.0 or higher

## Web Server

Any PHP-compatible web server:
- **Caddy** (recommended, included in Docker)
- Nginx
- Apache

Most shared hosting environments work out of the box.

## Optional (Advanced)

These are only needed for large-scale deployments:

- **Redis** - Faster caching and queue processing
- **S3-compatible storage** - Store uploads in the cloud instead of locally
