# RAG Indexing Process - Documentazione Tecnica

## 📋 Panoramica

Il sistema RAG (Retrieval-Augmented Generation) indicizza i contenuti web per permettere ricerche semantiche veloci ed economiche.

Il processo di indicizzazione si divide in **3 fasi principali**:

1. **Crawling** - Scoperta delle pagine del sito
2. **Indexing** - Scraping, chunking, embedding e storage ⭐ **FASE PRINCIPALE**
3. **Statistics** - Riepilogo risultati

---

## 🔄 Fase 1: Crawling

**Servizio**: `WebScraperService::crawlSite()`

### Funzionamento

1. Parte dall'URL iniziale (es. `https://www.example.com`)
2. Scarica la pagina e estrae tutti i link interni
3. Segue ricorsivamente ogni link trovato
4. Si ferma quando:
   - Raggiunge il limite di pagine (`--max-pages`)
   - Non ci sono più link da seguire
   - Tutti i link sono già stati visitati

### Configurazione

```bash
php artisan rag:index-site "https://example.com" --max-pages=500
```

- **Delay tra richieste**: 1 secondo (configurabile in `config/webscraper.php`)
- **Cache**: Le pagine scrapate vengono salvate in SQLite cache
- **Filtri**: Solo link dello stesso dominio

### Tempo Stimato

- **500 pagine**: ~8-10 minuti
- Dipende da:
  - Velocità del sito target
  - Complessità della struttura dei link
  - Dimensione delle pagine

### Output

Array di pagine scrapate:
```php
[
  [
    'url' => 'https://example.com/page1',
    'content' => ['main' => '...', 'full' => '...'],
    'metadata' => ['title' => '...', 'description' => '...'],
    'links' => [...],
    // ...
  ],
  // ...
]
```

---

## 🎯 Fase 2: Indexing ⭐

**Servizio**: `SiteIndexerService::indexUrl()`

Questa è la **fase più importante e complessa**. Per ogni pagina trovata nel crawling:

### Step 2.1: Scraping Singola Pagina

**Metodo**: `WebScraperService::scrapeSingleUrl()`

```php
$scrapedData = $this->scraperService->scrapeSingleUrl($url);
```

**Cosa fa:**
- Scarica l'HTML (usa cache se disponibile dal crawling)
- Parsing con `HtmlParserService`
- Estrazione dati strutturati

**Output:**
```php
[
  'url' => 'https://example.com/page',
  'metadata' => [
    'title' => 'Titolo Pagina',
    'description' => 'Descrizione meta tag',
    'keywords' => 'keyword1, keyword2',
  ],
  'content' => [
    'main' => 'Testo pulito senza HTML...',  // ← USATO PER CHUNKING
    'full' => 'Tutto il testo incluso header/footer...',
    'headings' => ['H1', 'H2', ...],
  ],
  'raw_html' => '<html>...</html>',
]
```

### Step 2.2: Chunking del Contenuto

**Metodo**: `SiteIndexerService::chunkText()`

```php
$chunks = $this->chunkText($page->content);
```

**Configurazione:**
- **Chunk size**: 800 parole per chunk
- **Overlap**: 100 parole tra chunks consecutivi

**Perché overlap?**

L'overlap evita di "tagliare" concetti a metà tra due chunks. Un concetto importante può essere presente in entrambi i chunks adiacenti, migliorando le probabilità di match nella ricerca semantica.

**Esempio Pratico:**

```
Testo originale: 2000 parole totali

┌─────────────────────────┐
│   Chunk 1: 1-800        │
│   "La carbonara è..."   │
└─────────────────────────┘
           ↓
      ┌────────┐ ← Overlap 100 parole (701-800)
      └────────┘
           ↓
┌─────────────────────────┐
│   Chunk 2: 701-1500     │
│   "...guanciale..."     │
└─────────────────────────┘
           ↓
      ┌────────┐ ← Overlap 100 parole (1401-1500)
      └────────┘
           ↓
┌─────────────────────────┐
│   Chunk 3: 1401-2000    │
│   "...pecorino romano"  │
└─────────────────────────┘
```

