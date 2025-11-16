# RAG Local Alternatives - Implementazione Vector Search

Questo documento descrive 3 alternative **locali** (self-hosted) a MongoDB Atlas per implementare Vector Search nel sistema RAG.

## 📋 Panoramica Alternative

| Soluzione | Performance | Setup | Scalabilità | Consigliato Per |
|-----------|-------------|-------|-------------|-----------------|
| **MongoDB Locale + PHP** | 🐌 Lento | ✅ Facile | ❌ Limitata | Dev/Testing |
| **PostgreSQL + pgvector** | 🚀 Veloce | ⚠️ Medio | ✅ Ottima | Produzione |
| **SQLite + sqlite-vec** | ⚡ Molto Veloce | ✅ Facile | ⚠️ Media | Piccoli progetti |

---

## 1️⃣ MongoDB Locale + Cosine Similarity in PHP

### 📝 Descrizione
Usa MongoDB locale senza Atlas Search. Calcola cosine similarity manualmente in PHP caricando tutti i chunk e facendo il confronto in loop.

### ✅ Vantaggi
- Zero configurazione aggiuntiva (usi già MongoDB)
- Nessun costo cloud
- Dati completamente locali
- Nessuna dipendenza esterna

### ❌ Svantaggi
- **MOLTO lento** con >1000 chunks
- Carica tutti i chunk in memoria
- CPU-intensive
- Non scala per produzione

### 🔧 Implementazione

#### Step 1: Model WebscraperChunk (già implementato)

Il model ha già il metodo `cosineSimilarity()`:

```php
// src/Modules/WebScraper/Models/WebscraperChunk.php (linea 56-92)
public function cosineSimilarity(array $queryEmbedding): float
{
    if (!$this->embedding || empty($this->embedding)) {
        return 0.0;
    }

    $chunkEmbedding = $this->embedding;

    // Ensure both vectors have the same dimensionality
    if (count($chunkEmbedding) !== count($queryEmbedding)) {
        return 0.0;
    }

    // Calculate dot product and magnitudes
    $dotProduct = 0.0;
    $chunkMagnitude = 0.0;
    $queryMagnitude = 0.0;

    for ($i = 0; $i < count($chunkEmbedding); $i++) {
        $dotProduct += $chunkEmbedding[$i] * $queryEmbedding[$i];
        $chunkMagnitude += $chunkEmbedding[$i] * $chunkEmbedding[$i];
        $queryMagnitude += $queryEmbedding[$i] * $queryEmbedding[$i];
    }

    $chunkMagnitude = sqrt($chunkMagnitude);
    $queryMagnitude = sqrt($queryMagnitude);

    if ($chunkMagnitude == 0.0 || $queryMagnitude == 0.0) {
        return 0.0;
    }

    $similarity = $dotProduct / ($chunkMagnitude * $queryMagnitude);
    return max(0.0, min(1.0, $similarity));
}
```

#### Step 2: ClientSiteQaService con PHP Similarity

