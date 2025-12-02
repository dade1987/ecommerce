# MongoDB Atlas Search Indexes - Configuration Reference

Questo documento contiene le configurazioni JSON complete per tutti gli indici Atlas Search necessari per il sistema RAG (Retrieval-Augmented Generation).

---

## 📍 Database e Collection

- **Database**: `avatar-3d-v1` (o il nome configurato in `.env`)
- **Collections**:
  - `webscraper_pages` - Pagine web indicizzate
  - `webscraper_chunks` - Chunks di testo con embeddings

---

## 1️⃣ Vector Search Index (vector_index_1)

### Collection: `webscraper_chunks`

**Nome indice**: `vector_index_1`

**Tipo**: Vector Search (Atlas Vector Search)

**Scopo**: Ricerca semantica usando embeddings OpenAI (1536 dimensioni)

### Configurazione JSON

```json
{
  "name": "vector_index_1",
  "type": "vectorSearch",
  "definition": {
    "fields": [
      {
        "type": "vector",
        "path": "embedding",
        "numDimensions": 1536,
        "similarity": "cosine"
      },
      {
        "type": "filter",
        "path": "domain"
      }
    ]
  }
}
```

### Note Importanti

- ✅ **Embedding dimensions**: 1536 (OpenAI text-embedding-3-small)
- ✅ **Similarity**: cosine (standard per OpenAI embeddings)
- ✅ **Filter field**: `domain` permette pre-filtering efficiente
- ⚠️ **Pre-filtering vs Post-filtering**:
  - Con `domain` come filter field → usa `filter` parameter in `$vectorSearch`
  - Senza filter field → usa `$match` post-filtering (attualmente implementato)

### Istruzioni Creazione

1. Vai su **MongoDB Atlas** → **Database** → **Browse Collections**
2. Seleziona database e collection: `webscraper_chunks`
3. Click su **"Search Indexes"** tab
4. Click **"Create Search Index"**
5. Seleziona **"JSON Editor"**
6. Seleziona **"Vector Search"** come tipo
7. Incolla la configurazione JSON sopra
8. Click **"Create Search Index"**

### Utilizzo nel Codice

**File**: `src/Modules/WebScraper/Services/ClientSiteQaService.php`

**Metodo**: `vectorSearch()`

```php
$pipeline = [
    [
        '$vectorSearch' => [
            'index' => 'vector_index_1',
            'path' => 'embedding',
            'queryVector' => $queryEmbedding,
            'numCandidates' => 500,
            'limit' => $this->topK,
            // Con domain come filter field, puoi usare:
            // 'filter' => ['domain' => ['$in' => $domainVariants]]
        ]
    ],
    // ... rest of pipeline
];
```

---

## 2️⃣ Text Search Index (text_search_index)

### Collection: `webscraper_chunks`

**Nome indice**: `text_search_index`

**Tipo**: Atlas Search (Text Search)

**Scopo**: Ricerca full-text con fuzzy matching, autocomplete, e faceting

### Configurazione JSON

#### Configurazione Base

```json
{
  "name": "text_search_index",
  "mappings": {
    "dynamic": false,
    "fields": {
      "content": {
        "type": "string",
        "analyzer": "lucene.standard"
      },
      "title": {
        "type": "autocomplete",
        "tokenization": "edgeGram",
        "minGrams": 2,
        "maxGrams": 15,
        "foldDiacritics": true
      },
      "domain": {
        "type": "string",
        "analyzer": "lucene.keyword"
      }
    }
  }
}
```

#### Configurazione Avanzata (Recommended - con Native Pre-filtering)

```json
{
  "name": "text_search_index",
  "mappings": {
    "dynamic": false,
    "fields": {
      "content": {
        "type": "string",
        "analyzer": "lucene.standard"
      },
      "title": {
        "type": "autocomplete",
        "tokenization": "edgeGram",
        "minGrams": 2,
        "maxGrams": 15,
        "foldDiacritics": true
      },
      "domain": [
        {
          "type": "string",
          "analyzer": "lucene.keyword"
        },
        {
          "type": "stringFacet"
        }
      ]
    }
  }
}
```

