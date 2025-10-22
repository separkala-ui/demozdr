#!/bin/bash
# Fix cache permissions script
chmod -R 777 storage/framework/cache
chown -R www-data:www-data storage/framework/cache
chmod -R 777 bootstrap/cache
chown -R www-data:www-data bootstrap/cache
php artisan cache:clear
echo "Cache permissions fixed successfully"
