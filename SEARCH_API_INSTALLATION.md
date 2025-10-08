# Search API Installation Guide

## Prerequisites Checklist
Before installing the Search API, ensure all these are running:

- ✅ PostgreSQL database (with all migrations run)
- ✅ Python embedding service (Flask on port 5001)
- ✅ Laravel API server (port 8000)
- ✅ Queue worker processing embeddings
- ✅ Sample data with generated embeddings

---

## Installation Steps

### Step 1: Create Required Files

Create the following directory structure in your Laravel backend:

```bash
backend/
├── app/
│   ├── Helpers/
│   │   └── SimilarityHelper.php          # NEW
│   ├── Services/
│   │   └── SearchService.php             # NEW
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── SearchController.php  # NEW
```

### Step 2: Copy the SimilarityHelper

**File:** `app/Helpers/SimilarityHelper.php`

```bash
# Create the Helpers directory if it doesn't exist
mkdir -p app/Helpers

# Copy the SimilarityHelper.php content from the artifact
# (Use the code provided in the SimilarityHelper.php artifact)
```

**Important:** Make sure the namespace is correct:
```php
namespace App\Helpers;
```

### Step 3: Copy the SearchService

**File:** `app/Services/SearchService.php`

```bash
# The Services directory should already exist
# Copy the SearchService.php content from the artifact
```

**Important:** Ensure EmbeddingService is injected in constructor:
```php
public function __construct(
    private EmbeddingService $embeddingService
) {}
```

### Step 4: Copy the SearchController

**File:** `app/Http/Controllers/Api/SearchController.php`

```bash
# Copy the SearchController.php content from the artifact
```

### Step 5: Add Routes

**File:** `routes/api.php`

Add these routes to your existing `routes/api.php` file (inside the `auth:sanctum` middleware group):

```php
// Add this to your existing authenticated routes group
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    
    // ... your existing routes ...
    
    // Search & Matching Routes (NEW)
    Route::post('/search/talents', [SearchController::class, 'searchTalents']);
    Route::post('/projects/{project}/match-talents', [SearchController::class, 'matchTalentsToProject']);
    Route::post('/talents/{talent}/match-projects', [SearchController::class, 'matchProjectsToTalent']);
    Route::get('/portfolios/{portfolio}/similar', [SearchController::class, 'similarPortfolios']);
    Route::get('/skills/{skill}/related', [SearchController::class, 'relatedSkills']);
    Route::get('/talents/{talent}/recommendations', [SearchController::class, 'recommendations']);
});
```

Don't forget to import the SearchController at the top:
```php
use App\Http\Controllers\Api\SearchController;
```

### Step 6: Verify Autoloading

Run Composer's autoload to ensure new classes are recognized:

```bash
cd /d/xampp/htdocs/projects/talents-you-need/backend
composer dump-autoload
```

### Step 7: Clear Cache

Clear Laravel's cache to ensure everything is fresh:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Step 8: Verify Routes

Check that the new routes are registered:

```bash
php artisan route:list | grep -i search
```

Expected output:
```
POST      api/v1/search/talents ..................... SearchController@searchTalents
POST      api/v1/projects/{project}/match-talents ... SearchController@matchTalentsToProject
POST      api/v1/talents/{talent}/match-projects .... SearchController@matchProjectsToTalent
GET       api/v1/portfolios/{portfolio}/similar ..... SearchController@similarPortfolios
GET       api/v1/skills/{skill}/related ............. SearchController@relatedSkills
GET       api/v1/talents/{talent}/recommendations ... SearchController@recommendations
```

---

## Verification Tests

### Test 1: Check Services are Running

```bash
# Test Python embedding service
curl http://localhost:5001/health

# Expected: {"status":"healthy","model":"all-MiniLM-L6-v2","dimensions":384}

# Test Laravel API
curl http://localhost:8000/api/v1/health

# Expected: {"status":"healthy"}
```

### Test 2: Verify Embeddings Exist

```bash
php artisan tinker
```

```php
// Check if embeddings exist
$profile = \App\Models\TalentProfile::whereNotNull('profile_embedding')->first();

if ($profile) {
    echo "✓ Found profile with embeddings\n";
    echo "  Profile embedding dimensions: " . count($profile->profile_embedding) . "\n";
    echo "  Skills embedding dimensions: " . count($profile->skills_embedding) . "\n";
    echo "  Expected: 384 for all\n";
} else {
    echo "✗ No profiles with embeddings found!\n";
    echo "  Run: php artisan queue:work --queue=embeddings\n";
}

exit;
```

### Test 3: Get Authentication Token

```bash
# Login to get a token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.talent@test.com",
    "password": "Password123!"
  }' | jq

# Save the token from the response
```

### Test 4: Test Talent Search

```bash
# Replace YOUR_TOKEN with your actual token
curl -X POST http://localhost:8000/api/v1/search/talents \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "React developer",
    "limit": 5
  }' | jq
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "results": [...],
    "total": 5,
    "query": "React developer",
    "execution_time_ms": 245.67
  }
}
```

### Test 5: Test Project Matching

First, get a project ID:
```bash
php artisan tinker
```

```php
$project = \App\Models\Project::whereNotNull('requirements_embedding')->first();
echo $project->id;
exit;
```

