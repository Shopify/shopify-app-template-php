#! /usr/bin/env bash

# Clean up openrc and nginx configurations
sed -i 's/hostname $opts/# hostname $opts/g' /etc/init.d/hostname
sed -i 's/#rc_sys=""/rc_sys="docker"/g' /etc/rc.conf
sed -i "s/listen PORT/listen $PORT/g" /etc/nginx/nginx.conf

cd /app

echo "Running database migrations..."
php artisan migrate --force

echo "Starting nginx server..."
openrc
touch /run/openrc/softlevel
rc-service nginx start

echo "Starting PHP server..."
php-fpm