**Codice:**

```php
protected function chunkText(string $text): array
{
    // Split in parole
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $totalWords = count($words);

    // Se il testo è più piccolo del chunk size, ritorna tutto
    if ($totalWords <= $this->chunkSize) {
        return [$text];
    }

    $chunks = [];
    $startIndex = 0;

    while ($startIndex < $totalWords) {
        // Estrai chunk di 800 parole
        $chunkWords = array_slice($words, $startIndex, $this->chunkSize);
        $chunkText = implode(' ', $chunkWords);
        $chunks[] = $chunkText;

        // Avanza di (800 - 100) = 700 parole
        // Così le ultime 100 parole si sovrappongono al chunk successivo
        $startIndex += ($this->chunkSize - $this->chunkOverlap);
    }

    return $chunks;
}
```

### Step 2.3: Generazione Embeddings ⏱️

**Servizio**: `EmbeddingService::generateEmbedding()`

**QUESTA È LA FASE PIÙ LENTA** perché richiede chiamate API a OpenAI.

**Per ogni chunk:**

```php
// Chiamata a OpenAI API
$embedding = $this->embeddingService->generateEmbedding($chunkText);
```

**API usata:** `text-embedding-3-small`

**Input:**
- Testo del chunk (max ~8000 token, ~6000 parole)
- Modello: `text-embedding-3-small`

**Output:**
- Array di **1536 float** (vettore embedding)

**Esempio embedding:**
```php
[
  0.023451,
  -0.145832,
  0.892341,
  -0.234567,
  // ... 1532 altri valori ...
  0.456789
]
```

**Cosa rappresenta?**

Il vettore embedding è una rappresentazione numerica del **significato semantico** del testo in uno spazio vettoriale a 1536 dimensioni.

Testi con significati simili avranno embeddings "vicini" nello spazio vettoriale (alta similarità del coseno).

**Esempio:**
```
"La carbonara è un piatto romano" → [0.23, -0.14, 0.89, ...]
"La carbonara è tipica di Roma"   → [0.25, -0.13, 0.87, ...] ← Simile!

"Il colosseo è a Roma"            → [0.12, 0.45, -0.67, ...] ← Diverso
```

**Tempo per chiamata:**
- Media: **2-3 secondi per chunk**
- Dipende da:
  - Latenza di rete
  - Carico sui server OpenAI
  - Dimensione del chunk

**Implementazione:**

```php
public function generateEmbedding(string $text): array
{
    try {
        $response = $this->client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $text,
        ]);

        return $response->data[0]->embedding;

    } catch (\Exception $e) {
        Log::error('EmbeddingService: Failed to generate embedding', [
            'error' => $e->getMessage(),
        ]);
        throw $e;
    }
}
```

### Step 2.4: Salvataggio in MongoDB Atlas

**Database**: MongoDB Atlas (cloud)
**Collections**: `webscraper_pages` e `webscraper_chunks`

#### A. Salva la pagina

**Collection**: `webscraper_pages`

```php
$page = new WebscraperPage();
$page->url = 'https://example.com/ricette/carbonara';
$page->url_hash = hash('sha256', $url); // Per unicità
$page->domain = 'example.com';
$page->title = 'Ricetta Carbonara Romana';
$page->description = 'La vera ricetta della carbonara';
$page->content = 'Testo completo della pagina...';
$page->raw_html = '<html>...</html>';
$page->word_count = 1500;
$page->chunk_count = 2; // Numero di chunks generati
$page->status = 'indexed';
$page->indexed_at = now();
$page->expires_at = now()->addDays(30); // TTL
$page->save();
```

#### B. Salva i chunks (con embeddings!)

**Collection**: `webscraper_chunks`

Per ogni chunk della pagina:

