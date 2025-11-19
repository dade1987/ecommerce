# 🚀 Guida Completa Setup Sistema RAG e WebScraper

Guida passo passo per configurare tutto il sistema che Marco ha implementato, basata sulla documentazione nei commit recenti.

---

## 📋 Indice

1. [Prerequisiti](#prerequisiti)
2. [Setup Docker con Playwright](#setup-docker-con-playwright)
3. [Configurazione MongoDB Atlas](#configurazione-mongodb-atlas)
4. [Configurazione Variabili Ambiente](#configurazione-variabili-ambiente)
5. [Setup Database e Migrazioni](#setup-database-e-migrazioni)
6. [Verifica Installazione Playwright](#verifica-installazione-playwright)
7. [Creazione Vector Search Index su Atlas](#creazione-vector-search-index-su-atlas)
8. [Test del Sistema Completo](#test-del-sistema-completo)
9. [Troubleshooting](#troubleshooting)

---

## 🔧 Prerequisiti

Prima di iniziare, assicurati di avere:

- ✅ Docker e Docker Compose installati
- ✅ Account MongoDB Atlas (gratuito) → [https://cloud.mongodb.com/](https://cloud.mongodb.com/)
- ✅ Chiave API OpenAI configurata nel progetto
- ✅ Accesso al file `.env` del progetto

---

## 🐳 Step 1: Setup Docker con Playwright

### 1.1 Verifica Struttura Docker

Il sistema Docker è già configurato in `docker-dev-playwright/`. Verifica che esistano questi file:

```bash
ls -la docker-dev-playwright/
```

Dovresti vedere:
- `docker-compose.yml`
- `Dockerfile`
- `Dockerfile-php`
- `nginx.conf`
- `php.ini`

### 1.2 Verifica package.json

Assicurati che `package.json` nella root del progetto contenga Playwright:

```bash
cat package.json | grep playwright
```

Dovresti vedere qualcosa come:
```json
"playwright": "^1.56.1"
```

Se manca, aggiungilo:

```bash
npm install playwright@1.56.1 --save
```

### 1.3 Verifica File Script Playwright

Verifica che esistano questi file nella root:

```bash
ls -la scraper-headless-wrapper.sh scraper-headless.js .env.playwright
```

Se mancano, creali:

**`.env.playwright`**:
```bash
PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright
```

**`scraper-headless-wrapper.sh`** (già presente, verifica che sia eseguibile):
```bash
chmod +x scraper-headless-wrapper.sh
```

### 1.4 Build e Avvio Container Docker

```bash
cd docker-dev-playwright

# Build delle immagini (include installazione Playwright)
docker-compose build php

# Avvia tutti i container
docker-compose up -d

# Verifica che i container siano attivi
docker-compose ps
```

**Tempo stimato**: 5-10 minuti per il build (scarica Chromium)

**Output atteso**:
```
✅ php_fpm_avatar-3d-v1-dev    Up
✅ nginx_avatar-3d-v1-dev      Up
✅ mysql_db_avatar-3d-v1-dev    Up
✅ memcached_avatar-3d-v1-dev  Up
```

---

## 🗄️ Step 2: Configurazione MongoDB Atlas

### 2.1 Crea Account MongoDB Atlas

1. Vai su [https://cloud.mongodb.com/](https://cloud.mongodb.com/)
2. Crea un account gratuito (se non ce l'hai)
3. Crea un nuovo **Cluster** (scegli il tier gratuito M0)
4. Attendi che il cluster sia pronto (~5 minuti)

### 2.2 Configura Network Access

1. Nel menu laterale, vai su **"Network Access"**
2. Click **"Add IP Address"**
3. Seleziona **"Allow Access from Anywhere"** (per sviluppo) oppure aggiungi il tuo IP
4. Click **"Confirm"**

### 2.3 Crea Database User

1. Nel menu laterale, vai su **"Database Access"**
2. Click **"Add New Database User"**
3. Scegli **"Password"** come metodo di autenticazione
4. Inserisci:
   - **Username**: `avatar3d_user` (o quello che preferisci)
   - **Password**: Genera una password sicura e **SALVALA**!
5. Assegna ruolo: **"Atlas Admin"** (per sviluppo) o **"Read and write to any database"**
6. Click **"Add User"**

### 2.4 Ottieni Connection String

1. Nel menu laterale, vai su **"Database"**
2. Click **"Connect"** sul tuo cluster
3. Scegli **"Connect your application"**
4. Copia la connection string, sarà simile a:
   ```
   mongodb+srv://avatar3d_user:<password>@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority
   ```
5. **Sostituisci** `<password>` con la password che hai creato

### 2.5 Crea Database

1. Nel menu laterale, vai su **"Database"**
2. Click **"Browse Collections"**
3. Click **"Create Database"**
4. Inserisci:
   - **Database Name**: `avatar3d_rag`
   - **Collection Name**: `webscraper_pages` (verrà creata automaticamente dal codice)
5. Click **"Create"**

**Nota**: Il codice creerà automaticamente le collection `webscraper_pages` e `webscraper_chunks` quando farai la prima indicizzazione.

---

## ⚙️ Step 3: Configurazione Variabili Ambiente

### 3.1 Apri il file `.env`

```bash
nano .env  # oppure usa il tuo editor preferito
```

### 3.2 Aggiungi Configurazione MongoDB

Aggiungi queste righe nel file `.env`:

```env
# MongoDB Atlas Configuration
MONGODB_URI=mongodb+srv://avatar3d_user:TUA_PASSWORD_QUI@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority
MONGODB_DATABASE=avatar3d_rag
```

**⚠️ IMPORTANTE**: Sostituisci:
- `TUA_PASSWORD_QUI` con la password del database user
- `cluster0.xxxxx.mongodb.net` con il tuo cluster URL

### 3.3 Verifica Configurazione OpenAI

Assicurati che nel `.env` ci sia:

```env
OPENAI_API_KEY=sk-...  # La tua chiave API OpenAI
```

### 3.4 Verifica Configurazione Database MySQL

Per il database SQLite del WebScraper, verifica che esista:

```env
# WebScraper SQLite Database
WEBSCRAPER_DB_PATH=/var/www/html/storage/webscraper/webscraper.sqlite
```

### 3.5 Riavvia Container PHP

Dopo aver modificato il `.env`, riavvia il container PHP:

```bash
cd docker-dev-playwright
docker-compose restart php
```

---

## 🗃️ Step 4: Setup Database e Migrazioni

### 4.1 Crea Directory Storage

```bash
docker exec php_fpm_avatar-3d-v1-dev mkdir -p /var/www/html/storage/webscraper
docker exec php_fpm_avatar-3d-v1-dev chmod -R 775 /var/www/html/storage/webscraper
```

### 4.2 Esegui Migrazioni WebScraper

```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan migrate --path=src/Modules/WebScraper/database/migrations --database=webscraper
```

**Output atteso**:
```
✅ Migrating: 2025_XX_XX_create_scraped_pages_table
✅ Migrated:  2025_XX_XX_create_scraped_pages_table
✅ Migrating: 2025_XX_XX_create_search_result_caches_table
✅ Migrated:  2025_XX_XX_create_search_result_caches_table
```

### 4.3 Verifica Connessione MongoDB

Testa la connessione a MongoDB Atlas:

```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan tinker
```

Nel tinker, esegui:

```php
DB::connection('mongodb')->getMongoClient()->listDatabases();
```

Dovresti vedere il database `avatar3d_rag` nella lista.

---

## ✅ Step 5: Verifica Installazione Playwright

### 5.1 Verifica Chromium Installato

```bash
docker exec php_fpm_avatar-3d-v1-dev ls -la /var/www/.cache/ms-playwright/chromium-*/chrome-linux/chrome
```

**Output atteso**:
```
-rwxr-xr-x 1 www-data www-data 123456789 Nov 15 23:22 chrome
```

### 5.2 Test Wrapper Script

```bash
docker exec -u www-data php_fpm_avatar-3d-v1-dev \
    bash /var/www/html/scraper-headless-wrapper.sh "https://www.example.com"
```

**Output atteso**:
```
Chrome found
{"success":true,"url":"https://www.example.com","title":"Example Domain","html":"<!DOCTYPE html>..."}
```

### 5.3 Test da PHP

```bash
docker exec php_fpm_avatar-3d-v1-dev php -r "
require '/var/www/html/vendor/autoload.php';
\$scraper = new \Modules\WebScraper\Services\BrowserScraperService();
echo \$scraper->isAvailable() ? 'AVAILABLE' : 'NOT AVAILABLE';
"
```

**Output atteso**: `AVAILABLE`

---

## 🔍 Step 6: Creazione Vector Search Index su Atlas

### 6.1 Accedi a MongoDB Atlas

1. Vai su [https://cloud.mongodb.com/](https://cloud.mongodb.com/)
2. Seleziona il tuo cluster
3. Vai su **"Atlas Search"** (o **"Search Indexes"**)

### 6.2 Crea Vector Search Index

1. Click **"Create Search Index"**
2. Scegli **"JSON Editor"** (o **"Atlas Vector Search"** se disponibile)
3. Inserisci questi valori:

| Campo | Valore |
|-------|--------|
| **Database** | `avatar3d_rag` |
| **Collection** | `webscraper_chunks` |
| **Index Name** | `vector_index` |

**⚠️ IMPORTANTE**: Il nome dell'index DEVE essere esattamente `vector_index` (case-sensitive)

### 6.3 Configurazione JSON Index

Incolla questa configurazione nell'editor JSON:

```json
{
  "mappings": {
    "dynamic": true,
    "fields": {
      "embedding": {
        "type": "knnVector",
        "dimensions": 1536,
        "similarity": "cosine"
      },
      "page_id": {
        "type": "token"
      }
    }
  }
}
```

**Spiegazione configurazione**:
- `embedding.type`: `knnVector` = Vector search field
- `embedding.dimensions`: `1536` = Dimensioni di OpenAI text-embedding-3-small
- `embedding.similarity`: `cosine` = Cosine similarity (migliore per embeddings normalizzati)
- `page_id.type`: `token` = Per filtrare chunks per page_id

### 6.4 Crea l'Index

1. Click **"Next"**
2. Review la configurazione
3. Click **"Create Search Index"**

### 6.5 Attendi Build dell'Index

Lo stato passerà da **"Building"** a **"Active"** (ci vogliono alcuni minuti).

**Tempo stimato**:
- 10 chunks → ~1 minuto
- 100 chunks → ~2-3 minuti
- 1000+ chunks → ~5-10 minuti

**⚠️ IMPORTANTE**: L'index deve essere **"Active"** prima di poter fare ricerche!

---

## 🧪 Step 7: Test del Sistema Completo

### 7.1 Test Indicizzazione Sito

Indicizza un sito di test (es. un sito piccolo):

```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan rag:index-site "https://www.example.com" --max-pages=5
```

**Output atteso**:
```
🔍 Starting RAG indexing for: https://www.example.com
📊 Max pages: 5
⏳ Crawling site...
✅ Found 5 pages
📝 Indexing pages...
✅ Indexed 5 pages, 10 chunks
✨ Indexing completed!
```

**Tempo stimato**: ~30 secondi per 5 pagine

### 7.2 Verifica Dati su MongoDB Atlas

1. Vai su MongoDB Atlas → **"Database"** → **"Browse Collections"**
2. Seleziona database `avatar3d_rag`
3. Dovresti vedere:
   - Collection `webscraper_pages` con 5 documenti
   - Collection `webscraper_chunks` con ~10 documenti (ogni pagina genera ~2 chunks)

### 7.3 Verifica Vector Search Index

1. Vai su **"Atlas Search"**
2. Verifica che l'index `vector_index` sia **"Active"** ✅
3. Se è ancora "Building", aspetta qualche minuto

### 7.4 Test Ricerca RAG

```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan rag:search "https://www.example.com" "example domain"
```

**Output atteso**:
```
🔍 RAG Search
Domain: www.example.com
Query: example domain

📊 Indexed: 5 pages, 10 chunks

🤖 Searching with RAG...

✅ Found 5 relevant chunks

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📝 ANSWER:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[La risposta generata da GPT-4o-mini basata sui chunks trovati]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔗 SOURCES:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

+------------------+--------------------------------+--------+
| Title            | URL                            | Score  |
+------------------+--------------------------------+--------+
| Example Domain   | https://www.example.com/       | 0.9234 |
+------------------+--------------------------------+--------+

✨ Search completed!
⚡ Method: atlas_vector_search
```

### 7.5 Test Statistiche

```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan rag:stats
```

**Output atteso**:
```
📊 RAG System Statistics

🌍 GLOBAL STATS
+------------------+-------+
| Metric           | Value |
+------------------+-------+
| Total Pages      | 5     |
| Indexed Pages    | 5     |
| Failed Pages     | 0     |
| Total Chunks     | 10    |
| Avg Chunks/Page  | 2.0   |
+------------------+-------+
```

### 7.6 Test Browser Scraping

Testa lo scraping con browser headless:

```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan tinker
```

Nel tinker:

```php
use Modules\WebScraper\Facades\WebScraper;

$result = WebScraper::scrape('https://www.example.com');
print_r($result);
```

Dovresti vedere un array con i dati scrapati della pagina.

---

## 🐛 Troubleshooting

### Problema: "Chromium not found"

**Causa**: Browser non installato correttamente

**Soluzione**:
```bash
docker exec -u www-data php_fpm_avatar-3d-v1-dev bash -c \
    "cd /var/www/html && PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright npx playwright install chromium"
```

### Problema: "MongoDB connection failed"

**Causa**: Connection string errata o network access non configurato

**Soluzione**:
1. Verifica `.env` → `MONGODB_URI` è corretto?
2. Verifica MongoDB Atlas → Network Access → Il tuo IP è whitelisted?
3. Testa connessione:
   ```bash
   docker exec php_fpm_avatar-3d-v1-dev php artisan tinker
   ```
   ```php
   DB::connection('mongodb')->getMongoClient()->listDatabases();
   ```

### Problema: "Index not found" o "No relevant chunks found"

**Causa**: Vector Search Index non creato o non attivo

**Soluzione**:
1. Vai su MongoDB Atlas → Atlas Search
2. Verifica che esista l'index `vector_index`
3. Verifica che lo stato sia **"Active"** (non "Building")
4. Se manca, crealo seguendo lo Step 6

### Problema: "No chunks found" dopo indicizzazione

**Causa**: Similarity threshold troppo alto

**Soluzione**:
```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan rag:search "url" "query" --min-similarity=0.5
```

### Problema: Playwright script non funziona

**Causa**: Permessi o path errato

**Soluzione**:
```bash
# Verifica permessi
docker exec php_fpm_avatar-3d-v1-dev ls -la /var/www/html/scraper-headless-wrapper.sh

# Verifica .env.playwright
docker exec php_fpm_avatar-3d-v1-dev cat /var/www/html/.env.playwright

# Dovrebbe contenere:
# PLAYWRIGHT_BROWSERS_PATH=/var/www/.cache/ms-playwright
```

### Problema: Indexing molto lento

**Causa**: OpenAI API lenta o rate limits

**Soluzione**:
- È normale che l'indicizzazione sia lenta (~6 secondi per pagina)
- Per 500 pagine ci vogliono ~50 minuti
- Puoi limitare con `--max-pages=50` per test iniziali

---

## 📚 Documentazione Aggiuntiva

Per approfondimenti, consulta:

- **[README.md](src/Modules/WebScraper/docs/README.md)** - Panoramica modulo WebScraper
- **[PLAYWRIGHT_SETUP.md](src/Modules/WebScraper/docs/PLAYWRIGHT_SETUP.md)** - Setup dettagliato Playwright
- **[ATLAS_VECTOR_SEARCH_SETUP.md](src/Modules/WebScraper/docs/ATLAS_VECTOR_SEARCH_SETUP.md)** - Setup Atlas Vector Search
- **[RAG_COMPLETE_FLOW.md](src/Modules/WebScraper/docs/RAG_COMPLETE_FLOW.md)** - Flusso completo RAG
- **[RAG_INDEXING_PROCESS.md](src/Modules/WebScraper/RAG_INDEXING_PROCESS.md)** - Processo di indicizzazione
- **[RAG_LOCAL_ALTERNATIVES.md](docs/RAG_LOCAL_ALTERNATIVES.md)** - Alternative locali a MongoDB Atlas

---

## ✅ Checklist Finale

Prima di considerare il setup completo, verifica:

- [ ] Container Docker attivi e funzionanti
- [ ] Chromium installato e accessibile
- [ ] MongoDB Atlas cluster creato e accessibile
- [ ] Vector Search Index `vector_index` creato e **"Active"**
- [ ] Variabili `.env` configurate correttamente
- [ ] Migrazioni database eseguite
- [ ] Test indicizzazione sito completato con successo
- [ ] Test ricerca RAG funzionante
- [ ] Statistiche RAG mostrano dati corretti

---

**Ultima modifica**: 2025-11-16  
**Autore**: Guida basata su documentazione di Marco Presti



