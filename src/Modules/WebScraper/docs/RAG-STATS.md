# rag:stats - Comando per Statistiche Sistema RAG

## 📋 Descrizione

Il comando `rag:stats` mostra statistiche dettagliate sul sistema RAG (Retrieval-Augmented Generation), includendo informazioni su pagine indicizzate, chunks generati, storage utilizzato e performance del sistema.

## 🎯 Sintassi

```bash
php artisan rag:stats [options]
```

### Opzioni

| Opzione | Descrizione |
|---------|-------------|
| `--domain` | Filtra statistiche per un dominio specifico |

## 🚀 Esempi d'Uso

### Statistiche Globali
```bash
php artisan rag:stats
```

### Statistiche per Dominio Specifico
```bash
php artisan rag:stats --domain=www.machebuoni.it
```

### Confronto Multi-Dominio
```bash
# Prima dominio
php artisan rag:stats --domain=www.example.com

# Poi altro dominio
php artisan rag:stats --domain=www.altro-sito.it
```

## 📊 Output Esempio

### Statistiche Globali

```
📊 RAG System Statistics

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🌍 Global Statistics
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Total Pages in Database:     856
├─ Indexed:                  850
├─ Processing:               3
├─ Failed:                   2
└─ Expired:                  1

Total Chunks:                3,420
Average Chunks per Page:     4.0
Total Words Indexed:         1,250,000

Storage Used:                ~45 MB
├─ Pages Collection:         ~15 MB
└─ Chunks Collection:        ~30 MB

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📈 Top Indexed Domains
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. www.machebuoni.it
   Pages: 326 | Chunks: 1,450 | Words: 450,000
   Indexed: 2025-11-16 10:30:00
   Expires: 2025-12-16 10:30:00

2. www.example.com
   Pages: 280 | Chunks: 1,120 | Words: 380,000
   Indexed: 2025-11-15 14:20:00
   Expires: 2025-12-15 14:20:00

3. www.isofin.it
   Pages: 244 | Chunks: 850 | Words: 420,000
   Indexed: 2025-11-14 09:15:00
   Expires: 2025-12-14 09:15:00

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚡ Performance Metrics
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

MongoDB Atlas Connection:    ✅ Connected
Vector Search Index:         ✅ Active (vector_index_1)
Embedding Model:             text-embedding-3-small (1536 dims)
LLM Model:                   gpt-3.5-turbo

Avg Vector Search Time:      ~150ms
Avg LLM Response Time:       ~2.5s
Cache Hit Rate (MySQL):      78%

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️  Warnings & Maintenance
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️  2 pages failed indexing (see logs for details)
⚠️  1 page expired and should be re-indexed
✅ 3 pages currently processing
```

### Statistiche per Dominio

```bash
php artisan rag:stats --domain=www.machebuoni.it
```

```
📊 RAG System Statistics - www.machebuoni.it

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🔍 Domain: www.machebuoni.it
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Total Pages:                 326
Status Breakdown:
├─ Indexed:                  326 (100%)
├─ Processing:               0 (0%)
├─ Failed:                   0 (0%)
└─ Expired:                  0 (0%)

Total Chunks:                1,450
Average Chunks per Page:     4.4
Total Words:                 450,000
Average Words per Page:      1,380

Storage Used:                ~16 MB
Last Indexed:                2025-11-16 10:30:00
Expires At:                  2025-12-16 10:30:00
Days Until Expiry:           30

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📄 Top Pages by Chunks
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. https://www.machebuoni.it/menu
   Chunks: 12 | Words: 3,500 | Title: "Menu Completo"

2. https://www.machebuoni.it/storia
   Chunks: 10 | Words: 2,850 | Title: "La Nostra Storia"

3. https://www.machebuoni.it/ingredienti
   Chunks: 9 | Words: 2,600 | Title: "Ingredienti di Qualità"

4. https://www.machebuoni.it/ricette
   Chunks: 8 | Words: 2,200 | Title: "Le Nostre Ricette"

5. https://www.machebuoni.it/about
   Chunks: 7 | Words: 1,950 | Title: "Chi Siamo"

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

💰 Estimated Costs (Last 30 Days)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Indexing Costs:
├─ Embeddings (1,450 chunks):    ~$0.03
└─ Total Indexing:               ~$0.03

Search Costs (estimated 500 queries/month):
├─ Query Embeddings:             ~$0.001
├─ LLM Responses:                ~$1.75
└─ Total Search:                 ~$1.75

Monthly Total:                   ~$1.78
```

