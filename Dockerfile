FROM php:8.2-fpm

# Install nginx and required extensions
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www/html

# Copy application
COPY . .

# Create nginx configuration
RUN echo 'server {' > /etc/nginx/sites-available/default
RUN echo '    listen 80;' >> /etc/nginx/sites-available/default
RUN echo '    server_name _;' >> /etc/nginx/sites-available/default
RUN echo '    root /var/www/html/public;' >> /etc/nginx/sites-available/default
RUN echo '    index index.php index.html;' >> /etc/nginx/sites-available/default
RUN echo '' >> /etc/nginx/sites-available/default
RUN echo '    location / {' >> /etc/nginx/sites-available/default
RUN echo '        try_files \$uri \$uri/ /index.php?\$query_string;' >> /etc/nginx/sites-available/default
RUN echo '    }' >> /etc/nginx/sites-available/default
RUN echo '' >> /etc/nginx/sites-available/default
RUN echo '    location ~ \.php\$ {' >> /etc/nginx/sites-available/default
RUN echo '        include fastcgi_params;' >> /etc/nginx/sites-available/default
RUN echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/sites-available/default
RUN echo '        fastcgi_index index.php;' >> /etc/nginx/sites-available/default
RUN echo '        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;' >> /etc/nginx/sites-available/default
RUN echo '    }' >> /etc/nginx/sites-available/default
RUN echo '}' >> /etc/nginx/sites-available/default

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# Cache configuration
RUN php artisan config:cache
RUN php artisan route:cache

EXPOSE 80

# Start both services
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"