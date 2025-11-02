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

# Créer la table blocked_accounts dans Neon si elle n'existe pas
echo "Checking and creating blocked_accounts table in Neon..."
php artisan tinker --execute="
try {
    \$tableExists = DB::connection('neon')->select(\"SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'blocked_accounts')\")[0]->exists ?? false;
    if (!\$tableExists) {
        DB::connection('neon')->statement(\"
            CREATE TABLE blocked_accounts (
                id UUID PRIMARY KEY,
                compte_id UUID,
                numero_compte VARCHAR(10),
                type_compte VARCHAR(255),
                client_id UUID,
                date_debut_blocage TIMESTAMP,
                date_fin_blocage TIMESTAMP NULL,
                motif TEXT,
                compte_data JSON,
                client_data JSON,
                archived_at TIMESTAMP,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )
        \");
        echo 'Table blocked_accounts created in Neon.';
    } else {
        echo 'Table blocked_accounts already exists in Neon.';
    }
} catch (Exception \$e) {
    echo 'Error creating table in Neon: ' . \$e->getMessage();
}
"

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
