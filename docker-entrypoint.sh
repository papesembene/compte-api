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

# Migrer les tables de queue (jobs, failed_jobs, job_batches)
php artisan migrate --path=database/migrations/ --force
# Créer le personal access client si manquant
if ! php artisan passport:client --personal --no-interaction --name="Default Personal Access Client" 2>/dev/null; then
  echo "Personal access client already exists."
fi

# Vérifier si les tables existent avant de faire les migrations
echo "Checking if migrations are needed..."
if ! php artisan migrate:status --force | grep -q "blocked_accounts.*Ran"; then
    echo "Running migrations for blocked_accounts table..."
    php artisan migrate --path=database/migrations/2025_10_29_104333_create_blocked_accounts_table.php --force
fi

php artisan db:seed --force

# Lancer l'application avec supervisor (qui gère les queues et scheduler)
exec "$@"
