# MongoDB Atlas Search Index - Documentazione

## 📋 Panoramica

MongoDB Atlas Search permette di eseguire ricerche full-text avanzate con:
- **Autocomplete** (search-as-you-type)
- **Fuzzy matching** (tolleranza errori di battitura)
- **Faceted search** (filtri per categoria/dominio)
- **Stemming** (ricerca forme derivate: "correre" trova "corro", "correndo")
- **Synonyms** (ricerca sinonimi)

## 🎯 Differenze: Vector Search vs Text Search

| Feature | Vector Search | Text Search |
|---------|--------------|-------------|
| **Metodo** | Semantic similarity (embeddings) | Keyword matching |
| **Algoritmo** | Cosine similarity | Lucene full-text |
| **Use Case** | "Concetti simili" (RAG Q&A) | "Parole esatte" (Autocomplete) |
| **Typo Tolerance** | ❌ No | ✅ Fuzzy matching |
| **Comprensione** | ✅ Semantica | ❌ Solo keywords |
| **Velocità** | ~150ms | ~50ms |
| **Storage** | 1536 float × chunks | Lucene index |

**Esempio**:
```
Query: "Come risparmiare energia?"

Vector Search:
✅ Trova: "Consigli per ridurre i consumi elettrici"
✅ Trova: "Efficienza energetica in casa"
❌ NON tollera: "Come risparmaire energia" (typo)

Text Search:
✅ Trova: "risparmiare energia"
✅ Tollera: "risparmaire energia" (fuzzy)
❌ NON trova: "ridurre consumi" (diverso wording)
```

## 🚀 Setup: Creare Search Index su Atlas

### 1. Genera Index Definition

```php
use Modules\WebScraper\Services\SearchIndexService;

$searchService = app(SearchIndexService::class);

// Per collection pages
$result = $searchService->createIndex(
    collection: 'webscraper_pages',
    fields: ['title', 'content', 'description'],
    options: []
);

// Output: JSON definition da copiare in Atlas
print_r($result['definition']);
```

### 2. Applica in Atlas Console

1. **MongoDB Atlas** → **Database** → **Browse Collections**
2. Seleziona database e collection `webscraper_pages`
3. Tab **"Search Indexes"**
4. Click **"Create Search Index"**
5. Scegli **"JSON Editor"**
6. Incolla questa definizione:

```json
{
  "name": "text_search_index",
  "mappings": {
    "dynamic": false,
    "fields": {
      "title": {
        "type": "autocomplete",
        "tokenization": "edgeGram",
        "minGrams": 2,
        "maxGrams": 15,
        "foldDiacritics": true
      },
      "content": {
        "type": "string",
        "analyzer": "lucene.standard"
      },
      "description": {
        "type": "string",
        "analyzer": "lucene.standard"
      },
      "domain": {
        "type": "string"
      }
    }
  }
}
```

7. Click **"Create Search Index"**
8. Attendi che lo status diventi **"Active"** (~2-5 minuti)

## 💡 Uso: SearchIndexService

### Text Search Standard

```php
use Modules\WebScraper\Services\SearchIndexService;

$searchService = app(SearchIndexService::class);

// Basic search
$results = $searchService->search(
    collection: 'webscraper_pages',
    query: 'infissi in alluminio',
    options: [
        'limit' => 10,
        'fuzzy' => true, // Tolleranza typo
        'domain' => 'isofin.it', // Filtra per dominio
        'fields' => ['title', 'content'], // Campi da cercare
    ]
);

// Output
foreach ($results['results'] as $result) {
    echo "Title: {$result['title']}\n";
    echo "Score: {$result['score']}\n"; // Relevance score
    echo "URL: {$result['url']}\n\n";
}
```

### Autocomplete (Search-as-you-type)

```php
// User digita "inf" → suggerisce "infissi", "infiltrazioni", etc.
$suggestions = $searchService->autocomplete(
    collection: 'webscraper_pages',
    query: 'inf',
    options: [
        'limit' => 5,
        'field' => 'title', // Campo autocomplete
    ]
);

// Output: Array di suggerimenti
print_r($suggestions['suggestions']);
```

### Faceted Search (Filtri)

```php
// Cerca + mostra conteggi per dominio
$facets = $searchService->facetedSearch(
    collection: 'webscraper_pages',
    query: 'finestre',
    facets: ['domain']
);

// Output:
// domain: isofin.it (45 results)
// domain: www.machebuoni.it (12 results)
```

## 🔧 Configurazione Avanzata

### Stemming Italiano

```json
{
  "mappings": {
    "fields": {
      "content": {
        "type": "string",
        "analyzer": "lucene.italian" // Stemming IT
      }
    }
  }
}
```

**Effetto**:
- Query "correre" → trova anche "corro", "correndo", "corsa"
- Query "finestra" → trova anche "finestre", "finestrino"

### Synonyms (Sinonimi)

