# Multi-stage build for optimized production image
FROM composer:2.8 AS composer

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies (no dev dependencies for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application code
COPY . .

# Generate optimized autoload
RUN composer dump-autoload --optimize

# Final stage
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    mbstring \
    xml \
    pdo \
    pdo_pgsql \
    zip \
    bcmath \
    opcache

# Install Node.js (for asset compilation if needed)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application from composer stage
COPY --from=composer /app /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy environment file if it doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate application key
RUN php artisan key:generate

# Clear and cache config
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan optimize

# Expose port 9000 for php-fpm
EXPOSE 9000

# Start php-fpm
CMD ["php-fpm"]