```php
// src/Modules/WebScraper/Services/ClientSiteQaService.php

<?php

namespace Modules\WebScraper\Services;

use Modules\WebScraper\Models\WebscraperChunk;
use Modules\WebScraper\Services\EmbeddingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientSiteQaService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Answer a question using RAG with PHP-based similarity search
     */
    public function answerQuestion(string $question, int $topK = 5): array
    {
        // 1. Generate embedding for the question
        $queryEmbedding = $this->embeddingService->generateEmbedding($question);

        // 2. Load ALL chunks from MongoDB (SLOW!)
        $allChunks = WebscraperChunk::withEmbeddings()->get();

        Log::info('ClientSiteQaService: Loaded chunks for similarity calculation', [
            'total_chunks' => $allChunks->count(),
            'query' => $question,
        ]);

        // 3. Calculate similarity for each chunk (IN PHP - VERY SLOW!)
        $chunksWithSimilarity = [];
        foreach ($allChunks as $chunk) {
            $similarity = $chunk->cosineSimilarity($queryEmbedding);

            if ($similarity > 0.5) { // Filter threshold
                $chunksWithSimilarity[] = [
                    'chunk' => $chunk,
                    'similarity' => $similarity,
                ];
            }
        }

        // 4. Sort by similarity (descending)
        usort($chunksWithSimilarity, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        // 5. Take top K
        $topChunks = array_slice($chunksWithSimilarity, 0, $topK);

        Log::info('ClientSiteQaService: Top chunks selected', [
            'top_k' => $topK,
            'selected_count' => count($topChunks),
        ]);

        // 6. Build context from top chunks
        $context = $this->buildContext($topChunks);

        // 7. Call LLM with context
        $answer = $this->callLLM($question, $context);

        return [
            'answer' => $answer,
            'sources' => array_map(fn($item) => [
                'url' => $item['chunk']->page->url,
                'title' => $item['chunk']->page->title,
                'similarity' => round($item['similarity'], 3),
            ], $topChunks),
        ];
    }

    protected function buildContext(array $topChunks): string
    {
        $context = '';
        foreach ($topChunks as $index => $item) {
            $chunk = $item['chunk'];
            $context .= sprintf(
                "[Source %d - %s]\n%s\n\n",
                $index + 1,
                $chunk->page->title,
                $chunk->content
            );
        }
        return $context;
    }

    protected function callLLM(string $question, string $context): string
    {
        $apiKey = config('openapi.key');

        $systemPrompt = "Sei un assistente AI. Rispondi alla domanda usando SOLO le informazioni fornite nel contesto. Se la risposta non è nel contesto, dillo chiaramente.";

        $userPrompt = sprintf(
            "CONTESTO:\n%s\n\nDOMANDA: %s\n\nRISPONDI:",
            $context,
            $question
        );

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 500,
        ]);

        if (!$response->successful()) {
            Log::error('ClientSiteQaService: LLM call failed', ['error' => $response->body()]);
            return 'Errore nella generazione della risposta.';
        }

        $result = $response->json();
        return $result['choices'][0]['message']['content'] ?? 'Nessuna risposta generata.';
    }
}
```

#### Step 3: Performance Optimization (opzionale)

Per migliorare le performance con MongoDB locale:

```php
// Usa indici MongoDB per filtrare prima di caricare
public function answerQuestion(string $question, int $topK = 5, ?string $domain = null): array
{
    $queryEmbedding = $this->embeddingService->generateEmbedding($question);

    // Filter by domain if specified (reduces chunks to load)
    $query = WebscraperChunk::withEmbeddings();

    if ($domain) {
        $query->whereHas('page', function($q) use ($domain) {
            $q->where('domain', $domain);
        });
    }

    // Limit to recent chunks (add created_at index)
    $query->where('created_at', '>=', now()->subDays(30));

    $allChunks = $query->get();

    // ... rest of the code
}
```

---

## 2️⃣ PostgreSQL + pgvector

### 📝 Descrizione
Migra da MongoDB a PostgreSQL e usa l'estensione `pgvector` per vector search nativo.

### ✅ Vantaggi
- **Vector search nativo** (query SQL)
- **Performance eccellente** (HNSW indexes)
- Produzione-ready
- Scalabile

### ❌ Svantaggi
- Richiede migrazione da MongoDB
- Setup PostgreSQL + estensione
- Cambiamento architettura database

### 🔧 Implementazione

#### Step 1: Docker Compose - Aggiungi PostgreSQL

```yaml
# ecommerce/docker-dev-playwright/docker-compose.yml

  postgres:
    image: pgvector/pgvector:pg16
    container_name: postgres_avatar-3d-v1-dev
    environment:
      POSTGRES_DB: avatar3d_rag
      POSTGRES_USER: avatar3d
      POSTGRES_PASSWORD: password_pg
    volumes:
      - postgres_data_avatar-3d-v1:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    networks:
      - avatar-3d-v1

  pgadmin:
    image: dpage/pgadmin4:latest
    container_name: pgadmin_avatar-3d-v1-dev
    depends_on:
      - postgres
    environment:
      PGADMIN_DEFAULT_EMAIL: admin@avatar3d.local
      PGADMIN_DEFAULT_PASSWORD: admin
    ports:
      - "5050:80"
    networks:
      - avatar-3d-v1

volumes:
  postgres_data_avatar-3d-v1:
```

