#!/bin/bash
set -e

echo "🚀 Starting Laravel container..."

echo "📡 Menunggu database MySQL..."

# Fallback ke env dari Railway
DB_HOST=${DB_HOST:-$MYSQLHOST}
DB_PORT=${DB_PORT:-$MYSQLPORT}
DB_USERNAME=${DB_USERNAME:-$MYSQLUSER}
DB_PASSWORD=${DB_PASSWORD:-$MYSQLPASSWORD}

echo "DB_HOST=$DB_HOST"
echo "DB_PORT=$DB_PORT"
echo "DB_USERNAME=$DB_USERNAME"

# Tunggu sampai MySQL siap
until MYSQL_PWD="$DB_PASSWORD" mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -e "SELECT 1;" > /dev/null 2>&1; do
  echo "⏳ Menunggu MySQL..."
  sleep 3
done

echo "✅ MySQL siap, lanjut proses Laravel..."

# Generate .env kalau belum ada
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Bersihkan cache
php artisan config:clear
php artisan cache:clear

# Cache ulang config
php artisan config:cache

# Migrasi dan seed ulang
echo "⚡ Jalankan migrate & seed..."
php artisan migrate:fresh --force
php artisan db:seed --force

echo "🎉 Aplikasi siap dijalankan!"

# Jalankan perintah dari CMD di Dockerfile
exec "$@"
