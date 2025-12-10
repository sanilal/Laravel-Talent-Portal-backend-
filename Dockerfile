# Dockerfile for Laravel 12 - Talents You Need Backend
# This file tells Docker how to build and run your Laravel application

# Start with PHP 8.3 with FPM (FastCGI Process Manager)
FROM php:8.3-fpm

# Install system dependencies
# These are Linux packages your app needs to run
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# These are required by Laravel and your dependencies
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Composer (PHP package manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install PHP dependencies (from composer.json)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories for Laravel
RUN mkdir -p \
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

# Set proper permissions
# www-data is the user that runs PHP-FPM and nginx
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy supervisor configuration
# Supervisor keeps multiple processes running (nginx, PHP-FPM, queue worker)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expose port 10000 (Render uses this port)
EXPOSE 10000

# Run the startup script
CMD ["/usr/local/bin/start.sh"]
