FROM webdevops/php-nginx:8.2

WORKDIR /app

COPY . .

# Set environment variables for nginx configuration
ENV WEB_DOCUMENT_ROOT=/app/public
ENV WEB_DOCUMENT_INDEX=index.php

RUN composer install --no-dev --optimize-autoloader
RUN php artisan config:cache
RUN php artisan route:cache

EXPOSE 80
CMD ["supervisord"]