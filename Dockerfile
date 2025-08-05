FROM php:8.2-fpm

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    libpq-dev \
    libmcrypt-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    mariadb-client \
    npm \
    nodejs

# Install ekstensi PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy semua file ke container
COPY . .

# Install dependencies Laravel
RUN composer install --no-interaction --optimize-autoloader

# Set permission
RUN chmod -R 777 storage bootstrap/cache

# Copy entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Gunakan entrypoint
ENTRYPOINT ["entrypoint.sh"]

# Laravel pakai port 8080 di Railway
EXPOSE 8080

# Ini perintah terakhir yang dijalankan setelah entrypoint
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
