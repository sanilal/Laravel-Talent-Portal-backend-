#!/bin/bash

# Backend API Testing Script
# This script tests your Laravel API endpoints

echo "=================================="
echo "BACKEND API TEST SUITE"
echo "=================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Base URL - change this if your API runs on a different port
BASE_URL="http://localhost:8000/api/v1"

echo -e "${YELLOW}Testing Base URL: $BASE_URL${NC}"
echo ""

# Test 1: Health Check
echo "1. Testing Health Endpoint..."
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/health")
if [ $response -eq 200 ]; then
    echo -e "${GREEN}✅ Health check passed${NC}"
else
    echo -e "${RED}❌ Health check failed (Status: $response)${NC}"
fi
echo ""

# Test 2: Get Public Projects
echo "2. Testing Public Projects..."
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/public/projects")
if [ $response -eq 200 ]; then
    echo -e "${GREEN}✅ Public projects endpoint works${NC}"
else
    echo -e "${RED}❌ Public projects failed (Status: $response)${NC}"
fi
echo ""

# Test 3: Get Public Talents
echo "3. Testing Public Talents..."
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/public/talents")
if [ $response -eq 200 ]; then
    echo -e "${GREEN}✅ Public talents endpoint works${NC}"
else
    echo -e "${RED}❌ Public talents failed (Status: $response)${NC}"
fi
echo ""

# Test 4: Get Categories
echo "4. Testing Categories..."
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/public/categories")
if [ $response -eq 200 ]; then
    echo -e "${GREEN}✅ Categories endpoint works${NC}"
else
    echo -e "${RED}❌ Categories failed (Status: $response)${NC}"
fi
echo ""

# Test 5: Get Skills
echo "5. Testing Skills..."
response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/public/skills")
if [ $response -eq 200 ]; then
    echo -e "${GREEN}✅ Skills endpoint works${NC}"
else
    echo -e "${RED}❌ Skills failed (Status: $response)${NC}"
fi
echo ""

# Test 6: User Registration
echo "6. Testing User Registration..."
response=$(curl -s -X POST "$BASE_URL/auth/register" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "name": "Test User",
        "email": "testuser'$(date +%s)'@example.com",
        "password": "password123",
        "password_confirmation": "password123",
        "role": "talent"
    }')

if echo "$response" | grep -q "token"; then
    echo -e "${GREEN}✅ User registration works${NC}"
    # Extract token for further tests
    TOKEN=$(echo $response | grep -o '"token":"[^"]*' | cut -d'"' -f4)
    echo "Token: ${TOKEN:0:20}..."
else
    echo -e "${RED}❌ User registration failed${NC}"
    echo "Response: $response"
fi
echo ""

# Test 7: User Login
echo "7. Testing User Login..."
echo "Creating test user first..."
test_email="testlogin$(date +%s)@example.com"
register_response=$(curl -s -X POST "$BASE_URL/auth/register" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "name": "Login Test User",
        "email": "'$test_email'",
        "password": "password123",
        "password_confirmation": "password123",
        "role": "talent"
    }')

echo "Now attempting login..."
login_response=$(curl -s -X POST "$BASE_URL/auth/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "email": "'$test_email'",
        "password": "password123"
    }')

if echo "$login_response" | grep -q "token"; then
    echo -e "${GREEN}✅ User login works${NC}"
    LOGIN_TOKEN=$(echo $login_response | grep -o '"token":"[^"]*' | cut -d'"' -f4)
else
    echo -e "${RED}❌ User login failed${NC}"
    echo "Response: $login_response"
fi
echo ""

# Test 8: Get Authenticated User (if we have token)
if [ ! -z "$LOGIN_TOKEN" ]; then
    echo "8. Testing Authenticated User Endpoint..."
    me_response=$(curl -s "$BASE_URL/auth/me" \
        -H "Authorization: Bearer $LOGIN_TOKEN" \
        -H "Accept: application/json")
    
    if echo "$me_response" | grep -q "email"; then
        echo -e "${GREEN}✅ Get authenticated user works${NC}"
    else
        echo -e "${RED}❌ Get authenticated user failed${NC}"
        echo "Response: $me_response"
    fi
    echo ""
fi

# Test 9: Embedding Service (if running)
echo "9. Testing Embedding Service..."
embed_response=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:5000/health" 2>/dev/null)
if [ $embed_response -eq 200 ]; then
    echo -e "${GREEN}✅ Embedding service is running${NC}"
else
    echo -e "${YELLOW}⚠️  Embedding service not running (Status: $embed_response)${NC}"
    echo "   Start it with: cd embedding-service && python app.py"
fi
echo ""

echo "=================================="
echo "TEST SUMMARY"
echo "=================================="
echo ""
echo "If all tests passed, your backend is working correctly!"
echo "If some failed, check the error messages above."
echo ""
echo "Next steps:"
echo "1. Make sure your Laravel server is running: php artisan serve"
echo "2. Make sure database is migrated: php artisan migrate"
echo "3. Seed initial data if needed: php artisan db:seed"
echo ""