#### Step 2: .env - Configura PostgreSQL

```env
# PostgreSQL for RAG
PGSQL_RAG_HOST=postgres
PGSQL_RAG_PORT=5432
PGSQL_RAG_DATABASE=avatar3d_rag
PGSQL_RAG_USERNAME=avatar3d
PGSQL_RAG_PASSWORD=password_pg
```

#### Step 3: config/database.php

```php
'pgsql_rag' => [
    'driver' => 'pgsql',
    'host' => env('PGSQL_RAG_HOST', '127.0.0.1'),
    'port' => env('PGSQL_RAG_PORT', '5432'),
    'database' => env('PGSQL_RAG_DATABASE', 'avatar3d_rag'),
    'username' => env('PGSQL_RAG_USERNAME', 'forge'),
    'password' => env('PGSQL_RAG_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
],
```

#### Step 4: Migration - Tabelle con pgvector

```php
// database/migrations/2025_11_16_create_webscraper_pages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('pgsql_rag')->create('webscraper_pages', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048);
            $table->string('url_hash', 64)->unique();
            $table->string('domain', 255)->index();
            $table->string('title', 512)->nullable();
            $table->text('description')->nullable();
            $table->text('content');
            $table->text('raw_html')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('word_count')->default(0);
            $table->integer('chunk_count')->default(0);
            $table->string('status', 50)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamp('last_scraped_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql_rag')->dropIfExists('webscraper_pages');
    }
};
```

```php
// database/migrations/2025_11_16_create_webscraper_chunks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable pgvector extension
        DB::connection('pgsql_rag')->statement('CREATE EXTENSION IF NOT EXISTS vector');

        // Create chunks table
        DB::connection('pgsql_rag')->statement('
            CREATE TABLE webscraper_chunks (
                id BIGSERIAL PRIMARY KEY,
                page_id BIGINT NOT NULL REFERENCES webscraper_pages(id) ON DELETE CASCADE,
                content TEXT NOT NULL,
                chunk_index INTEGER DEFAULT 0,
                word_count INTEGER DEFAULT 0,
                embedding vector(1536), -- pgvector type for 1536 dimensions
                chunk_hash VARCHAR(64),
                metadata JSONB,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )
        ');

        // Create indexes
        DB::connection('pgsql_rag')->statement('CREATE INDEX ON webscraper_chunks (page_id, chunk_index)');
        DB::connection('pgsql_rag')->statement('CREATE INDEX ON webscraper_chunks (chunk_hash)');

        // HNSW index for vector similarity search (FAST!)
        DB::connection('pgsql_rag')->statement('
            CREATE INDEX ON webscraper_chunks
            USING hnsw (embedding vector_cosine_ops)
        ');
    }

    public function down(): void
    {
        DB::connection('pgsql_rag')->statement('DROP TABLE IF EXISTS webscraper_chunks CASCADE');
        DB::connection('pgsql_rag')->statement('DROP EXTENSION IF EXISTS vector');
    }
};
```

#### Step 5: Models - Usa PostgreSQL

```php
// src/Modules/WebScraper/Models/WebscraperPage.php

<?php

namespace Modules\WebScraper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebscraperPage extends Model
{
    protected $connection = 'pgsql_rag'; // PostgreSQL connection
    protected $table = 'webscraper_pages';

    protected $fillable = [
        'url', 'url_hash', 'domain', 'title', 'description',
        'content', 'raw_html', 'metadata', 'word_count', 'chunk_count',
        'status', 'error_message', 'indexed_at', 'last_scraped_at', 'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'indexed_at' => 'datetime',
        'last_scraped_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(WebscraperChunk::class, 'page_id');
    }

    public static function generateUrlHash(string $url): string
    {
        return hash('sha256', $url);
    }

    public static function extractDomain(string $url): string
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? '';
    }
}
```