### Note Importanti

- ✅ **content**: Standard text search con stemming (lucene.standard)
- ✅ **title**: Autocomplete per search-as-you-type
- ✅ **domain (base)**: Keyword analyzer per exact match
- ✅ **domain (advanced)**: Array di tipi per native pre-filtering + facets
- ✅ **dynamic: false**: Solo campi definiti vengono indicizzati (performance)

**Differenza tra Base e Advanced**:
- **Base**: Domain come semplice string → Post-filtering con `$match`
- **Advanced**: Domain come array `[string, stringFacet]` → Native pre-filtering con `compound` query

### Istruzioni Creazione

1. Vai su **MongoDB Atlas** → **Database** → **Browse Collections**
2. Seleziona database e collection: `webscraper_chunks`
3. Click su **"Search Indexes"** tab
4. Click **"Create Search Index"**
5. Seleziona **"JSON Editor"**
6. Seleziona **"Search Index"** (NON Vector Search) come tipo
7. Incolla la configurazione JSON sopra
8. Click **"Create Search Index"**

### Utilizzo nel Codice

**File**: `src/Modules/WebScraper/Services/SearchIndexService.php`

**Metodo**: `search()`

#### Senza Domain Filter (Simple Text Search)

```php
$searchStage = [
    'index' => 'text_search_index',
    'text' => [
        'query' => $enhancedQuery,
        'path' => ['content', 'title'],
        'fuzzy' => [
            'maxEdits' => 2,
            'prefixLength' => 3,
        ]
    ]
];
```

#### Con Domain Filter (Compound Query - Native Pre-filtering)

```php
$searchStage = [
    'index' => 'text_search_index',
    'compound' => [
        'must' => [
            ['text' => [
                'query' => $enhancedQuery,
                'path' => ['content', 'title'],
                'fuzzy' => [
                    'maxEdits' => 2,
                    'prefixLength' => 3,
                ]
            ]]
        ],
        'filter' => [
            ['text' => [
                'query' => ['isofin.it', 'www.isofin.it'],
                'path' => 'domain'
            ]]
        ]
    ]
];
```

**Note**:
- La `compound` query applica il filtro domain **PRIMA** della text search
- Richiede che `domain` sia configurato come `[string, stringFacet]` nell'indice
- Migliore performance rispetto a `$match` post-filtering

---

## 🔀 Vector Search vs Text Search: Domain Filtering Syntax

### Differenze Chiave nel Filtering

MongoDB Atlas usa **sintassi diverse** per il filtering in Vector Search e Text Search:

| Aspetto | Vector Search | Text Search |
|---------|--------------|-------------|
| **Index Type** | Atlas Vector Search | Atlas Search |
| **Operator** | `$vectorSearch` | `$search` |
| **Filter Syntax** | `filter` parameter (diretto) | `compound` query |
| **Field Config** | `type: "filter"` | `type: "stringFacet"` |
| **Pre-filtering** | Native con `filter` | Native con `compound.filter` |

### Vector Search - Filter Syntax

**Index Configuration**:
```json
{
  "type": "filter",
  "path": "domain"
}
```

**Query Syntax**:
```php
$vectorSearchStage = [
    'index' => 'vector_index_1',
    'path' => 'embedding',
    'queryVector' => $queryEmbedding,
    'numCandidates' => 100,
    'limit' => 10,
    'filter' => [  // ← Parametro diretto
        'domain' => ['$in' => ['isofin.it', 'www.isofin.it']]
    ]
];

$pipeline = [
    ['$vectorSearch' => $vectorSearchStage],
    // ... rest
];
```

**Caratteristiche**:
- ✅ Sintassi diretta e semplice
- ✅ Usa standard MongoDB query operators (`$in`, `$eq`, etc.)
- ✅ Pre-filtering nativo ad alta performance

