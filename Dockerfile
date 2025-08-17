# Multi-stage build for optimized production image
FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    nodejs \
    npm \
    netcat-openbsd

# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-jpeg \
    --with-webp \
    --with-xpm \
    --with-freetype

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    gd \
    zip \
    bcmath \
    opcache \
    pcntl

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Configure PHP
COPY directmeds/docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files from directmeds subdirectory
COPY directmeds/ .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# Install and build frontend assets
RUN npm ci && npm run build && rm -rf node_modules

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy nginx configuration
COPY directmeds/docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY directmeds/docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy supervisor configuration
COPY directmeds/docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup scripts
COPY directmeds/docker/scripts/startup.sh /usr/local/bin/startup.sh
COPY directmeds/docker/scripts/startup-remote.sh /usr/local/bin/startup-remote.sh
RUN chmod +x /usr/local/bin/startup.sh /usr/local/bin/startup-remote.sh

# Expose port (Traefik will proxy to this)
EXPOSE 80

# Health check for container orchestration
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start services (use startup-remote.sh for remote database)
CMD ["/usr/local/bin/startup-remote.sh"]