FROM webdevops/php-nginx:8.2

WORKDIR /app

COPY . .

# Render.com specific permissions fix
RUN mkdir -p /app/storage/framework/views \
    /app/storage/framework/cache \
    /app/storage/framework/sessions \
    /app/storage/logs

# Set wide permissions for Render.com environment
RUN chmod -R 777 /app/storage
RUN chmod -R 777 /app/bootstrap/cache

# Set ownership
RUN chown -R application:application /app/storage
RUN chown -R application:application /app/bootstrap/cache

RUN composer install --no-dev --optimize-autoloader
RUN php artisan config:cache
RUN php artisan route:cache

EXPOSE 80
CMD ["supervisord"]