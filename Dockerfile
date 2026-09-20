# MobiTrace API - Laravel / PHP 8.3 / PostgreSQL / TCPDF

# ---------- Étape 1 : dépendances Composer ----------
FROM composer:2.8 AS composer-build
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ---------- Étape 2 : PHP + Apache ----------
FROM php:8.3-apache-bookworm
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev libpng-dev libjpeg62-turbo-dev \
    libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd pdo_pgsql intl bcmath zip opcache \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf \
    && printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

COPY --from=composer-build /app /var/www/html

# Dossiers Laravel + dossier temporaire des PDF
RUN mkdir -p storage/app/exports storage/framework/cache storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN { echo "memory_limit=256M"; echo "max_execution_time=60"; \
      echo "opcache.enable=1"; echo "opcache.enable_cli=0"; \
    } > /usr/local/etc/php/conf.d/production.ini

EXPOSE 80

CMD ["sh", "-c", "php artisan migrate --force || echo 'ATTENTION : la migration a échoué'; php artisan config:cache || true; php artisan route:cache || true; exec apache2-foreground"]