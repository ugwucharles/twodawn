#!/bin/bash

# Laravel Deployment Script for cPanel
# Usage: ./deploy-cpanel.sh

echo "🚀 Starting Laravel deployment to cPanel..."

# Configuration - Update these values
REPO_URL="https://github.com/ugwucharles/2DAWN.git"
DEPLOY_PATH="/home/yourusername/public_html/your-folder"  # Update with your cPanel path
BRANCH="main"

# Navigate to deployment directory
echo "📂 Navigating to deployment directory..."
cd $DEPLOY_PATH || { echo "❌ Failed to navigate to $DEPLOY_PATH"; exit 1; }

# Pull latest changes
echo "📥 Pulling latest changes from GitHub..."
git pull origin $BRANCH || { echo "❌ Failed to pull from GitHub"; exit 1; }

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader || { echo "❌ Composer install failed"; exit 1; }

# Install node dependencies
echo "📦 Installing Node dependencies..."
npm install || { echo "❌ NPM install failed"; exit 1; }

# Build assets
echo "🔨 Building frontend assets..."
npm run build || { echo "❌ Asset build failed"; exit 1; }

# Copy environment file if not exists
if [ ! -f .env ]; then
    echo "⚙️  Creating .env file..."
    cp .env.example .env
    echo "⚠️  Please update your .env file with proper configuration"
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force || { echo "❌ Migration failed"; exit 1; }

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

echo "✅ Deployment completed successfully!"
echo "🌐 Your application should now be live"