### Text Search - Compound Filter Syntax

**Index Configuration**:
```json
{
  "domain": [
    {"type": "string", "analyzer": "lucene.keyword"},
    {"type": "stringFacet"}
  ]
}
```

**Query Syntax**:
```php
$searchStage = [
    'index' => 'text_search_index',
    'compound' => [  // ← Usa compound query
        'must' => [
            ['text' => [
                'query' => 'contatti isofin',
                'path' => ['content', 'title']
            ]]
        ],
        'filter' => [  // ← Filter dentro compound
            ['text' => [
                'query' => ['isofin.it', 'www.isofin.it'],
                'path' => 'domain'
            ]]
        ]
    ]
];

$pipeline = [
    ['$search' => $searchStage],
    // ... rest
];
```

**Caratteristiche**:
- ✅ Richiede `compound` query structure
- ✅ `must` = condizioni obbligatorie (text search)
- ✅ `filter` = condizioni di filtro (non influenzano score)
- ⚠️ Sintassi più complessa ma flessibile

### Esempio Completo: Hybrid Search con Entrambi i Filtering

**Vector Search con Native Filter**:
```php
// ClientSiteQaService.php
$vectorSearchStage = [
    'index' => 'vector_index_1',
    'path' => 'embedding',
    'queryVector' => $queryEmbedding,
    'numCandidates' => 100,
    'limit' => 10,
    'filter' => [
        'domain' => ['$in' => ['isofin.it', 'www.isofin.it']]
    ]
];
```

**Text Search con Compound Filter**:
```php
// SearchIndexService.php
$searchStage = [
    'index' => 'text_search_index',
    'compound' => [
        'must' => [
            ['text' => [
                'query' => 'contatti isofin',
                'path' => ['content', 'title'],
                'fuzzy' => ['maxEdits' => 2, 'prefixLength' => 3]
            ]]
        ],
        'filter' => [
            ['text' => [
                'query' => ['isofin.it', 'www.isofin.it'],
                'path' => 'domain'
            ]]
        ]
    ]
];
```

**Risultato**:
- Entrambi applicano pre-filtering nativo
- Migliore performance rispetto a `$match` post-filtering
- Hybrid Search combina i risultati con RRF

### Perché Sintassi Diverse?

MongoDB ha sviluppato due tecnologie search separate:

1. **Atlas Vector Search** (2023):
   - Progettato specificamente per embeddings e similarity search
   - Sintassi ottimizzata per operazioni vettoriali
   - Filter integrato nativamente per alta performance

2. **Atlas Search** (basato su Lucene):
   - Tecnologia text search full-featured
   - Supporto completo per compound queries (must/should/filter/mustNot)
   - Più flessibile ma sintassi più complessa

---

## 3️⃣ Hybrid Search (RRF)

Il sistema combina **Vector Search** + **Text Search** usando l'algoritmo **Reciprocal Rank Fusion (RRF)**.

### Come Funziona

1. **Vector Search** trova chunks semanticamente simili (embedding cosine similarity)
2. **Text Search** trova chunks con keyword matching (Lucene text search)
3. **RRF** combina i risultati usando la formula:

```
RRF_score(doc) = Σ(weight / (k + rank))
```

Dove:
- `k = 60` (costante standard)
- `rank` = posizione del documento nei risultati (0-based)
- `weight` = peso del metodo (default 1.0 per entrambi)

### Vantaggi

- ✅ Nessuna normalizzazione di score necessaria
- ✅ Algoritmo industry-standard (usato da Elasticsearch)
- ✅ Migliori risultati combinando semantica + keywords
- ✅ Tolleranza ai typo (fuzzy matching del text search)

### Configurazione

**File**: `src/Modules/WebScraper/Services/HybridSearchService.php`

```php
$mergedResults = $this->reciprocalRankFusion(
    vectorResults: $vectorResults,
    textResults: $textResults,
    k: 60,
    vectorWeight: 1.0,
    textWeight: 1.0
);
```

---

