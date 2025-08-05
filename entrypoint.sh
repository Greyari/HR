#!/bin/bash

# Tunggu DB siap
echo "Menunggu database siap..."
until php artisan migrate:status > /dev/null 2>&1; do
  sleep 2
done

# Clear dan cache config agar baca dari Railway ENV
php artisan config:clear
php artisan config:cache

# Jalankan migrate dan seeder
echo "Menjalankan migrate dan seed..."
php artisan migrate --force
php artisan db:seed --force

# Jalankan Laravel
php artisan serve --host=0.0.0.0 --port=8080
