#!/usr/bin/env bash
# Simple deploy script to run on the cPanel server after pulling changes
set -euo pipefail

APP_DIR="$(pwd)"

echo "Deploying in ${APP_DIR}"

# Fetch latest code and force server to match GitHub
git fetch origin
git reset --hard origin/main

echo "Installing PHP dependencies..."
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader --no-interaction
else
  echo "composer not found in PATH"
fi

echo "Running post-deploy artisan commands..."
if command -v php >/dev/null 2>&1; then
  php artisan migrate --force || true
  php artisan storage:link || true
  php artisan optimize:clear || true
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
else
  echo "php not found in PATH"
fi

if [ -f package.json ]; then
  if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build || true
  else
    echo "npm not found in PATH; skipping frontend build"
  fi
fi

echo "Deploy finished."