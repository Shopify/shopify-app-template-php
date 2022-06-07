#! /usr/bin/env sh

echo "Running database migrations..."
php artisan migrate

echo "Starting nginx server..."
openrc
touch /run/openrc/softlevel
rc-service nginx start

echo "Starting PHP server..."
php-fpm
