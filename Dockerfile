# Dockerfile simplifié pour Laravel 10 API
FROM php:8.3-cli-alpine

# Installer les extensions PHP nécessaires pour Laravel et PostgreSQL
RUN apk add --no-cache postgresql-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev libxml2-dev curl-dev git unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql bcmath mbstring zip gd curl xml \
    && apk del git unzip

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Copier le code source
WORKDIR /var/www
COPY . .
RUN rm -f .env  # Supprimer .env pour éviter les conflits avec les variables d'environnement

# Installer les dépendances
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-interaction

# Publier les actifs Swagger UI
RUN mkdir -p public/swagger-ui
RUN cp -r vendor/swagger-api/swagger-ui/dist/* public/swagger-ui/

# Exposer le port
EXPOSE 10000

# Commande pour démarrer Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
