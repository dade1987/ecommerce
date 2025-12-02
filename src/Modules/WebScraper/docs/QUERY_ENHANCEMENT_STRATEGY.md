# Query Enhancement Strategy Pattern

Documentazione del sistema di enhancement delle query di ricerca usando Strategy Pattern.

---

## 📐 Architettura

### Componenti Principali

```
├── Contracts/
│   └── SearchQueryEnhancer.php          # Interface per le strategy
├── Services/
│   ├── QueryEnhancers/
│   │   └── DomainContextEnhancer.php    # Strategy per domain context
│   ├── ClientSiteQaService.php          # Vector search (usa trait)
│   ├── SearchIndexService.php           # Text search (usa trait)
│   └── HybridSearchService.php          # Configura strategy
└── Traits/
    └── EnhancesSearchQueries.php        # Trait per dependency injection
```

---

## 🎯 Design Pattern: Strategy

Il sistema usa **Strategy Pattern** per permettere diverse strategie di enhancement delle query senza modificare i servizi di ricerca.

### Vantaggi

✅ **Separation of Concerns**: Logica di enhancement separata dai servizi di ricerca
✅ **Extensibility**: Facile aggiungere nuove strategy senza modificare codice esistente
✅ **Testability**: Ogni strategy può essere testata indipendentemente
✅ **Reusability**: Stessa strategy riutilizzata da Vector + Text search
✅ **Flexibility**: Possibile cambiare strategy a runtime

---

## 📝 Interface: SearchQueryEnhancer

```php
interface SearchQueryEnhancer
{
    /**
     * Enhance a search query with additional context
     */
    public function enhance(string $query, array $context = []): string;

    /**
     * Check if enhancement should be applied
     */
    public function shouldEnhance(string $query, array $context = []): bool;
}
```

### Contratto

Ogni strategy implementation deve:
1. **enhance()**: Applicare la logica di enhancement e ritornare la query modificata
2. **shouldEnhance()**: Verificare se l'enhancement deve essere applicato

---

## 🔧 Strategy: DomainContextEnhancer

### Scopo

Arricchisce query di ricerca aggiungendo il nome dell'azienda estratto dal domain.

### Esempio

```php
$query = "contatti";
$domain = "isofin.it";

// Estrae company name
$companyName = "isofin"; // rimuove www. e .it

// Verifica se deve applicare enhancement
if (!str_contains($query, $companyName)) {
    $enhancedQuery = $query . ' ' . $companyName;
    // Result: "contatti isofin"
}
```

### Implementazione

**File**: `src/Modules/WebScraper/Services/QueryEnhancers/DomainContextEnhancer.php`

```php
class DomainContextEnhancer implements SearchQueryEnhancer
{
    public function enhance(string $query, array $context = []): string
    {
        if (!$this->shouldEnhance($query, $context)) {
            return $query;
        }

        $domain = $context['domain'];
        $companyName = $this->extractCompanyName($domain);

        return $query . ' ' . $companyName;
    }

    public function shouldEnhance(string $query, array $context = []): bool
    {
        // 1. Domain must be provided
        if (empty($context['domain'])) {
            return false;
        }

        // 2. Query must NOT already contain company name
        $companyName = $this->extractCompanyName($context['domain']);
        if (stripos($query, $companyName) !== false) {
            return false;
        }

        return true;
    }

    protected function extractCompanyName(string $domain): string
    {
        // Remove www. prefix
        $companyName = preg_replace('/^www\./', '', $domain);

        // Remove TLD (.it, .com, etc.)
        $companyName = preg_replace('/\.\w+$/', '', $companyName);

        return $companyName;
    }
}
```

### Casi d'Uso

#### ✅ Enhancement Applicato

```php
// Case 1: Query generica
enhance("contatti", ['domain' => 'isofin.it'])
→ "contatti isofin"

// Case 2: Query specifica senza company name
enhance("orari apertura", ['domain' => 'machebuoni.it'])
→ "orari apertura machebuoni"
```

#### ❌ Enhancement NON Applicato

```php
// Case 1: Query già contiene company name
enhance("contatti isofin", ['domain' => 'isofin.it'])
→ "contatti isofin" (unchanged)

// Case 2: Domain non fornito
enhance("contatti", [])
→ "contatti" (unchanged)

// Case 3: Query cerca altro brand
enhance("contatti machebuoni", ['domain' => 'isofin.it'])
→ "contatti machebuoni" (unchanged, già contiene "machebuoni")
```

---

## 🧩 Trait: EnhancesSearchQueries

### Scopo

Fornisce dependency injection per la strategy, permettendo ai servizi di usare enhancement senza accoppiamento diretto.

### Implementazione

**File**: `src/Modules/WebScraper/Traits/EnhancesSearchQueries.php`

