#!/bin/bash

# Tunggu MySQL agar siap
echo "Menunggu database siap..."
until php artisan migrate:status > /dev/null 2>&1; do
  sleep 2
done

# Cache config agar ENV dari Railway dibaca
php artisan config:clear
php artisan config:cache

# Jalankan migrate dan seed
echo "Menjalankan migrate dan seed..."
php artisan migrate --force
php artisan db:seed --force

# Jalankan Laravel server
php artisan serve --host=0.0.0.0 --port=8080
