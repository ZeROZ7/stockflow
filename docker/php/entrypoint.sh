#!/bin/sh
# StockFlow container entrypoint.
#   entrypoint migrate          -> run migrations (and seed when SEED=true), then exit
#   entrypoint php-fpm -F       -> default: serve PHP
#   entrypoint php artisan ...  -> any other command (queue:work, tinker, ...)
set -e

: "${PHP_OPCACHE_VALIDATE_TIMESTAMPS:=0}"
export PHP_OPCACHE_VALIDATE_TIMESTAMPS

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
  exit 1
fi

# Cache config/routes/views from the *runtime* environment (never at build time,
# so no env values or secrets end up baked into the image).
if [ "$APP_ENV" = "production" ]; then
  php artisan optimize --quiet || true
fi

if [ "$1" = "migrate" ]; then
  php artisan migrate --force
  if [ "$SEED" = "true" ]; then
    php artisan db:seed --force
  fi
  exit 0
fi

# Opt-in auto-migrate (handy for single-replica dev; use the Job/migrate service otherwise).
if [ "$RUN_MIGRATIONS" = "true" ]; then
  php artisan migrate --force
fi

exec "$@"