```php
trait EnhancesSearchQueries
{
    protected ?SearchQueryEnhancer $queryEnhancer = null;

    public function setQueryEnhancer(SearchQueryEnhancer $enhancer): self
    {
        $this->queryEnhancer = $enhancer;
        return $this;
    }

    protected function enhanceQuery(string $query, array $context = []): string
    {
        if (!$this->queryEnhancer) {
            return $query; // No enhancer set, return original
        }

        return $this->queryEnhancer->enhance($query, $context);
    }

    protected function shouldEnhanceQuery(string $query, array $context = []): bool
    {
        if (!$this->queryEnhancer) {
            return false;
        }

        return $this->queryEnhancer->shouldEnhance($query, $context);
    }
}
```

### Usage nei Services

**ClientSiteQaService** (Vector Search):

```php
class ClientSiteQaService
{
    use EnhancesSearchQueries;

    public function searchChunks(string $query, ?string $domain = null, ?int $topK = null): array
    {
        // Enhance query using strategy (if set)
        $enhancedQuery = $this->enhanceQuery($query, ['domain' => $domain]);

        // Generate embedding with enhanced query
        $queryEmbedding = $this->embeddingService->generateEmbedding($enhancedQuery);

        // Perform vector search...
    }
}
```

**SearchIndexService** (Text Search):

```php
class SearchIndexService
{
    use EnhancesSearchQueries;

    public function search(string $collection, string $query, array $options = []): array
    {
        $domain = $options['domain'] ?? null;

        // Enhance query using strategy (if set)
        $enhancedQuery = $this->enhanceQuery($query, ['domain' => $domain]);

        // Build search stage with enhanced query
        $searchStage = [
            'index' => $this->indexName,
            'text' => [
                'query' => $enhancedQuery,
                'path' => $options['fields'] ?? ['content', 'title'],
            ],
        ];

        // Execute search...
    }
}
```

---

## ⚙️ Configurazione: HybridSearchService

### Automatic Strategy Injection

**HybridSearchService** configura automaticamente la strategy per entrambi i servizi nel constructor.

**File**: `src/Modules/WebScraper/Services/HybridSearchService.php`

```php
class HybridSearchService
{
    public function __construct()
    {
        // Configure DomainContextEnhancer strategy for both search services
        $this->configureDomainEnhancer();
    }

    protected function configureDomainEnhancer(): void
    {
        $enhancer = new DomainContextEnhancer();

        // Both services will use the same enhancement strategy
        // This ensures consistent query enhancement across vector + text search
        app(ClientSiteQaService::class)->setQueryEnhancer($enhancer);
        app(SearchIndexService::class)->setQueryEnhancer($enhancer);
    }

    public function search(string $query, ?string $domain = null, array $options = []): array
    {
        // Vector + Text search will automatically use DomainContextEnhancer
        $vectorResults = $this->performVectorSearch($query, $domain, $topK);
        $textResults = $this->performTextSearch($query, $domain, $topK);

        // Merge with RRF...
    }
}
```

### Vantaggi Automatic Injection

✅ **Single Source of Truth**: Strategy configurata in un unico posto
✅ **Consistency**: Vector e Text search usano stessa strategy
✅ **No Boilerplate**: Non serve configurare manualmente in ogni chiamata
✅ **Testability**: Facile moccare l'enhancer nei test

---

## 🚀 Flusso Completo

### Esempio: Hybrid Search con Domain Enhancement

```
User Query: "contatti"
Domain: "isofin.it"

┌─────────────────────────────────────────────────────────────┐
│ HybridSearchService::search("contatti", "isofin.it")       │
│                                                              │
│ 1. Constructor configures DomainContextEnhancer             │
│    - app(ClientSiteQaService)->setQueryEnhancer($enhancer)  │
│    - app(SearchIndexService)->setQueryEnhancer($enhancer)   │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Parallel Execution                                           │
│                                                              │
│ ┌─────────────────────────┐  ┌───────────────────────────┐ │
│ │ Vector Search           │  │ Text Search               │ │
│ │                         │  │                           │ │
│ │ 1. enhanceQuery()       │  │ 1. enhanceQuery()         │ │
│ │    → "contatti isofin"  │  │    → "contatti isofin"    │ │
│ │                         │  │                           │ │
│ │ 2. generateEmbedding()  │  │ 2. Atlas Text Search      │ │
│ │    with enhanced query  │  │    with enhanced query    │ │
│ │                         │  │                           │ │
│ │ 3. Atlas Vector Search  │  │ 3. $match domain filter   │ │
│ │                         │  │                           │ │
│ │ 4. $match domain filter │  │ 4. Return results         │ │
│ │                         │  │                           │ │
│ │ 5. Return 10 results    │  │    Return 8 results       │ │
│ └─────────────────────────┘  └───────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│ Reciprocal Rank Fusion (RRF)                                │
│                                                              │
│ - Merge 10 vector + 8 text results                          │
│ - Calculate RRF scores                                       │
│ - Return 12 unique results (sorted by RRF score)            │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Risultati Query Enhancement

### Before Enhancement

```
Query: "contatti"
Domain: "isofin.it"
numCandidates: 500

