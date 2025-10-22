#!/bin/bash
echo 'تنظیم مجوزهای Laravel...'
chown -R www-data:www-data storage/
chmod -R 775 storage/
chown -R www-data:www-data bootstrap/cache/
chmod -R 775 bootstrap/cache/
echo 'مجوزها تنظیم شدند ✓'
/var/www/zdr/php-www-data.sh cache:clear
/var/www/zdr/php-www-data.sh view:clear
/var/www/zdr/php-www-data.sh route:clear
/var/www/zdr/php-www-data.sh config:clear
echo 'کش Laravel پاک شد ✓'
