# Search API Documentation

## Overview
The Search API provides 6 powerful semantic search endpoints that leverage 384-dimensional embeddings to deliver intelligent talent matching, project recommendations, and content discovery.

All endpoints require authentication via Laravel Sanctum.

---

## Authentication
Include your Sanctum token in the `Authorization` header:
```bash
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

## Endpoints

### 1. Semantic Talent Search

**Endpoint:** `POST /api/v1/search/talents`

**Description:** Search for talents using natural language queries. The system converts your query into an embedding and finds the most semantically similar talent profiles.

**Request Body:**
```json
{
  "query": "React developer with 5 years experience in fintech",
  "limit": 20,
  "min_similarity": 0.7,
  "filters": {
    "experience_level": "senior",
    "hourly_rate_max": 150,
    "availability": true,
    "category_id": "uuid-here"
  }
}
```

**Parameters:**
- `query` (required, string): Natural language search query (2-500 characters)
- `limit` (optional, integer): Maximum results (1-100, default: 20)
- `min_similarity` (optional, float): Minimum similarity threshold (0.0-1.0, default: 0.5)
- `filters` (optional, object): Additional filters
  - `experience_level` (optional): junior, mid, senior, expert
  - `hourly_rate_max` (optional, numeric): Maximum hourly rate
  - `availability` (optional, boolean): Filter by availability status
  - `category_id` (optional, uuid): Filter by primary category

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/v1/search/talents \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "experienced Python developer with machine learning skills",
    "limit": 10,
    "min_similarity": 0.7
  }'
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "results": [
      {
        "id": "uuid",
        "user_id": "uuid",
        "professional_title": "Senior Python Developer & ML Engineer",
        "summary": "8+ years experience in Python, TensorFlow, and data science...",
        "similarity_score": 0.8945,
        "match_quality": "very_good",
        "hourly_rate_min": 80,
        "hourly_rate_max": 150,
        "currency": "USD",
        "experience_level": "senior",
        "is_available": true,
        "match_reasons": [
          "Strong skills match (0.92)",
          "Relevant experience (0.87)",
          "Profile alignment (0.89)"
        ],
        "breakdown": {
          "profile": 0.89,
          "skills": 0.92,
          "experience": 0.87
        }
      }
    ],
    "total": 15,
    "query": "experienced Python developer with machine learning skills",
    "execution_time_ms": 245.67,
    "filters_applied": false
  }
}
```

---

### 2. Project-to-Talent Matching

**Endpoint:** `POST /api/v1/projects/{project_id}/match-talents`

**Description:** Find the best matching talents for a specific project. Uses multi-factor scoring to compare project requirements against talent profiles.

**Authorization:** Requires project owner or admin role.

**Request Body:**
```json
{
  "limit": 20,
  "min_similarity": 0.65,
  "filters": {
    "availability": true,
    "max_hourly_rate": 150,
    "experience_level": "senior"
  }
}
```

