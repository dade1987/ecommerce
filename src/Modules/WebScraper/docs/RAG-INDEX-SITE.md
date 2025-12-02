# rag:index-site - Comando per Indicizzazione Siti Web

## 📋 Descrizione

Il comando `rag:index-site` indicizza un sito web completo nel sistema RAG (Retrieval-Augmented Generation) usando MongoDB Atlas Vector Search. Questo consente di effettuare ricerche semantiche veloci ed efficienti sul contenuto del sito.

## 🎯 Sintassi

```bash
php artisan rag:index-site {url} [options]
```

### Argomenti

- `url` **(required)** - L'URL del sito web da indicizzare (es. `https://www.example.com`)

### Opzioni

| Opzione | Default | Descrizione |
|---------|---------|-------------|
| `--max-pages` | `50` | Numero massimo di pagine da indicizzare |
| `--ttl` | `30` | Time-to-live in giorni (0 = mai scadenza) |
| `--force` | `false` | Forza la re-indicizzazione anche se già indicizzato |
| `--use-sitemap` | `auto` | Usa sitemap.xml invece di crawling |
| `--crawl` | `false` | Forza il crawling invece di usare sitemap |

## 🚀 Esempi d'Uso

### Esempio Base
```bash
php artisan rag:index-site "https://www.machebuoni.it"
```

### Indicizzazione Completa (500 pagine, 30 giorni TTL)
```bash
php artisan rag:index-site "https://www.machebuoni.it" --max-pages=500 --ttl=30
```

### Re-indicizzazione Forzata
```bash
php artisan rag:index-site "https://www.example.com" --force
```

### Indicizzazione Permanente (no scadenza)
```bash
php artisan rag:index-site "https://www.example.com" --ttl=0
```

### Forzare Crawling (ignora sitemap)
```bash
php artisan rag:index-site "https://www.example.com" --crawl
```

## 🔄 Flusso di Lavoro

Il comando segue questo processo:

1. **Controllo Esistente**
   - Verifica se il sito è già stato indicizzato
   - Se esistono pagine e `--force` non è specificato, chiede conferma

2. **Raccolta URL**
   - **Auto-detect** (default): Cerca sitemap.xml automaticamente
   - Se trovato sitemap → estrae tutti gli URL
   - Altrimenti → fallback a crawling intelligente
   - **--use-sitemap**: Forza l'uso di sitemap.xml
   - **--crawl**: Forza il crawling manuale del sito

3. **Indicizzazione Pagine**
   - Scarica ogni pagina (max: `--max-pages`)
   - Estrae contenuto pulito usando HtmlParser
   - Divide il contenuto in chunks (max 1000 tokens ciascuno)
   - Genera embedding per ogni chunk (OpenAI text-embedding-3-small, 1536 dim)
   - Salva in MongoDB Atlas:
     - Collection: `webscraper_pages`
     - Collection: `webscraper_chunks`

4. **Statistiche Finali**
   - Mostra summary dell'indicizzazione:
     - Pagine totali nel DB
     - Pagine indicizzate con successo
     - Pagine fallite
     - Chunks totali generati
     - Media chunks per pagina

## 📊 Output Esempio

```
🚀 Starting site indexing for: https://www.machebuoni.it
📊 Max pages: 500
⏰ TTL: 30 days

🔍 Checking for sitemap.xml...
✅ Found sitemap with 324 URLs
📡 Step 1/3: Fetching URLs from sitemap...
✅ Found 324 URLs from sitemap

🔍 Step 2/3: Indexing pages (chunking + embedding)...

  324/324 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100% 19 secs

📈 Step 3/3: Indexing statistics

+------------------------+-------+
| Metric                 | Value |
+------------------------+-------+
| Total Pages in DB      | 326   |
| Indexed Pages          | 326   |
| Failed Pages           | 0     |
| Processing Pages       | 0     |
| Total Chunks           | 1,450 |
| Avg Chunks/Page        | 4.4   |
| ---                    | ---   |
| This Session - Indexed | 324   |
| This Session - Skipped | 0     |
| This Session - Failed  | 0     |
+------------------------+-------+

✨ Site indexing completed!
💡 You can now use RAG search with: php artisan rag:search "{url}" "your question"
```

