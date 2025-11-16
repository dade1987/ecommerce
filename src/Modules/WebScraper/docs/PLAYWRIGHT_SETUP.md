# Playwright Browser Scraping - Setup e Configurazione

## 📋 Panoramica

Il modulo WebScraper utilizza **Playwright** (headless browser) per scraping di siti con contenuti JavaScript-rendered o protezioni anti-bot.

## 🏗️ Architettura

### Componenti

1. **BrowserScraperService** (`Services/BrowserScraperService.php`)
   - Orchestrazione scraping con browser headless
   - Gestisce timeout e errori
   - Attivo per **tutti i siti** di default

2. **scraper-headless-wrapper.sh** (root progetto)
   - Script bash wrapper che carica variabili d'ambiente
   - Esegue come utente `www-data`
   - Verifica esistenza eseguibile Chromium

3. **scraper-headless.js** (root progetto)
   - Script Node.js che usa Playwright API
   - Configurazione anti-detection (stealth mode)
   - Ritorna JSON con HTML scaricato

4. **.env.playwright** (root progetto)
   - Configurazione path browser
   - Caricato automaticamente dal wrapper

### Directory Structure

```
/var/www/
├── .cache/
│   └── ms-playwright/
│       ├── chromium-1194/
│       │   └── chrome-linux/
│       │       └── chrome          # Eseguibile browser
│       ├── chromium_headless_shell-1194/
│       └── ffmpeg-1011/
└── html/
    ├── .env.playwright             # Config path browser
    ├── scraper-headless-wrapper.sh # Wrapper bash
    └── scraper-headless.js         # Script Playwright
```

## 🔧 Configurazione

### File: `.env.playwright`

```bash
PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright
```

**Perché `/var/www/.cache/`?**
- Segue standard XDG Base Directory Specification
- `www-data` user ha `HOME=/var/www`
- Playwright usa automaticamente `$HOME/.cache/ms-playwright`

### File: `scraper-headless-wrapper.sh`

```bash
#!/bin/bash
# Carica .env.playwright
source /var/www/html/.env.playwright

# Fallback se .env mancante
export PLAYWRIGHT_BROWSERS_PATH=${PLAYWRIGHT_BROWSERS_PATH:-/var/www/.cache/ms-playwright}

# Verifica esistenza Chromium
if [ -f "$PLAYWRIGHT_BROWSERS_PATH/chromium-1194/chrome-linux/chrome" ]; then
    echo "Chrome found" >&2
else
    echo "ERROR: Chrome NOT found" >&2
fi

# Esegue script Node.js
node /var/www/html/scraper-headless.js "$1"
```

### File: `scraper-headless.js`

```javascript
const { chromium } = require('playwright');

const browserPath = process.env.PLAYWRIGHT_BROWSERS_PATH || '/var/www/.cache/ms-playwright';
const executablePath = `${browserPath}/chromium-1194/chrome-linux/chrome`;

const browser = await chromium.launch({
    headless: true,
    executablePath: executablePath,
    args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-blink-features=AutomationControlled',
        // ... altre opzioni anti-detection
    ]
});
```

## 🚀 Installazione

### Durante Docker Build (automatico)

Il Dockerfile deve includere questi step:

```dockerfile
# 1. Installare Node.js e npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# 2. Installare dipendenze Playwright
RUN apt-get install -y \
    libnss3 \
    libatk-bridge2.0-0 \
    libdrm2 \
    libxkbcommon0 \
    libgbm1 \
    libasound2

# 3. Creare directory cache con ownership corretta
RUN mkdir -p /var/www/.cache && \
    chown -R www-data:www-data /var/www/.cache

# 4. Installare Playwright package
WORKDIR /var/www/html
RUN npm install playwright@1.56.1

# 5. Installare browser Chromium come www-data
USER www-data
RUN PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright \
    npx playwright install chromium

# 6. Tornare a root per il resto del setup
USER root
```

### Manualmente (se necessario)

```bash
# 1. Accedere al container
docker exec -it php_fpm_avatar-3d-v1-dev bash

# 2. Creare directory cache
mkdir -p /var/www/.cache
chown -R www-data:www-data /var/www/.cache

# 3. Installare come www-data
su - www-data -s /bin/bash
cd /var/www/html
PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright npx playwright install chromium
```

## ✅ Verifica Installazione

### Test 1: Verifica file eseguibile

```bash
docker exec php_fpm_avatar-3d-v1-dev ls -la /var/www/.cache/ms-playwright/chromium-1194/chrome-linux/chrome
```

Output atteso:
```
-rwxr-xr-x 1 www-data www-data 123456789 Nov 15 23:22 chrome
```

### Test 2: Test wrapper script