**Parameters:**
- `limit` (optional, integer): Maximum results (1-100, default: 20)
- `min_similarity` (optional, float): Minimum match threshold (0.0-1.0, default: 0.6)
- `filters` (optional, object): Additional filters

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/v1/projects/{project_id}/match-talents \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "limit": 10,
    "min_similarity": 0.7,
    "filters": {
      "availability": true
    }
  }'
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "project": {
      "id": "uuid",
      "title": "Senior React Developer Needed",
      "budget": "$80k-120k USD",
      "work_type": "remote"
    },
    "matches": [
      {
        "talent_id": "uuid",
        "professional_title": "Senior Full Stack Developer",
        "overall_score": 0.8734,
        "breakdown": {
          "requirements_match": 0.89,
          "skills_match": 0.92,
          "experience_match": 0.81
        },
        "strengths": [
          "Exact skill match: React, TypeScript, Node.js",
          "Experience level matches requirement",
          "Rate within budget"
        ],
        "gaps": [
          "Missing: AWS certification"
        ],
        "hourly_rate_range": "$75-150 USD"
      }
    ],
    "total_analyzed": 47,
    "total_matched": 12,
    "execution_time_ms": 312.45
  }
}
```

---

### 3. Talent-to-Project Matching

**Endpoint:** `POST /api/v1/talents/{talent_id}/match-projects`

**Description:** Find suitable projects for a specific talent. Helps talents discover opportunities that match their profile.

**Authorization:** Requires talent owner or admin role.

**Request Body:**
```json
{
  "limit": 20,
  "min_similarity": 0.65,
  "filters": {
    "project_type": "full-time",
    "work_type": "remote",
    "budget_max": 200000
  }
}
```

**Parameters:**
- `limit` (optional, integer): Maximum results (1-100, default: 20)
- `min_similarity` (optional, float): Minimum match threshold (0.0-1.0, default: 0.6)
- `filters` (optional, object): Additional filters
  - `project_type` (optional): full-time, part-time, contract, freelance
  - `work_type` (optional): remote, onsite, hybrid
  - `budget_max` (optional, numeric): Maximum budget

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/v1/talents/{talent_id}/match-projects \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "limit": 15,
    "filters": {
      "work_type": "remote"
    }
  }'
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "talent": {
      "id": "uuid",
      "professional_title": "AI/ML Engineer",
      "experience_level": "senior"
    },
    "recommended_projects": [
      {
        "project_id": "uuid",
        "title": "Machine Learning Engineer for NLP Project",
        "match_score": 0.9123,
        "compatibility": {
          "profile_match": 0.93,
          "skills_match": 0.89,
          "experience_match": 0.91
        },
        "why_good_fit": [
          "Your skills align perfectly with requirements",
          "Your experience matches project needs",
          "Remote work matches your preferences",
          "Budget aligns with your rate expectations"
        ],
        "budget": "$100k-140k USD",
        "work_type": "remote",
        "duration": "6 months"
      }
    ],
    "total": 8,
    "execution_time_ms": 198.23
  }
}
```

---

### 4. Similar Portfolios

**Endpoint:** `GET /api/v1/portfolios/{portfolio_id}/similar`

**Description:** Find portfolios similar to a given portfolio based on content similarity.

**Query Parameters:**
- `limit` (optional, integer): Maximum results (1-50, default: 10)
- `min_similarity` (optional, float): Minimum similarity threshold (0.0-1.0, default: 0.6)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/portfolios/{portfolio_id}/similar?limit=10&min_similarity=0.6" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "source_portfolio": {
      "id": "uuid",
      "title": "E-commerce Platform Redesign",
      "user_id": "uuid"
    },
    "similar_portfolios": [
      {
        "id": "uuid",
        "title": "Shopify Store Custom Theme",
        "similarity_score": 0.8734,
        "user": {
          "id": "uuid",
          "name": "Jane Designer"
        },
        "common_elements": [
          "E-commerce focus",
          "Common tags: UI/UX, responsive",
          "Similar role: Lead Designer"
        ],
        "project_type": "web-development"
      }
    ],
    "total": 6,
    "execution_time_ms": 89.12
  }
}
```

---

### 5. Related Skills

**Endpoint:** `GET /api/v1/skills/{skill_id}/related`

**Description:** Find skills related to a given skill, with automatic clustering of related skills by category and relationship type.

**Query Parameters:**
- `limit` (optional, integer): Maximum results (1-50, default: 15)
- `min_similarity` (optional, float): Minimum similarity threshold (0.0-1.0, default: 0.7)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/skills/{skill_id}/related?limit=15&min_similarity=0.7" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "skill": {
      "id": "uuid",
      "name": "React",
      "category": "Frontend Frameworks"
    },
    "related_skills": [
      {
        "id": "uuid",
        "name": "Next.js",
        "similarity_score": 0.9123,
        "category": "Frontend Frameworks",
        "relationship": "alternative"
      },
      {
        "id": "uuid",
        "name": "TypeScript",
        "similarity_score": 0.8547,
        "category": "Programming Languages",
        "relationship": "commonly_used_together"
      },
      {
        "id": "uuid",
        "name": "Vue.js",
        "similarity_score": 0.8234,
        "category": "Frontend Frameworks",
        "relationship": "alternative"
      }
    ],
    "clusters": [
      {
        "name": "Frontend Frameworks",
        "skills": ["Next.js", "Vue.js", "Gatsby"],
        "avg_similarity": 0.8812
      },
      {
        "name": "Programming Languages",
        "skills": ["TypeScript", "JavaScript"],
        "avg_similarity": 0.8423
      }
    ],
    "total": 12,
    "execution_time_ms": 76.45
  }
}
```