```php
// src/Modules/WebScraper/Models/WebscraperChunk.php

<?php

namespace Modules\WebScraper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebscraperChunk extends Model
{
    protected $connection = 'pgsql_rag';
    protected $table = 'webscraper_chunks';

    protected $fillable = [
        'page_id', 'content', 'chunk_index', 'word_count',
        'embedding', 'chunk_hash', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'chunk_index' => 'integer',
        'word_count' => 'integer',
    ];

    // Embedding getter/setter for pgvector format
    public function setEmbeddingAttribute($value)
    {
        if (is_array($value)) {
            // Convert PHP array to PostgreSQL vector format: '[1,2,3,...]'
            $this->attributes['embedding'] = '[' . implode(',', $value) . ']';
        } else {
            $this->attributes['embedding'] = $value;
        }
    }

    public function getEmbeddingAttribute($value)
    {
        if (is_string($value) && str_starts_with($value, '[')) {
            // Convert PostgreSQL vector to PHP array
            return json_decode($value, true);
        }
        return $value;
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(WebscraperPage::class, 'page_id');
    }

    public static function generateChunkHash(string $content): string
    {
        return hash('sha256', $content);
    }
}
```

#### Step 6: ClientSiteQaService con pgvector

```php
// src/Modules/WebScraper/Services/ClientSiteQaService.php

<?php

namespace Modules\WebScraper\Services;

use Modules\WebScraper\Models\WebscraperChunk;
use Modules\WebScraper\Services\EmbeddingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientSiteQaService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Answer a question using RAG with pgvector similarity search
     */
    public function answerQuestion(string $question, int $topK = 5): array
    {
        // 1. Generate embedding for the question
        $queryEmbedding = $this->embeddingService->generateEmbedding($question);

        // 2. Convert to pgvector format
        $vectorString = '[' . implode(',', $queryEmbedding) . ']';

        // 3. Query PostgreSQL with pgvector cosine similarity (FAST!)
        $topChunks = DB::connection('pgsql_rag')
            ->table('webscraper_chunks')
            ->selectRaw('
                webscraper_chunks.*,
                1 - (embedding <=> ?::vector) as similarity
            ', [$vectorString])
            ->where(DB::raw('1 - (embedding <=> ?::vector)'), '>', 0.5)
            ->orderByDesc('similarity')
            ->limit($topK)
            ->get();

        Log::info('ClientSiteQaService: Vector search completed', [
            'query' => $question,
            'results_count' => $topChunks->count(),
        ]);

        // 4. Load full chunk models with relationships
        $chunkIds = $topChunks->pluck('id');
        $chunks = WebscraperChunk::with('page')
            ->whereIn('id', $chunkIds)
            ->get()
            ->keyBy('id');

        // 5. Build context with similarity scores
        $contextChunks = $topChunks->map(function($row) use ($chunks) {
            return [
                'chunk' => $chunks[$row->id],
                'similarity' => $row->similarity,
            ];
        })->toArray();

        $context = $this->buildContext($contextChunks);

        // 6. Call LLM
        $answer = $this->callLLM($question, $context);

        return [
            'answer' => $answer,
            'sources' => array_map(fn($item) => [
                'url' => $item['chunk']->page->url,
                'title' => $item['chunk']->page->title,
                'similarity' => round($item['similarity'], 3),
            ], $contextChunks),
        ];
    }

    protected function buildContext(array $topChunks): string
    {
        $context = '';
        foreach ($topChunks as $index => $item) {
            $chunk = $item['chunk'];
            $context .= sprintf(
                "[Source %d - %s]\n%s\n\n",
                $index + 1,
                $chunk->page->title,
                $chunk->content
            );
        }
        return $context;
    }

    protected function callLLM(string $question, string $context): string
    {
        $apiKey = config('openapi.key');

        $systemPrompt = "Sei un assistente AI. Rispondi alla domanda usando SOLO le informazioni fornite nel contesto. Se la risposta non è nel contesto, dillo chiaramente.";

        $userPrompt = sprintf(
            "CONTESTO:\n%s\n\nDOMANDA: %s\n\nRISPONDI:",
            $context,
            $question
        );

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 500,
        ]);

        if (!$response->successful()) {
            Log::error('ClientSiteQaService: LLM call failed', ['error' => $response->body()]);
            return 'Errore nella generazione della risposta.';
        }

        $result = $response->json();
        return $result['choices'][0]['message']['content'] ?? 'Nessuna risposta generata.';
    }
}
```