## 💾 Dati Visualizzati

### Statistiche Globali

```php
[
  'total_pages' => 856,
  'indexed_pages' => 850,
  'processing_pages' => 3,
  'failed_pages' => 2,
  'expired_pages' => 1,
  'total_chunks' => 3420,
  'avg_chunks_per_page' => 4.0,
  'total_words' => 1250000,
  'storage_mb' => 45,
  'domains' => [
    [
      'domain' => 'www.machebuoni.it',
      'pages' => 326,
      'chunks' => 1450,
      'words' => 450000,
      'indexed_at' => '2025-11-16 10:30:00',
      'expires_at' => '2025-12-16 10:30:00',
    ],
    // ... altri domini
  ],
]
```

### Statistiche per Dominio

```php
[
  'domain' => 'www.machebuoni.it',
  'total_pages' => 326,
  'indexed_pages' => 326,
  'processing_pages' => 0,
  'failed_pages' => 0,
  'expired_pages' => 0,
  'total_chunks' => 1450,
  'avg_chunks_per_page' => 4.4,
  'total_words' => 450000,
  'avg_words_per_page' => 1380,
  'storage_mb' => 16,
  'last_indexed' => '2025-11-16 10:30:00',
  'expires_at' => '2025-12-16 10:30:00',
  'days_until_expiry' => 30,
  'top_pages' => [
    [
      'url' => 'https://www.machebuoni.it/menu',
      'chunk_count' => 12,
      'word_count' => 3500,
      'title' => 'Menu Completo',
    ],
    // ... altre pagine
  ],
]
```

## 📊 Metriche Dettagliate

### Status Pagine

| Status | Descrizione | Significato |
|--------|-------------|-------------|
| `indexed` | Pagina indicizzata correttamente | ✅ Pronta per ricerche RAG |
| `processing` | Indicizzazione in corso | ⏳ Ancora in elaborazione |
| `failed` | Indicizzazione fallita | ❌ Richiede attenzione |
| `expired` | TTL scaduto | ⚠️ Dovrebbe essere re-indicizzata |

### Storage Breakdown

**Pages Collection** (~15 MB):
- Metadati pagina (URL, title, domain)
- Contenuto full-text
- Status e timestamps

**Chunks Collection** (~30 MB):
- Embedding vectors (1536 float × chunks)
- Chunk content (max 1000 tokens)
- Relazioni page_id

**Calcolo Storage**:
```
Chunk size = 1536 float × 4 bytes + ~500 bytes text = ~6.6 KB
1000 chunks × 6.6 KB = ~6.6 MB
```

### Performance Metrics

| Metrica | Valore Tipico | Soglia Critica |
|---------|---------------|----------------|
| Vector Search Time | 50-200ms | >500ms |
| LLM Response Time | 1-3s | >5s |
| Cache Hit Rate | 60-80% | <40% |
| MongoDB Connection | Connected | Disconnected |
| Vector Index Status | Active | Inactive |

## 🔍 Interpretazione Risultati

### ✅ Sistema Sano
```
Total Pages: 850
Indexed: 850 (100%)
Failed: 0 (0%)
Avg Vector Search: 150ms
Cache Hit Rate: 78%
```
→ Tutto funziona correttamente!

### ⚠️ Attenzione Richiesta
```
Total Pages: 850
Indexed: 820 (96%)
Processing: 25 (3%)
Failed: 5 (1%)
Avg Vector Search: 450ms
```
→ Controllare:
1. Log per i 5 fallimenti
2. Performance MongoDB (slow queries?)
3. 25 pagine processing da >1h?

