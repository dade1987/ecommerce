# rag:search - Comando per Ricerca Semantica

## 📋 Descrizione

Il comando `rag:search` esegue ricerche semantiche sul contenuto indicizzato usando MongoDB Atlas Vector Search e genera risposte contestuali tramite GPT-3.5-turbo.

## 🎯 Sintassi

```bash
php artisan rag:search {url} {query} [options]
```

### Argomenti

- `url` **(required)** - L'URL del sito web su cui cercare
- `query` **(required)** - La domanda o query di ricerca

### Opzioni

| Opzione | Default | Descrizione |
|---------|---------|-------------|
| `--top-k` | `5` | Numero di chunks simili da recuperare (max 20) |
| `--min-similarity` | `0.7` | Score minimo di similarità (0-1) |

## 🚀 Esempi d'Uso

### Ricerca Base
```bash
php artisan rag:search "https://www.machebuoni.it" "Quali prodotti offrite?"
```

### Ricerca Approfondita (più chunks)
```bash
php artisan rag:search "https://www.example.com" "Come funziona il servizio?" --top-k=10
```

### Ricerca Rigorosa (alta similarità)
```bash
php artisan rag:search "https://www.example.com" "Prezzi abbonamenti" --min-similarity=0.85
```

### Query Complesse
```bash
php artisan rag:search "https://www.example.com" "Differenze tra piano base e premium, inclusi prezzi e funzionalità" --top-k=15
```

## 🔄 Come Funziona

### 1. Generazione Embedding Query
```
Input: "Quali prodotti offrite?"
  ↓
OpenAI text-embedding-3-small
  ↓
Output: [0.123, -0.456, ...] (1536 dimensions)
```

### 2. Vector Search su MongoDB Atlas
```javascript
db.webscraper_chunks.aggregate([
  {
    $vectorSearch: {
      index: "vector_index_1",
      path: "embedding",
      queryVector: [0.123, -0.456, ...],
      numCandidates: 100,
      limit: 10  // --top-k value
    }
  },
  {
    $addFields: {
      score: { $meta: "vectorSearchScore" }
    }
  }
])
```

### 3. Costruzione Contesto
I chunks recuperati vengono formattati come:
```
[Source 1 - www.example.com (Score: 0.89)]
Title: Homepage
URL: https://www.example.com/

Chunk content here...

---

[Source 2 - www.example.com (Score: 0.85)]
Title: Products
URL: https://www.example.com/products

Another chunk content...
```

### 4. Generazione Risposta LLM
```
System Prompt + Context
  ↓
GPT-3.5-turbo (max_tokens: 1000)
  ↓
Risposta contestuale basata SOLO sul contenuto indicizzato
```

## 📊 Output Esempio

```
🔍 Searching indexed content...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Search Results
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✅ Found 10 relevant chunks
🔗 Sources: 5 unique pages

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
💡 AI Answer
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Secondo [Source 1], i prodotti offerti includono:

1. **Tagliatelle al Ragù** - Pasta fresca fatta in casa con ragù
   bolognese tradizionale

2. **Lasagne alla Bolognese** - Lasagne fresche con besciamella
   e ragù

3. **Tortellini in Brodo** - Tortellini artigianali serviti in
   brodo di carne

Tutti i prodotti sono realizzati con ingredienti locali di alta
qualità. [Source 2] menziona anche la disponibilità di menu
personalizzati per eventi.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📚 Sources
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. Menu Principale (Score: 0.92)
   https://www.machebuoni.it/menu

2. Chi Siamo (Score: 0.87)
   https://www.machebuoni.it/about

3. Eventi Catering (Score: 0.84)
   https://www.machebuoni.it/catering

4. Ingredienti (Score: 0.81)
   https://www.machebuoni.it/ingredients

5. FAQ (Score: 0.78)
   https://www.machebuoni.it/faq
```

## ⚙️ Parametri Ottimizzazione

### --top-k (Numero Chunks)

