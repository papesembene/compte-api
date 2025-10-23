# Compte API - Déploiement Docker

Ce projet est une API Laravel 10 pour la gestion de clients et comptes bancaires, entièrement containerisée avec Docker pour le développement local et la production. Utilise un Dockerfile optimisé pour un déploiement sécurisé et performant.

## Architecture

- **Backend**: Laravel 10 avec PHP 8.2, containerisé pour cohérence dev/prod.
- **Base de données**: PostgreSQL (local via Docker, cloud via Render/Supabase/Neon).
- **Déploiement**: Docker pour tout (app, DB, pgAdmin), runtime PHP pour production sur Render.

## Fichiers Clés

### Dockerfile
- **Optimisé** : Single-stage build avec PHP 8.2-cli-alpine, extensions PostgreSQL, Composer, optimisations Laravel (config:cache, route:cache).
- **Sécurité** : Utilisateur non-root, healthcheck, nettoyage des deps.
- **Performance** : Image légère, CMD artisan serve pour API simple.
- **Port** : Expose 10000, compatible Render.

### docker-compose.yml
- **Services**:
  - `postgres`: Base de données PostgreSQL 15 avec volume persistant.
  - `app`: Application Laravel containerisée, monte le code pour dev.
  - `pgadmin`: Interface web pour gérer la DB (localhost:5050).
- **Ports**: App sur 10000, PostgreSQL sur 5433, pgAdmin sur 5050.
- **Healthchecks**: Vérifie la santé de tous les services.
- **Volumes**: Persistant pour DB, code monté pour dev.

### .dockerignore
- Exclut .env, logs, vendor, etc., pour build sécurisé et image légère.

### .env.example
- Configuration pour PostgreSQL (local et cloud).
- Variables pour DB_HOST (postgres pour local, URL cloud pour production).

## Déploiement Local

### Prérequis
- Docker et Docker Compose installés.

### Étapes
1. **Cloner le repo**:
   ```bash
   git clone <repo-url>
   cd compte-api
   ```

2. **Copier .env**:
   ```bash
   cp .env.example .env
   ```

3. **Générer APP_KEY**:
   ```bash
   php artisan key:generate --show
   ```
   Copier la clé dans .env.

4. **Lancer tous les services**:
   ```bash
   docker-compose up -d
   ```

5. **Migrer la DB**:
   ```bash
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed  # Optionnel
   ```

6. **Accéder aux outils**:
   - API: http://localhost:10000
   - Swagger: http://localhost:10000/api/documentation
   - pgAdmin: http://localhost:5050 (admin@admin.com / admin)

7. **Arrêter**:
   ```bash
   docker-compose down
   ```

## Déploiement sur Render

### Prérequis
- Compte Render avec service PostgreSQL (ou Supabase/Neon).
- Repo Git connecté à Render.

### Étapes
1. **Créer une DB PostgreSQL sur Render**:
   - Aller à https://render.com, créer un service PostgreSQL.
   - Noter l'URL externe (ex: postgresql://user:pass@host:5432/db).

2. **Connecter le repo**:
   - Dans Render Dashboard, créer un Web Service.
   - Connecter le repo Git.

3. **Configuration Render**:
   - **Runtime**: Docker
   - **Dockerfile Path**: Dockerfile
   - **Build Command**: (laisser vide, utilise Dockerfile)
   - **Start Command**: (laisser vide, utilise CMD du Dockerfile)
   - **Port**: 10000

4. **Variables d'environnement** (dans Render Dashboard):
   ```
   APP_NAME=Compte API
   APP_ENV=production
   APP_KEY=<générer avec php artisan key:generate --show>
   APP_DEBUG=false
   APP_URL=https://your-render-app.onrender.com

   DB_CONNECTION=pgsql
   DB_HOST=<DB_HOST de Render PostgreSQL>
   DB_PORT=5432
   DB_DATABASE=<DB_NAME>
   DB_USERNAME=<DB_USER>
   DB_PASSWORD=<DB_PASS>

   LOG_CHANNEL=stack
   LOG_LEVEL=error
   ```

5. **Déployer**:
   - Render build l'image Docker et déploie.
   - Accéder à https://your-render-app.onrender.com/api/documentation

### Bonnes Pratiques
- **Sécurité**: .env exclu via .dockerignore, variables d'environnement Render.
- **Optimisation**: Dockerfile optimisé, image légère, cache Laravel.
- **Cache**: Utiliser config:cache, route:cache pour la production.
- **DB**: Utiliser une DB cloud pour persistance.

## Explications Supplémentaires

- **Containerisation complète**: Docker pour app, DB et pgAdmin assure la parité dev/prod, reproductibilité et sécurité.
- **Optimisations DevOps**: Dockerfile léger, non-root, cache Laravel, healthchecks pour monitoring.
- **PostgreSQL Cloud**: Facile à intégrer avec Render PostgreSQL, Supabase ou Neon.
- **Production**: Image Docker optimisée pour Render, déploiement scalable et sécurisé.

Pour plus d'infos, voir la doc Laravel et Render.