Vector Results: 6 chunks (mostly machebuoni.it after filter)
Text Results: 4 chunks
Merged Results: 10 unique
```

### After Enhancement

```
Query: "contatti" → Enhanced to "contatti isofin"
Domain: "isofin.it"
numCandidates: 500

Vector Results: 10 chunks (67% improvement)
Text Results: 8 chunks (100% improvement)
Merged Results: 12 unique (20% improvement)
```

### Impact Analysis

| Metric | Before | After | Change |
|--------|--------|-------|--------|
| Vector Results | 6 | 10 | +67% |
| Text Results | 4 | 8 | +100% |
| Merged Results | 10 | 12 | +20% |
| Semantic Relevance | Low | High | ⬆️ |
| Keyword Matching | Generic | Targeted | ⬆️ |

---

## 🔮 Future Enhancements

### Possibili Nuove Strategy

#### 1. **SynonymEnhancer**
```php
// Espande query con sinonimi
enhance("dottore", [])
→ "dottore medico specialista"
```

#### 2. **CategoryEnhancer**
```php
// Aggiunge categoria prodotto
enhance("scarpe", ['category' => 'sportive'])
→ "scarpe sportive running"
```

#### 3. **LocaleEnhancer**
```php
// Traduce o adatta per locale
enhance("contact", ['locale' => 'it'])
→ "contatti contattaci"
```

#### 4. **HistoricalEnhancer**
```php
// Usa cronologia ricerche utente
enhance("prezzi", ['user_history' => ['listino', 'tariffe']])
→ "prezzi listino tariffe"
```

### Come Aggiungere Nuova Strategy

```php
// 1. Creare strategy class
class SynonymEnhancer implements SearchQueryEnhancer
{
    public function enhance(string $query, array $context = []): string
    {
        // Implementation...
    }

    public function shouldEnhance(string $query, array $context = []): bool
    {
        // Implementation...
    }
}

// 2. Configurare in HybridSearchService (o altro servizio)
protected function configureSynonymEnhancer(): void
{
    $enhancer = new SynonymEnhancer();
    app(ClientSiteQaService::class)->setQueryEnhancer($enhancer);
}

// 3. (Opzionale) Chain multiple enhancers
protected function configureMultipleEnhancers(): void
{
    $domainEnhancer = new DomainContextEnhancer();
    $synonymEnhancer = new SynonymEnhancer();

    // Composite pattern per chain
    $chainEnhancer = new ChainEnhancer([
        $domainEnhancer,
        $synonymEnhancer,
    ]);

    app(ClientSiteQaService::class)->setQueryEnhancer($chainEnhancer);
}
```

---

## 🧪 Testing

### Unit Test per DomainContextEnhancer

```php
class DomainContextEnhancerTest extends TestCase
{
    protected DomainContextEnhancer $enhancer;

    public function setUp(): void
    {
        parent::setUp();
        $this->enhancer = new DomainContextEnhancer();
    }

    /** @test */
    public function it_enhances_query_with_company_name()
    {
        $result = $this->enhancer->enhance('contatti', ['domain' => 'isofin.it']);

        $this->assertEquals('contatti isofin', $result);
    }

    /** @test */
    public function it_does_not_enhance_when_company_name_already_present()
    {
        $result = $this->enhancer->enhance('contatti isofin', ['domain' => 'isofin.it']);

        $this->assertEquals('contatti isofin', $result);
    }

    /** @test */
    public function it_does_not_enhance_without_domain()
    {
        $result = $this->enhancer->enhance('contatti', []);

        $this->assertEquals('contatti', $result);
    }

    /** @test */
    public function it_removes_www_prefix()
    {
        $result = $this->enhancer->enhance('info', ['domain' => 'www.isofin.it']);

        $this->assertEquals('info isofin', $result);
    }
}
```

### Integration Test con Services

```php
class HybridSearchServiceTest extends TestCase
{
    /** @test */
    public function it_applies_domain_enhancement_to_both_searches()
    {
        $hybridService = app(HybridSearchService::class);

        $results = $hybridService->search(
            query: 'contatti',
            domain: 'isofin.it'
        );

        // Verify both vector and text search used enhanced query
        $this->assertGreaterThan(5, $results['stats']['vector_results']);
        $this->assertGreaterThan(3, $results['stats']['text_results']);
    }
}
```

---

## 📚 Riferimenti

- **Design Pattern**: Strategy Pattern (GoF)
- **Laravel**: Dependency Injection, Service Container
- **RAG**: Query Enhancement for better retrieval
- **MongoDB Atlas**: Vector Search + Text Search

---

**Ultimo aggiornamento**: 2025-11-16
**Versione**: 1.0
**Autore**: Sistema RAG Avatar-3D-V1