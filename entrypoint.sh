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

php artisan config:clear
php artisan cache:clear
php artisan config:cache

# -----------------------------
# Migration
# -----------------------------
php artisan migrate --force

# -----------------------------
# Jalankan scheduler & Laravel server di background
# -----------------------------
echo "🚀 Menjalankan scheduler & Laravel server..."
php artisan schedule:work --verbose &
php artisan serve --host=0.0.0.0 --port=8080 &

# -----------------------------
# Jalankan queue worker di foreground supaya log muncul
# -----------------------------
echo "🚀 Menjalankan queue worker..."
exec php artisan queue:work --tries=3 --sleep=3 --verbose
