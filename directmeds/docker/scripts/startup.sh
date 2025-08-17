#!/bin/bash
set -e

echo "Starting Direct Meds Application..."

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
while ! nc -z ${DB_HOST:-mysql} ${DB_PORT:-3306}; do
  sleep 1
done
echo "MySQL is ready!"

# Wait for Redis to be ready
echo "Waiting for Redis..."
while ! nc -z ${REDIS_HOST:-redis} ${REDIS_PORT:-6379}; do
  sleep 1
done
echo "Redis is ready!"

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and cache config
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage link
echo "Creating storage link..."
php artisan storage:link || true

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start supervisor
echo "Starting application services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf