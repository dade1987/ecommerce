# How to Create MongoDB Atlas Text Search Index

## 📋 Quick Guide

Follow these steps to create the **text_search_index** on MongoDB Atlas for keyword-based search with fuzzy matching.

---

## 🎯 Prerequisites

- MongoDB Atlas account
- Database: `avatar-3d-v1` (or your configured database name)
- Collection: `webscraper_chunks`

---

## 📝 Step-by-Step Instructions

### 1. Access MongoDB Atlas Console

1. Go to [MongoDB Atlas](https://cloud.mongodb.com/)
2. Log in to your account
3. Select your **Project**
4. Click on your **Cluster**

### 2. Navigate to Search Indexes

1. Click **"Browse Collections"**
2. Select database: `avatar-3d-v1`
3. Select collection: `webscraper_chunks`
4. Click on **"Search Indexes"** tab (top menu)

### 3. Create Text Search Index

1. Click **"Create Search Index"** button
2. Select **"Atlas Search"** (NOT "Atlas Vector Search")
3. Choose **"JSON Editor"**
4. Click **"Next"**

### 4. Paste the JSON Configuration

Copy and paste this **exact JSON** into the editor:

#### Basic Configuration (Required)

```json
{
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

#### Enhanced Configuration with Native Domain Pre-filtering (Recommended)

For **better performance** when filtering by domain, configure `domain` to support both **exact matching** and **faceted search**.

This allows the code to use Atlas **compound queries** with native pre-filtering, similar to vector search.

**Enhanced Configuration:**

```json
{
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

**What This Enables**:

The dual-type `domain` field configuration enables:

1. **`string` with `lucene.keyword`**: Exact matching for filtering
2. **`stringFacet`**: Faceted search capabilities

**Benefits of Native Pre-filtering**:
- ⚡ **Performance**: Domain filter applied BEFORE text search using `compound` operator
- 💰 **Cost**: Fewer text search operations = lower cost
- 🎯 **Accuracy**: Search only in relevant domain chunks
- 📊 **Facets**: Enables faceted search by domain (e.g., "show me all domains with this term")

**How It Works in Code**:

When domain is specified, the code uses a **compound query**:

```php
// Compound query with native pre-filtering
$searchStage = [
    'index' => 'text_search_index',
    'compound' => [
        'must' => [
            ['text' => [
                'query' => 'contatti isofin',
                'path' => ['content', 'title']
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

This ensures domain filtering happens **before** the expensive text search operation.

### 5. Configure Index Name

1. **Index Name**: `text_search_index`
2. Click **"Next"**
3. Review configuration
4. Click **"Create Search Index"**

### 6. Wait for Index Build

⏳ Index build can take 5-30 minutes depending on data size

✅ Status will change from "Building" to "Active" when ready

---

## 🔍 Index Configuration Explained

### Content Field (Full-Text Search)

```json
{
  "type": "string",
  "analyzer": "lucene.standard"
}
```

- **type**: `string` - Standard text field
- **analyzer**: `lucene.standard` - Provides:
  - Tokenization (splits text into words)
  - Lowercasing
  - Stop words removal
  - Stemming (e.g., "running" → "run")

**Use Case**: Main content search with linguistic analysis

### Title Field (Autocomplete)

```json
{
  "type": "autocomplete",
  "tokenization": "edgeGram",
  "minGrams": 2,
  "maxGrams": 15,
  "foldDiacritics": true
}
```

- **type**: `autocomplete` - Enables search-as-you-type
- **tokenization**: `edgeGram` - Creates prefixes (e.g., "pizza" → "pi", "piz", "pizz", "pizza")
- **minGrams**: `2` - Minimum 2 characters to match
- **maxGrams**: `15` - Maximum 15 characters for prefix
- **foldDiacritics**: `true` - Treats "café" same as "cafe"

**Use Case**: Autocomplete search bars, partial word matching

### Domain Field (Exact Matching + Facets)

#### Basic Configuration:
```json
{
  "type": "string",
  "analyzer": "lucene.keyword"
}
```

#### Enhanced Configuration (Recommended):
```json
[
  {
    "type": "string",
    "analyzer": "lucene.keyword"
  },
  {
    "type": "stringFacet"
  }
]
```

**Configuration Explained**:
- **type**: `string` - Text field for filtering
- **analyzer**: `lucene.keyword` - No tokenization, exact match only
- **type**: `stringFacet` - Enables faceted search and counting

**Use Cases**:
- Basic: Post-filtering by domain (slower)
- Enhanced: Native pre-filtering with `compound` queries (faster)

---

## ✅ Verify Index is Active

### Via Atlas UI

1. Go to **Search Indexes** tab
2. Check `text_search_index` status = **"Active"**
3. Green checkmark ✅ means ready to use

### Via Code (Test)

```bash
php artisan rag:hybrid-search "contatti" --domain="isofin.it" --topK=10
```

Expected output:
```
Vector Results: 10
Text Results: 10
Merged Results: 14
```

---

## 🚨 Troubleshooting

### Index Build Fails

**Problem**: Index status shows "Failed"

**Solutions**:
1. Verify collection has documents with `content` and `title` fields
2. Check field types are strings (not arrays or objects)
3. Delete failed index and recreate

### Text Search Returns 0 Results

**Problem**: No results even though chunks exist

**Check**:
```bash
# Verify fields exist
db.webscraper_chunks.findOne({ content: { $exists: true, $ne: null } })

# Check sample data
db.webscraper_chunks.findOne({}, { content: 1, title: 1, domain: 1 })
```

**Expected**: Documents should have `content`, `title`, and `domain` fields

### Fuzzy Search Not Working

**Problem**: Typos not being matched (e.g., "contati" doesn't find "contatti")

**Solution**:

Ensure you're using fuzzy option in code:

```php
$searchService->search(
    collection: 'webscraper_chunks',
    query: 'contati', // Typo
    options: [
        'fuzzy' => true, // ← Enable fuzzy matching
        'limit' => 10,
    ]
);
```

Fuzzy configuration is applied in code, not in index:
```php
'fuzzy' => [
    'maxEdits' => 2,      // Allow up to 2 character changes
    'prefixLength' => 3,  // First 3 chars must match
]
```

### Autocomplete Not Working

**Problem**: Autocomplete search returns no suggestions

**Check**:
1. Verify you're using the `autocomplete()` method, not `search()`
2. Ensure `title` field is indexed with `autocomplete` type
3. Query must be at least 2 characters (minGrams = 2)

**Example**:
```php
$searchService->autocomplete(
    collection: 'webscraper_chunks',
    query: 'con', // At least 2 chars
    options: ['field' => 'title']
);
```

---

## 📊 Index Statistics

Check index size and performance:

1. Atlas UI → **Search Indexes** tab
2. Click on `text_search_index`
3. View **"Index Details"**:
   - Documents indexed
   - Index size (MB)
   - Last updated

---

## 🔗 Related Documentation

- [MONGODB_ATLAS_INDEXES.md](../../../MONGODB_ATLAS_INDEXES.md) - Complete index reference
- [HOW-TO-CREATE-VECTOR-INDEX.md](./HOW-TO-CREATE-VECTOR-INDEX.md) - Vector search index guide
- [QUERY_ENHANCEMENT_STRATEGY.md](../../../QUERY_ENHANCEMENT_STRATEGY.md) - Query enhancement docs
- [MongoDB Text Search Docs](https://www.mongodb.com/docs/atlas/atlas-search/text/)
- [MongoDB Autocomplete Docs](https://www.mongodb.com/docs/atlas/atlas-search/autocomplete/)

---

## 💡 Pro Tips

1. **Index Name**: Must be exactly `text_search_index` (used in code)
2. **Dynamic Mapping**: Set to `false` to avoid indexing unwanted fields
3. **Analyzer Choice**:
   - Use `lucene.standard` for natural language text
   - Use `lucene.keyword` for exact matching (domains, IDs)
4. **Fuzzy Matching**: Enable in code with `fuzzy: true`, not in index config
5. **Test Incrementally**: Test with small dataset before indexing entire collection

---

## 🆚 Text Search vs Vector Search

| Feature | Text Search | Vector Search |
|---------|-------------|---------------|
| **Index Name** | `text_search_index` | `vector_index_1` |
| **Type** | Atlas Search | Atlas Vector Search |
| **Method** | Keyword matching | Semantic similarity |
| **Best For** | Exact terms, typos | Meaning, synonyms |
| **Speed** | Very fast | Fast |
| **Analyzer** | Lucene (stemming) | Cosine similarity |
| **Use Case** | "find pizza restaurants" | "where to eat Italian food" |

**Recommendation**: Use **Hybrid Search** (combining both) for best results!

---

**Created**: 2025-11-16
**Version**: 1.0
**Status**: Production Ready ✅