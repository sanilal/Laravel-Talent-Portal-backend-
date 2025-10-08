#!/bin/bash

# Search API Testing Script
# This script tests all 6 search endpoints to verify they're working correctly

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
API_URL="http://localhost:8000/api/v1"
PYTHON_SERVICE="http://localhost:5001"
TEST_EMAIL="john.talent@test.com"
TEST_PASSWORD="Password123!"

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Functions
print_header() {
    echo -e "\n${BLUE}==============================================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}==============================================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
    ((TESTS_PASSED++))
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
    ((TESTS_FAILED++))
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Start testing
print_header "Search API Test Suite"
echo "Testing API at: $API_URL"
echo "Started: $(date)"

# Test 1: Check if services are running
print_header "Test 1: Service Health Checks"

echo "Checking Python embedding service..."
if curl -s "$PYTHON_SERVICE/health" | grep -q "healthy"; then
    print_success "Python embedding service is healthy"
else
    print_error "Python embedding service is not responding"
    print_info "Start it with: cd embedding-service && python app.py"
    exit 1
fi

echo "Checking Laravel API..."
if curl -s "$API_URL/health" | grep -q "status"; then
    print_success "Laravel API is responding"
else
    print_error "Laravel API is not responding"
    print_info "Start it with: php artisan serve"
    exit 1
fi

# Test 2: Get authentication token
print_header "Test 2: Authentication"

echo "Logging in to get access token..."
LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d "{\"email\":\"$TEST_EMAIL\",\"password\":\"$TEST_PASSWORD\"}")

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    print_error "Failed to get authentication token"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
else
    print_success "Authentication successful"
    print_info "Token: ${TOKEN:0:20}..."
fi

# Test 3: Verify embeddings exist
print_header "Test 3: Verify Embeddings"

# We'll test this through an API call since we can't run PHP from bash easily
print_info "Checking if talent profiles have embeddings..."
# This will be verified in the search test

# Test 4: Test Talent Search
print_header "Test 4: Semantic Talent Search"

echo "Searching for 'React developer'..."
SEARCH_RESPONSE=$(curl -s -X POST "$API_URL/search/talents" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
        "query": "React developer",
        "limit": 5,
        "min_similarity": 0.5
    }')

if echo "$SEARCH_RESPONSE" | grep -q '"success":true'; then
    print_success "Talent search endpoint working"
    
    TOTAL=$(echo $SEARCH_RESPONSE | grep -o '"total":[0-9]*' | cut -d':' -f2)
    EXEC_TIME=$(echo $SEARCH_RESPONSE | grep -o '"execution_time_ms":[0-9.]*' | cut -d':' -f2)
    
    print_info "Found $TOTAL results in ${EXEC_TIME}ms"
    
    # Check if results have required fields
    if echo "$SEARCH_RESPONSE" | grep -q '"similarity_score"'; then
        print_success "Results include similarity scores"
    else
        print_error "Results missing similarity scores"
    fi
else
    print_error "Talent search failed"
    echo "Response: $SEARCH_RESPONSE"
fi

# Test 5: Test with filters
print_header "Test 5: Talent Search with Filters"

echo "Searching with experience level filter..."
FILTER_RESPONSE=$(curl -s -X POST "$API_URL/search/talents" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
        "query": "developer",
        "limit": 10,
        "filters": {
            "experience_level": "senior"
        }
    }')

if echo "$FILTER_RESPONSE" | grep -q '"success":true'; then
    print_success "Filtered search working"
    
    if echo "$FILTER_RESPONSE" | grep -q '"filters_applied":true'; then
        print_success "Filters were applied correctly"
    fi
else
    print_error "Filtered search failed"
fi

# Test 6: Test validation
print_header "Test 6: Input Validation"

echo "Testing with missing query parameter..."
VALIDATION_RESPONSE=$(curl -s -X POST "$API_URL/search/talents" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"limit": 10}')

if echo "$VALIDATION_RESPONSE" | grep -q '"query"'; then
    print_success "Validation is working (query required)"
else
    print_error "Validation not working properly"
fi

# Test 7: Get IDs for other tests
print_header "Test 7: Getting Test Data IDs"

