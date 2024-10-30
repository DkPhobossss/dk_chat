#!/bin/sh

cd /var/www/
mkdir -p storage/logs
chown -R www-data:www-data storage/
composer install --no-interaction --prefer-dist

php artisan migrate --force
php artisan migrate:status
#php artisan optimize
php artisan config:clear

php-fpm & php artisan reverb:start