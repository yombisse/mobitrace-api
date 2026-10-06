#!/bin/sh
cd /var/www/html || exit 1

if [ "${RUN_MIGRATE_FRESH:-false}" = "true" ]; then
    echo ">> RESET COMPLET DE LA BASE..."
    php artisan migrate:fresh --seed --force
else
    echo ">> Migration de la base..."
    php artisan migrate --force || echo "ATTENTION : la migration a échoué"

    if [ "${RUN_SEEDERS:-true}" = "true" ]; then
        echo ">> Seeding..."
        php artisan db:seed --force || echo "ATTENTION : le seeding a échoué"
    fi
fi

php artisan config:cache || true
php artisan route:cache || true

chown -R www-data:www-data storage bootstrap/cache

exec "$@"