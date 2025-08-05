# Gunakan image PHP resmi dengan ekstensi Laravel
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

# Atur direktori kerja
WORKDIR /var/www

# Copy semua file ke container
COPY . .

# Install dependencies Laravel
RUN composer install --no-interaction --optimize-autoloader
RUN php artisan config:clear

# Set permission storage & bootstrap
RUN chmod -R 777 storage bootstrap/cache

# Copy entrypoint
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Jalankan script ketika container run
ENTRYPOINT ["/entrypoint.sh"]

# Laravel biasanya pakai port 8000, tapi Railway kamu pakai 8080
EXPOSE 8080

# Jalankan Laravel pakai built-in server
CMD php artisan serve --host=0.0.0.0 --port=8080
