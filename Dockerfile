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

# Installer cron et supervisor pour gérer les processus en arrière-plan
RUN apk add --no-cache dcron supervisor

# Créer les répertoires nécessaires pour supervisor
RUN mkdir -p /var/log/supervisor /etc/supervisor/conf.d

# Configuration supervisor pour les queues et scheduler
COPY <<EOF /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-jobs=1000 --timeout=90
directory=/var/www/html
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-queue-worker.log

[program:laravel-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan schedule:work
directory=/var/www/html
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-scheduler.log
EOF

USER laravel
EXPOSE 8000

# Configuration supervisor principale
COPY <<EOF /etc/supervisor/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:laravel-app]
command=php artisan serve --host=0.0.0.0 --port=8000
directory=/var/www/html
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-app.log

[include]
files = /etc/supervisor/conf.d/*.conf
EOF

# Commande par défaut via entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
