# Docker Dev Playwright

Ambiente Docker per sviluppo locale con supporto Playwright per browser scraping.

## Setup Iniziale

### 1. Symlink .env (OBBLIGATORIO)

Docker Compose ha bisogno delle variabili dal `.env` principale. Crea il symlink:

```bash
cd docker-dev-playwright
ln -sf ../.env .env
```

> **Nota:** Il symlink è in `.gitignore`, quindi va ricreato dopo ogni clone del repository.

### 2. Avvia i container

```bash
docker-compose up -d --build
```

## Container Disponibili

| Container | Porta | Descrizione |
|-----------|-------|-------------|
| nginx | 80, 443 | Web server con SSL |
| php | 5173 | PHP-FPM + Vite dev server |
| db | 3306 | MySQL |
| memcached | 11211 | Cache |
| phpmyadmin | 8080 | Database admin |
| tts | 3001 | Azure TTS con Visemi (Avatar 3D) |

## Azure TTS (Avatar 3D)

Il container `tts` fornisce text-to-speech con visemi per lip-sync dell'avatar 3D.

### Variabili richieste in `.env`:

```env
AZURE_SPEECH_KEY=<tua-chiave>
AZURE_SPEECH_REGION=italynorth
AZURE_DEFAULT_VOICE=it-IT-ElsaNeural
```

### Test endpoint:

```bash
# Health check
curl http://localhost:3001/health

# Sintesi vocale con visemi
curl -X POST http://localhost:3001/talk \
  -H "Content-Type: application/json" \
  -d '{"text": "Ciao, sono il tuo avatar!"}'
```

### Architettura:

```
PHP (Laravel) ──HTTP──▶ TTS Container (:3001)
                              │
                              ▼
                    public/avatar3d/audio/
                      (volume condiviso)
```

## Comandi Utili

```bash
# Logs di tutti i container
docker-compose logs -f

# Logs singolo container
docker logs -f tts_avatar-3d-v1-dev

# Accedi al container PHP
docker exec -it php_fpm_avatar-3d-v1-dev bash

# Ricostruisci singolo container
docker-compose up -d --build tts
```