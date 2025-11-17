FROM webdevops/php-nginx:8.2

WORKDIR /app

COPY . .
COPY nginx.conf /opt/docker/etc/nginx/vhost.common.d/

RUN composer install --no-dev --optimize-autoloader
RUN php artisan config:cache
RUN php artisan route:cache

EXPOSE 80
CMD ["supervisord"]