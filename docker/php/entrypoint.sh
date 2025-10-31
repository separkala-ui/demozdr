#!/usr/bin/env bash
set -e

cd /var/www/html || exit 1

# Ensure required directories exist before the app boots
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
mkdir -p bootstrap/cache

# Relax permissions for shared directories inside the container
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

exec docker-php-entrypoint "$@"
