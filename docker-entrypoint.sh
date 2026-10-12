#!/bin/sh

cd /var/www/html || exit 1

echo ">> Vérification de la connexion à la base distante..."

php artisan migrate:status || {
    echo "ERREUR : impossible d'accéder à la base de données."
    exit 1
}

echo ">> Exécution des migrations..."

php artisan migrate --force || {
    echo "ERREUR : les migrations ont échoué."
    exit 1
}

echo ">> Initialisation des données de référence..."

php artisan db:seed --class=ReferenceDataSeeder --force || {
    echo "ERREUR : le seeding des données de référence a échoué."
    exit 1
}

echo ">> Optimisation du cache de configuration..."
php artisan config:cache || exit 1

echo ">> Optimisation du cache des routes..."
php artisan route:cache || exit 1

echo ">> Attribution des permissions..."
chown -R www-data:www-data storage bootstrap/cache || exit 1

echo "============================================"
echo "   DEMARRAGE PRET"
echo "============================================"

exec "$@"