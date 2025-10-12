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

# Optionally seed an admin user from env (run only if ADMIN_EMAIL and ADMIN_PASSWORD are set)
if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
  echo "Seeding admin user from env..."
  php artisan db:seed --class="Database\\Seeders\\AdminFromEnvSeeder" || true
fi

# Optionally run a queue worker inside the web service (saves a separate Render Worker)
if [ "${ENABLE_QUEUE_WORKER:-0}" = "1" ] || [ "${ENABLE_QUEUE_WORKER:-}" = "true" ]; then
  echo "Starting Laravel queue worker..."
  (
    while true; do
      php artisan queue:work --sleep="${QUEUE_SLEEP:-1}" --tries="${QUEUE_TRIES:-3}" --backoff="${QUEUE_BACKOFF:-3}" --timeout="${QUEUE_TIMEOUT:-60}" || true
      echo "[queue-worker] exited with $?, restarting in 5s"
      sleep 5
    done
  ) &
fi

# Optionally run the scheduler loop inside the web service (replaces Cron)
if [ "${ENABLE_SCHEDULER:-0}" = "1" ] || [ "${ENABLE_SCHEDULER:-}" = "true" ]; then
  echo "Starting Laravel scheduler loop..."
  (
    while true; do
      php artisan schedule:run || true
      sleep 60
    done
  ) &
fi

# Start services
php-fpm -D
exec nginx -g "daemon off;"
