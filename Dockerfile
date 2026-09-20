# ============================================================
# MobiTrace API - Production Dockerfile
# Laravel 13 / PHP 8.3 / PostgreSQL / TCPDF
# ============================================================


# ============================================================
# STAGE 1 : Composer
# ============================================================

FROM composer:2.8 AS composer-build

WORKDIR /app

# Copier les fichiers Composer
COPY composer.json composer.lock ./

# Copier toute l'application AVANT Composer
# afin que artisan soit disponible
COPY . .

# Installer les dépendances sans exécuter
# les scripts Laravel pendant le build.
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Générer l'autoload optimisé sans exécuter
# les scripts Laravel.
RUN composer dump-autoload \
    --optimize \
    --no-dev \
    --no-scripts


# ============================================================
# STAGE 2 : PHP + Apache
# ============================================================

FROM php:8.3-apache-bookworm

WORKDIR /var/www/html


# ============================================================
# Dépendances système
# ============================================================

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*


# ============================================================
# Extensions PHP
# ============================================================

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


# ============================================================
# Apache
# ============================================================

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

RUN printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel


# ============================================================
# Copier l'application
# ============================================================

COPY --from=composer-build /app /var/www/html


# ============================================================
# Préparer les dossiers Laravel
# ============================================================

RUN mkdir -p \
    storage/app/mpdf \
    storage/app/exports \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache


# ============================================================
# Permissions
# ============================================================

RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache


# ============================================================
# Configuration PHP
# ============================================================

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


# ============================================================
# Port HTTP
# ============================================================

EXPOSE 80


# ============================================================
# Healthcheck
# ============================================================

HEALTHCHECK \
    --interval=30s \
    --timeout=5s \
    --start-period=30s \
    --retries=3 \
    CMD curl -f http://localhost/ || exit 1


# ============================================================
# Démarrage Laravel
# ============================================================

CMD ["sh", "-c", "\
    php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    exec apache2-foreground \
"]