#### Step 7: Run Migrations

```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan migrate --database=pgsql_rag
```

---

## 3️⃣ SQLite + sqlite-vec

### 📝 Descrizione
Usa SQLite con l'estensione `sqlite-vec` per vector search in un singolo file.

### ✅ Vantaggi
- **Setup semplicissimo** (file-based)
- **Vector search nativo**
- **Performance eccellente** per piccoli dataset
- Zero configurazione server

### ❌ Svantaggi
- **NON produzione-ready** (lock su write)
- Limitato a ~100k chunks
- Non scalabile per multi-tenant

### 🔧 Implementazione

#### Step 1: Install sqlite-vec Extension

```bash
# Download sqlite-vec prebuilt binary
cd /tmp
wget https://github.com/asg017/sqlite-vec/releases/download/v0.1.1/sqlite-vec-0.1.1-loadable-linux-x86_64.tar.gz
tar xvzf sqlite-vec-0.1.1-loadable-linux-x86_64.tar.gz
sudo mv vec0.so /usr/local/lib/
```

Oppure in Docker:

```dockerfile
# docker-dev-playwright/Dockerfile-php
RUN wget https://github.com/asg017/sqlite-vec/releases/download/v0.1.1/sqlite-vec-0.1.1-loadable-linux-x86_64.tar.gz \
 && tar xvzf sqlite-vec-0.1.1-loadable-linux-x86_64.tar.gz \
 && mv vec0.so /usr/local/lib/ \
 && rm sqlite-vec-0.1.1-loadable-linux-x86_64.tar.gz
```

#### Step 2: .env - SQLite Database

```env
# SQLite for RAG
SQLITE_RAG_DATABASE=/var/www/html/storage/rag/rag.sqlite
```

#### Step 3: config/database.php

```php
'sqlite_rag' => [
    'driver' => 'sqlite',
    'database' => env('SQLITE_RAG_DATABASE', storage_path('rag/rag.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => true,
],
```

#### Step 4: Migration - Tabelle con sqlite-vec

```php
// database/migrations/2025_11_16_create_webscraper_pages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Load sqlite-vec extension
        DB::connection('sqlite_rag')->statement("SELECT load_extension('/usr/local/lib/vec0.so')");

        Schema::connection('sqlite_rag')->create('webscraper_pages', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2048);
            $table->string('url_hash', 64)->unique();
            $table->string('domain', 255)->index();
            $table->string('title', 512)->nullable();
            $table->text('description')->nullable();
            $table->text('content');
            $table->text('raw_html')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('word_count')->default(0);
            $table->integer('chunk_count')->default(0);
            $table->string('status', 50)->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('indexed_at')->nullable();
            $table->timestamp('last_scraped_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('sqlite_rag')->dropIfExists('webscraper_pages');
    }
};
```

