FROM php:8.2-fpm

# Install nginx and git
RUN apt-get update && apt-get install -y nginx git

WORKDIR /var/www/html

# Copy application
COPY . .

# Debug: Show what files we have
RUN echo "=== Debug: File Structure ===" && \
    ls -la /var/www/html/ && \
    echo "=== Public Directory ===" && \
    ls -la /var/www/html/public/ && \
    echo "=== Checking index.php ===" && \
    if [ -f /var/www/html/public/index.php ]; then echo "✓ index.php exists"; else echo "✗ index.php MISSING"; fi

# Simple nginx config that definitely works
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

# Create simple test files in the exact location nginx expects
RUN echo "<?php echo 'PHP TEST: File path is ' . __FILE__; ?>" > /var/www/html/public/test.php
RUN echo "<h1>HTML TEST: File path is /var/www/html/public/test.html</h1>" > /var/www/html/public/test.html
RUN echo "<?php echo 'Simple PHP works'; ?>" > /var/www/html/public/simple.php

# Install composer (if composer.json exists)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --prefer-dist; fi

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

# Start command
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"