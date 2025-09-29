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
# Hapus .env lama jika ada
# -----------------------------
if [ -f .env ]; then
    rm .env
fi

# -----------------------------
# Bersihkan tanda kutip otomatis Railway dari env vars
# -----------------------------
export CLOUDINARY_API_KEY=$(echo $CLOUDINARY_API_KEY | sed 's/^"\(.*\)"$/\1/')
export CLOUDINARY_API_SECRET=$(echo $CLOUDINARY_API_SECRET | sed 's/^"\(.*\)"$/\1/')
export CLOUDINARY_CLOUD_NAME=$(echo $CLOUDINARY_CLOUD_NAME | sed 's/^"\(.*\)"$/\1/')

# -----------------------------
# Buat file .env dari Railway vars
# -----------------------------
cat > .env <<EOL
APP_NAME=${APP_NAME}
APP_ENV=${APP_ENV}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG}
APP_URL=${APP_URL}
APP_TIMEZONE=${APP_TIMEZONE}

DB_CONNECTION=${DB_CONNECTION}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

QUEUE_CONNECTION=${QUEUE_CONNECTION}

MAIL_MAILER=${MAIL_MAILER}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}
MAIL_FROM_NAME=${MAIL_FROM_NAME}
BREVO_API_KEY=${BREVO_API_KEY}

CLOUDINARY_URL=${CLOUDINARY_URL}
CLOUDINARY_CLOUD_NAME=${CLOUDINARY_CLOUD_NAME}
CLOUDINARY_API_KEY=${CLOUDINARY_API_KEY}
CLOUDINARY_API_SECRET=${CLOUDINARY_API_SECRET}
EOL

# -----------------------------
# Install dependencies
# -----------------------------
composer install --no-interaction --optimize-autoloader
composer dump-autoload -o

# -----------------------------
# Clear config cache supaya env baru terbaca
# -----------------------------
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# -----------------------------
# Migration & Seed (opsional reset DB)
# -----------------------------
RESET_DB=true   # ganti ke true kalau mau fresh + seed

if [ "$RESET_DB" = "true" ]; then
  echo "⚠️ Jalankan migrate:fresh --seed (semua data akan direset)"
  php artisan migrate:fresh --seed --force
else
  echo "✅ Jalankan migrate --force (aman, tanpa reset data)"
  php artisan migrate --force
fi

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
