#!/bin/sh

# Attendre la DB
echo "Waiting for database..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
  sleep 1
done

# Configuration complète de Passport pour environnements éphémères
echo "Setting up Passport for ephemeral environments..."

# Créer le répertoire storage s'il n'existe pas
mkdir -p storage

# Générer les clés OAuth
echo "Generating OAuth keys..."
php artisan passport:keys --force --no-interaction

# Installer Passport (crée les clients et migrations)
echo "Installing Passport..."
php artisan passport:install --force --no-interaction

# Vérifier et s'assurer que les clés existent avec les bonnes permissions
if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
  echo "ERROR: OAuth keys not generated properly!"
  ls -la storage/
  exit 1
fi

# S'assurer que les clés sont lisibles
chmod 600 storage/oauth-private.key
chmod 644 storage/oauth-public.key
chown laravel:laravel storage/oauth-private.key storage/oauth-public.key

echo "OAuth keys generated and verified successfully"
echo "Private key permissions: $(ls -l storage/oauth-private.key)"
echo "Public key permissions: $(ls -l storage/oauth-public.key)"

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