## 📊 Query Enrichment (Ottimizzazione Domain Filtering)

Entrambi i servizi (Vector + Text Search) implementano **query enrichment** per migliorare la rilevanza semantica quando si filtra per domain.

### Logica

```php
// Input
$query = "contatti";
$domain = "isofin.it";

// Estrae company name dal domain
$companyName = preg_replace('/^www\./', '', $domain);    // rimuove www.
$companyName = preg_replace('/\.\w+$/', '', $companyName); // rimuove .it, .com, etc.

// Arricchisce query
$enrichedQuery = $query . ' ' . $companyName;  // "contatti isofin"
```

### Esempio

**Prima** (senza enrichment):
- Query: "contatti"
- Domain: "isofin.it"
- Vector results: 6 chunks (molti da altri domini)

**Dopo** (con enrichment):
- Query: "contatti isofin"
- Domain: "isofin.it"
- Vector results: 10 chunks (+67% rilevanza)

### Implementazione

**Vector Search** (`ClientSiteQaService.php:369-387`):
```php
$enrichedQuery = $query;
if ($domain) {
    $companyName = preg_replace('/^www\./', '', $domain);
    $companyName = preg_replace('/\.\w+$/', '', $companyName);
    $enrichedQuery = $query . ' ' . $companyName;
}
$queryEmbedding = $this->embeddingService->generateEmbedding($enrichedQuery);
```

**Text Search** (`SearchIndexService.php:143-158`):
```php
$enrichedQuery = $query;
if ($domain) {
    $companyName = preg_replace('/^www\./', '', $domain);
    $companyName = preg_replace('/\.\w+$/', '', $companyName);
    $enrichedQuery = $query . ' ' . $companyName;
}
// Usa enrichedQuery nella $search pipeline
```

---

## 🔍 Verifica Indici Esistenti

### Via MongoDB Compass

1. Connetti a MongoDB Atlas
2. Seleziona database `avatar-3d-v1`
3. Seleziona collection `webscraper_chunks`
4. Tab **"Indexes"** → vedi indici standard MongoDB
5. Tab **"Search Indexes"** → vedi indici Atlas Search

### Via Atlas UI

1. MongoDB Atlas Dashboard
2. **Database** → **Browse Collections**
3. Seleziona `webscraper_chunks`
4. Click **"Search Indexes"** tab
5. Verifica:
   - ✅ `vector_index_1` (type: Vector Search)
   - ✅ `text_search_index` (type: Atlas Search)

### Via MongoDB Shell

```javascript
// Connetti a MongoDB
mongosh "mongodb+srv://cluster.mongodb.net/avatar-3d-v1"

// Lista tutti gli indici Atlas Search
db.webscraper_chunks.aggregate([
  { $listSearchIndexes: {} }
])
```

---

## ⚙️ Parametri di Tuning

### Vector Search

**numCandidates**: Numero di candidati iniziali prima del filtering

```php
'numCandidates' => 500,  // Default: 100
```

- ⬆️ Aumentare per migliorare domain filtering (più candidati = più match dopo filter)
- ⬇️ Diminuire per performance (meno candidati = query più veloce)
- 💡 Raccomandato: 5-10x il valore di `limit`

**limit**: Numero risultati finali

```php
'limit' => 10,  // Default: 10
```

**topK**: Numero chunks da recuperare per RAG

```php
protected int $topK = 10;  // Default in ClientSiteQaService
```

### Text Search

**fuzzy**: Tolleranza typo

```php
'fuzzy' => [
    'maxEdits' => 2,      // Max 2 caratteri differenti
    'prefixLength' => 3,  // Primi 3 char devono match esatto
]
```

**maxEdits**:
- `1` = tolleranza bassa (typo singolo)
- `2` = tolleranza alta (raccomandato)

**prefixLength**:
- `0` = nessun prefix fisso
- `3` = primi 3 caratteri devono match (raccomandato)

---

## 🚨 Troubleshooting

### Vector Search ritorna 0 risultati

**Problema**: Nessun chunk trovato anche se esistono nel DB

**Cause possibili**:
1. ❌ Indice `vector_index_1` non creato
2. ❌ Field `embedding` non popolato
3. ❌ `numCandidates` troppo basso con domain filter
4. ❌ Query embedding dimensioni errate (deve essere 1536)

**Soluzioni**:
```php
// 1. Verifica indice esiste
db.webscraper_chunks.aggregate([{ $listSearchIndexes: {} }])

// 2. Verifica embedding popolato
db.webscraper_chunks.findOne({ embedding: { $exists: true, $ne: null } })

// 3. Aumenta numCandidates
'numCandidates' => 500,

// 4. Verifica embedding dimensions
count($queryEmbedding) === 1536
```

### Text Search ritorna 0 risultati

**Problema**: Nessun match anche se il testo esiste

**Cause possibili**:
1. ❌ Indice `text_search_index` non creato
2. ❌ Field `content` o `title` non indicizzati
3. ❌ Analyzer errato
4. ❌ Query troppo specifica (prova fuzzy)

**Soluzioni**:
```php
// 1. Verifica indice
db.webscraper_chunks.aggregate([{ $listSearchIndexes: {} }])

// 2. Abilita fuzzy matching
'fuzzy' => true,

// 3. Verifica field path
'path' => ['content', 'title'],
```

### Errore "Path 'domain' needs to be indexed as filter"

**Problema**: Tentativo di usare `filter` parameter senza filter field configurato

**Soluzione**: Crea/Aggiorna `vector_index_1` con domain come filter field (vedi configurazione sopra)

### Hybrid Search combina male i risultati

**Problema**: RRF score strani o risultati duplicati

**Cause possibili**:
1. ❌ URL non estratto correttamente (BSONDocument vs array)
2. ❌ Domain normalization inconsistente (www vs non-www)
3. ❌ Pesi RRF sbilanciati

**Soluzioni**:
```php
// 1. Verifica extractUrl() handle BSONDocument
if (is_object($result)) {
    $result = (array) $result;
}

// 2. Normalizza sempre domain
$domainVariants = [$domain, 'www.' . $domain];

// 3. Testa pesi diversi
'vector_weight' => 1.5,  // Più peso alla semantica
'text_weight' => 1.0,
```

---

## 📈 Metriche e Monitoring

### Log Levels

**File**: `config/logging.php`

```php
'channels' => [
    'webscraper' => [
        'driver' => 'daily',
        'path' => storage_path('logs/webscraper.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

### Log Key Metrics

**Vector Search**:
```
ClientSiteQa: Vector search completed
- results_count: 10
- numCandidates: 500
- domain: isofin.it
```

**Text Search**:
```
SearchIndex: Search completed
- results_count: 8
- query: contatti isofin
- method: atlas_text_search
```

**Hybrid Search**:
```
HybridSearch: Search completed
- results_count: 12
- duration_ms: 245
- vector_results: 10
- text_results: 8
- merged_results: 12 (RRF fusion)
```

### Performance Targets

- ⚡ Vector Search: < 300ms
- ⚡ Text Search: < 200ms
- ⚡ Hybrid Search: < 500ms (parallel execution)
- ⚡ RAG Q&A: < 5s (include LLM generation)

---

## 🔗 Risorse Utili

- [MongoDB Atlas Vector Search Docs](https://www.mongodb.com/docs/atlas/atlas-vector-search/vector-search-overview/)
- [MongoDB Atlas Search Docs](https://www.mongodb.com/docs/atlas/atlas-search/)
- [OpenAI Embeddings Guide](https://platform.openai.com/docs/guides/embeddings)
- [Reciprocal Rank Fusion Paper](https://plg.uwaterloo.ca/~gvcormac/cormacksigir09-rrf.pdf)

---

**Ultimo aggiornamento**: 2025-11-16

**Versione**: 1.0

**Autore**: Sistema RAG Avatar-3D-V1