#!/bin/bash

# Laravel Update Script for cPanel (Manual Upload)
# Run this in cPanel terminal after uploading files via file manager

echo "🚀 Updating Laravel application on cPanel..."

# Navigate to your existing folder (update this path)
cd ~/public_html/two-dawn

# Install/update dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install/update node dependencies
echo "📦 Installing Node dependencies..."
npm install

# Build frontend assets
echo "🔨 Building frontend assets..."
npm run build

# Copy environment file if not exists
if [ ! -f .env ]; then
    echo "⚙️  Creating .env file..."
    cp .env.example .env
    echo "⚠️  Please update .env with your database credentials"
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
echo "🧹 Clearing and caching configurations..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Clear opcache if available
if command -v php &> /dev/null; then
    php -r "if(function_exists('opcache_reset')){opcache_reset();}"
fi

echo "✅ Update completed successfully!"
