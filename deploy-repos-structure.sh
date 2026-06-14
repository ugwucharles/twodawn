#!/bin/bash

# Laravel Deployment Script for cPanel (repositories structure)
# Your Laravel app is in ~/repositories/2DAWN

echo "🚀 Deploying Laravel from ~/repositories/2DAWN..."

# Navigate to your Laravel app
cd ~/repositories/2DAWN

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

# Create symlink to public_html if needed
if [ ! -L ~/public_html ]; then
    echo "🔗 Creating symlink to public_html..."
    rm -rf ~/public_html
    ln -s ~/repositories/2DAWN/public ~/public_html
fi

echo "✅ Deployment completed successfully!"