### ❌ Problemi Critici
```
Total Pages: 850
Indexed: 650 (76%)
Failed: 200 (24%)
Vector Index: ❌ Inactive
```
→ Azioni immediate:
1. Verificare Vector Index su Atlas
2. Check log errori indicizzazione
3. Re-index pagine fallite con `--force`

## 💰 Stima Costi

### Costi Indicizzazione (Una Tantum)

**Embedding Generation**:
```
Costo = (Total Words / 1000) × $0.00002
Esempio: (1,250,000 / 1000) × 0.00002 = $0.025
```

### Costi Ricerca (Mensili)

**Per Query**:
- Embedding query: ~$0.000002
- LLM response (GPT-3.5-turbo): ~$0.0035
- **Totale/query**: ~$0.0035

**Mensile (500 queries)**:
```
500 queries × $0.0035 = $1.75/mese
```

### MongoDB Atlas Storage

| Tier | Storage | Costo |
|------|---------|-------|
| M0 (Free) | 512 MB | $0 |
| M10 | 10 GB | $0.08/GB/mese |
| M20 | 20 GB | $0.08/GB/mese |

**Esempio**: 45 MB storage su M0 = $0/mese ✅

## 🛠️ Manutenzione Consigliata

### Check Settimanale
```bash
# Verifica statistiche
php artisan rag:stats

# Controlla pagine scadute
php artisan rag:stats | grep "Expired"

# Verifica fallimenti
php artisan rag:stats | grep "Failed"
```

### Pulizia Mensile
```bash
# Rimuovi pagine scadute (TODO: comando da creare)
# php artisan rag:cleanup --expired

# Re-index pagine fallite
# php artisan rag:reindex --failed-only
```

## 🐛 Troubleshooting

### Output: "MongoDB connection failed"
**Soluzione**:
```bash
# Verifica .env
cat .env | grep MONGODB

# Test connessione manuale
docker exec php_fpm_avatar-3d-v1-dev php -r "
  \$client = new MongoDB\Client(getenv('MONGODB_URI'));
  var_dump(\$client->listDatabases());
"
```

### Output: "Vector index inactive"
**Soluzione**:
1. Accedi a MongoDB Atlas Console
2. Database → Browse Collections
3. Vai a `webscraper_chunks`
4. Tab "Search Indexes"
5. Verifica `vector_index_1` sia "Active"
6. Se inattivo → Click "Create Index" → usa config da [ATLAS-SEARCH.md](./ATLAS-SEARCH.md)

### Performance Degradata (>500ms vector search)
**Possibili Cause**:
1. Troppi chunks (>100K) → considera sharding
2. Query non ottimizzata → riduci `top_k`
3. Network latency → verifica connessione Atlas

**Soluzione**:
```bash
# Test performance diretta MongoDB
docker exec php_fpm_avatar-3d-v1-dev php artisan rag:search "https://test.com" "test query" --top-k=5
# Controlla log: "Vector search completed" → time?
```

## 📈 Metriche Avanzate

### Query Analysis (da implementare)
```bash
# Top 10 query più frequenti
# php artisan rag:stats --queries

# Query più lente
# php artisan rag:stats --slow-queries
```

### Domain Health Score
```
Score = (Indexed / Total) × 100 × (1 - Failed/Total)

Esempio:
- Total: 326
- Indexed: 326
- Failed: 0
Score = (326/326) × 100 × (1 - 0/326) = 100%
```

## 🔗 Comandi Correlati

- **`rag:index-site`** - Indicizza un sito web
- **`rag:search`** - Cerca nel contenuto indicizzato
- **`webscraper:clear-cache`** - Pulisce cache scraping

## 📚 Riferimenti

- [RAG Complete Flow](./RAG_COMPLETE_FLOW.md)
- [Atlas Vector Search Setup](./ATLAS_VECTOR_SEARCH_SETUP.md)
- [SiteIndexerService.php](../Services/SiteIndexerService.php)
- [MongoDB Atlas Docs](https://www.mongodb.com/docs/atlas/)