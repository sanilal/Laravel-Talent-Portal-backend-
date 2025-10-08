# Search API - Quick Reference Card

## 🚀 Installation (5 Minutes)

```bash
# 1. Create files in your Laravel backend
mkdir -p app/Helpers
# Copy: SimilarityHelper.php, SearchService.php, SearchController.php

# 2. Add routes to routes/api.php
# Copy the 6 route definitions

# 3. Refresh Laravel
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 4. Verify
php artisan route:list | grep search
```

## 📡 The 6 Endpoints

| # | Method | Endpoint | Purpose |
|---|--------|----------|---------|
| 1 | POST | `/search/talents` | Find talents by text query |
| 2 | POST | `/projects/{id}/match-talents` | Find talents for project |
| 3 | POST | `/talents/{id}/match-projects` | Find projects for talent |
| 4 | GET | `/portfolios/{id}/similar` | Similar portfolios |
| 5 | GET | `/skills/{id}/related` | Related skills |
| 6 | GET | `/talents/{id}/recommendations` | Smart recommendations |

## 🧪 Quick Test

```bash
# Get token
TOKEN=$(curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john.talent@test.com","password":"Password123!"}' \
  | jq -r '.token')

# Test search
curl -X POST http://localhost:8000/api/v1/search/talents \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":"React developer","limit":5}' | jq
```

## 🔥 Most Common Requests

### 1. Search Talents
```bash
curl -X POST http://localhost:8000/api/v1/search/talents \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "senior Python developer with ML experience",
    "limit": 10,
    "min_similarity": 0.7,
    "filters": {
      "experience_level": "senior",
      "availability": true
    }
  }'
```

### 2. Match Talents to Project
```bash
curl -X POST http://localhost:8000/api/v1/projects/{PROJECT_ID}/match-talents \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "limit": 20,
    "min_similarity": 0.65,
    "filters": {
      "availability": true
    }
  }'
```

### 3. Get Recommendations
```bash
curl -X GET "http://localhost:8000/api/v1/talents/{TALENT_ID}/recommendations?limit=10" \
  -H "Authorization: Bearer $TOKEN"
```

## 📊 Understanding Scores

| Score Range | Quality | Meaning |
|-------------|---------|---------|
| 0.9 - 1.0 | Excellent | Perfect match |
| 0.8 - 0.9 | Very Good | Strong match |
| 0.7 - 0.8 | Good | Solid match |
| 0.6 - 0.7 | Fair | Acceptable match |
| 0.5 - 0.6 | Moderate | Weak match |
| < 0.5 | Poor | Not recommended |

## ⚙️ Similarity Weights

### Talent Search
- 40% Profile
- 40% Skills
- 20% Experience

### Project Matching
- 50% Requirements
- 30% Skills
- 20% Experience

### Recommendations
- 30% Profile
- 30% Skills
- 20% Experience
- 20% Preferences

## 🐛 Troubleshooting

### Problem: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
```

### Problem: No results / All zeros
```bash
# Check embeddings exist
php artisan tinker
$profile = \App\Models\TalentProfile::first();
count($profile->profile_embedding); # Should be 384
```

### Problem: Slow performance
```bash
# Check how many have embeddings
php artisan tinker
$total = \App\Models\TalentProfile::count();
$with = \App\Models\TalentProfile::whereNotNull('profile_embedding')->count();
echo "$with / $total\n";

# Make sure queue worker is running
php artisan queue:work --queue=embeddings
```

### Problem: Python service error
```bash
# Check service health
curl http://localhost:5001/health

# Restart if needed
cd embedding-service
source venv/Scripts/activate
python app.py
```

## 🔍 Health Checks

```bash
# Python service
curl http://localhost:5001/health
# Expected: {"status":"healthy","model":"all-MiniLM-L6-v2"}

# Laravel API
curl http://localhost:8000/api/v1/health
# Expected: {"status":"healthy"}

# Check routes
php artisan route:list | grep search
# Expected: 6 search routes

# Run tests
php artisan test --filter SearchTest
# Expected: All tests passing
```

## 📈 Performance Targets

| Dataset Size | Target Response Time |
|--------------|---------------------|
| < 100 records | < 500ms |
| 100-1000 records | < 2s |
| 1000+ records | 2-5s |

## 🎯 Recommended Settings

### For Quality (Strict Matching)
```json
{
  "min_similarity": 0.8,
  "limit": 10
}
```

### For Discovery (Broad Search)
```json
{
  "min_similarity": 0.6,
  "limit": 50
}
```

### For Production (Balanced)
```json
{
  "min_similarity": 0.7,
  "limit": 20
}
```

## 🔐 Common Headers

```bash
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json
```

## 📝 Validation Rules

| Field | Type | Rules |
|-------|------|-------|
| query | string | Required, 2-500 chars |
| limit | integer | Optional, 1-100 |
| min_similarity | float | Optional, 0.0-1.0 |
| experience_level | enum | junior/mid/senior/expert |

## 🎨 Response Structure

```json
{
  "success": true,
  "data": {
    "results": [...],
    "total": 15,
    "query": "...",
    "execution_time_ms": 245.67,
    "filters_applied": false
  }
}
```

## 🛠️ Useful Commands

```bash
# View logs
tail -f storage/logs/laravel.log | grep -i search

# Check queue
php artisan queue:monitor

# Failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all

# Test in Tinker
php artisan tinker
$service = app(\App\Services\SearchService::class);
$results = $service->searchTalents('developer', [], 5);
```

## 📂 File Locations

```
app/
├── Helpers/
│   └── SimilarityHelper.php         # Cosine similarity
├── Services/
│   └── SearchService.php            # Search logic
└── Http/Controllers/Api/
    └── SearchController.php         # API endpoints

routes/
└── api.php                          # Route definitions

tests/
├── Unit/
│   └── SimilarityHelperTest.php    # Unit tests
└── Feature/
    └── SearchTest.php              # Integration tests
```

## 🎓 Key Concepts

**Cosine Similarity**: Measures angle between vectors (0-1 scale)
**Embedding**: 384-dimensional vector representing semantic meaning
**Weighted Scoring**: Combining multiple similarities with different importance
**Threshold**: Minimum similarity to include in results

## 🆘 Emergency Restart

```bash
# Stop all
# Ctrl+C in each terminal

# Restart Python service
cd embedding-service && source venv/Scripts/activate && python app.py &

# Restart Laravel
php artisan serve &

# Restart queue worker
php artisan queue:work --queue=embeddings --tries=3 --timeout=60 &
```

## 📞 Support Checklist

Before asking for help:
- [ ] All 3 services running (Python, Laravel, Queue)
- [ ] Routes visible in `php artisan route:list`
- [ ] Embeddings exist (check in tinker)
- [ ] Authentication token obtained
- [ ] Checked logs for errors
- [ ] Ran `composer dump-autoload`

## 🎉 Success Indicators

✅ Routes registered (6 routes)  
✅ Services running (health checks pass)  
✅ Tests passing (`php artisan test`)  
✅ Embeddings generated (384 dimensions)  
✅ Search returns results (>0 matches)  
✅ Response times < 2s  

---

**Quick Help**: Check `SEARCH_API_DOCUMENTATION.md` for full API docs  
**Installation**: See `SEARCH_API_INSTALLATION.md` for setup guide  
**Overview**: Read `IMPLEMENTATION_SUMMARY.md` for complete details