## 💾 Dati Salvati

### Collection: `webscraper_pages`
```json
{
  "_id": "ObjectId(...)",
  "url": "https://www.example.com/page",
  "domain": "www.example.com",
  "title": "Page Title",
  "content": "Full page content...",
  "word_count": 1500,
  "chunk_count": 5,
  "status": "indexed",
  "indexed_at": "2025-11-16T10:30:00Z",
  "expires_at": "2025-12-16T10:30:00Z"
}
```

### Collection: `webscraper_chunks`
```json
{
  "_id": "ObjectId(...)",
  "page_id": "ObjectId(...)",
  "chunk_index": 0,
  "content": "Chunk content (max 1000 tokens)...",
  "embedding": [0.123, -0.456, ...], // 1536 dimensions BSON array
  "word_count": 200
}
```

## 🔍 Atlas Vector Search Index

Il comando utilizza l'indice `vector_index_1` su MongoDB Atlas:

```json
{
  "fields": [
    {
      "type": "vector",
      "path": "embedding",
      "numDimensions": 1536,
      "similarity": "cosine"
    }
  ]
}
```

## ⚙️ Configurazione

### File: `config/webscraper.php`

```php
return [
    'scraping' => [
        'max_pages' => 50,
        'timeout' => 60,
        'delay' => 1000, // ms between requests
    ],
    'chunking' => [
        'max_tokens' => 1000,
        'overlap' => 100,
    ],
];
```

### File: `.env`

```env
MONGODB_URI=mongodb+srv://user:pass@cluster.mongodb.net/database
MONGODB_DATABASE=avatar3d_rag
OPENAI_API_KEY=sk-proj-xxx
```

## 📝 Note Importanti

### Dominio Normalizzazione
Il sistema normalizza automaticamente i domini con `www.`:
- Input: `https://example.com` → Stored as: `www.example.com`
- Questo garantisce coerenza nelle ricerche

### Chunking Strategy
- **Max tokens per chunk**: 1000
- **Overlap**: 100 tokens (per mantenere contesto tra chunks)
- **Embedding model**: OpenAI text-embedding-3-small (1536 dimensioni)

### TTL (Time-to-Live)
- **Default**: 30 giorni
- **0**: Mai scadenza
- Dopo scadenza, le pagine vengono marcate come `expired` e non compaiono nelle ricerche

### Costi API
- **Embedding**: ~$0.00002 per 1K tokens
- **Esempio**: 300 pagine × 5 chunks × 500 tokens = ~750K tokens = ~$0.015

## 🐛 Troubleshooting

### Errore: "No sitemap found, falling back to crawling"
**Soluzione**: Il sito non ha sitemap.xml. Usa `--max-pages` più basso o `--crawl` per crawling manuale.

### Errore: "Embedding generation failed"
**Soluzione**: Verifica `OPENAI_API_KEY` in `.env` e controlla il credito API OpenAI.

### Errore: "MongoDB connection failed"
**Soluzione**: Verifica `MONGODB_URI` in `.env` e assicurati che MongoDB Atlas sia accessibile.

### Warning: "This model's maximum context length is..."
**Soluzione**: Riduci `chunking.max_tokens` in `config/webscraper.php`.

## 🔗 Comandi Correlati

- **`rag:search`** - Cerca nel contenuto indicizzato
- **`rag:stats`** - Mostra statistiche sistema RAG
- **`webscraper:clear-cache`** - Pulisce cache scraping

## 📚 Riferimenti

- [RAG Complete Flow](./RAG_COMPLETE_FLOW.md)
- [Atlas Vector Search Setup](./ATLAS_VECTOR_SEARCH_SETUP.md)
- [OpenAI Embeddings Docs](https://platform.openai.com/docs/guides/embeddings)