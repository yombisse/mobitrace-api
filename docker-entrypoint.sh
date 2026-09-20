#!/bin/bash

# Entrypoint script for MobiTrace API
# Generates Laravel caches at container startup after environment injection

set -e

# Wait for database connection if needed
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection..."
    until php -r "try { \$pdo = new PDO('pgsql:host=$DB_HOST;port=${DB_PORT:-5432};dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD'); echo 'Database connected'; exit(0); } catch (Exception \$e) { echo 'Database not ready yet...'; exit(1); }"; do
        echo "Database unavailable - waiting..."
        sleep 2
    done
fi

# Generate application key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Run database seeders only on first startup
if [ ! -f storage/.seeded ]; then
    echo "Running database seeders for the first time..."
    php artisan db:seed --force
    touch storage/.seeded
    echo "Database seeded successfully."
else
    echo "Database already seeded. Skipping seeders."
fi

# Clear and cache Laravel configurations
echo "Optimizing Laravel for production..."
php artisan config:cache --force
php artisan route:cache --force
php artisan view:cache --force
php artisan optimize --force

# Execute the main command
exec "$@"
