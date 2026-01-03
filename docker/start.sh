#!/bin/sh
set -e

# Ensure storage directories exist and are writable
mkdir -p /var/www/html/storage/framework/{cache,sessions,views}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

# Run migrations and seeders if AUTO_MIGRATE is set
if [ "$AUTO_MIGRATE" = "true" ]; then
    php artisan migrate --force
    php artisan db:seed --class=RolesAndPermissionsSeeder --force
fi

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
