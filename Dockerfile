FROM php:8.2-fpm

# Install system dependencies INCLUDING ZIP TOOLS
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

WORKDIR /var/www/html

# Copy application
COPY . .

# Create Laravel directories
RUN mkdir -p \
    storage/framework/views \
    storage/framework/cache \
    storage/framework/sessions \
    storage/logs \
    bootstrap/cache

# Simple nginx config
RUN echo 'events {} \
http { \
    server { \
        listen 8080; \
        root /var/www/html/public; \
        index index.php index.html; \
        \
        location / { \
            try_files $uri $uri/ /index.php?$query_string; \
        } \
        \
        location ~ \.php$ { \
            fastcgi_pass 127.0.0.1:9000; \
            fastcgi_index index.php; \
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
            include fastcgi_params; \
        } \
    } \
}' > /etc/nginx/nginx.conf

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies (should work now with zip tools)
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache

# Create test files
RUN echo "<?php echo 'BASIC PHP WORKS: ' . date('Y-m-d H:i:s'); ?>" > /var/www/html/public/simple.php
RUN echo "<h1>HTML TEST WORKS</h1>" > /var/www/html/public/test.html

EXPOSE 8080

# Start command
CMD php-fpm -D && nginx -g 'daemon off;'