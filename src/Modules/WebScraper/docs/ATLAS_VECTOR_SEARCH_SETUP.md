# Atlas Vector Search Index Setup

Guida completa per configurare MongoDB Atlas Vector Search per il sistema RAG.

---

## 📋 Informazioni Database

Controlla il tuo file `.env` per verificare la configurazione:

```env
MONGODB_URI=mongodb+srv://username:password@cluster.mongodb.net/?appName=AppName
MONGODB_DATABASE=avatar3d_rag
```

**Configurazione attiva:**
- **Cluster**: Definito in `MONGODB_URI`
- **Database**: `avatar3d_rag` (definito in `.env` → `MONGODB_DATABASE`)
- **Collection**: `webscraper_chunks` (creata automaticamente dal codice)
- **Index Name**: `vector_index` (hardcoded in `ClientSiteQaService.php:104`)

---

## 🔧 Step 1: Accedi a MongoDB Atlas

1. Vai su [MongoDB Atlas](https://cloud.mongodb.com/)
2. Login con le tue credenziali
3. Seleziona il tuo cluster (es. `TestScraping`)

---

## 🔍 Step 2: Crea Vector Search Index

### 2.1 Naviga alla sezione Search

1. Nel menu del cluster, vai alla tab **"Atlas Search"** (o **"Search Indexes"**)
2. Click sul pulsante **"Create Search Index"**

### 2.2 Seleziona tipo di index

- Scegli **"Atlas Vector Search"** (se disponibile)
- Oppure **"JSON Editor"** per configurazione manuale

### 2.3 Configurazione Index

**Inserisci questi valori:**

| Campo | Valore |
|-------|--------|
| **Database** | `avatar3d_rag` |
| **Collection** | `webscraper_chunks` |
| **Index Name** | `vector_index` |

⚠️ **IMPORTANTE**: Il nome dell'index DEVE essere esattamente `vector_index` (vedi `ClientSiteQaService.php` linea 104)

### 2.4 Mapping JSON

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

### 2.5 Spiegazione Configurazione

| Campo | Descrizione |
|-------|-------------|
| `dynamic: true` | Indicizza automaticamente tutti i campi (utile per metadata) |
| `embedding.type` | `knnVector` = Vector search field |
| `embedding.dimensions` | `1536` = OpenAI text-embedding-3-small dimensioni |
| `embedding.similarity` | `cosine` = Cosine similarity (migliore per embeddings normalizzati) |
| `page_id.type` | `token` = Per filtrare chunks per page_id (opzionale) |

### 2.6 Alternative Similarity Metrics

```json
"similarity": "cosine"      // ✅ CONSIGLIATO per OpenAI embeddings
"similarity": "euclidean"   // Distanza euclidea (meno accurato)
"similarity": "dotProduct"  // Prodotto scalare (richiede normalizzazione)
```

---

## ⏱️ Step 3: Attendi Build dell'Index

Dopo aver creato l'index:

1. Atlas mostrerà lo stato: **"Building"** → Sta costruendo l'index
2. Attendi qualche minuto (dipende dal numero di documenti)
3. Stato diventa: **"Active"** ✅ → Pronto per le query!

**Tempo stimato:**
- 10 chunks → ~1 minuto
- 100 chunks → ~2-3 minuti
- 1000+ chunks → ~5-10 minuti

---

## ✅ Step 4: Verifica Funzionamento

### Test da Command Line

```bash
# Testa con una ricerca RAG
php artisan rag:search "https://www.machebuoni.it" "tagliatelle al ragù" --top-k=5
```

**Output atteso se funziona:**
```
✅ Found 5 relevant chunks
📝 ANSWER:
Secondo Source 1, le tagliatelle al ragù...

🔗 SOURCES:
- https://www.machebuoni.it/products/tagliatelle-ragu (Score: 0.92)
...
```

**Output se index mancante:**
```
⚠️  No relevant chunks found
💡 Try:
  - Lowering --min-similarity
  - Creating Atlas Vector Search Index
```

### Test dalla Chat

Chiedi al chatbot:
```
"Cerca nel sito www.machebuoni.it la ricetta delle tagliatelle al ragù"
```

Controlla i log in `storage/logs/webscraper-{date}.log`:
```
[INFO] ClientSiteQa: Vector search completed {"results_count":5}
```

---

## 🔍 Dove è definito il Database Name?

### Nel Codice

**File**: `config/database.php` (linea 88-99)

```php
'mongodb' => [
    'driver' => 'mongodb',
    'dsn' => env('MONGODB_URI'),
    'database' => env('MONGODB_DATABASE', 'avatar3d_rag'),  // ← QUI
    'options' => [
        'retryWrites' => true,
        'w' => 'majority',
    ],
],
```

**Fallback**: Se `MONGODB_DATABASE` non è definito nel `.env`, usa `avatar3d_rag` come default.

### Nel .env

**File**: `.env` (linea 26)

```env
MONGODB_DATABASE=avatar3d_rag
```

**Come cambiarlo:**

1. Modifica `.env`:
   ```env
   MONGODB_DATABASE=mio_nuovo_database
   ```

2. Ricrea il database su Atlas con il nuovo nome

3. Ricrea il Vector Search Index sul nuovo database

4. Re-indicizza i siti:
   ```bash
   php artisan rag:index-site "https://example.com" --max-pages=100
   ```

---

## 📊 Monitoraggio Index

### Atlas UI

1. Vai su **Atlas Search** → `vector_index`
2. Tab **"Metrics"**:
   - **Documents Indexed**: Numero di chunks indicizzati
   - **Query Count**: Numero di ricerche effettuate
   - **Avg Query Time**: Tempo medio di risposta

### Via Command Line

```bash
# Statistiche RAG system
php artisan rag:stats

# Output:
# Total Chunks: 324
# Avg Chunks/Page: 1.2
```

---

## 🐛 Troubleshooting

### Errore: "Index not found"

**Causa**: Index `vector_index` non esiste o non è attivo

**Soluzione**:
1. Verifica su Atlas che l'index esista
2. Controlla che lo stato sia **"Active"** (non "Building")
3. Verifica nome esatto: `vector_index` (case-sensitive)

### Errore: "Dimensions mismatch"

**Causa**: Index configurato con dimensioni diverse da 1536

**Soluzione**:
1. Elimina l'index su Atlas
2. Ricrealo con `"dimensions": 1536`

### No results found (0 chunks)

**Causa 1**: Vector Search Index non attivo su Atlas

**Verifica**:
1. Vai su MongoDB Atlas → Cluster → Tab "Atlas Search"
2. Cerca l'index `vector_index`
3. Verifica Status: deve essere **"Active"** ✅
   - Se è "Building" → aspetta che finisca
   - Se è "Failed" → ricrea l'index
   - Se manca → crealo seguendo la guida sopra

**Causa 2**: Nessun documento indicizzato

**Verifica**:
```bash
# Conta chunks in MongoDB
php artisan tinker --execute="echo DB::connection('mongodb')->getCollection('webscraper_chunks')->count();"

# Se ritorna 0, indicizza il sito:
php artisan rag:index-site "https://www.machebuoni.it" --max-pages=10
```

**Causa 3**: Similarity threshold troppo alta

**Soluzione**:
```bash
# Abbassa minSimilarity (default: 0.7)
php artisan rag:search "url" "query" --min-similarity=0.5

# Aumenta top-k (default: 5)
php artisan rag:search "url" "query" --top-k=10

# Test con threshold molto basso per debug
php artisan rag:search "url" "query" --min-similarity=0.3
```

**Causa 4**: Query embedding generation fallita

**Debug**:
Controlla log webscraper:
```bash
tail -f storage/logs/webscraper-$(date +%Y-%m-%d).log

# Cerca queste righe:
# ✅ "ClientSiteQa: Embedding generated" (query embedding OK)
# ✅ "ClientSiteQa: Vector search completed" (Atlas chiamato)
# ⚠️  "ClientSiteQa: No relevant chunks found" (nessun match)
```

### Performance lenta

**Causa**: Index non ottimizzato o numCandidates troppo alto

**Soluzione**: Modifica `ClientSiteQaService.php` linea 107:
```php
'numCandidates' => 50,  // Default: 100, riduci per query più veloci
```

---

## 📚 Riferimenti

- [MongoDB Atlas Vector Search Docs](https://www.mongodb.com/docs/atlas/atlas-vector-search/vector-search-overview/)
- [OpenAI Embeddings Guide](https://platform.openai.com/docs/guides/embeddings)
- Codice sorgente:
  - `src/Modules/WebScraper/Services/ClientSiteQaService.php` - Vector search logic
  - `src/Modules/WebScraper/Services/SiteIndexerService.php` - Indexing logic
  - `src/Modules/WebScraper/Services/EmbeddingService.php` - Embedding generation

---

## ⚡ Performance Tips

### 1. Ottimizza numCandidates

```php
// Più veloce, meno accurato
'numCandidates' => 50,

// Più lento, più accurato
'numCandidates' => 200,
```

### 2. Usa filtri domain

```php
// Filtra per dominio = query più veloce
$qaService->answerQuestion($query, 'www.machebuoni.it');

// Senza filtro = cerca in tutti i siti
$qaService->answerQuestion($query, null);
```

### 3. Cacheing

Il sistema usa già cache per risultati di ricerca (7 giorni TTL).

Vedi: `SearchResultCache` in `IntelligentCrawlerService.php`

---

**Ultima modifica**: 2025-11-16