```json
{
  "synonyms": [
    {
      "name": "product_synonyms",
      "analyzer": "lucene.standard",
      "source": {
        "collection": "synonyms"
      }
    }
  ],
  "mappings": {
    "fields": {
      "content": {
        "type": "string",
        "analyzer": "lucene.standard",
        "searchAnalyzer": "product_synonyms"
      }
    }
  }
}
```

**Collection `synonyms`** (MongoDB):
```json
[
  { "mappingType": "equivalent", "synonyms": ["finestra", "serramento", "infisso"] },
  { "mappingType": "equivalent", "synonyms": ["casa", "abitazione", "immobile"] }
]
```

**Effetto**: Query "finestra" → trova anche "serramento" e "infisso"

## 📊 Performance & Costi

### Velocità Tipica

| Operation | Latency |
|-----------|---------|
| Text Search | 20-80ms |
| Autocomplete | 10-50ms |
| Faceted Search | 50-150ms |

**Confronto**:
- Vector Search: ~150ms (più lento per semantic analysis)
- Text Search: ~50ms (più veloce per keyword matching)

### Costi MongoDB Atlas

| Tier | Storage | RAM | Costo/mese |
|------|---------|-----|------------|
| M0 (Free) | 512 MB | 512 MB | $0 |
| M10 | 10 GB | 2 GB | $57 |
| M20 | 20 GB | 4 GB | $140 |

**Note**:
- Search Index consuma RAM aggiuntiva (~10-20% del data size)
- Per 500 MB di data → ~50-100 MB RAM per index
- M0 Free tier supporta 1 search index (sufficiente per test)

### Storage Index

```
Text Index Size ≈ 15-25% del contenuto testuale

Esempio:
- 1000 pages × 500 words avg = 500K words
- ~3.5 MB di testo
- Search index: ~0.7-1 MB

Vector Index Size (confronto):
- 1000 pages × 2 chunks avg × 1536 float × 4 bytes = ~12 MB
```

## 🔗 Hybrid Search (Vector + Text)

Combina semantic similarity (vector) con keyword matching (text):

```php
use Modules\WebScraper\Services\ClientSiteQaService;
use Modules\WebScraper\Services\SearchIndexService;

// 1. Vector search (semantic)
$ragService = app(ClientSiteQaService::class);
$vectorResults = $ragService->answerQuestion(
    query: 'Come risparmiare energia?',
    domain: 'isofin.it'
);

// 2. Text search (keywords)
$searchService = app(SearchIndexService::class);
$textResults = $searchService->search(
    collection: 'webscraper_pages',
    query: 'risparmiare energia',
    options: ['domain' => 'isofin.it', 'fuzzy' => true]
);

// 3. Merge & re-rank by combined score
$hybridResults = array_merge(
    $vectorResults['sources'],
    $textResults['results']
);

// Deduplicate by URL
$seen = [];
$deduplicated = array_filter($hybridResults, function($r) use (&$seen) {
    if (in_array($r['url'], $seen)) return false;
    $seen[] = $r['url'];
    return true;
});

// Sort by combined score (vector_score + text_score)
usort($deduplicated, function($a, $b) {
    $scoreA = ($a['vector_score'] ?? 0) + ($a['text_score'] ?? 0);
    $scoreB = ($b['vector_score'] ?? 0) + ($b['text_score'] ?? 0);
    return $scoreB <=> $scoreA;
});
```

**Vantaggi Hybrid Search**:
- ✅ Trova risultati semanticamente simili (vector)
- ✅ Tollera typo (text fuzzy)
- ✅ Migliore accuracy combinando entrambi

## 🐛 Troubleshooting

### Index Status "Failed"

**Soluzione**:
1. Verifica JSON definition è valido
2. Controlla campo `name` sia univoco
3. Rimuovi index fallito e ricrea

### Search Returns 0 Results

**Possibili Cause**:
1. **Index non attivo** → Verifica status "Active" in Atlas
2. **Field name errato** → Usa `db.collection.findOne()` per vedere field names
3. **Analyzer incompatibile** → Prova `lucene.standard` invece analyzer custom

**Debug**:
```php
// Test raw aggregation
$pipeline = [
    ['$search' => [
        'index' => 'text_search_index',
        'text' => [
            'query' => 'test',
            'path' => 'content',
        ],
    ]],
    ['$limit' => 1],
];

$result = DB::connection('mongodb')
    ->getCollection('webscraper_pages')
    ->aggregate($pipeline)
    ->toArray();

dd($result); // Debug output
```

### Autocomplete Not Working

**Verifica**:
1. Field deve avere `type: "autocomplete"` (non `"string"`)
2. `tokenization: "edgeGram"` configurato
3. Query minima 2 caratteri (`minGrams: 2`)

## 📚 Riferimenti

- **MongoDB Atlas Search Docs**: https://www.mongodb.com/docs/atlas/atlas-search/
- **Lucene Analyzers**: https://www.mongodb.com/docs/atlas/atlas-search/analyzers/
- **SearchIndexService**: `src/Modules/WebScraper/Services/SearchIndexService.php`
- **Vector Search (RAG)**: `RAG_COMPLETE_FLOW.md`

---

*Ultimo aggiornamento: 2025-11-16*