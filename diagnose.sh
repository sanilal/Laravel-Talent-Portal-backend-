#!/bin/bash

echo "🔍 Laravel Sanctum Diagnostic Tool"
echo "===================================="
echo ""

API_URL="http://localhost:8000/api/v1"
EMAIL="design@iconceptme.com"
PASSWORD="Iconcept@987"  # ⚠️ UPDATE THIS!

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}Step 1: Check Configuration${NC}"
echo "----------------------------------------"

# Auto-detect Laravel directory
if [ -f "config/auth.php" ]; then
    CONFIG_PATH="config/auth.php"
    BOOTSTRAP_PATH="bootstrap/app.php"
    LOG_PATH="storage/logs/laravel.log"
elif [ -f "backend/config/auth.php" ]; then
    CONFIG_PATH="backend/config/auth.php"
    BOOTSTRAP_PATH="backend/bootstrap/app.php"
    LOG_PATH="backend/storage/logs/laravel.log"
else
    echo -e "${RED}❌ Cannot find Laravel config files${NC}"
    echo "Run this script from either the backend folder or its parent directory"
    exit 1
fi

echo "Using config path: $CONFIG_PATH"
echo ""

# Check if Sanctum guard exists
if grep -q "'sanctum'" "$CONFIG_PATH"; then
    echo -e "${GREEN}✅ Sanctum guard found in config/auth.php${NC}"
else
    echo -e "${RED}❌ Sanctum guard NOT FOUND in config/auth.php${NC}"
    echo "   Add this to your guards array:"
    echo "   'sanctum' => ['driver' => 'sanctum', 'provider' => 'users'],"
    exit 1
fi

# Check if EnsureFrontendRequestsAreStateful is enabled
if grep -q "EnsureFrontendRequestsAreStateful::class" "$BOOTSTRAP_PATH" && ! grep -q "// EnsureFrontendRequestsAreStateful::class" "$BOOTSTRAP_PATH"; then
    echo -e "${GREEN}✅ EnsureFrontendRequestsAreStateful is enabled${NC}"
else
    echo -e "${RED}❌ EnsureFrontendRequestsAreStateful is NOT enabled${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}Step 2: Login and Get Token${NC}"
echo "----------------------------------------"

LOGIN_RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X POST "$API_URL/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

HTTP_STATUS=$(echo "$LOGIN_RESPONSE" | grep "HTTP_STATUS:" | cut -d: -f2)
RESPONSE_BODY=$(echo "$LOGIN_RESPONSE" | sed '/HTTP_STATUS:/d')

echo "Status Code: $HTTP_STATUS"
echo "Response Body (first 500 chars):"
echo "$RESPONSE_BODY" | head -c 500
echo ""

if [ "$HTTP_STATUS" != "200" ]; then
    echo -e "${RED}❌ Login failed with status $HTTP_STATUS${NC}"
    exit 1
fi

TOKEN=$(echo "$RESPONSE_BODY" | grep -o '"token":"[^"]*' | sed 's/"token":"//')

if [ -z "$TOKEN" ]; then
    echo -e "${RED}❌ Could not extract token from response${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Login successful!${NC}"
echo "Token: ${TOKEN:0:30}..."
echo ""

echo -e "${BLUE}Step 3: Test Protected Endpoints${NC}"
echo "----------------------------------------"

# Function to test endpoint with full details
test_detailed() {
    local name=$1
    local endpoint=$2
    
    echo ""
    echo "Testing: $name"
    echo "URL: $API_URL$endpoint"
    echo "---"
    
    RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X GET "$API_URL$endpoint" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Accept: application/json")
    
    STATUS=$(echo "$RESPONSE" | grep "HTTP_STATUS:" | cut -d: -f2)
    BODY=$(echo "$RESPONSE" | sed '/HTTP_STATUS:/d')
    
    echo "Status: $STATUS"
    echo "Response (first 300 chars):"
    echo "$BODY" | head -c 300
    echo ""
    
    if [ "$STATUS" == "200" ]; then
        echo -e "${GREEN}✅ PASS${NC}"
    else
        echo -e "${RED}❌ FAIL - Expected 200, got $STATUS${NC}"
    fi
    echo "========================================"
}

# Test each endpoint
test_detailed "Get Current User (/auth/me)" "/auth/me"
test_detailed "Talent Dashboard" "/talent/dashboard"
test_detailed "Get Projects" "/projects?limit=5"
test_detailed "Talent Applications" "/talent/applications?limit=5"

echo ""
echo -e "${BLUE}Step 4: Test Without Token (Should Return 401)${NC}"
echo "----------------------------------------"

RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" -X GET "$API_URL/talent/dashboard" \
  -H "Accept: application/json")

STATUS=$(echo "$RESPONSE" | grep "HTTP_STATUS:" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_STATUS:/d')

echo "Status: $STATUS"
echo "Response: $BODY" | head -c 200
echo ""

if [ "$STATUS" == "401" ]; then
    echo -e "${GREEN}✅ Correctly returns 401 without token${NC}"
else
    echo -e "${YELLOW}⚠️ Expected 401, got $STATUS${NC}"
fi

echo ""
echo -e "${BLUE}Step 5: Check Latest Laravel Log${NC}"
echo "----------------------------------------"
echo "Last 10 lines from laravel.log:"
tail -10 "$LOG_PATH" 2>/dev/null || echo "No log file found"

echo ""
echo "===================================="
echo "Diagnostic Complete!"
echo "===================================="