#!/bin/sh
set -eu

if [ ! -f vendor/autoload.php ]; then
    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p writable/cache writable/logs writable/session public/uploads
chown -R www-data:www-data writable public/uploads
chmod -R 775 writable public/uploads

exec "$@"
