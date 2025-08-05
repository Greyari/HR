#!/bin/bash

# Tunggu database siap (opsional, jika perlu)
echo "Menunggu MySQL..."
until mysqladmin ping -h"$DB_HOST" --silent; do
  sleep 1
done

# Generate key
php artisan key:generate

# Jalankan migrate dan seeder
php artisan migrate --force
php artisan db:seed --force

# Jalankan perintah utama dari Dockerfile (php artisan serve ...)
exec "$@"
