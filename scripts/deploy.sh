#!/usr/bin/env bash
# Simple deploy script to run on the cPanel server after pulling changes
set -euo pipefail

APP_DIR="$(pwd)"
FAILED_STEPS=()

run_step() {
  local step="$1"
  shift

  echo "Running ${step}..."
  if "$@"; then
    echo "Completed ${step}."
  else
    local status=$?
    echo "WARNING: ${step} failed with exit code ${status}; continuing deployment."
    FAILED_STEPS+=("${step}")
  fi
}

echo "Deploying in ${APP_DIR}"

# Fetch latest code and force server to match GitHub
git fetch origin
git reset --hard origin/main

echo "Installing PHP dependencies..."
if command -v composer >/dev/null 2>&1; then
  run_step "Composer install" composer install --no-dev --optimize-autoloader --no-interaction
else
  echo "WARNING: Composer not found in PATH; continuing deployment."
  FAILED_STEPS+=("Composer install (composer not found)")
fi

echo "Running post-deploy artisan commands..."
if command -v php >/dev/null 2>&1; then
  run_step "Database migrations" php artisan migrate --force
  run_step "Storage link" php artisan storage:link
  run_step "Clearing optimized caches" php artisan optimize:clear
  run_step "Configuration cache" php artisan config:cache
  run_step "Route cache" php artisan route:cache
  run_step "View cache" php artisan view:cache
else
  echo "WARNING: PHP not found in PATH; skipping Artisan commands."
  FAILED_STEPS+=("Artisan commands (php not found)")
fi

if [ -f package.json ]; then
  if command -v npm >/dev/null 2>&1; then
    run_step "Frontend dependency install" npm ci
    run_step "Frontend build" npm run build
  else
    echo "WARNING: npm not found in PATH; skipping frontend build."
    FAILED_STEPS+=("Frontend build (npm not found)")
  fi
fi

if [ "${#FAILED_STEPS[@]}" -gt 0 ]; then
  echo "Deploy finished with errors. Code was updated, but these steps need attention:"
  printf ' - %s\n' "${FAILED_STEPS[@]}"
  exit 1
fi

echo "Deploy finished successfully."