#!/bin/sh
set -e

echo "=== Avatar 3D v1 - Production Container Starting ==="
echo "APP_ENV: ${APP_ENV:-not set}"
echo "APP_URL: ${APP_URL:-not set}"

# Cambia UID/GID di www-data per matchare con l'utente Plesk
# Questo permette al container di scrivere sui volumi montati da Plesk
# Default: 82 (www-data standard Alpine)
# Plesk: PUID=10050 PGID=1004 (o l'UID/GID del tuo utente)
PUID=${PUID:-82}
PGID=${PGID:-82}

if [ "$PUID" != "82" ] || [ "$PGID" != "82" ]; then
    echo "Changing www-data UID:GID to ${PUID}:${PGID}..."

    # Prima elimina l'utente www-data esistente
    deluser www-data 2>/dev/null || true

    # Elimina il gruppo www-data esistente
    delgroup www-data 2>/dev/null || true

    # Verifica se il GID è già in uso da un altro gruppo
    EXISTING_GROUP=$(getent group "$PGID" | cut -d: -f1)

    if [ -n "$EXISTING_GROUP" ] && [ "$EXISTING_GROUP" != "www-data" ]; then
        echo "GID $PGID is used by group '$EXISTING_GROUP', removing it..."
        delgroup "$EXISTING_GROUP" 2>/dev/null || true
    fi

    # Verifica se l'UID è già in uso da un altro utente
    EXISTING_USER=$(getent passwd "$PUID" | cut -d: -f1)

    if [ -n "$EXISTING_USER" ] && [ "$EXISTING_USER" != "www-data" ]; then
        echo "UID $PUID is used by user '$EXISTING_USER', removing it..."
        deluser "$EXISTING_USER" 2>/dev/null || true
    fi

    # Crea il gruppo www-data con il nuovo GID
    addgroup -g "$PGID" www-data

    # Crea l'utente www-data con il nuovo UID
    adduser -D -u "$PUID" -G www-data -s /bin/sh www-data

    echo "www-data user created with UID:$PUID GID:$PGID"

    # Fix ownership delle directory applicazione
    chown -R www-data:www-data /var/www/html/bootstrap/cache 2>/dev/null || true
    chown www-data:www-data /var/www/html 2>/dev/null || true
fi

# Crea directory per logs se non esistono
mkdir -p /var/log/php
mkdir -p /var/log/nginx
mkdir -p /var/log/supervisor
chown -R www-data:www-data /var/log/php

# Crea directory storage necessarie (escluse dal .dockerignore)
echo "Creating storage directories..."
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/testing
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

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
    php artisan migrate --database=webscraper --path=src/Modules/WebScraper/database/migrations --force
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

# Reset OPcache per evitare problemi di caching stale
echo "Resetting OPcache..."
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache reset done\n'; } else { echo 'OPcache not available\n'; }" || true

echo ""
echo "=== Container ready! Starting services... ==="
echo ""

# Esporta variabili Azure per TTS server (Node.js)
# Il TTS server legge da variabili d'ambiente, non da .env Laravel
ENV_FILE="/var/www/html/.env"
if [ -f "$ENV_FILE" ]; then
    echo "Exporting Azure TTS variables from .env..."

    # Leggi AZURE_SPEECH_KEY
    AZURE_SPEECH_KEY=$(grep -E "^AZURE_SPEECH_KEY=" "$ENV_FILE" | cut -d'=' -f2- | tr -d '"' | tr -d "'")
    if [ -n "$AZURE_SPEECH_KEY" ]; then
        export AZURE_SPEECH_KEY
        echo "  AZURE_SPEECH_KEY: SET (${#AZURE_SPEECH_KEY} chars)"
    fi

    # Leggi AZURE_SPEECH_REGION
    AZURE_SPEECH_REGION=$(grep -E "^AZURE_SPEECH_REGION=" "$ENV_FILE" | cut -d'=' -f2- | tr -d '"' | tr -d "'")
    if [ -n "$AZURE_SPEECH_REGION" ]; then
        export AZURE_SPEECH_REGION
        echo "  AZURE_SPEECH_REGION: $AZURE_SPEECH_REGION"
    fi

    # Leggi AZURE_DEFAULT_VOICE (opzionale)
    AZURE_DEFAULT_VOICE=$(grep -E "^AZURE_DEFAULT_VOICE=" "$ENV_FILE" | cut -d'=' -f2- | tr -d '"' | tr -d "'")
    if [ -n "$AZURE_DEFAULT_VOICE" ]; then
        export AZURE_DEFAULT_VOICE
        echo "  AZURE_DEFAULT_VOICE: $AZURE_DEFAULT_VOICE"
    fi
fi

echo ""
echo "Comandi utili da eseguire nel container:"
echo "  docker exec -it <container> sh"
echo "  php artisan migrate:status"
echo "  php artisan queue:restart"
echo "  php artisan optimize:clear"
echo ""

# Esegue il comando passato (supervisord)
exec "$@"