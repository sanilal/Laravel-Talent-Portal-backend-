#!/bin/bash

echo "=== Testing Search API ==="

# Your token
TOKEN="43|ScGaYJmBbdldilBGdJe1rbMaDxYfmcXY3kLpAXAv938ee60d"

echo ""
echo "1. Testing talent search..."
curl -s -X POST http://localhost:8000/api/v1/search/talents \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":"Full Stack Developer","limit":3}' > search.json

if grep -q '"success":true' search.json; then
    echo "✓ Search API is working!"
    
    # Extract key info
    echo ""
    echo "Results:"
    grep -o '"total":[0-9]*' search.json
    grep -o '"execution_time_ms":[0-9.]*' search.json
    
    echo ""
    echo "First result:"
    grep -o '"professional_title":"[^"]*' search.json | head -1
    grep -o '"similarity_score":[0-9.]*' search.json | head -1
else
    echo "✗ Search failed"
    cat search.json
fi

echo ""
echo "=== Test Complete ==="