| Valore | Uso Consigliato | Pro | Contro |
|--------|------------------|-----|--------|
| `5` | Domande semplici, risposte rapide | Veloce, economico | Può mancare contesto |
| `10` | **Default consigliato** | Bilanciato | - |
| `15-20` | Domande complesse, analisi dettagliate | Massimo contesto | Più lento, più costoso |

**Attenzione**: Con GPT-3.5-turbo (16K context limit), `top-k=20` può causare errori se i chunks sono molto grandi.

### --min-similarity (Threshold)

| Valore | Comportamento | Uso |
|--------|---------------|-----|
| `0.5-0.6` | Molto permissivo | Esplorativo, poche pagine indicizzate |
| `0.7` | **Default** | Bilanciato |
| `0.8-0.9` | Molto rigoroso | Query precise, molte pagine indicizzate |

## 💾 Dati Utilizzati

### Input
- **Query embedding**: Array di 1536 float
- **Domain filter**: Normalizzato con `www.`

### Output
```php
[
  'answer' => 'Risposta generata da GPT-3.5-turbo...',
  'sources' => [
    [
      'url' => 'https://www.example.com/page',
      'title' => 'Page Title',
      'domain' => 'www.example.com',
      'score' => 0.92,
    ],
    // ... altre sources
  ],
  'chunks_found' => 10,
  'method' => 'atlas_vector_search',
]
```

## 🔍 System Prompt Utilizzato

```php
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
{context con tutti i chunks recuperati}
```

## 💰 Costi Stimati

### Per Query
- **Embedding generazione**: ~$0.000002 per query
- **LLM risposta** (GPT-3.5-turbo):
  - Input: 10 chunks × 500 tokens = 5K tokens = ~$0.0025
  - Output: ~500 tokens = ~$0.001
  - **Totale**: ~$0.0035 per query

### Confronto con Scraping
| Metodo | Costo | Tempo | Cache |
|--------|-------|-------|-------|
| **RAG** | $0.0035 | <1s | MongoDB Atlas |
| **Scraping** | $0.05-0.10 | 5-10s | MySQL + indexing |

**Risparmio**: ~95% costo, ~90% tempo

## 🐛 Troubleshooting

### Output: "Non ho trovato informazioni rilevanti..."

**Possibili Cause**:
1. **Sito non indicizzato**
   ```bash
   php artisan rag:stats --domain=www.example.com
   ```
   Se 0 pagine → usa `rag:index-site`

2. **Domain mismatch**
   - Indicizzato: `example.com`
   - Query su: `www.example.com`
   - **Fix**: Usa sempre lo stesso formato (con o senza `www.`)

3. **Threshold troppo alto**
   ```bash
   php artisan rag:search "..." "..." --min-similarity=0.5
   ```

4. **Query troppo vaga**
   - ❌ "Dimmi tutto"
   - ✅ "Quali sono i prodotti disponibili nel menu?"

### Output: "This model's maximum context length..."

**Soluzione**: Riduci `--top-k`
```bash
php artisan rag:search "..." "..." --top-k=5
```

### Performance Lenta

**Soluzioni**:
1. Verifica indice Atlas Vector Search sia attivo
2. Riduci `--top-k` a 5-10
3. Controlla log MongoDB Atlas per bottlenecks

## 📊 Metriche Performance

### Vector Search (MongoDB Atlas)
- **Latenza media**: 50-200ms
- **Throughput**: 1000+ queries/sec

### LLM Generation (GPT-3.5-turbo)
- **Latenza media**: 1-3s
- **Rate limit**: 3500 requests/min

### End-to-End
- **Totale**: ~2-4s per query completa

## 🔗 Comandi Correlati

- **`rag:index-site`** - Indicizza un sito web
- **`rag:stats`** - Statistiche sistema RAG

## 📚 Riferimenti

- [RAG Complete Flow](./RAG_COMPLETE_FLOW.md)
- [Atlas Search Setup](./ATLAS-SEARCH.md)
- [ClientSiteQaService.php](../Services/ClientSiteQaService.php)