# Étape 1 : Build des dépendances PHP
FROM composer:2.6 AS composer-build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Étape 2 : Image finale
FROM php:8.3-fpm-alpine

RUN apk add --no-cache postgresql-dev postgresql-client bash \
    && docker-php-ext-install pdo pdo_pgsql

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

WORKDIR /var/www/html

COPY --from=composer-build /app/vendor ./vendor
COPY . .

RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Copier script d'entrée
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

USER laravel
EXPOSE 8000

# Commande par défaut via entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
