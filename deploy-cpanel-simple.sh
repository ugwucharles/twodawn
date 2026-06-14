#!/bin/bash

# Simple Laravel Deployment for cPanel Terminal
# Run this in your cPanel terminal/SSH

echo "🚀 Deploying Laravel to cPanel..."

# 1. Navigate to your folder (update this path)
cd ~/public_html/your-folder

# 2. Pull latest code from GitHub
git pull origin main

# 3. Install/update dependencies
composer install --no-dev --optimize-autoloader

# 4. Build frontend assets
npm install
npm run build

# 5. Run migrations
php artisan migrate --force

# 6. Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "✅ Deployment complete!"