```php
// database/migrations/2025_11_16_create_webscraper_chunks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Load sqlite-vec extension
        DB::connection('sqlite_rag')->statement("SELECT load_extension('/usr/local/lib/vec0.so')");

        // Create chunks table
        DB::connection('sqlite_rag')->statement('
            CREATE TABLE webscraper_chunks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                page_id INTEGER NOT NULL,
                content TEXT NOT NULL,
                chunk_index INTEGER DEFAULT 0,
                word_count INTEGER DEFAULT 0,
                embedding BLOB, -- sqlite-vec stores embeddings as BLOB
                chunk_hash VARCHAR(64),
                metadata TEXT, -- JSON as TEXT in SQLite
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                FOREIGN KEY (page_id) REFERENCES webscraper_pages(id) ON DELETE CASCADE
            )
        ');

        // Create virtual table for vector search
        DB::connection('sqlite_rag')->statement('
            CREATE VIRTUAL TABLE vec_chunks USING vec0(
                chunk_id INTEGER PRIMARY KEY,
                embedding FLOAT[1536]
            )
        ');

        // Create indexes
        DB::connection('sqlite_rag')->statement('CREATE INDEX idx_chunks_page ON webscraper_chunks(page_id, chunk_index)');
        DB::connection('sqlite_rag')->statement('CREATE INDEX idx_chunks_hash ON webscraper_chunks(chunk_hash)');
    }

    public function down(): void
    {
        DB::connection('sqlite_rag')->statement('DROP TABLE IF EXISTS vec_chunks');
        DB::connection('sqlite_rag')->statement('DROP TABLE IF EXISTS webscraper_chunks');
    }
};
```

#### Step 5: Models - Usa SQLite

```php
// src/Modules/WebScraper/Models/WebscraperChunk.php

<?php

namespace Modules\WebScraper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class WebscraperChunk extends Model
{
    protected $connection = 'sqlite_rag';
    protected $table = 'webscraper_chunks';

    protected $fillable = [
        'page_id', 'content', 'chunk_index', 'word_count',
        'embedding', 'chunk_hash', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'chunk_index' => 'integer',
        'word_count' => 'integer',
    ];

    // Override save to sync with vec_chunks virtual table
    public function save(array $options = [])
    {
        $saved = parent::save($options);

        if ($saved && $this->embedding) {
            // Insert/Update in vec_chunks virtual table
            $embeddingBlob = pack('f*', ...$this->embedding);

            DB::connection('sqlite_rag')->statement('
                INSERT OR REPLACE INTO vec_chunks (chunk_id, embedding)
                VALUES (?, ?)
            ', [$this->id, $embeddingBlob]);
        }

        return $saved;
    }

    // Embedding getter/setter
    public function setEmbeddingAttribute($value)
    {
        if (is_array($value)) {
            // Store as packed binary BLOB
            $this->attributes['embedding'] = pack('f*', ...$value);
        } else {
            $this->attributes['embedding'] = $value;
        }
    }

    public function getEmbeddingAttribute($value)
    {
        if (is_string($value)) {
            // Unpack binary BLOB to array
            return array_values(unpack('f*', $value));
        }
        return $value;
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(WebscraperPage::class, 'page_id');
    }

    public static function generateChunkHash(string $content): string
    {
        return hash('sha256', $content);
    }
}
```

#### Step 6: ClientSiteQaService con sqlite-vec