---

### 6. Smart Recommendations

**Endpoint:** `GET /api/v1/talents/{talent_id}/recommendations`

**Description:** Generate personalized project recommendations for a talent based on their profile, skills, experience, and preferences. Excludes projects they've already applied to.

**Authorization:** Requires talent owner or admin role.

**Query Parameters:**
- `limit` (optional, integer): Maximum results (1-50, default: 10)

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/talents/{talent_id}/recommendations?limit=10" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "recommendations": [
      {
        "project_id": "uuid",
        "title": "Senior Full Stack Developer",
        "recommendation_score": 0.8923,
        "reasons": [
          "Perfect skill match based on your profile",
          "Your experience aligns with project needs",
          "Matches your work preferences",
          "Budget aligns with your rates"
        ],
        "confidence": "high",
        "budget": "$90k-130k USD",
        "work_type": "remote",
        "posted_days_ago": 2
      }
    ],
    "personalization_factors": {
      "based_on_profile": 0.3,
      "based_on_skills": 0.3,
      "based_on_experience": 0.2,
      "based_on_preferences": 0.2
    },
    "total": 8,
    "execution_time_ms": 234.56
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "query": ["The query field is required."],
    "limit": ["The limit must be between 1 and 100."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Unauthorized access to this project"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Project not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Search failed",
  "error": "Detailed error message (only in debug mode)"
}
```

---

## Understanding Similarity Scores

### Score Ranges
- **0.9 - 1.0**: Excellent match (90-100%)
- **0.8 - 0.9**: Very good match (80-90%)
- **0.7 - 0.8**: Good match (70-80%)
- **0.6 - 0.7**: Fair match (60-70%)
- **0.5 - 0.6**: Moderate match (50-60%)
- **< 0.5**: Poor match (below 50%)

### Match Quality Labels
- `excellent`: 90%+ similarity
- `very_good`: 80-90% similarity
- `good`: 70-80% similarity
- `fair`: 60-70% similarity
- `moderate`: 50-60% similarity
- `poor`: below 50% similarity

---

## Performance Considerations

### Expected Response Times
- **Small datasets** (<100 records): < 500ms
- **Medium datasets** (100-1000 records): < 2 seconds
- **Large datasets** (1000+ records): 2-5 seconds

### Optimization Tips
1. Use `min_similarity` to filter out poor matches early
2. Set appropriate `limit` values (don't request more than needed)
3. Apply filters to reduce the search space
4. Consider caching frequently searched queries

---

## Best Practices

### 1. Query Formation
- **Good**: "React developer with 5 years experience in fintech"
- **Good**: "Senior Python engineer specializing in machine learning"
- **Avoid**: Single words like "developer" (too broad)
- **Avoid**: Very long queries (>200 words)

### 2. Setting Thresholds
- Start with `min_similarity: 0.6` for broad results
- Use `0.7-0.8` for high-quality matches
- Use `0.8+` for very strict matching

### 3. Using Filters
- Combine semantic search with filters for best results
- Use availability filter when time-sensitive
- Use experience_level to match seniority requirements

### 4. Handling Large Result Sets
- Always use pagination (limit parameter)
- Sort results client-side if needed beyond similarity
- Cache frequently accessed results

---

## Testing Endpoints

### Using cURL
```bash
# Get your auth token first
TOKEN=$(curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john.talent@test.com","password":"Password123!"}' \
  | jq -r '.token')

# Then use it in requests
curl -X POST http://localhost:8000/api/v1/search/talents \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":"React developer","limit":10}'
```

### Using Postman
1. Create a new request
2. Set method and URL
3. Go to Authorization tab → Type: Bearer Token
4. Enter your token
5. Go to Body tab → raw → JSON
6. Enter request body
7. Send

---

## Rate Limiting
- Standard rate limits apply (60 requests per minute per user)
- Search endpoints may have additional throttling during peak times
- Monitor `X-RateLimit-*` headers in responses

---

## Support
For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Verify embeddings: `php artisan tinker` → check embedding counts
- Health check: `GET /api/v1/health`
- Python service: `GET http://localhost:5001/health`