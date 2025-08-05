#!/bin/bash

echo "Menunggu database MySQL..."

# Tunggu sampai MySQL bisa diakses
until mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SHOW DATABASES;" > /dev/null 2>&1; do
  echo "Menunggu MySQL..."
  sleep 3
done

echo "MySQL siap, jalankan migrate dan seeder..."

# Generate key jika belum
if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan config:clear
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force

# Jalankan perintah CMD dari Dockerfile (php artisan serve ...)
exec "$@"