```php
// src/Modules/WebScraper/Services/ClientSiteQaService.php

<?php

namespace Modules\WebScraper\Services;

use Modules\WebScraper\Models\WebscraperChunk;
use Modules\WebScraper\Services\EmbeddingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientSiteQaService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Answer a question using RAG with sqlite-vec similarity search
     */
    public function answerQuestion(string $question, int $topK = 5): array
    {
        // 1. Load sqlite-vec extension
        DB::connection('sqlite_rag')->statement("SELECT load_extension('/usr/local/lib/vec0.so')");

        // 2. Generate embedding for the question
        $queryEmbedding = $this->embeddingService->generateEmbedding($question);

        // 3. Convert to binary BLOB
        $queryBlob = pack('f*', ...$queryEmbedding);

        // 4. Query with sqlite-vec cosine distance (FAST!)
        $topChunkIds = DB::connection('sqlite_rag')
            ->select('
                SELECT
                    chunk_id,
                    distance
                FROM vec_chunks
                WHERE embedding MATCH ?
                ORDER BY distance
                LIMIT ?
            ', [$queryBlob, $topK]);

        $chunkIds = array_column($topChunkIds, 'chunk_id');

        Log::info('ClientSiteQaService: Vector search completed', [
            'query' => $question,
            'results_count' => count($chunkIds),
        ]);

        // 5. Load full chunk models
        $chunks = WebscraperChunk::with('page')
            ->whereIn('id', $chunkIds)
            ->get()
            ->keyBy('id');

        // 6. Build context with distances
        $contextChunks = collect($topChunkIds)->map(function($row) use ($chunks) {
            return [
                'chunk' => $chunks[$row->chunk_id],
                'distance' => $row->distance,
                'similarity' => 1 / (1 + $row->distance), // Convert distance to similarity
            ];
        })->toArray();

        $context = $this->buildContext($contextChunks);

        // 7. Call LLM
        $answer = $this->callLLM($question, $context);

        return [
            'answer' => $answer,
            'sources' => array_map(fn($item) => [
                'url' => $item['chunk']->page->url,
                'title' => $item['chunk']->page->title,
                'similarity' => round($item['similarity'], 3),
            ], $contextChunks),
        ];
    }

    protected function buildContext(array $topChunks): string
    {
        $context = '';
        foreach ($topChunks as $index => $item) {
            $chunk = $item['chunk'];
            $context .= sprintf(
                "[Source %d - %s]\n%s\n\n",
                $index + 1,
                $chunk->page->title,
                $chunk->content
            );
        }
        return $context;
    }

    protected function callLLM(string $question, string $context): string
    {
        $apiKey = config('openapi.key');

        $systemPrompt = "Sei un assistente AI. Rispondi alla domanda usando SOLO le informazioni fornite nel contesto. Se la risposta non è nel contesto, dillo chiaramente.";

        $userPrompt = sprintf(
            "CONTESTO:\n%s\n\nDOMANDA: %s\n\nRISPONDI:",
            $context,
            $question
        );

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 500,
        ]);

        if (!$response->successful()) {
            Log::error('ClientSiteQaService: LLM call failed', ['error' => $response->body()]);
            return 'Errore nella generazione della risposta.';
        }

        $result = $response->json();
        return $result['choices'][0]['message']['content'] ?? 'Nessuna risposta generata.';
    }
}
```

#### Step 7: Create Storage Directory

```bash
mkdir -p storage/rag
touch storage/rag/rag.sqlite
chmod 664 storage/rag/rag.sqlite
```

#### Step 8: Run Migrations

```bash
docker exec php_fpm_avatar-3d-v1-dev php artisan migrate --database=sqlite_rag
```

---

## 📊 Performance Comparison

| Soluzione | 1k chunks | 10k chunks | 100k chunks | Query Time |
|-----------|-----------|------------|-------------|------------|
| **MongoDB + PHP** | ~1s | ~10s | ~100s | O(n) |
| **PostgreSQL + pgvector** | <50ms | <100ms | <200ms | O(log n) |
| **SQLite + sqlite-vec** | <30ms | <80ms | ~500ms | O(log n) |
| **MongoDB Atlas** | <50ms | <100ms | <150ms | O(log n) |

---

## 🎯 Raccomandazioni Finali

### **Per Sviluppo/Testing**
→ **MongoDB Locale + PHP** (già implementato, zero config)

### **Per Produzione Locale**
→ **PostgreSQL + pgvector** (performance + scalabilità)

### **Per Piccoli Progetti**
→ **SQLite + sqlite-vec** (semplicità + performance)

### **Per Produzione Cloud**
→ **MongoDB Atlas** (migliore performance/costo, free tier generoso)

---

## 📝 Next Steps

1. Scegli l'implementazione basata sul tuo caso d'uso
2. Segui gli step corrispondenti
3. Testa con `php artisan tinker`
4. Integra con `SiteIndexerService` per indicizzazione
5. Deploy!

---

**Documento creato**: 2025-11-16
**Versione**: 1.0
**Autore**: Claude + Marco Presti