```bash
docker exec -u www-data php_fpm_avatar-3d-v1-dev \
    bash /var/www/html/scraper-headless-wrapper.sh "https://www.example.com"
```

Output atteso:
```
Running as user: www-data
Using PLAYWRIGHT_BROWSERS_PATH: /var/www/.cache/ms-playwright
Chrome executable found at: /var/www/.cache/ms-playwright/chromium-1194/chrome-linux/chrome
{"success":true,"url":"https://www.example.com","title":"Example Domain","html":"<!DOCTYPE html>..."}
```

### Test 3: Test da PHP

```bash
docker exec php_fpm_avatar-3d-v1-dev php -r "
    require '/var/www/html/vendor/autoload.php';
    \$scraper = new \Modules\WebScraper\Services\BrowserScraperService();
    echo \$scraper->isAvailable() ? 'AVAILABLE' : 'NOT AVAILABLE';
"
```

Output atteso: `AVAILABLE`

## 🐛 Troubleshooting

### Errore: "Chromium not found"

**Causa**: Browser non installato o path errato

**Soluzione**:
```bash
# Verifica path
docker exec php_fpm_avatar-3d-v1-dev cat /var/www/html/.env.playwright

# Reinstalla come www-data
docker exec -u www-data php_fpm_avatar-3d-v1-dev bash -c \
    "cd /var/www/html && PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright npx playwright install chromium"
```

### Errore: "EACCES: permission denied"

**Causa**: Directory cache non ha ownership corretta

**Soluzione**:
```bash
docker exec php_fpm_avatar-3d-v1-dev chown -R www-data:www-data /var/www/.cache
```

### Errore: "node: command not found"

**Causa**: Node.js non installato

**Soluzione**: Rifare build Docker con Node.js incluso

### Errore: "playwright not found"

**Causa**: npm package non installato

**Soluzione**:
```bash
docker exec php_fpm_avatar-3d-v1-dev bash -c "cd /var/www/html && npm install playwright@1.56.1"
```

## 📊 Performance e Caching

### Timeout Configuration

**File**: `config/webscraper.php`
```php
'browser_scraping' => [
    'timeout' => 150, // 2.5 minuti per POW captcha
],
```

### Caching Results

I risultati di browser scraping vengono cachati in SQLite (`SearchResultCache`) con TTL di 24h.

### Quando usare Browser vs HTTP

**BrowserScraperService.shouldUseBrowserScraping()** ritorna `true` per **tutti i siti** di default.

Per disabilitare selettivamente:
```php
// In BrowserScraperService.php
public static function shouldUseBrowserScraping(string $url): bool
{
    // Lista siti che NON necessitano browser
    $httpOnlyDomains = ['example.com'];

    foreach ($httpOnlyDomains as $domain) {
        if (stripos($url, $domain) !== false) {
            return false; // Usa HTTP semplice
        }
    }

    return true; // Default: usa browser
}
```

## 🔐 Sicurezza

### Anti-Detection Features

Lo script Playwright include:
- User-Agent realistici
- Viewport randomizzato
- JavaScript enabled
- Cookies e storage abilitati
- WebGL, Canvas fingerprinting simulation
- Timezone e locale configurati

### Permissions

- Browser gira come utente `www-data` (non root)
- Sandbox disabilitato solo dove necessario (`--no-sandbox` per Docker)
- Nessun accesso a file system esterno al container

## 📝 Logging

I log di browser scraping vanno in:
```
storage/logs/webscraper-{date}.log
```

Canale: `webscraper`

Esempio:
```
[INFO] BrowserScraper: Starting headless browser scrape
[INFO] BrowserScraper: Successfully scraped with headless browser
       url: https://example.com
       final_url: https://example.com/
       html_length: 1234
```

## 🔄 Aggiornamenti

### Aggiornare Chromium

```bash
# 1. Rimuovere vecchia versione
docker exec -u www-data php_fpm_avatar-3d-v1-dev rm -rf /var/www/.cache/ms-playwright/chromium-*

# 2. Reinstallare
docker exec -u www-data php_fpm_avatar-3d-v1-dev bash -c \
    "cd /var/www/html && PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright npx playwright install chromium"

# 3. Aggiornare versione in scraper-headless.js
# Cambiare: chromium-1194 → chromium-XXXX (nuova versione)
```

### Aggiornare Playwright

```bash
# 1. Aggiornare package
docker exec php_fpm_avatar-3d-v1-dev bash -c "cd /var/www/html && npm update playwright"

# 2. Reinstallare browser
docker exec -u www-data php_fpm_avatar-3d-v1-dev bash -c \
    "cd /var/www/html && PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright npx playwright install chromium"
```

---

**Versione Playwright**: 1.56.1
**Versione Chromium**: 141.0.7390.37 (build 1194)
**Ultimo aggiornamento**: 2025-11-15