#!/bin/sh

# Attendre la DB
echo "Waiting for database..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
  sleep 1
done

# Générer les clés Passport à chaque démarrage (nécessaire pour les environnements éphémères comme Render)
echo "Generating Passport keys..."
php artisan passport:keys --force --no-interaction

# Migrer la DB
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Créer le personal access client si manquant
echo "Checking personal access client..."
if ! php artisan passport:client --personal --no-interaction --name="Default Personal Access Client" 2>/dev/null; then
  echo "Personal access client already exists."
fi

# Seed la DB
echo "Seeding database..."
php artisan db:seed --force --no-interaction

# Lancer l'application avec supervisor (qui gère les queues et scheduler)
exec gosu laravel "$@"
