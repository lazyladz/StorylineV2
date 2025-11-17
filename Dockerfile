FROM webdevops/php-nginx:8.2

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN php artisan config:cache
RUN php artisan route:cache

EXPOSE 80
CMD ["supervisord"]
