#!/bin/bash

echo "Build frontend assets (Vite)..."
npm install && npm run build

echo "Menunggu database MySQL..."

# Debug environment variables (sementara, bisa hapus setelah sukses)
echo "DB_HOST=$DB_HOST"
echo "DB_PORT=$DB_PORT"
echo "DB_USERNAME=$DB_USERNAME"

# Tunggu sampai MySQL bisa diakses
until MYSQL_PWD="$DB_PASSWORD" mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -e "SELECT 1;" > /dev/null 2>&1; do
  echo "Menunggu MySQL..."
  sleep 3
done

echo "✅ MySQL siap, jalankan migrate dan seeder..."

# Generate .env jika belum ada
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Bersihkan cache sebelum caching ulang
php artisan config:clear
php artisan cache:clear

# Cache ulang config
php artisan config:cache

# Migrasi dan seeder
php artisan migrate --force
php artisan db:seed --force

# Jalankan perintah dari CMD Dockerfile (php artisan serve ...)
exec "$@"
