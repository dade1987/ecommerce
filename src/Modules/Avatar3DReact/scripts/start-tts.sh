#!/bin/sh
# TTS Server Startup Script
# Legge le variabili Azure dal .env di Laravel e avvia il server Node.js

ENV_FILE="/var/www/html/.env"

# Leggi e esporta variabili Azure
if [ -f "$ENV_FILE" ]; then
    export AZURE_SPEECH_KEY=$(grep -E "^AZURE_SPEECH_KEY=" "$ENV_FILE" | cut -d'=' -f2- | tr -d '"' | tr -d "'")
    export AZURE_SPEECH_REGION=$(grep -E "^AZURE_SPEECH_REGION=" "$ENV_FILE" | cut -d'=' -f2- | tr -d '"' | tr -d "'")
    export AZURE_DEFAULT_VOICE=$(grep -E "^AZURE_DEFAULT_VOICE=" "$ENV_FILE" | cut -d'=' -f2- | tr -d '"' | tr -d "'")

    echo "TTS Server starting with:"
    echo "  AZURE_SPEECH_KEY: ${AZURE_SPEECH_KEY:+SET (${#AZURE_SPEECH_KEY} chars)}"
    echo "  AZURE_SPEECH_REGION: $AZURE_SPEECH_REGION"
    echo "  AZURE_DEFAULT_VOICE: $AZURE_DEFAULT_VOICE"
fi

# Avvia Node.js server
exec node /var/www/html/src/Modules/Avatar3DReact/scripts/server.js