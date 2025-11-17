FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www/html

COPY . .

# Debug: List files to verify they exist
RUN echo "=== Checking file structure ===" && \
    ls -la /var/www/html/ && \
    echo "=== Checking public directory ===" && \
    ls -la /var/www/html/public/ && \
    echo "=== Checking index.php exists ===" && \
    ls -la /var/www/html/public/index.php

# Create test files
RUN echo "<?php echo 'PHP TEST: Works at ' . date('Y-m-d H:i:s'); ?>" > /var/www/html/public/test.php
RUN echo "<html><body><h1>HTML TEST: Works</h1><p>File: <?php echo __FILE__; ?></p></body></html>" > /var/www/html/public/test.html

# Simple nginx config
RUN echo 'server { \
    listen 8080; \
    root /var/www/html/public; \
    index index.php index.html; \
    \
    location / { \
        try_files \$uri \$uri/ /index.php?\$query_string; \
    } \
    \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name; \
        include fastcgi_params; \
    } \
}' > /etc/nginx/sites-available/default

RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Fix permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 8080

CMD php-fpm -D && nginx -g 'daemon off;'