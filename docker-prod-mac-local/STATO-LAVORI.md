# Stato Lavori - Docker Production Setup

**Data:** 2025-11-20

## Riepilogo

Setup Docker di produzione per Avatar 3D v1 completato con due varianti di immagine.

---

## Immagini Disponibili

| Versione | Tag | Dimensione | Uso |
|----------|-----|------------|-----|
| **Base (leggera)** | `avatar-3d-v1:latest` | ~908MB | Produzione su Plesk |
| **Con Playwright** | `avatar-3d-v1:latest-playwright` | ~2.63GB | Dev locale con browser scraping |

---

## Comandi Principali

### Build
```bash
# Versione leggera (produzione)
./docker-prod/build.sh latest

# Versione con Playwright (scraping)
./docker-prod/build.sh latest --playwright

# Esporta per upload su Plesk
./docker-prod/build.sh latest --save
./docker-prod/build.sh latest --playwright --save
```

### Test Locale
```bash
# Run versione leggera
./docker-prod/run-local.sh

# Run versione Playwright
./docker-prod/run-local.sh --playwright
```

### Comandi Utili Container
```bash
docker logs -f avatar-3d-v1-test
docker exec -it avatar-3d-v1-test bash
docker stop avatar-3d-v1-test
```

---

## File Creati/Modificati

### Directory `docker-prod/`
- `Dockerfile` - Versione base leggera
- `Dockerfile.playwright` - Versione con Node.js + Playwright
- `nginx.conf` - Configurazione nginx con fix Livewire
- `php.ini` - Configurazione PHP
- `supervisord.conf` - Supervisor per nginx, php-fpm, queue workers
- `www.conf` - Configurazione PHP-FPM pool
- `entrypoint.sh` - Script avvio container
- `build.sh` - Script build con opzione --playwright
- `run-local.sh` - Script test locale con opzione --playwright
- `docker-compose.prod.yml` - Docker Compose per produzione
- `DEPLOY-PLESK.md` - Documentazione deploy su Plesk

### Altri File
- `.dockerignore` - Esclusioni per build Docker
- `.dockerignore.dev` - Backup per riferimento
- `.env.prod.local` - Variabili ambiente per test locale
- `scraper-headless.js` - Rimosso path hardcoded, usa auto-discovery
- `scraper-headless-wrapper.sh` - Aggiornato per trovare versione Chromium automaticamente

### Fix Applicati
- `routes/web.php` - Rimossa route duplicata "home"
- `resources/views/errors/404.blade.php` - Corretto componente da `<x-layouts.app>` a `<x-app-layout>`
- `resources/js/app.js` - Aggiunto `props.teamSlug` per EnjoyTalk3D
- `resources/views/components/filament-fabricator/page-blocks/enjoy-talk-3d.blade.php` - Aggiunto `data-team-slug`

---

## Funzionalità Entrypoint

All'avvio del container vengono eseguiti automaticamente:

1. **Creazione directory storage** (cache, sessions, views, logs)
2. **Permessi storage** (775 per www-data)
3. **Storage link** (`public/storage` → `storage/app/public`)
4. **Database SQLite WebScraper** (se non esiste):
   - Crea `/var/www/html/storage/webscraper/webscraper.sqlite`
   - Esegue migration con `--database=webscraper`
5. **Cache configurazioni** (config, routes, views, events)
6. **Autoloader optimization**

---

## Problemi Risolti

1. **Livewire.js 404** → Aggiunto `location ^~ /livewire` in nginx.conf
2. **teamSlug vuoto in EnjoyTalk3D** → Passato via props da query params
3. **SQLite WebScraper non trovato** → Creato in entrypoint con migration
4. **Cache directory mancanti** → Create in entrypoint
5. **Playwright path hardcoded** → Rimosso, usa auto-discovery
6. **PECL build fallito** → Aggiunti autoconf, g++, make
7. **Debugbar ServiceProvider** → Rimosso config/debugbar.php
8. **Route duplicate** → Rimossa da routes/web.php
9. **Component layouts.app** → Corretto in 404.blade.php

---

## Configurazione Ambiente Produzione

### Variabili Richieste in Plesk
```env
APP_NAME="Avatar 3D v1"
APP_ENV=production
APP_KEY=base64:xxxxx
APP_DEBUG=false
APP_URL=https://demo-avatar.trentaduebit.it

DB_CONNECTION=mysql
DB_HOST=<plesk-mysql-host>
DB_PORT=3306
DB_DATABASE=avatar-3d-v1
DB_USERNAME=<user>
DB_PASSWORD=<password>

# MongoDB Atlas
MONGODB_URI=mongodb+srv://...
MONGODB_DATABASE=avatar3d_rag

# OpenAI
OPENAI_API_KEY=sk-proj-...
OPENAI_ASSISTANT_ID=asst_...

# HeyGen (se usato)
HEYGEN_API_KEY=...
```

### Opzionale
```env
RUN_MIGRATIONS=true  # Esegue php artisan migrate --force all'avvio
```

---

## Prossimi Passi

1. **Test completo** della versione leggera su http://localhost:8081
2. **Export immagine** con `./docker-prod/build.sh latest --save`
3. **Upload su Plesk** del file `avatar-3d-v1-latest.tar.gz`
4. **Configurazione variabili ambiente** in Plesk Docker
5. **Configurazione rete** per connessione MySQL Plesk
6. **DNS** per demo-avatar.trentaduebit.it

---

## Note Tecniche

### Stack Container
- PHP 8.3 FPM Alpine
- Nginx
- Supervisor (gestisce nginx, php-fpm, 2 queue workers)
- MongoDB extension con SSL per Atlas
- Memcached extension

### Playwright (solo versione --playwright)
- Node.js 22 Alpine
- Playwright 1.49.0
- Chromium headless (build 1148)
- Path: `/var/www/.cache/ms-playwright/chromium-1148/`

### Health Check
```
GET http://localhost/health
```
Restituisce "healthy" con status 200.

---

## Dominio Target
**demo-avatar.trentaduebit.it**