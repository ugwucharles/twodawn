#!/usr/bin/env bash
# -------------------------------------------------
# deploy_fixes.sh – apply the live‑site fixes
# -------------------------------------------------
# 1. Go to the Laravel project root
cd "$(dirname "$0")"
PROJECT_ROOT=$(pwd)

# sanity check – make sure we really are in a Laravel install
if [[ ! -f "$PROJECT_ROOT/artisan" ]]; then
  echo "Error: artisan not found – run this script inside the Laravel project root."
  exit 1
fi

echo "Working directory: $PROJECT_ROOT"

# -------------------------------------------------
# 2. Permissions – storage & bootstrap/cache must be writable
# -------------------------------------------------
echo "Fixing permissions..."
chmod -R 755 "$PROJECT_ROOT/storage"
chmod -R 755 "$PROJECT_ROOT/bootstrap/cache"
chmod -R 755 "$PROJECT_ROOT/public/storage" 2>/dev/null || true

# -------------------------------------------------
# 3. Create the storage symlink (required for local fallback images)
# -------------------------------------------------
if [[ ! -e "$PROJECT_ROOT/public/storage" ]]; then
  echo "Creating storage symlink..."
  php artisan storage:link
else
  echo "Storage symlink already exists – skipping."
fi

# -------------------------------------------------
# 4. Clear every Laravel cache so the new CSP / env values take effect
# -------------------------------------------------
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# -------------------------------------------------
# 5. Re‑optimise the autoloader (helps on shared hosting)
# -------------------------------------------------
echo "Optimising class map..."
php artisan optimize

# -------------------------------------------------
# 6. (Optional) Hint at a PHP restart – many cPanel setups watch this file
# -------------------------------------------------
if [[ -d "/home/$(whoami)/tmp" ]]; then
  echo "Touching tmp/restart.txt to hint at a PHP restart..."
  touch "/home/$(whoami)/tmp/restart.txt"
fi

echo "All fixes applied! Reload your site (or clear your browser cache) to see the changes."