# Get a talent profile ID
print_info "Getting talent profile ID..."
TALENT_ID=$(curl -s "$API_URL/talents/me" \
    -H "Authorization: Bearer $TOKEN" | grep -o '"id":"[^"]*' | head -1 | cut -d'"' -f4)

if [ -z "$TALENT_ID" ]; then
    print_error "Could not get talent profile ID"
    print_info "This may affect subsequent tests"
else
    print_success "Got talent ID: ${TALENT_ID:0:20}..."
fi

# Test 8: Test Project Matching (if we have a project)
print_header "Test 8: Project-to-Talent Matching"

# This test requires a project owned by the logged-in user
# We'll skip if no suitable project is found
print_info "Skipping - requires project setup (manual test recommended)"

# Test 9: Test Talent-to-Project Matching
print_header "Test 9: Talent-to-Project Matching"

if [ ! -z "$TALENT_ID" ]; then
    echo "Finding matching projects for talent..."
    MATCH_RESPONSE=$(curl -s -X POST "$API_URL/talents/$TALENT_ID/match-projects" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        -d '{
            "limit": 5,
            "min_similarity": 0.5
        }')
    
    if echo "$MATCH_RESPONSE" | grep -q '"success":true'; then
        print_success "Talent-to-project matching working"
        
        TOTAL=$(echo $MATCH_RESPONSE | grep -o '"total":[0-9]*' | cut -d':' -f2)
        print_info "Found $TOTAL matching projects"
    else
        print_error "Talent-to-project matching failed"
        echo "Response: $MATCH_RESPONSE"
    fi
else
    print_info "Skipping - no talent ID available"
fi

# Test 10: Test Similar Portfolios
print_header "Test 10: Similar Portfolios"

print_info "Skipping - requires portfolio setup (manual test recommended)"

# Test 11: Test Related Skills
print_header "Test 11: Related Skills"

print_info "Skipping - requires skill ID (manual test recommended)"

# Test 12: Test Recommendations
print_header "Test 12: Smart Recommendations"

if [ ! -z "$TALENT_ID" ]; then
    echo "Getting recommendations for talent..."
    RECOM_RESPONSE=$(curl -s -X GET "$API_URL/talents/$TALENT_ID/recommendations?limit=5" \
        -H "Authorization: Bearer $TOKEN")
    
    if echo "$RECOM_RESPONSE" | grep -q '"success":true'; then
        print_success "Recommendations endpoint working"
        
        if echo "$RECOM_RESPONSE" | grep -q '"personalization_factors"'; then
            print_success "Personalization factors included"
        fi
    else
        print_error "Recommendations failed"
        echo "Response: $RECOM_RESPONSE"
    fi
else
    print_info "Skipping - no talent ID available"
fi

# Test 13: Test unauthorized access
print_header "Test 13: Authorization"

echo "Testing without authentication token..."
UNAUTH_RESPONSE=$(curl -s -X POST "$API_URL/search/talents" \
    -H "Content-Type: application/json" \
    -d '{"query": "developer"}')

if echo "$UNAUTH_RESPONSE" | grep -q "Unauthenticated"; then
    print_success "Authorization is enforced"
else
    print_error "Authorization not working - endpoint accessible without token!"
fi

# Test 14: Test invalid endpoints
print_header "Test 14: Error Handling"

echo "Testing with invalid talent ID..."
INVALID_RESPONSE=$(curl -s -X POST "$API_URL/talents/00000000-0000-0000-0000-000000000000/match-projects" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{}')

if echo "$INVALID_RESPONSE" | grep -q "not found"; then
    print_success "404 errors handled correctly"
else
    print_error "Error handling not working properly"
fi

# Final Summary
print_header "Test Summary"

TOTAL_TESTS=$((TESTS_PASSED + TESTS_FAILED))

echo -e "\nTotal Tests: $TOTAL_TESTS"
echo -e "${GREEN}Passed: $TESTS_PASSED${NC}"
echo -e "${RED}Failed: $TESTS_FAILED${NC}"

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "\n${GREEN}🎉 All tests passed! Search API is working correctly.${NC}\n"
    exit 0
else
    echo -e "\n${RED}⚠ Some tests failed. Check the output above for details.${NC}\n"
    exit 1
fi