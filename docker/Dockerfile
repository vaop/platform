# syntax=docker/dockerfile:1

# Composer stage to get vendor CSS files needed for frontend build
FROM composer:2 AS composer

ARG TARGETARCH

# Install intl extension required by Filament
RUN apk add --no-cache icu-dev \
    && docker-php-ext-install intl

WORKDIR /app

COPY composer.json composer.lock ./
RUN --mount=type=cache,id=composer-$TARGETARCH,target=/root/.composer/cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Build stage for frontend assets
FROM node:25-alpine AS frontend

ARG TARGETARCH

WORKDIR /app

COPY package.json package-lock.json ./
RUN --mount=type=cache,id=npm-$TARGETARCH,target=/root/.npm \
    npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
# Copy Filament CSS from composer stage (needed for Tailwind imports in theme.css)
COPY --from=composer /app/vendor/filament/filament/resources/css ./vendor/filament/filament/resources/css

RUN npm run build

# Production PHP image
FROM php:8.5-fpm-alpine AS production

ARG TARGETARCH

# Copy Caddy binary from official image (pinned version for security updates via Dependabot)
COPY --from=caddy:2.11-alpine /usr/bin/caddy /usr/sbin/caddy

# Install system dependencies
RUN apk add --no-cache \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    mariadb-client

# Install PHP extensions
# Note: mbstring is built-in as of PHP 8.5, opcache is built-in as of PHP 8.5
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/99-custom.ini"
COPY docker/opcache.ini "$PHP_INI_DIR/conf.d/opcache.ini"

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev)
RUN --mount=type=cache,id=composer-$TARGETARCH,target=/root/.composer/cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application code
COPY . .

# Copy built frontend assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Ensure directories exist and set permissions
RUN mkdir -p /var/www/html/storage/framework/{cache,sessions,views} \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy Caddy configuration
COPY docker/Caddyfile /etc/caddy/Caddyfile

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create startup script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Disable web installer in Docker (users configure via env vars)
ENV INSTALLER_ENABLED=false

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