```php
$chunk = new WebscraperChunk();
$chunk->page_id = $page->_id; // Riferimento alla pagina
$chunk->content = 'La carbonara è un primo piatto...';
$chunk->chunk_index = 0; // 0, 1, 2, ...
$chunk->word_count = 800;
$chunk->embedding = [0.023, -0.145, 0.892, ..., 0.234]; // 1536 floats! ⭐
$chunk->chunk_hash = hash('sha256', $content);
$chunk->metadata = [
    'created_at' => now()->toIso8601String(),
];
$chunk->save();
```

**Nota importante:** L'array `embedding` contiene 1536 valori float che verranno utilizzati da MongoDB Atlas Vector Search per la ricerca semantica!

---

## ⏱️ Calcolo Tempi Fase 2

### Scenario Esempio: 500 Pagine

**Assunzioni:**
- Pagina media: **1000 parole**
- Chunks per pagina: **~2 chunks** (1° chunk 800 parole + 2° chunk 300 parole con overlap)
- Totale chunks: **500 × 2 = 1000 chunks**

### Breakdown Tempi per Pagina:

| Step | Operazione | Tempo |
|------|------------|-------|
| 2.1 | Scraping | ~0 sec (già in cache) |
| 2.2 | Chunking | ~0.1 sec (istantaneo) |
| 2.3 | Embedding (2 chunks) | 2 × 2.5 = **5 sec** ⏱️ |
| 2.4 | Salvataggio MongoDB | ~0.5 sec |
| **TOTALE** | **Per pagina** | **~6 secondi** |

### Tempo Totale Fase 2:

```
500 pagine × 6 secondi = 3000 secondi = 50 minuti
```

### Ottimizzazioni Possibili:

1. **Batch Embeddings** (Future)
   - OpenAI supporta fino a 2048 input per chiamata
   - Ridurrebbe drasticamente i tempi
   - Implementazione: `embeddings()->create(['input' => [...]])`

2. **Parallelizzazione** (Future)
   - Processare più pagine in parallelo
   - Laravel Queues + multiple workers
   - Attenzione ai rate limits OpenAI

3. **Skip Already Indexed**
   - Se la pagina esiste e non è expired → skip
   - Verifica tramite `url_hash`

---

## 💰 Costi Stimati

### OpenAI Embeddings API

**Modello**: `text-embedding-3-small`
**Prezzo**: **$0.02 per 1 milione di token**

### Calcolo per 500 Pagine:

| Item | Valore |
|------|--------|
| Pagine | 500 |
| Chunks per pagina | 2 |
| Totale chunks | 1000 |
| Token medi per chunk | ~500 |
| **Totale token** | **500,000** |
| **Costo** | **$0.01** 💵 |

**Un centesimo per indicizzare 500 pagine!** Molto economico 🎉

### Comparazione Costi:

| Operazione | Token | Costo |
|------------|-------|-------|
| Indicizzare 500 pagine | 500k | $0.01 |
| Rispondere 1 query (no RAG) | 5k | $0.0001 |
| Rispondere 100 query (con RAG) | 5k × 100 | $0.01 |

**Break-even:** Dopo ~100 query, il RAG è già conveniente!

---

## 📊 Monitoraggio Progresso

### Durante l'Indexing:

```bash
# Visualizza output comando in tempo reale
docker exec php_fpm_avatar-3d-v1-dev php artisan rag:index-site "https://example.com" --max-pages=500

# Log in tempo reale
docker exec php_fpm_avatar-3d-v1-dev tail -f storage/logs/webscraper.log
```

### Statistiche Database:

```bash
# Statistiche globali
php artisan rag:stats

# Statistiche per dominio specifico
php artisan rag:stats --domain=example.com
```

**Output esempio:**
```
📊 RAG System Statistics

🌍 GLOBAL STATS
+------------------+-------+
| Metric           | Value |
+------------------+-------+
| Total Pages      | 500   |
| Indexed Pages    | 485   |
| Failed Pages     | 15    |
| Processing Pages | 0     |
| Total Chunks     | 970   |
| Avg Chunks/Page  | 2.0   |
+------------------+-------+

🌐 BY DOMAIN
Domain: example.com
+---------+-------+--------+
| Status  | Pages | Chunks |
+---------+-------+--------+
| indexed | 485   | 970    |
| failed  | 15    | 0      |
+---------+-------+--------+
```

---

## 🔍 Utilizzo del Sistema RAG

Dopo l'indicizzazione, puoi fare ricerche semantiche:

### 1. Via Comando CLI:

```bash
php artisan rag:search "https://example.com" "Come si fa la carbonara?"
```

**Output:**
```
🔍 RAG Search
Domain: example.com
Query: Come si fa la carbonara?

📊 Indexed: 485 pages, 970 chunks

🤖 Searching with RAG...

✅ Found 5 relevant chunks

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📝 ANSWER:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Per preparare la carbonara romana autentica servono:
- Guanciale (non pancetta!)
- Uova (solo tuorli)
- Pecorino Romano
- Pepe nero

Procedimento: [...]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔗 SOURCES:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

+------------------+--------------------------------+--------+
| Title            | URL                            | Score  |
+------------------+--------------------------------+--------+
| Ricetta Carbonara| https://example.com/carbonara  | 0.9234 |
| Primi Piatti     | https://example.com/primi      | 0.8567 |
+------------------+--------------------------------+--------+
```

### 2. Via API (Chatbot):

Il chatbot usa automaticamente RAG quando l'utente chiede:
```
"mi cerchi sul sito www.example.com ricette con uova"
```

**Flusso:**
1. User query → function call `searchSite`
2. `ChatbotController` → `WebScraperService::searchWithRag()`
3. RAG search in MongoDB Atlas
4. Se trova chunks → risposta immediata (no scraping!)
5. Se non trova → fallback a scraping tradizionale + auto-indexing

---

## 🎯 Best Practices

### 1. Scegliere il giusto TTL

```bash
# Contenuti che cambiano spesso (news, blog)
--ttl=7  # 7 giorni

# Contenuti statici (documentazione, ricette)
--ttl=90  # 90 giorni

# Contenuti che non cambiano mai
--ttl=0  # Mai scadono
```

### 2. Limitare le pagine

```bash
# Test iniziale
--max-pages=50

# Sito medio
--max-pages=200

# Sito grande
--max-pages=1000
```

### 3. Re-indexing

```bash
# Forza re-indicizzazione (ignora cache e pagine già indicizzate)
php artisan rag:index-site "https://example.com" --force
```

### 4. Pulizia Pagine Expired

```bash
# TODO: Implementare comando
php artisan rag:cleanup-expired
```

---

## 🐛 Troubleshooting

### Problema: "No relevant chunks found"

**Soluzione:**
```bash
# Prova a ridurre la soglia di similarità
php artisan rag:search "https://example.com" "query" --min-similarity=0.5

# Aumenta il numero di chunks restituiti
php artisan rag:search "https://example.com" "query" --top-k=10
```

### Problema: Indexing lento

**Cause:**
- OpenAI API lenta → normale, dipende dal carico
- Molte pagine → usa `--max-pages` per limitare
- Pagine grandi → chunks più numerosi

**Monitoraggio:**
```bash
tail -f storage/logs/webscraper.log | grep "SiteIndexer"
```

### Problema: MongoDB connection timeout

**Soluzione:**
Verifica la stringa di connessione in `.env`:
```env
MONGODB_URI=mongodb+srv://user:pass@cluster.mongodb.net/?retryWrites=true&w=majority
```

---

## 📚 Riferimenti

- **OpenAI Embeddings**: https://platform.openai.com/docs/guides/embeddings
- **MongoDB Atlas Vector Search**: https://www.mongodb.com/docs/atlas/atlas-vector-search/
- **Cosine Similarity**: https://en.wikipedia.org/wiki/Cosine_similarity

---

*Documento creato: 2025-11-16*
*Ultima modifica: 2025-11-16*