#!/bin/bash
set -e

echo "🚀 Starting Laravel Application..."

# Use Railway's PORT or default to 8080
export PORT=${PORT:-8080}
echo "📡 Using port: $PORT"

# Wait for database
echo "⏳ Waiting for database connection..."
until php artisan db:show > /dev/null 2>&1; do
    echo "⏳ Database not ready, waiting..."
    sleep 2
done

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force || true
php artisan route:cache

# Cache configuration
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan view:cache

# Clear old caches
echo "🧹 Clearing old cache..."
php artisan cache:clear

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create PHP-FPM run directory
mkdir -p /var/run/php
chown www-data:www-data /var/run/php

# Update nginx config with actual port (envsubst replacement)
echo "🔧 Configuring nginx for port $PORT..."
envsubst '${PORT}' < /etc/nginx/sites-available/default > /tmp/nginx-site.conf
mv /tmp/nginx-site.conf /etc/nginx/sites-available/default

# Test nginx configuration
echo "🔍 Testing nginx configuration..."
nginx -t

if [ $? -ne 0 ]; then
    echo "❌ Nginx configuration test failed!"
    cat /etc/nginx/sites-available/default
    exit 1
fi

echo "✅ Laravel application ready!"
echo "🌐 Starting web server on port $PORT..."

# Start supervisor (which manages nginx, php-fpm, and workers)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf