#!/bin/bash

# Laravel 500 Error Diagnostic Script for cPanel
# Usage: cd repositories/2DAWN && bash diagnose-500-error.sh

echo "=========================================="
echo "Laravel 500 Error Diagnostic Script"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from your Laravel project root (repositories/2DAWN)"
    exit 1
fi

echo "✅ Running diagnostics in: $(pwd)"
echo ""

# 1. Check Laravel logs
echo "📋 Checking Laravel logs..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "Last 20 lines of Laravel log:"
    tail -n 20 storage/logs/laravel.log
    echo ""
else
    echo "⚠️  No Laravel log file found"
    echo ""
fi

# 2. Check PHP error log
echo "📋 Checking PHP error log..."
PHP_ERROR_LOG=$(php -i | grep "error_log" | cut -d "=>" -f 2 | xargs)
if [ -f "$PHP_ERROR_LOG" ]; then
    echo "PHP error log location: $PHP_ERROR_LOG"
    echo "Last 20 lines of PHP error log:"
    tail -n 20 "$PHP_ERROR_LOG"
    echo ""
else
    echo "⚠️  PHP error log not found or not accessible"
    echo ""
fi

# 3. Check file permissions
echo "📋 Checking file permissions..."
echo "Storage directory permissions:"
ls -ld storage
echo "Bootstrap cache permissions:"
ls -ld bootstrap/cache
echo ""

# 4. Check .env file
echo "📋 Checking .env configuration..."
if [ -f ".env" ]; then
    echo "✅ .env file exists"
    if [ -f ".env.example" ]; then
        echo "Checking if .env has required variables..."
        grep -q "APP_KEY" .env && echo "✅ APP_KEY set" || echo "❌ APP_KEY missing"
        grep -q "APP_ENV" .env && echo "✅ APP_ENV set" || echo "❌ APP_ENV missing"
        grep -q "APP_DEBUG" .env && echo "✅ APP_DEBUG set" || echo "❌ APP_DEBUG missing"
        grep -q "DB_DATABASE" .env && echo "✅ DB_DATABASE set" || echo "❌ DB_DATABASE missing"
    fi
else
    echo "❌ .env file missing - copy .env.example to .env"
fi
echo ""

# 5. Check composer dependencies
echo "📋 Checking composer dependencies..."
if [ -d "vendor" ]; then
    echo "✅ Vendor directory exists"
else
    echo "❌ Vendor directory missing - run: composer install"
fi
echo ""

# 6. Check storage link
echo "📋 Checking storage link..."
if [ -L "public/storage" ]; then
    echo "✅ Storage symlink exists"
    ls -la public/storage
else
    echo "❌ Storage symlink missing - run: php artisan storage:link"
fi
echo ""

# 7. Check cache/config
echo "📋 Checking application cache..."
echo "Clearing caches..."
php artisan cache:clear 2>&1
php artisan config:clear 2>&1
php artisan route:clear 2>&1
php artisan view:clear 2>&1
echo ""

# 8. Test database connection
echo "📋 Testing database connection..."
php artisan tinker --execute="try { \DB::connection()->getPdo(); echo '✅ Database connection successful'; } catch (\Exception \$e) { echo '❌ Database connection failed: ' . \$e->getMessage(); }" 2>&1
echo ""

# 9. Check for common issues
echo "📋 Checking for common issues..."
if [ -w "storage" ]; then
    echo "✅ Storage directory is writable"
else
    echo "❌ Storage directory is not writable"
fi

if [ -w "bootstrap/cache" ]; then
    echo "✅ Bootstrap cache is writable"
else
    echo "❌ Bootstrap cache is not writable"
fi
echo ""

# 10. Generate diagnostic summary
echo "=========================================="
echo "Diagnostic Summary"
echo "=========================================="
echo ""
echo "Common fixes for 500 errors:"
echo "1. Run: php artisan key:generate"
echo "2. Run: composer install"
echo "3. Run: php artisan storage:link"
echo "4. Set proper permissions: chmod -R 775 storage bootstrap/cache"
echo "5. Clear caches: php artisan cache:clear"
echo "6. Check .env configuration matches your server"
echo "7. Ensure PHP version matches requirements"
echo ""
echo "For detailed debugging, set APP_DEBUG=true in .env"
echo "=========================================="
