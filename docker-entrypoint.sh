#!/bin/sh
cd /var/www/html || exit 1

echo ">> Migration de la base..."
php artisan migrate --force || echo "ATTENTION : la migration a échoué"

if [ "${RUN_SEEDERS:-true}" = "true" ]; then
  echo ">> Seeding..."
  php artisan db:seed --force || echo "ATTENTION : le seeding a échoué"
fi

php artisan config:cache || true
php artisan route:cache || true

# Les commandes ci-dessus tournent en root : rendre les fichiers à Apache
chown -R www-data:www-data storage bootstrap/cache

# Lance la commande du Dockerfile (apache2-foreground)
exec "$@"