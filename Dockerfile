# ============================================================
# MobiTrace API - Production Dockerfile
# Laravel 13.x / PHP 8.3 / PostgreSQL / TCPDF
# ============================================================

# ------------------------------------------------------------
# STAGE 1 : Installation des dépendances Composer
# ------------------------------------------------------------
FROM composer:2.8 AS composer-build

WORKDIR /app

# Copier les fichiers Composer en premier
# afin de profiter du cache Docker
COPY composer.json composer.lock ./

# Copier le code de l'application AVANT composer install
# pour que artisan soit disponible pour les hooks
COPY . .

# Installer uniquement les dépendances de production
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Vérifier/générer l'autoload optimisé
RUN composer dump-autoload \
    --optimize \
    --no-dev


# ------------------------------------------------------------
# STAGE 2 : Image PHP + Apache
# ------------------------------------------------------------
FROM php:8.3-apache-bookworm

WORKDIR /var/www/html

# ------------------------------------------------------------
# Dépendances système
# ------------------------------------------------------------
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# ------------------------------------------------------------
# Extensions PHP nécessaires à Laravel / MobiTrace
#
# gd          -> génération de PDF / images
# pdo_pgsql   -> PostgreSQL
# intl        -> internationalisation / Carbon
# mbstring    -> Laravel
# bcmath      -> calculs précis
# zip         -> gestion des archives
# opcache     -> performances PHP
# ------------------------------------------------------------
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        pdo \
        pdo_pgsql \
        intl \
        mbstring \
        bcmath \
        zip \
        opcache


# ------------------------------------------------------------
# Apache
# ------------------------------------------------------------

# Activer les URLs propres de Laravel
RUN a2enmod rewrite

# Laravel doit être servi depuis /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/sites-available/default-ssl.conf

# Autoriser le .htaccess de Laravel
RUN printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel


# ------------------------------------------------------------
# Copier l'application depuis le stage Composer
# ------------------------------------------------------------
COPY --from=composer-build /app /var/www/html


# ------------------------------------------------------------
# Préparer les dossiers Laravel
#
# storage/app/mpdf
# storage/app/exports
#
# peuvent être utilisés par TCPDF pour les fichiers temporaires
# ou les exports PDF.
# ------------------------------------------------------------
RUN mkdir -p \
    storage/app/mpdf \
    storage/app/exports \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache


# ------------------------------------------------------------
# Permissions Laravel
# ------------------------------------------------------------
RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache


# ------------------------------------------------------------
# Configuration PHP production
# ------------------------------------------------------------
RUN { \
        echo "memory_limit=256M"; \
        echo "upload_max_filesize=10M"; \
        echo "post_max_size=10M"; \
        echo "max_execution_time=60"; \
        echo "max_input_time=60"; \
        echo "opcache.enable=1"; \
        echo "opcache.memory_consumption=128"; \
        echo "opcache.interned_strings_buffer=8"; \
        echo "opcache.max_accelerated_files=10000"; \
        echo "opcache.revalidate_freq=2"; \
        echo "opcache.validate_timestamps=1"; \
        echo "opcache.enable_cli=0"; \
    } > /usr/local/etc/php/conf.d/production.ini


# ------------------------------------------------------------
# Entrypoint
#
# Les caches Laravel sont générés au démarrage du conteneur,
# après injection des variables d'environnement de production.
# ------------------------------------------------------------
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh


# ------------------------------------------------------------
# Port HTTP
# ------------------------------------------------------------
EXPOSE 80


# ------------------------------------------------------------
# Healthcheck
# ------------------------------------------------------------
HEALTHCHECK \
    --interval=30s \
    --timeout=5s \
    --start-period=30s \
    --retries=3 \
    CMD curl -f http://localhost/ || exit 1


# ------------------------------------------------------------
# Démarrage
# ------------------------------------------------------------
ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["apache2-foreground"]