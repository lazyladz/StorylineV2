FROM php:8.2-fpm

# Install nginx
RUN apt-get update && apt-get install -y nginx

WORKDIR /var/www/html
COPY . .

# Replace the default nginx config entirely
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

# Create test files
RUN echo "<?php echo 'PHP is working!'; ?>" > /var/www/html/public/test.php
RUN echo "<h1>HTML is working!</h1>" > /var/www/html/public/test.html

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD php-fpm -D && nginx -g 'daemon off;'