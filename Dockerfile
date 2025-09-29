FROM php:8.2-fpm

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    build-essential \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    libpq-dev \
    mariadb-client \
    nodejs \
    npm && \
    rm -rf /var/lib/apt/lists/*

# Install ekstensi GD (untuk PhpSpreadsheet)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd

# Install ekstensi PHP lain
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy semua file ke container
COPY . .

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Set permission storage & cache
RUN chmod -R 777 storage bootstrap/cache

# Build frontend (npm)
RUN npm install && npm run build

# Copy entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Gunakan entrypoint
ENTRYPOINT ["entrypoint.sh"]

# Railway pakai port 8080
EXPOSE 8080

# Default command → serve Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
