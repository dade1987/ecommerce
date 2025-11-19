#!/bin/sh
set -e

echo "=== Avatar 3D v1 - Production Container Starting ==="
echo "APP_ENV: ${APP_ENV:-not set}"
echo "APP_URL: ${APP_URL:-not set}"

# Crea directory per logs se non esistono
mkdir -p /var/log/php
mkdir -p /var/log/nginx
mkdir -p /var/log/supervisor
chown -R www-data:www-data /var/log/php

# Verifica permessi storage
echo "Checking storage permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Verifica storage link
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

# Esegui migrazioni se richiesto (PRIMA del caching)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Crea database SQLite per WebScraper se non esiste
WEBSCRAPER_DB_DIR="/var/www/html/storage/webscraper"
WEBSCRAPER_DB_FILE="${WEBSCRAPER_DB_DIR}/webscraper.sqlite"
if [ ! -f "$WEBSCRAPER_DB_FILE" ]; then
    echo "Creating WebScraper SQLite database..."
    mkdir -p "$WEBSCRAPER_DB_DIR"
    touch "$WEBSCRAPER_DB_FILE"
    chown -R www-data:www-data "$WEBSCRAPER_DB_DIR"
    chmod 664 "$WEBSCRAPER_DB_FILE"
    echo "Running WebScraper migrations..."
    php artisan migrate --path=src/Modules/WebScraper/database/migrations --force
fi

# Cache delle configurazioni (solo se APP_ENV=production)
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configurations for production..."
    php artisan config:cache

    # Route cache può fallire se ci sono route duplicate
    php artisan route:cache || echo "WARNING: Route cache failed (possible duplicate route names)"

    php artisan view:cache || echo "WARNING: View cache failed"
    php artisan event:cache || true
fi

# Genera APP_KEY se non presente (solo per primo avvio)
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY not set! Generating one..."
    php artisan key:generate --force
fi

# Ottimizza autoloader
echo "Optimizing autoloader..."
composer dump-autoload --optimize --no-dev 2>/dev/null || true

echo ""
echo "=== Container ready! Starting services... ==="
echo ""
echo "Comandi utili da eseguire nel container:"
echo "  docker exec -it <container> sh"
echo "  php artisan migrate:status"
echo "  php artisan queue:restart"
echo "  php artisan optimize:clear"
echo ""

# Esegue il comando passato (supervisord)
exec "$@"