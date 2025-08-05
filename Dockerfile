# Gunakan image resmi Laravel yang ringan
FROM php:8.2-fpm

# Install dependensi sistem
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy semua file Laravel ke container
COPY . .

# Install dependensi Laravel
RUN composer install --no-dev --optimize-autoloader

# Set permission
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Jalankan Laravel command jika perlu (opsional)
# RUN php artisan config:cache
# RUN php artisan route:cache

# Expose port
EXPOSE 8000

# Jalankan Laravel server
CMD php artisan serve --host=0.0.0.0 --port=8000
