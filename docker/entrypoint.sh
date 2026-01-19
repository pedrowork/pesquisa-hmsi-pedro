#!/usr/bin/env sh

set -e

mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

cd /var/www/html

php artisan migrate --force --no-interaction || true

mkdir -p storage/app/public || true
php artisan storage:link >/dev/null 2>&1 || {
  rm -rf public/storage
  mkdir -p public/storage
  cp -a storage/app/public/. public/storage/ 2>/dev/null || true
}
chown -R www-data:www-data public/storage storage/app/public || true

if php artisan list --no-interaction 2>/dev/null | grep -Eq '^[[:space:]]*filament:optimize([[:space:]]|$)'; then
  php artisan filament:optimize >/dev/null 2>&1 || true
fi

exec "$@"
