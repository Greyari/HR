#!/bin/bash
set -e

echo "🚀 Starting Laravel container..."

# -----------------------------
# Tunggu sampai database siap
# -----------------------------
echo "📡 Menunggu database MySQL..."

DB_HOST=${DB_HOST:-$MYSQLHOST}
DB_PORT=${DB_PORT:-$MYSQLPORT}
DB_USERNAME=${DB_USERNAME:-$MYSQLUSER}
DB_PASSWORD=${DB_PASSWORD:-$MYSQLPASSWORD}

echo "DB_HOST=$DB_HOST"
echo "DB_PORT=$DB_PORT"
echo "DB_USERNAME=$DB_USERNAME"

until MYSQL_PWD="$DB_PASSWORD" mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -e "SELECT 1;" > /dev/null 2>&1; do
  echo "⏳ Menunggu MySQL..."
  sleep 3
done

echo "✅ MySQL siap, lanjut proses Laravel..."

# -----------------------------
# Generate .env jika belum ada
# -----------------------------
if [ ! -f .env ]; then
    cp .env.example .env
fi

# -----------------------------
# Install dependencies
# -----------------------------
composer install --no-interaction --optimize-autoloader
composer dump-autoload -o

# Bersihkan cache dan cache ulang config
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# -----------------------------
# Migration
# -----------------------------
# Production → hanya migrate tanpa hapus data
php artisan migrate --force

# Uncomment baris berikut **hanya untuk testing/reset database**
# php artisan migrate:fresh --force
# php artisan db:seed --force

# -----------------------------
# Jalankan scheduler & queue worker
# -----------------------------
echo "🚀 Menjalankan scheduler & queue worker..."

# Scheduler jalan di background
php artisan schedule:work > /proc/1/fd/1 2>/proc/1/fd/2 &

# Queue worker jalan di background (background supaya Laravel server tetap listen)
# php artisan queue:work --sleep=3 --tries=3 > /proc/1/fd/1 2>/proc/1/fd/2 &
php artisan queue:work --tries=3 --sleep=3

# -----------------------------
# Jalankan Laravel HTTP server di foreground
# Supaya Railway bisa detect container "up"
# -----------------------------
echo "🎉 Menjalankan Laravel server di port 8080..."
exec php artisan serve --host=0.0.0.0 --port=8080
