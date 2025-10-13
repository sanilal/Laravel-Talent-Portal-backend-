#!/bin/bash

# Authentication Testing Script
# Run this after applying the fixes to verify everything works

echo "🧪 Laravel Sanctum Authentication Test Suite"
echo "=============================================="
echo ""

# Configuration
API_URL="http://localhost:8000/api/v1"
EMAIL="design@iconceptme.com"
PASSWORD="Iconcept@987"  # ⚠️ UPDATE THIS!

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counter
PASSED=0
FAILED=0

# Function to test an endpoint
test_endpoint() {
    local name=$1
    local method=$2
    local endpoint=$3
    local headers=$4
    local data=$5
    local expected_status=$6
    
    echo "📝 Test: $name"
    
    if [ -z "$data" ]; then
        response=$(curl -s -w "\n%{http_code}" -X $method "$API_URL$endpoint" $headers)
    else
        response=$(curl -s -w "\n%{http_code}" -X $method "$API_URL$endpoint" $headers -d "$data")
    fi
    
    # Split response and status code
    status=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$status" == "$expected_status" ]; then
        echo -e "${GREEN}✅ PASS${NC} - Status: $status"
        ((PASSED++))
    else
        echo -e "${RED}❌ FAIL${NC} - Expected: $expected_status, Got: $status"
        echo "Response: $body"
        ((FAILED++))
    fi
    echo ""
}

# ============================================
# Test 1: Login and get token
# ============================================
echo "🔐 Step 1: Testing Login..."
echo "----------------------------"

LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

echo "Response: $LOGIN_RESPONSE"

# Extract token
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | sed 's/"token":"//')

if [ -z "$TOKEN" ]; then
    echo -e "${RED}❌ FAILED: Could not extract token from login response${NC}"
    echo "Make sure your credentials are correct and the API is running."
    exit 1
else
    echo -e "${GREEN}✅ Login successful!${NC}"
    echo "Token: ${TOKEN:0:20}..."
    echo ""
fi

# ============================================
# Test 2: Verify token with /auth/me
# ============================================
test_endpoint \
    "Get Current User (/auth/me)" \
    "GET" \
    "/auth/me" \
    "-H 'Authorization: Bearer $TOKEN' -H 'Accept: application/json'" \
    "" \
    "200"

# ============================================
# Test 3: Test Sanctum guard configuration
# ============================================
echo "🔒 Step 2: Testing Sanctum Protected Endpoints..."
echo "---------------------------------------------------"

test_endpoint \
    "Talent Dashboard" \
    "GET" \
    "/talent/dashboard" \
    "-H 'Authorization: Bearer $TOKEN' -H 'Accept: application/json'" \
    "" \
    "200"

test_endpoint \
    "Get Projects" \
    "GET" \
    "/projects?limit=5" \
    "-H 'Authorization: Bearer $TOKEN' -H 'Accept: application/json'" \
    "" \
    "200"

test_endpoint \
    "Talent Applications" \
    "GET" \
    "/talent/applications?limit=5" \
    "-H 'Authorization: Bearer $TOKEN' -H 'Accept: application/json'" \
    "" \
    "200"

# ============================================
# Test 4: Test without token (should fail)
# ============================================
echo "🚫 Step 3: Testing Unauthorized Access (Should Fail)..."
echo "--------------------------------------------------------"

test_endpoint \
    "Dashboard Without Token (Should Return 401)" \
    "GET" \
    "/talent/dashboard" \
    "-H 'Accept: application/json'" \
    "" \
    "401"

# ============================================
# Test 5: Test with invalid token (should fail)
# ============================================
test_endpoint \
    "Dashboard With Invalid Token (Should Return 401)" \
    "GET" \
    "/talent/dashboard" \
    "-H 'Authorization: Bearer invalid_token_12345' -H 'Accept: application/json'" \
    "" \
    "401"

# ============================================
# Summary
# ============================================
echo "============================================"
echo "📊 Test Results Summary"
echo "============================================"
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}🎉 All tests passed! Your authentication is working correctly.${NC}"
    echo ""
    echo "✅ Next steps:"
    echo "   1. Clear frontend localStorage"
    echo "   2. Hard refresh your browser (Ctrl+Shift+R)"
    echo "   3. Login again and check DevTools console"
    echo "   4. You should see successful API calls without 401 errors"
    exit 0
else
    echo -e "${RED}⚠️  Some tests failed. Check the output above for details.${NC}"
    echo ""
    echo "🔍 Troubleshooting:"
    echo "   1. Make sure you updated the PASSWORD variable in this script"
    echo "   2. Verify config/auth.php has the Sanctum guard"
    echo "   3. Check that bootstrap/app.php has EnsureFrontendRequestsAreStateful uncommented"
    echo "   4. Run: php artisan config:clear && php artisan cache:clear"
    echo "   5. Check backend/storage/logs/laravel.log for errors"
    exit 1
fi