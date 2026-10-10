#!/bin/sh

cd /var/www/html || exit 1

echo "============================================"
echo "   REINITIALISATION DE LA BASE DE DONNEES"
echo "============================================"

echo ">> Vérification de la connexion à la base distante..."

php artisan migrate:status || {
    echo "ERREUR : impossible d'accéder à la base de données."
    exit 1
}

echo ">> Suppression des tables et réexécution des migrations..."

php artisan migrate:fresh --seed --force || {
    echo "ERREUR : la réinitialisation ou le seeding a échoué."
    exit 1
}

echo ">> Optimisation du cache de configuration..."
php artisan config:cache || true

echo ">> Optimisation du cache des routes..."
php artisan route:cache || true

echo ">> Attribution des permissions..."
chown -R www-data:www-data storage bootstrap/cache

echo "============================================"
echo "   REINITIALISATION TERMINEE"
echo "============================================"

exec "$@"