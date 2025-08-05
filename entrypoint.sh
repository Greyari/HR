#!/bin/bash

echo "Menunggu database MySQL..."

# Tunggu sampai MySQL bisa diakses
until mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SHOW DATABASES;" > /dev/null 2>&1; do
  echo "Menunggu MySQL..."
  sleep 3
done

echo "MySQL siap, jalankan migrate dan seeder..."

# Generate .env jika belum ada
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Bersihkan cache sebelum caching ulang
php artisan config:clear
php artisan cache:clear

# ❌ HAPUS BARIS INI:
# php artisan key:generate

# Baru cache ulang config
php artisan config:cache

# Migrasi dan seeder
php artisan migrate --force
php artisan db:seed --force

# Jalankan perintah dari CMD Dockerfile (php artisan serve ...)
exec "$@"
