#!/bin/bash
set -e

echo "🚀 Starting Laravel container..."
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

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --optimize-autoloader
composer dump-autoload -o

php artisan config:clear
php artisan cache:clear
php artisan config:cache

# -----------------------------
# Untuk Production → Hanya migrate tanpa hapus data
php artisan migrate --force

# -----------------------------
# Reset database & seed ulang → **Hanya untuk testing**
# php artisan migrate:fresh --force
# php artisan db:seed --force
# -----------------------------

echo "🚀 Menjalankan scheduler & queue worker..."

# Scheduler berjalan background
php artisan schedule:work > /proc/1/fd/1 2>/proc/1/fd/2 &

# Queue worker foreground untuk debug email
php artisan queue:work --sleep=3 --tries=3

# Supaya container tetap hidup
wait

exec "$@"
