#!/bin/bash
# Startup Script for Laravel Application
# This runs every time your Docker container starts

set -e

echo "🚀 Starting Laravel Application..."

# Wait a moment for database to be ready
echo "⏳ Waiting for database connection..."
sleep 5

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force --no-interaction

# Cache configuration for better performance
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear any old cache
echo "🧹 Clearing old cache..."
php artisan cache:clear

# Create storage link if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Set proper permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "✅ Laravel application ready!"
echo "🌐 Starting web server on port 10000..."

# Start supervisor (which starts nginx, PHP-FPM, and queue worker)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf