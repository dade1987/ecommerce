# RAG System - Flusso Completo End-to-End

Documentazione completa del sistema RAG (Retrieval-Augmented Generation) per ricerche semantiche sui siti web indicizzati.

---

## 📋 Indice

1. [Panoramica Sistema](#panoramica-sistema)
2. [Architettura](#architettura)
3. [Flusso Completo](#flusso-completo)
4. [Componenti Principali](#componenti-principali)
5. [API Reference](#api-reference)
6. [Testing](#testing)
7. [Performance](#performance)
8. [Troubleshooting](#troubleshooting)

---

## 📊 Panoramica Sistema

Il sistema RAG permette di rispondere a domande su siti web utilizzando:
- **Embeddings vettoriali** (OpenAI text-embedding-3-small)
- **MongoDB Atlas Vector Search** per ricerca semantica
- **GPT-4o-mini** per generare risposte contestuali

### Vantaggi vs Scraping Tradizionale

| Metodo | Velocità | Costo | Accuratezza | Limitazioni |
|--------|----------|-------|-------------|-------------|
| **RAG** | ⚡ ~1-2 sec | 💰 $0.0001/query | 🎯 Alta (semantic) | Richiede indicizzazione |
| **Scraping** | 🐌 ~10-30 sec | 💰💰 $0.01/query | 🎯 Media (keyword) | Sempre disponibile |

---

## 🏗️ Architettura

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER REQUEST                              │
│  "Cerca nel sito www.machebuoni.it la ricetta tagliatelle"      │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                    OPENAI ASSISTANT (GPT-4o)                     │
│  Analizza richiesta → Chiama function searchSite()              │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              CHATBOT CONTROLLER (Laravel)                        │
│  case 'searchSite':                                             │
│    → WebScraperService::searchWithRag()                         │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              WEB SCRAPER SERVICE                                 │
│  Decision Layer:                                                │
│    1. Try RAG search (fast) ✅                                  │
│    2. If fails → Fallback to scraping                           │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              CLIENT SITE QA SERVICE                              │
│  Step 1: Generate query embedding [1536 floats]                │
│  Step 2: Vector search on MongoDB Atlas                        │
│  Step 3: Build context from top K chunks                       │
│  Step 4: Call GPT-4o-mini with context                         │
└────────────────────────┬────────────────────────────────────────┘
                         │
          ┌──────────────┴──────────────┐
          │                             │
          ▼                             ▼
┌──────────────────┐         ┌──────────────────────┐
│ EMBEDDING SERVICE│         │  MONGODB ATLAS       │
│ OpenAI API       │         │  Vector Search       │
│ text-embedding   │         │  Index: vector_index │
│ -3-small         │         │  Collection: chunks  │
└──────────────────┘         └──────────────────────┘
          │                             │
          │                             ▼
          │                  ┌──────────────────────┐
          │                  │ Top 5 Similar Chunks │
          │                  │ Score > 0.7          │
          │                  └──────────┬───────────┘
          │                             │
          └─────────────┬───────────────┘
                        ▼
          ┌──────────────────────────────┐
          │   GPT-4o-mini Generation     │
          │   With Context               │
          └──────────────┬───────────────┘
                        │
                        ▼
          ┌──────────────────────────────┐
          │   FINAL ANSWER TO USER       │
          │   + Sources                  │
          │   + Method: "rag"            │
          └──────────────────────────────┘
```

---

## 🔄 Flusso Completo Step-by-Step

### Esempio Query: *"Cerca nel sito www.machebuoni.it la ricetta delle tagliatelle al ragù"*

#### 🎯 FASE 1: User Input → AI Function Call

**1.1 User invia messaggio al chatbot**
```javascript
// Frontend (Vue.js)
fetch('/api/chatbot', {
    method: 'POST',
    body: JSON.stringify({
        message: "Cerca nel sito www.machebuoni.it la ricetta delle tagliatelle al ragù",
        team: "aziendasrl",
        thread_id: "thread_abc123"
    })
})
```

**1.2 AI Assistant analizza il prompt**

File: `resources/lang/it/chatbot_prompts.php` (linea 14)
```php
"Se fornisco un URL specifico e chiedo di cercare o trovare informazioni su quel sito,
esegui la function call searchSite."
```

**1.3 OpenAI Assistant chiama function**
```json
{
    "tool_calls": [{
        "function": {
            "name": "searchSite",
            "arguments": {
                "url": "https://www.machebuoni.it",
                "query": "ricetta tagliatelle al ragù",
                "max_pages": 10
            }
        }
    }]
}
```

---

#### ⚙️ FASE 2: Backend Processing

**2.1 ChatbotController riceve function call**

File: `app/Http/Controllers/Api/ChatbotController.php` (linea 387-426)
```php
case 'searchSite':
    $scraper = app(\Modules\WebScraper\Services\WebScraperService::class);
    $ragResult = $scraper->searchWithRag(
        'https://www.machebuoni.it',
        'ricetta tagliatelle al ragù',
        [
            'max_pages' => 10,
            'ttl_days' => 30,
            'top_k' => 5,              // Top 5 chunks
            'min_similarity' => 0.7,   // Similarity threshold
        ]
    );
```

**2.2 WebScraperService::searchWithRag()**

File: `src/Modules/WebScraper/Services/WebScraperService.php` (linea 516-665)
```php
public function searchWithRag(string $url, string $query, array $options): array
{
    $domain = parse_url($url, PHP_URL_HOST); // "www.machebuoni.it"

    // STEP 1: Try RAG search first (FAST)
    try {
        $qaService = app(ClientSiteQaService::class);
        $qaService->setTopK($options['top_k'] ?? 5);
        $qaService->setMinSimilarity($options['min_similarity'] ?? 0.7);

        $ragResult = $qaService->answerQuestion($query, $domain);

        if ($ragResult['chunks_found'] > 0) {
            return [
                'success' => true,
                'method' => 'rag',  // ✅ RAG USATO!
                'answer' => $ragResult['answer'],
                'sources' => $ragResult['sources'],
                'chunks_found' => $ragResult['chunks_found'],
            ];
        }
    } catch (\Exception $e) {
        Log::warning('RAG search failed, falling back to scraping');
    }

    // STEP 2: Fallback to traditional scraping (SLOW)
    // ... (only if RAG fails)
}
```

---

#### 🔍 FASE 3: RAG Search Process

**3.1 ClientSiteQaService::answerQuestion()**

File: `src/Modules/WebScraper/Services/ClientSiteQaService.php` (linea 41-89)

```php
public function answerQuestion(string $query, ?string $domain = null): array
{
    // STEP A: Generate query embedding
    $queryEmbedding = $this->embeddingService->generateEmbedding($query);
    // Input: "ricetta tagliatelle al ragù"
    // Output: [0.123, -0.456, 0.789, ...] (1536 floats)

    // STEP B: Vector search on MongoDB Atlas
    $similarChunks = $this->vectorSearch($queryEmbedding, $domain);
    // Returns: Top 5 chunks with scores > 0.7

    // STEP C: Build context from chunks
    $context = $this->buildContext($similarChunks);

    // STEP D: Generate answer with LLM
    $answer = $this->generateAnswer($query, $context);

    // STEP E: Extract sources
    $sources = $this->extractSources($similarChunks);

    return [
        'answer' => $answer,
        'sources' => $sources,
        'chunks_found' => count($similarChunks),
        'method' => 'atlas_vector_search',
    ];
}
```

**3.2 EmbeddingService::generateEmbedding()**

File: `src/Modules/WebScraper/Services/EmbeddingService.php`
```php
public function generateEmbedding(string $text): array
{
    $response = OpenAI::embeddings()->create([
        'model' => 'text-embedding-3-small',
        'input' => $text,
    ]);

    return $response->embeddings[0]->embedding; // [1536 floats]
}
```

**Input**: `"ricetta tagliatelle al ragù"`
**Output**: `[0.023, -0.145, 0.567, ..., 0.089]` (1536 dimensioni)
**Cost**: ~$0.00002 per query

---

#### 🗄️ FASE 4: MongoDB Atlas Vector Search

**4.1 Vector Search Query**

File: `ClientSiteQaService.php` (linea 98-172)
```php
protected function vectorSearch(array $queryEmbedding, ?string $domain): array
{
    // MongoDB aggregation pipeline
    $pipeline = [
        [
            '$vectorSearch' => [
                'index' => 'vector_index',        // ⚠️ ATLAS INDEX NAME
                'path' => 'embedding',            // Field with vectors
                'queryVector' => $queryEmbedding, // [1536 floats]
                'numCandidates' => 100,           // Candidates to consider
                'limit' => 5,                     // Top K results
            ]
        ],
        [
            '$addFields' => [
                'score' => ['$meta' => 'vectorSearchScore']  // Cosine similarity
            ]
        ],
    ];

    // Add domain filter
    if ($domain) {
        $pipeline[] = [
            '$lookup' => [
                'from' => 'webscraper_pages',
                'localField' => 'page_id',
                'foreignField' => '_id',
                'as' => 'page'
            ]
        ];
        $pipeline[] = [
            '$match' => [
                'page.domain' => 'www.machebuoni.it'  // Filter by domain
            ]
        ];
    }

    // Execute on MongoDB Atlas
    $results = DB::connection('mongodb')
        ->getCollection('webscraper_chunks')
        ->aggregate($pipeline)
        ->toArray();

    // Filter by minimum similarity
    $chunks = [];
    foreach ($results as $result) {
        if ($result['score'] >= 0.7) {  // min_similarity
            $chunks[] = [
                'chunk' => WebscraperChunk::find($result['_id']),
                'score' => $result['score'],
            ];
        }
    }

    return $chunks; // Top 5 chunks
}
```

**Example Results:**
```json
[
    {
        "chunk": {
            "content": "Le tagliatelle al ragù sono preparate con pasta fresca...",
            "page_id": "abc123"
        },
        "score": 0.92
    },
    {
        "chunk": {
            "content": "Ingredienti: pasta fresca, ragù di carne...",
            "page_id": "abc123"
        },
        "score": 0.85
    },
    // ... top 5 total
]
```

**Performance**: ~100-200ms (con index ottimizzato)

---

#### 🧩 FASE 5: Context Building

**5.1 Build Context String**

File: `ClientSiteQaService.php` (linea 180-207)
```php
protected function buildContext(array $similarChunks): string
{
    $contextParts = [];

    foreach ($similarChunks as $index => $item) {
        $chunk = $item['chunk'];
        $score = $item['score'];
        $page = $chunk->page;  // Load relationship

        $contextParts[] = sprintf(
            "[Source %d - %s (Score: %.2f)]\nTitle: %s\nURL: %s\n\n%s\n",
            $index + 1,
            $page->domain,
            $score,
            $page->title,
            $page->url,
            $chunk->content
        );
    }

    return implode("\n---\n\n", $contextParts);
}
```

**Example Output:**
```
[Source 1 - www.machebuoni.it (Score: 0.92)]
Title: Tagliatelle al Ragù - Ricetta Tradizionale
URL: https://www.machebuoni.it/products/tagliatelle-ragu

Le nostre tagliatelle al ragù sono preparate con pasta fresca artigianale
e un ragù di carne cotto lentamente per 6 ore. Ingredienti: pasta all'uovo,
ragù di manzo, parmigiano reggiano DOP. Tempo di cottura: 2 minuti nel microonde.

---

[Source 2 - www.machebuoni.it (Score: 0.85)]
Title: Primi Piatti - Catalogo
URL: https://www.machebuoni.it/collections/primi-piatti

Le tagliatelle al ragù sono uno dei nostri piatti più venduti. Disponibili
in porzioni da 350g e 500g. Confezione sottovuoto per massima freschezza.

---

[... 3 more sources ...]
```

---

#### 🤖 FASE 6: AI Answer Generation

**6.1 GPT-4o-mini con Context**

File: `ClientSiteQaService.php` (linea 217-262)
```php
protected function generateAnswer(string $query, string $context): string
{
    $systemPrompt = <<<EOT
Sei un assistente AI che risponde a domande basandoti ESCLUSIVAMENTE
sulle informazioni fornite nel contesto.

REGOLE:
1. Rispondi SOLO usando le informazioni presenti nel contesto
2. Se il contesto non contiene informazioni sufficienti, dillo chiaramente
3. Cita le fonti quando possibile (es. "Secondo [Source 1]...")
4. Sii conciso ma completo
5. Usa un tono professionale e amichevole
6. Rispondi in italiano

CONTESTO:
{$context}
EOT;

    $response = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $query],
        ],
        'temperature' => 0.7,
        'max_tokens' => 500,
    ]);

    return $response->choices[0]->message->content;
}
```

**Example Response:**
```
Secondo Source 1, le tagliatelle al ragù di Ma Che Buoni sono preparate con
pasta fresca artigianale e ragù di carne cotto lentamente per 6 ore. Gli
ingredienti principali sono pasta all'uovo, ragù di manzo e parmigiano
reggiano DOP. Come indicato in Source 2, questo piatto è uno dei più venduti
e viene fornito in confezione sottovuoto per garantire la massima freschezza.
Il tempo di cottura è di soli 2 minuti nel microonde.
```

**Cost**: ~$0.0001 per risposta (GPT-4o-mini)

---

#### 📤 FASE 7: Response to User

**7.1 Format Output**

File: `ChatbotController.php` (linea 402-417)
```php
$output = [
    'url' => 'https://www.machebuoni.it',
    'query' => 'ricetta tagliatelle al ragù',
    'analysis' => $ragResult['answer'],
    'method' => 'rag',  // ✅ Indicates RAG was used
    'sources' => [
        [
            'title' => 'Tagliatelle al Ragù',
            'url' => 'https://www.machebuoni.it/products/tagliatelle-ragu',
            'score' => 0.92
        ],
        // ... more sources
    ],
    'chunks_found' => 5
];
```

**7.2 Send to OpenAI**
```php
$toolOutputs[] = [
    'tool_call_id' => $toolCall->id,
    'output' => json_encode($output, JSON_UNESCAPED_UNICODE),
];

// Submit tool outputs
$client->threads()->runs()->submitToolOutputs(
    threadId: $threadId,
    runId: $run->id,
    parameters: ['tool_outputs' => $toolOutputs]
);
```

**7.3 Final Response to User**
```
🤖 Ma Che Buoni Chatbot:

Le tagliatelle al ragù di Ma Che Buoni sono preparate con pasta fresca
artigianale e ragù di carne cotto lentamente per 6 ore. Gli ingredienti
principali sono pasta all'uovo, ragù di manzo e parmigiano reggiano DOP.
Questo piatto è uno dei più venduti e viene fornito in confezione sottovuoto
per garantire la massima freschezza. Il tempo di cottura è di soli 2 minuti
nel microonde.

📚 Fonti:
• Tagliatelle al Ragù (Score: 0.92)
• Primi Piatti - Catalogo (Score: 0.85)
```

---

## 🧩 Componenti Principali

### 1. Models

#### WebscraperPage
```php
// File: src/Modules/WebScraper/Models/WebscraperPage.php
protected $connection = 'mongodb';
protected $table = 'webscraper_pages';

protected $fillable = [
    'url', 'url_hash', 'domain', 'title', 'description',
    'content', 'raw_html', 'metadata', 'word_count',
    'chunk_count', 'status', 'indexed_at', 'expires_at',
];

// Relationships
public function chunks()
{
    return $this->hasMany(WebscraperChunk::class, 'page_id');
}

// Scopes
public static function indexed() { /* ... */ }
public static function expired() { /* ... */ }

// Methods
public function isExpired(): bool
public function markAsIndexed(): void
public function markAsFailed(string $error): void
```

#### WebscraperChunk
```php
// File: src/Modules/WebScraper/Models/WebscraperChunk.php
protected $connection = 'mongodb';
protected $table = 'webscraper_chunks';

protected $fillable = [
    'page_id', 'content', 'chunk_index', 'word_count',
    'embedding', 'chunk_hash', 'metadata',
];

protected $casts = [
    'embedding' => 'array',  // ← 1536 floats
];

// Relationships
public function page()
{
    return $this->belongsTo(WebscraperPage::class, 'page_id');
}

// Methods
public static function generateChunkHash(string $content): string
```

### 2. Services

#### SiteIndexerService
```php
// File: src/Modules/WebScraper/Services/SiteIndexerService.php

// Chunking configuration
protected int $chunkSize = 800;      // words per chunk
protected int $chunkOverlap = 100;   // words overlap

// Main methods
public function indexUrl(string $url, ?int $ttlDays = 30): WebscraperPage
public function indexUrls(array $urls, ?int $ttlDays = 30): array
public function reindexExpiredPages(int $limit = 10): int
public function getStats(): array

// Internal methods
protected function chunkText(string $text): array
```

**Chunking Strategy:**
```
Original text: 2000 words

Chunk 1: words 1-800
Chunk 2: words 701-1500  (overlap: 700-800)
Chunk 3: words 1401-2000 (overlap: 1401-1500)
```

#### EmbeddingService
```php
// File: src/Modules/WebScraper/Services/EmbeddingService.php

public function generateEmbedding(string $text): array
{
    // OpenAI text-embedding-3-small
    // Input: string (max 8191 tokens)
    // Output: array of 1536 floats
    // Cost: $0.00002 per 1K tokens
}

public function generateEmbeddings(array $texts): array
{
    // Batch processing for efficiency
}
```

#### ClientSiteQaService
```php
// File: src/Modules/WebScraper/Services/ClientSiteQaService.php

// Configuration
protected int $topK = 5;              // Number of chunks
protected float $minSimilarity = 0.7; // Similarity threshold

// Public API
public function answerQuestion(string $query, ?string $domain = null): array
public function setTopK(int $topK): void
public function setMinSimilarity(float $minSimilarity): void

// Internal methods
protected function vectorSearch(array $queryEmbedding, ?string $domain): array
protected function buildContext(array $similarChunks): string
protected function generateAnswer(string $query, string $context): string
protected function extractSources(array $similarChunks): array
```

---

## 📚 API Reference

### Artisan Commands

#### rag:index-site
```bash
# Index entire website using sitemap
php artisan rag:index-site "https://www.machebuoni.it" --max-pages=324 --ttl=30

# Force crawling instead of sitemap
php artisan rag:index-site "https://example.com" --crawl --max-pages=50

# Force re-indexing
php artisan rag:index-site "https://example.com" --force
```

**Options:**
- `--max-pages=N`: Maximum pages to index (default: 50)
- `--ttl=N`: Time-to-live in days (0 = never expires, default: 30)
- `--force`: Force re-indexing even if already indexed
- `--use-sitemap`: Force sitemap usage
- `--crawl`: Force crawling instead of sitemap

#### rag:search
```bash
# Search with RAG
php artisan rag:search "https://www.machebuoni.it" "tagliatelle al ragù"

# With options
php artisan rag:search "https://example.com" "query" --top-k=10 --min-similarity=0.6
```

**Options:**
- `--top-k=N`: Number of chunks to retrieve (default: 5)
- `--min-similarity=N`: Minimum similarity score 0-1 (default: 0.7)

#### rag:stats
```bash
# Global statistics
php artisan rag:stats

# Filter by domain
php artisan rag:stats --domain=www.machebuoni.it
```

---

## 🧪 Testing

### Test RAG Search from CLI

```bash
# 1. Index a site
php artisan rag:index-site "https://www.machebuoni.it" --max-pages=50

# 2. Test search
php artisan rag:search "https://www.machebuoni.it" "tagliatelle al ragù"

# Expected output:
✅ Found 5 relevant chunks

📝 ANSWER:
Le tagliatelle al ragù di Ma Che Buoni sono preparate...

🔗 SOURCES:
- https://www.machebuoni.it/products/tagliatelle-ragu (Score: 0.92)

✨ Search completed!
⚡ Method: atlas_vector_search
```

### Test from Chatbot

**User message:**
```
Cerca nel sito www.machebuoni.it la ricetta delle tagliatelle al ragù
```

**Check logs:**
```bash
tail -f storage/logs/webscraper-$(date +%Y-%m-%d).log
```

**Expected log entries:**
```
[INFO] ClientSiteQa: Processing question {"query":"ricetta tagliatelle al ragù"}
[INFO] ClientSiteQa: Vector search completed {"results_count":5}
[INFO] ClientSiteQa: Answer generated {"chunks_found":5}
[INFO] WebScraper: searchWithRag completed {"method":"rag","chunks_found":5}
```

### Verify MongoDB Data

```bash
php artisan tinker

# Check indexed pages
>>> \Modules\WebScraper\Models\WebscraperPage::count()
=> 221

# Check chunks with embeddings
>>> \Modules\WebScraper\Models\WebscraperChunk::count()
=> 221

# Check embedding dimensions
>>> $chunk = \Modules\WebScraper\Models\WebscraperChunk::first();
>>> count($chunk->embedding)
=> 1536
```

---

## ⚡ Performance

### Benchmarks

| Operation | Time | Cost |
|-----------|------|------|
| **Index 1 page** | ~4 sec | $0.00002 |
| **Index 100 pages** | ~7 min | $0.002 |
| **Index 500 pages** | ~35 min | $0.01 |
| **RAG query** | ~1-2 sec | $0.0001 |
| **Scraping query** | ~10-30 sec | $0.01 |

### Optimization Tips

**1. Adjust numCandidates**
```php
// ClientSiteQaService.php (linea 107)
'numCandidates' => 50,  // Faster, less accurate
'numCandidates' => 200, // Slower, more accurate
```

**2. Use domain filters**
```php
// Always filter by domain for faster queries
$qaService->answerQuestion($query, 'www.machebuoni.it');
```

**3. Batch indexing**
```bash
# Index in batches during off-peak hours
for i in {1..10}; do
    php artisan rag:index-site "https://example.com" --max-pages=50
    sleep 60
done
```

---

## 🐛 Troubleshooting

### Issue: No chunks found

**Symptoms:**
```
⚠️  No relevant chunks found
```

**Causes:**
1. Site not indexed
2. min_similarity too high
3. Atlas index not created

**Solutions:**
```bash
# Check if site is indexed
php artisan rag:stats --domain=www.example.com

# Lower similarity threshold
php artisan rag:search "url" "query" --min-similarity=0.5

# Verify Atlas index exists (see ATLAS_VECTOR_SEARCH_SETUP.md)
```

### Issue: Slow queries (> 5 seconds)

**Causes:**
1. numCandidates too high
2. No domain filter
3. Index not optimized

**Solutions:**
```php
// Reduce numCandidates
'numCandidates' => 50,

// Always use domain filter
$qaService->answerQuestion($query, $domain);
```

### Issue: Fallback to scraping

**Log entry:**
```
[WARNING] RAG search failed, falling back to scraping
```

**Causes:**
1. Atlas index not found
2. Connection timeout
3. No indexed pages for domain

**Solutions:**
1. Verify Atlas index: `vector_index`
2. Check MongoDB connection in logs
3. Index the site first

---

## 📖 Related Documentation

- [ATLAS_VECTOR_SEARCH_SETUP.md](./ATLAS_VECTOR_SEARCH_SETUP.md) - Setup Atlas index
- [RAG_INDEXING_PROCESS.md](./RAG_INDEXING_PROCESS.md) - Indexing details
- [README.md](./README.md) - WebScraper module overview

---

**Ultima modifica**: 2025-11-16
**Autore**: Claude AI Assistant
**Versione**: 1.0