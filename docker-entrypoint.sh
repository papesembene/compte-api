#!/bin/sh

# Attendre la DB
echo "Waiting for database..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
  sleep 1
done

# Générer les clés Passport si elles n'existent pas
if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
  echo "Generating Passport keys..."
  php artisan passport:keys --force
fi

# Migrer et seed la DB
php artisan migrate --force
php artisan db:seed --force

# Lancer l'application
exec "$@"
