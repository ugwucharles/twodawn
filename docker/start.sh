#!/usr/bin/env sh
set -e

# Render provides $PORT; inject it into Nginx config.
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

# Ensure required directories exist before optimization (sessions, views, cache, logs)
mkdir -p /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views /app/storage/logs /app/bootstrap/cache
# Pre-create log file to prevent "failed to open stream" warnings
touch /app/storage/logs/laravel.log || true

# Ensure writable dirs (also covers mounted disk)
chown -R www-data:www-data /app/storage /app/bootstrap/cache || true
chmod -R 775 /app/storage /app/bootstrap/cache || true

# Optimize once APP_KEY is provided
if [ -n "${APP_KEY:-}" ]; then
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
else
  echo "WARNING: APP_KEY is not set; skipping artisan optimize steps."
fi

# Link storage and run migrations (idempotent)
php artisan storage:link || true
php artisan migrate --force || true

# Start services
php-fpm -D
exec nginx -g "daemon off;"
