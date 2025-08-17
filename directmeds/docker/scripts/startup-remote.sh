#!/bin/bash
set -e

echo "Starting Direct Meds Application with Remote Database..."

# Wait for remote MySQL to be reachable
echo "Testing remote MySQL connection..."
timeout=30
counter=0
until nc -z -v -w5 ${DB_HOST} ${DB_PORT} 2>/dev/null; do
  echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
  sleep 2
  counter=$((counter + 2))
  if [ $counter -ge $timeout ]; then
    echo "Error: Unable to connect to MySQL at ${DB_HOST}:${DB_PORT} after ${timeout} seconds"
    exit 1
  fi
done
echo "MySQL is reachable!"

# Wait for Redis to be ready
echo "Waiting for Redis..."
while ! nc -z ${REDIS_HOST:-redis} ${REDIS_PORT:-6379}; do
  sleep 1
done
echo "Redis is ready!"

# Test database connection with Laravel
echo "Testing database connection..."
php artisan db:show || {
  echo "Warning: Could not connect to database. Please check your credentials."
  echo "Continuing anyway..."
}

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force || {
  echo "Warning: Migrations failed. This might be okay if they already ran."
}

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