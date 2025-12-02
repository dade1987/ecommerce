# How to Create MongoDB Atlas Vector Search Index

## 📋 Quick Guide

Follow these steps to create the **vector_index_1** on MongoDB Atlas for semantic search.

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

### 3. Create Vector Search Index

1. Click **"Create Search Index"** button
2. Select **"Atlas Vector Search"** (NOT "Atlas Search")
3. Choose **"JSON Editor"**
4. Click **"Next"**

### 4. Paste the JSON Configuration

Copy and paste this **exact JSON** into the editor:

```json
{
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
```

### 5. Configure Index Name

1. **Index Name**: `vector_index_1`
2. Click **"Next"**
3. Review configuration
4. Click **"Create Search Index"**

### 6. Wait for Index Build

⏳ Index build can take 5-30 minutes depending on data size

✅ Status will change from "Building" to "Active" when ready

---

## 🔍 Index Configuration Explained

### Vector Field (`embedding`)

```json
{
  "type": "vector",
  "path": "embedding",
  "numDimensions": 1536,
  "similarity": "cosine"
}
```

- **type**: `vector` - Enables vector search
- **path**: `embedding` - Field containing OpenAI embeddings
- **numDimensions**: `1536` - OpenAI text-embedding-3-small dimensions
- **similarity**: `cosine` - Cosine similarity metric (standard for OpenAI)

### Filter Field (`domain`)

```json
{
  "type": "filter",
  "path": "domain"
}
```

- **type**: `filter` - Enables native pre-filtering
- **path**: `domain` - Field to filter by (e.g., "isofin.it")

**Why filter field?**
- ⚡ **Performance**: Filter applied BEFORE vector search
- 💰 **Cost**: Fewer vector operations = lower cost
- 🎯 **Accuracy**: Search only in relevant domain chunks

---

## ✅ Verify Index is Active

### Via Atlas UI

1. Go to **Search Indexes** tab
2. Check `vector_index_1` status = **"Active"**
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
1. Check collection has documents with `embedding` field
2. Verify `embedding` is array of 1536 floats
3. Delete failed index and recreate

### Vector Search Returns 0 Results

**Problem**: No results even though chunks exist

**Check**:
```bash
# Verify embeddings exist
db.webscraper_chunks.findOne({ embedding: { $exists: true, $ne: null } })

# Check embedding dimensions
db.webscraper_chunks.aggregate([
  { $match: { embedding: { $exists: true } } },
  { $project: { dimensions: { $size: "$embedding" } } },
  { $limit: 1 }
])
```

**Expected**: `dimensions: 1536`

### Filter Not Working

**Problem**: Domain filter not being applied

**Solution**: Make sure you added `domain` as **filter field** in index configuration (see step 4)

---

## 📊 Index Statistics

Check index size and performance:

1. Atlas UI → **Search Indexes** tab
2. Click on `vector_index_1`
3. View **"Index Details"**:
   - Documents indexed
   - Index size (MB)
   - Last updated

---

## 🔗 Related Documentation

- [MONGODB_ATLAS_INDEXES.md](../../../MONGODB_ATLAS_INDEXES.md) - Complete index reference
- [QUERY_ENHANCEMENT_STRATEGY.md](../../../QUERY_ENHANCEMENT_STRATEGY.md) - Query enhancement docs
- [MongoDB Vector Search Docs](https://www.mongodb.com/docs/atlas/atlas-vector-search/)
- [OpenAI Embeddings Guide](https://platform.openai.com/docs/guides/embeddings)

---

## 💡 Pro Tips

1. **Index Name**: Must be exactly `vector_index_1` (used in code)
2. **Dimensions**: Must be `1536` for OpenAI text-embedding-3-small
3. **Filter Field**: Always add `domain` for better performance
4. **Test Early**: Test with small query before indexing all sites

---

**Created**: 2025-11-16
**Version**: 1.0
**Status**: Production Ready ✅