Then test the endpoint:
```bash
# Replace PROJECT_ID and YOUR_TOKEN
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/match-talents \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "limit": 5,
    "min_similarity": 0.6
  }' | jq
```

---

## Troubleshooting

### Issue: "Class 'App\Helpers\SimilarityHelper' not found"

**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

### Issue: "Call to undefined method EmbeddingService::generateEmbedding()"

**Check:** Ensure EmbeddingService exists and has this method:
```bash
ls -la app/Services/EmbeddingService.php
```

If missing, the EmbeddingService should already exist from your previous setup.

### Issue: "No query results for model [App\Models\TalentProfile]"

**Solution:** The UUID in the URL might be wrong. Get a valid ID:
```bash
php artisan tinker
$talent = \App\Models\TalentProfile::first();
echo $talent->id;
```

### Issue: Embeddings are NULL

**Solution:** Generate embeddings:
```bash
# Make sure queue worker is running
php artisan queue:work --queue=embeddings --tries=3 --timeout=60

# Trigger embedding generation
php artisan tinker
$profile = \App\Models\TalentProfile::first();
$profile->touch(); // This triggers the observer
```

### Issue: "Embeddings not generated yet" error

**Solution:** Wait for queue jobs to process, or check:
```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Issue: Slow search performance (>5 seconds)

**Possible causes:**
1. Too many records without embeddings (filter them out)
2. Queue worker not running (embeddings being generated on-the-fly)
3. Python service slow to respond

**Solution:**
```bash
# Check how many profiles have embeddings
php artisan tinker
$total = \App\Models\TalentProfile::count();
$withEmbeddings = \App\Models\TalentProfile::whereNotNull('profile_embedding')->count();
echo "Total: $total, With embeddings: $withEmbeddings\n";
```

### Issue: Python service connection error

**Solution:**
```bash
# Check if Python service is running
curl http://localhost:5001/health

# If not running, start it:
cd embedding-service
source venv/Scripts/activate
python app.py
```

---

## Performance Optimization

### 1. Add Database Indexes

Add these indexes for faster queries:

```php
// Create a new migration
php artisan make:migration add_search_indexes
```

```php
public function up()
{
    Schema::table('talent_profiles', function (Blueprint $table) {
        $table->index('experience_level');
        $table->index('is_available');
        $table->index('embeddings_generated_at');
    });
    
    Schema::table('projects', function (Blueprint $table) {
        $table->index('status');
        $table->index(['status', 'work_type']);
        $table->index('embeddings_generated_at');
    });
}
```

Run the migration:
```bash
php artisan migrate
```

### 2. Enable Query Caching

Add to `.env`:
```env
CACHE_DRIVER=redis  # or memcached
```

### 3. Optimize Eager Loading

The SearchService already uses eager loading, but verify relationships are loaded:
```php
->with(['user', 'skills.skill', 'primaryCategory'])
```

---

## Running Tests

### Unit Tests

```bash
# Test SimilarityHelper
php artisan test --filter SimilarityHelperTest

# Expected: All tests should pass
```

### Feature Tests

```bash
# Test Search API
php artisan test --filter SearchTest

# Expected: All tests should pass
```

### Run All Tests

```bash
php artisan test

# Or with coverage
php artisan test --coverage
```

---

## Deployment Checklist

Before deploying to production:

- [ ] All tests passing
- [ ] Python service configured to auto-start
- [ ] Queue worker configured as supervisor process
- [ ] Database indexes added
- [ ] Caching configured (Redis/Memcached)
- [ ] Rate limiting configured
- [ ] API documentation published
- [ ] Monitoring/logging configured
- [ ] Error tracking setup (Sentry, etc.)

---

## Monitoring

### Key Metrics to Monitor

1. **Search Response Times**
   - Target: <500ms for <100 records
   - Alert: >2 seconds

2. **Embedding Generation Times**
   - Target: <500ms per job
   - Check: `storage/logs/laravel.log`

3. **Queue Job Failures**
   ```bash
   php artisan queue:failed
   ```

4. **Python Service Health**
   ```bash
   curl http://localhost:5001/health
   ```

### Logging

Search operations are logged automatically. Check logs:
```bash
tail -f storage/logs/laravel.log | grep -i "search\|similarity"
```

---

## Next Steps

1. **Frontend Integration**: Build UI components that call these endpoints
2. **Advanced Features**: Add faceted search, filters, sorting
3. **Analytics**: Track search queries and match quality
4. **A/B Testing**: Experiment with different similarity weights
5. **Scaling**: Consider vector databases (Pinecone, Weaviate) for >10k records

---

## Support Commands

```bash
# Clear everything and start fresh
php artisan config:clear
php artisan cache:clear
php artisan route:clear
composer dump-autoload

# Check routes
php artisan route:list | grep search

# Run tinker for debugging
php artisan tinker

# Check logs
tail -f storage/logs/laravel.log

# Monitor queue
php artisan queue:monitor

# Test endpoint health
curl http://localhost:8000/api/v1/health
curl http://localhost:5001/health
```

---

## Congratulations! 🎉

Your Search API is now fully installed and operational. You have:

✅ 6 semantic search endpoints
✅ Intelligent talent-project matching
✅ Portfolio and skill similarity
✅ Personalized recommendations
✅ Comprehensive tests
✅ Full documentation

Start testing with the provided examples and integrate with your frontend!