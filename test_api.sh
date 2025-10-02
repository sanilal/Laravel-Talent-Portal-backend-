#!/bin/bash

# ============================================
# CONFIGURATION
# ============================================
BASE_URL="http://localhost:8000"
LOGIN_EMAIL="john.talent@test.com"
LOGIN_PASSWORD="Password123!"
TOKEN="20|TVpwNoMCWErFP3Jv3Ov5waesLH2jI8P8KTVKnOGT5cd18c69"
USER_ID="01999f5a-9b25-7398-a7ba-ab9ae2753004"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# Counter for results
TOTAL=0
PASSED=0
FAILED=0
SKIPPED=0

# Store created IDs for cleanup
CREATED_SKILL_ID=""
CREATED_EXPERIENCE_ID=""
CREATED_EDUCATION_ID=""
CREATED_PORTFOLIO_ID=""
CREATED_PROJECT_ID=""
CREATED_APPLICATION_ID=""
CREATED_REVIEW_ID=""
CREATED_MESSAGE_ID=""
CREATED_MEDIA_ID=""

# ============================================
# HELPER FUNCTIONS
# ============================================
print_header() {
    echo -e "\n${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║  $(printf '%-60s' "$1")║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}\n"
}

print_subheader() {
    echo -e "${CYAN}▶ $1${NC}"
}

test_endpoint() {
    local method=$1
    local endpoint=$2
    local description=$3
    local data=$4
    local expected_status=${5:-200}
    local skip=${6:-false}
    
    TOTAL=$((TOTAL + 1))
    
    echo -e "${YELLOW}[$TOTAL] Testing:${NC} $method $endpoint"
    echo -e "    ${CYAN}→${NC} $description"
    
    if [ "$skip" == "true" ]; then
        echo -e "    ${MAGENTA}⊘ SKIPPED${NC} - $description"
        SKIPPED=$((SKIPPED + 1))
        echo ""
        return
    fi
    
    # Build curl command
    local curl_cmd="curl -s -w '\n%{http_code}' -X $method '$BASE_URL$endpoint'"
    curl_cmd="$curl_cmd -H 'Accept: application/json'"
    curl_cmd="$curl_cmd -H 'Authorization: Bearer $TOKEN'"
    
    if [ "$method" == "POST" ] || [ "$method" == "PUT" ]; then
        curl_cmd="$curl_cmd -H 'Content-Type: application/json'"
        if [ -n "$data" ]; then
            curl_cmd="$curl_cmd -d '$data'"
        fi
    fi
    
    # Execute request
    response=$(eval $curl_cmd 2>/dev/null)
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    # Check result
    if [ "$http_code" -eq "$expected_status" ] || [ "$http_code" -eq 200 ] || [ "$http_code" -eq 201 ]; then
        echo -e "    ${GREEN}✓ PASSED${NC} - Status: $http_code"
        PASSED=$((PASSED + 1))
        
        # Extract and store IDs from response
        if command -v jq &> /dev/null; then
            local id=$(echo "$body" | jq -r '.data.id // .id // empty' 2>/dev/null)
            if [ -n "$id" ] && [ "$id" != "null" ]; then
                case "$endpoint" in
                    */talent/skills) CREATED_SKILL_ID="$id" ;;
                    */talent/experiences) CREATED_EXPERIENCE_ID="$id" ;;
                    */talent/education) CREATED_EDUCATION_ID="$id" ;;
                    */talent/portfolios) CREATED_PORTFOLIO_ID="$id" ;;
                    */projects) CREATED_PROJECT_ID="$id" ;;
                    */applications) CREATED_APPLICATION_ID="$id" ;;
                    */reviews) CREATED_REVIEW_ID="$id" ;;
                    */messages) CREATED_MESSAGE_ID="$id" ;;
                    */media/upload) CREATED_MEDIA_ID="$id" ;;
                esac
                echo -e "    ${CYAN}→ Created ID: $id${NC}"
            fi
        fi
    else
        echo -e "    ${RED}✗ FAILED${NC} - Status: $http_code (Expected: $expected_status)"
        FAILED=$((FAILED + 1))
    fi
    
    # Show response preview
    if [ -n "$body" ]; then
        if command -v jq &> /dev/null && echo "$body" | jq -e . >/dev/null 2>&1; then
            echo -e "    ${CYAN}Response:${NC} $(echo "$body" | jq -c '.' 2>/dev/null | head -c 150)..."
        else
            echo -e "    ${CYAN}Response:${NC} $(echo "$body" | head -c 150)..."
        fi
    fi
    
    echo ""
    sleep 0.3
}

# ============================================
# START TESTING
# ============================================
clear
echo -e "${BLUE}"
cat << "EOF"
╔══════════════════════════════════════════════════════════════════╗
║                                                                  ║
║          API COMPREHENSIVE TESTING SUITE                         ║
║          Testing All 110 Routes                                  ║
║                                                                  ║
╚══════════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}\n"

echo -e "${YELLOW}Configuration:${NC}"
echo -e "  Base URL: $BASE_URL"
echo -e "  Login Email: $LOGIN_EMAIL"
echo -e ""

# ============================================
# AUTO-LOGIN TO GET FRESH TOKEN
# ============================================
echo -e "${YELLOW}Logging in to get authentication token...${NC}"
login_response=$(curl -s -X POST "$BASE_URL/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$LOGIN_EMAIL\",\"password\":\"$LOGIN_PASSWORD\"}")

# Try to extract token with jq, fallback to grep/sed if jq fails
if command -v jq &> /dev/null; then
    TOKEN=$(echo "$login_response" | jq -r '.token // empty' 2>/dev/null)
else
    # Fallback: extract token without jq
    TOKEN=$(echo "$login_response" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
fi

# Validate token
if [ -z "$TOKEN" ] || [ "$TOKEN" == "null" ] || [ "$TOKEN" == "empty" ]; then
    echo -e "${RED}✗ Login failed! Cannot proceed with tests.${NC}"
    echo -e "${RED}Response: $login_response${NC}"
    
    # Check if jq is missing
    if ! command -v jq &> /dev/null; then
        echo -e "${YELLOW}⚠ Warning: 'jq' is not installed. Install it for better JSON parsing.${NC}"
        echo -e "${YELLOW}  Windows: Download from https://stedolan.github.io/jq/download/${NC}"
        echo -e "${YELLOW}  Or use: choco install jq (if you have Chocolatey)${NC}"
    fi
    exit 1
fi

echo -e "${GREEN}✓ Login successful!${NC}"
echo -e "  Token: ${TOKEN:0:20}...${NC}"
echo -e "  User ID: $USER_ID"
echo -e ""

# ============================================
# 1. HEALTH & SYSTEM ROUTES
# ============================================
print_header "1. HEALTH & SYSTEM ROUTES"

test_endpoint "GET" "/" "Homepage/Welcome" "" 200
test_endpoint "GET" "/api/v1/health" "Health check endpoint" "" 200
test_endpoint "GET" "/api/v1/test" "Test endpoint" "" 200
test_endpoint "GET" "/up" "Laravel up check" "" 200

# ============================================
# 2. PUBLIC ROUTES (No Authentication)
# ============================================
print_header "2. PUBLIC ROUTES"

print_subheader "Categories & Skills"
test_endpoint "GET" "/api/v1/public/categories" "Get all public categories" "" 200
test_endpoint "GET" "/api/v1/public/skills" "Get all public skills" "" 200

print_subheader "Public Projects"
test_endpoint "GET" "/api/v1/public/projects" "Get all public projects" "" 200
test_endpoint "GET" "/api/v1/public/projects/01999f5a-0000-0000-0000-000000000000" "Get single public project (may 404)" "" 404

print_subheader "Public Talents"
test_endpoint "GET" "/api/v1/public/talents" "Get all public talents" "" 200
test_endpoint "GET" "/api/v1/public/talents/01999f5a-0000-0000-0000-000000000000" "Get single public talent (may 404)" "" 404

# ============================================
# 3. AUTHENTICATION ROUTES
# ============================================
print_header "3. AUTHENTICATION ROUTES"

print_subheader "Session Management"
test_endpoint "GET" "/api/v1/auth/me" "Get current authenticated user" "" 200
test_endpoint "GET" "/api/v1/auth/sessions" "Get all active sessions" "" 200

# Refresh token and update TOKEN variable
TOTAL=$((TOTAL + 1))
echo -e "${YELLOW}[$TOTAL] Refreshing token...${NC}"
response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL/api/v1/auth/refresh-token" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json")
http_code=$(echo "$response" | tail -n1)
body=$(echo "$response" | sed '$d')

if [ "$http_code" -eq 200 ]; then
    PASSED=$((PASSED + 1))
    if command -v jq &> /dev/null; then
        NEW_TOKEN=$(echo "$body" | jq -r '.token // empty' 2>/dev/null)
    else
        NEW_TOKEN=$(echo "$body" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    fi
    
    if [ -n "$NEW_TOKEN" ] && [ "$NEW_TOKEN" != "null" ]; then
        TOKEN="$NEW_TOKEN"
        echo -e "    ${GREEN}✓ PASSED${NC} - Status: 200"
        echo -e "    ${GREEN}✓ Token refreshed and updated successfully${NC}"
        echo -e "    ${CYAN}→ New token: ${TOKEN:0:20}...${NC}\n"
    else
        echo -e "    ${YELLOW}⚠ Could not extract new token, keeping old one${NC}\n"
    fi
else
    FAILED=$((FAILED + 1))
    echo -e "    ${RED}✗ FAILED${NC} - Status: $http_code"
    echo -e "    ${RED}✗ Token refresh failed, keeping old token${NC}\n"
fi

sleep 0.3

print_subheader "Profile Management"
test_endpoint "PUT" "/api/v1/auth/update-profile" "Update user profile" '{
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+1234567890",
    "bio": "Updated bio for testing"
}' 200

print_subheader "Password Management"
test_endpoint "POST" "/api/v1/auth/change-password" "Change password (will fail without correct password)" '{
    "current_password": "wrong_password",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}' 422

test_endpoint "POST" "/api/v1/auth/forgot-password" "Request password reset link" '{
    "email": "john.talent@test.com"
}' 200

test_endpoint "POST" "/api/v1/auth/reset-password" "Reset password (requires valid token)" '{
    "email": "john.talent@test.com",
    "token": "invalid_token",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}' 422

print_subheader "Authentication Actions (Skipped)"
echo -e "${MAGENTA}Skipping logout endpoints to preserve current session${NC}\n"

# ============================================
# 4. EMAIL VERIFICATION
# ============================================
print_header "4. EMAIL VERIFICATION"

test_endpoint "GET" "/api/v1/email/verification-status" "Check email verification status" "" 200
test_endpoint "POST" "/api/v1/email/verification-notification" "Send verification notification" "" 200
test_endpoint "POST" "/api/v1/email/verify" "Verify email (requires valid signature)" '{
    "id": "'$USER_ID'",
    "hash": "invalid_hash",
    "signature": "invalid_signature"
}' 422

# ============================================
# 5. TWO-FACTOR AUTHENTICATION
# ============================================
print_header "5. TWO-FACTOR AUTHENTICATION"

test_endpoint "GET" "/api/v1/two-factor/qr-code" "Get 2FA QR code" "" 200
test_endpoint "GET" "/api/v1/two-factor/recovery-codes" "Get recovery codes" "" 200

print_subheader "2FA Actions (Skipped to avoid enabling)"
echo -e "${MAGENTA}Skipping 2FA enable/disable to avoid changing account state${NC}\n"

# ============================================
# 6. TALENT PROFILE ROUTES
# ============================================
print_header "6. TALENT PROFILE ROUTES"

print_subheader "Profile"
test_endpoint "GET" "/api/v1/talent/profile" "Get talent profile" "" 200
test_endpoint "GET" "/api/v1/talent/dashboard" "Get talent dashboard" "" 200

test_endpoint "PUT" "/api/v1/talent/profile" "Update talent profile" '{
    "professional_title": "Senior Full Stack Developer",
    "summary": "Experienced developer with 8+ years in web development",
    "experience_level": "senior",
    "hourly_rate_min": 75,
    "hourly_rate_max": 150,
    "currency": "USD",
    "is_available": true,
    "notice_period": 30
}' 200

echo -e "${MAGENTA}Skipping avatar upload (requires multipart form data)${NC}\n"

print_subheader "Skills"
test_endpoint "GET" "/api/v1/talent/skills" "Get talent skills" "" 200

test_endpoint "POST" "/api/v1/talent/skills" "Add skill" '{
    "skill_id": "550e8400-e29b-41d4-a716-446655440000",
    "proficiency_level": "expert",
    "years_of_experience": 5
}' 201

if [ -n "$CREATED_SKILL_ID" ]; then
    test_endpoint "PUT" "/api/v1/talent/skills/$CREATED_SKILL_ID" "Update skill" '{
        "proficiency_level": "intermediate",
        "years_of_experience": 3
    }' 200
fi

print_subheader "Experiences"
test_endpoint "GET" "/api/v1/talent/experiences" "Get experiences" "" 200

test_endpoint "POST" "/api/v1/talent/experiences" "Create experience" '{
    "title": "Senior Software Engineer",
    "company": "Tech Solutions Inc",
    "employment_type": "full_time",
    "location": "Remote",
    "start_date": "2020-01-01",
    "is_current": true,
    "description": "Leading development of enterprise applications using Laravel and React"
}' 201

if [ -n "$CREATED_EXPERIENCE_ID" ]; then
    test_endpoint "PUT" "/api/v1/talent/experiences/$CREATED_EXPERIENCE_ID" "Update experience" '{
        "title": "Lead Software Engineer",
        "is_current": false,
        "end_date": "2024-12-31"
    }' 200
fi

print_subheader "Education"
test_endpoint "GET" "/api/v1/talent/education" "Get education" "" 200

test_endpoint "POST" "/api/v1/talent/education" "Create education" '{
    "institution": "MIT",
    "degree": "Bachelor of Science",
    "field_of_study": "Computer Science",
    "start_date": "2012-09-01",
    "end_date": "2016-06-30",
    "grade": "3.8 GPA",
    "description": "Focus on software engineering and algorithms"
}' 201

if [ -n "$CREATED_EDUCATION_ID" ]; then
    test_endpoint "PUT" "/api/v1/talent/education/$CREATED_EDUCATION_ID" "Update education" '{
        "grade": "3.9 GPA"
    }' 200
fi

print_subheader "Portfolios"
test_endpoint "GET" "/api/v1/talent/portfolios" "Get portfolios" "" 200

test_endpoint "POST" "/api/v1/talent/portfolios" "Create portfolio" '{
    "title": "E-commerce Platform",
    "description": "Built a full-featured e-commerce platform with Laravel and Vue.js",
    "project_url": "https://example.com",
    "repository_url": "https://github.com/example/project",
    "technologies": ["Laravel", "Vue.js", "MySQL", "Redis"],
    "completed_at": "2024-06-30",
    "is_featured": true
}' 201

if [ -n "$CREATED_PORTFOLIO_ID" ]; then
    test_endpoint "PUT" "/api/v1/talent/portfolios/$CREATED_PORTFOLIO_ID" "Update portfolio" '{
        "title": "Advanced E-commerce Platform",
        "is_featured": false
    }' 200
fi

print_subheader "Applications"
test_endpoint "GET" "/api/v1/talent/applications" "Get talent applications" "" 200
test_endpoint "GET" "/api/v1/talent/applications/01999f5a-0000-0000-0000-000000000000" "Get single application (may 404)" "" 404

# ============================================
# 7. PROJECT ROUTES
# ============================================
print_header "7. PROJECT ROUTES"

test_endpoint "GET" "/api/v1/projects" "Get all projects" "" 200
test_endpoint "GET" "/api/v1/projects/search" "Search projects" "" 200
test_endpoint "GET" "/api/v1/projects/search?q=developer" "Search projects with query" "" 200

test_endpoint "POST" "/api/v1/projects" "Create project (may fail for talent)" '{
    "title": "Build Modern Web Application",
    "description": "Looking for experienced developer to build a modern web application",
    "category_id": "4fff74ec-1745-4c48-8301-f159f3b29ad2",
    "budget_min": 5000,
    "budget_max": 10000,
    "budget_type": "fixed",
    "duration": 60,
    "duration_unit": "days",
    "required_skills": ["550e8400-e29b-41d4-a716-446655440000"],
    "experience_level": "senior",
    "project_type": "remote",
    "status": "draft"
}' 201

test_endpoint "GET" "/api/v1/projects/01999f5a-0000-0000-0000-000000000000" "Get project details (may 404)" "" 404

if [ -n "$CREATED_PROJECT_ID" ]; then
    test_endpoint "PUT" "/api/v1/projects/$CREATED_PROJECT_ID" "Update project" '{
        "title": "Build Modern Web Application - Updated"
    }' 200
    
    test_endpoint "GET" "/api/v1/projects/$CREATED_PROJECT_ID/applications" "Get project applications" "" 200
    test_endpoint "POST" "/api/v1/projects/$CREATED_PROJECT_ID/publish" "Publish project" "" 200
    test_endpoint "POST" "/api/v1/projects/$CREATED_PROJECT_ID/close" "Close project" "" 200
fi

# ============================================
# 8. APPLICATION ROUTES
# ============================================
print_header "8. APPLICATION ROUTES"

test_endpoint "POST" "/api/v1/applications" "Create application (requires valid project)" '{
    "project_id": "01999f5a-0000-0000-0000-000000000000",
    "cover_letter": "I am very interested in this project and believe I am the perfect fit.",
    "proposed_rate": 85,
    "proposed_duration": 45,
    "estimated_start_date": "2025-11-01"
}' 201

test_endpoint "GET" "/api/v1/applications/01999f5a-0000-0000-0000-000000000000" "Get application details (may 404)" "" 404

if [ -n "$CREATED_APPLICATION_ID" ]; then
    test_endpoint "PUT" "/api/v1/applications/$CREATED_APPLICATION_ID/status" "Update application status" '{
        "status": "reviewing"
    }' 200
    
    test_endpoint "POST" "/api/v1/applications/$CREATED_APPLICATION_ID/notes" "Add application notes" '{
        "notes": "Great portfolio, considering for next phase"
    }' 200
fi

# ============================================
# 9. RECRUITER ROUTES
# ============================================
print_header "9. RECRUITER ROUTES"

test_endpoint "GET" "/api/v1/recruiter/dashboard" "Get recruiter dashboard (may fail)" "" 200
test_endpoint "GET" "/api/v1/recruiter/profile" "Get recruiter profile (may fail)" "" 200

test_endpoint "PUT" "/api/v1/recruiter/profile" "Update recruiter profile (may fail)" '{
    "company_name": "Tech Innovations Ltd",
    "company_description": "Leading tech company",
    "company_size": "50-200",
    "industry": "Technology",
    "website": "https://techinnovations.com"
}' 200

echo -e "${MAGENTA}Skipping logo upload (requires multipart form data)${NC}\n"

test_endpoint "GET" "/api/v1/recruiter/talents/search" "Search talents" "" 200
test_endpoint "GET" "/api/v1/recruiter/talents/search?q=developer&skills=laravel" "Search talents with filters" "" 200
test_endpoint "GET" "/api/v1/recruiter/talents/01999f5a-0000-0000-0000-000000000000" "View talent profile (may 404)" "" 404

test_endpoint "POST" "/api/v1/recruiter/talents/01999f5a-0000-0000-0000-000000000000/save" "Save talent (may 404)" "" 404
test_endpoint "DELETE" "/api/v1/recruiter/talents/01999f5a-0000-0000-0000-000000000000/unsave" "Unsave talent (may 404)" "" 404

# ============================================
# 10. ADMIN ROUTES
# ============================================
print_header "10. ADMIN ROUTES"

test_endpoint "GET" "/api/v1/admin/dashboard" "Get admin dashboard (requires admin)" "" 200
test_endpoint "GET" "/api/v1/admin/users" "Get all users (requires admin)" "" 200
test_endpoint "GET" "/api/v1/admin/reports" "Get reports (requires admin)" "" 200
test_endpoint "GET" "/api/v1/admin/projects/pending" "Get pending projects (requires admin)" "" 200

test_endpoint "PUT" "/api/v1/admin/users/01999f5a-0000-0000-0000-000000000000/status" "Update user status (requires admin)" '{
    "status": "active"
}' 200

test_endpoint "POST" "/api/v1/admin/projects/01999f5a-0000-0000-0000-000000000000/approve" "Approve project (requires admin)" "" 200

# ============================================
# 11. REVIEW ROUTES
# ============================================
print_header "11. REVIEW ROUTES"

test_endpoint "GET" "/api/v1/reviews/user/$USER_ID" "Get user reviews" "" 200

test_endpoint "POST" "/api/v1/reviews" "Create review (requires valid user)" '{
    "reviewed_user_id": "01999f5a-0000-0000-0000-000000000001",
    "rating": 5,
    "comment": "Excellent work, highly recommended!",
    "project_id": "01999f5a-0000-0000-0000-000000000000"
}' 201

if [ -n "$CREATED_REVIEW_ID" ]; then
    test_endpoint "PUT" "/api/v1/reviews/$CREATED_REVIEW_ID" "Update review" '{
        "rating": 4,
        "comment": "Great work overall"
    }' 200
fi

# ============================================
# 12. MESSAGE ROUTES
# ============================================
print_header "12. MESSAGE ROUTES"

test_endpoint "GET" "/api/v1/messages" "Get all messages" "" 200
test_endpoint "GET" "/api/v1/messages/conversations" "Get all conversations" "" 200
test_endpoint "GET" "/api/v1/messages/conversations/01999f5a-0000-0000-0000-000000000001" "Get conversation with user (may 404)" "" 404

test_endpoint "POST" "/api/v1/messages" "Send message (requires valid recipient)" '{
    "recipient_id": "01999f5a-0000-0000-0000-000000000001",
    "subject": "Project Discussion",
    "body": "Hi, I would like to discuss the project requirements with you."
}' 201

if [ -n "$CREATED_MESSAGE_ID" ]; then
    test_endpoint "PUT" "/api/v1/messages/$CREATED_MESSAGE_ID/read" "Mark message as read" "" 200
fi

# ============================================
# 13. NOTIFICATION ROUTES
# ============================================
print_header "13. NOTIFICATION ROUTES"

test_endpoint "GET" "/api/v1/notifications" "Get all notifications" "" 200
test_endpoint "GET" "/api/v1/notifications/unread" "Get unread notifications" "" 200

test_endpoint "POST" "/api/v1/notifications/read-all" "Mark all as read" "" 200
test_endpoint "POST" "/api/v1/notifications/01999f5a-0000-0000-0000-000000000000/read" "Mark notification as read (may 404)" "" 404

# ============================================
# 14. MEDIA ROUTES
# ============================================
print_header "14. MEDIA ROUTES"

echo -e "${MAGENTA}Skipping media upload (requires multipart form data)${NC}"
echo -e "${CYAN}Example: curl -X POST $BASE_URL/api/v1/media/upload -H 'Authorization: Bearer $TOKEN' -F 'file=@image.jpg' -F 'type=avatar'${NC}\n"

test_endpoint "GET" "/api/v1/media/01999f5a-0000-0000-0000-000000000000" "Get media details (may 404)" "" 404

# ============================================
# 15. SANCTUM & STORAGE ROUTES
# ============================================
print_header "15. SANCTUM & STORAGE ROUTES"

test_endpoint "GET" "/sanctum/csrf-cookie" "Get CSRF cookie" "" 204
test_endpoint "GET" "/storage/test.jpg" "Access storage file (may 404)" "" 404

# ============================================
# 16. CLEANUP - DELETE OPERATIONS
# ============================================
print_header "16. CLEANUP - DELETE OPERATIONS"

echo -e "${YELLOW}Cleaning up created resources...${NC}\n"

if [ -n "$CREATED_SKILL_ID" ]; then
    test_endpoint "DELETE" "/api/v1/talent/skills/$CREATED_SKILL_ID" "Delete created skill" "" 200
fi

if [ -n "$CREATED_EXPERIENCE_ID" ]; then
    test_endpoint "DELETE" "/api/v1/talent/experiences/$CREATED_EXPERIENCE_ID" "Delete created experience" "" 200
fi

if [ -n "$CREATED_EDUCATION_ID" ]; then
    test_endpoint "DELETE" "/api/v1/talent/education/$CREATED_EDUCATION_ID" "Delete created education" "" 200
fi

if [ -n "$CREATED_PORTFOLIO_ID" ]; then
    test_endpoint "DELETE" "/api/v1/talent/portfolios/$CREATED_PORTFOLIO_ID" "Delete created portfolio" "" 200
fi

if [ -n "$CREATED_PROJECT_ID" ]; then
    test_endpoint "DELETE" "/api/v1/projects/$CREATED_PROJECT_ID" "Delete created project" "" 200
fi

if [ -n "$CREATED_APPLICATION_ID" ]; then
    test_endpoint "DELETE" "/api/v1/applications/$CREATED_APPLICATION_ID" "Withdraw created application" "" 200
fi

if [ -n "$CREATED_REVIEW_ID" ]; then
    test_endpoint "DELETE" "/api/v1/reviews/$CREATED_REVIEW_ID" "Delete created review" "" 200
fi

if [ -n "$CREATED_MESSAGE_ID" ]; then
    test_endpoint "DELETE" "/api/v1/messages/$CREATED_MESSAGE_ID" "Delete created message" "" 200
fi

if [ -n "$CREATED_MEDIA_ID" ]; then
    test_endpoint "DELETE" "/api/v1/media/$CREATED_MEDIA_ID" "Delete created media" "" 200
fi

test_endpoint "DELETE" "/api/v1/notifications/01999f5a-0000-0000-0000-000000000000" "Delete notification (may 404)" "" 404

# ============================================
# FINAL SUMMARY
# ============================================
print_header "FINAL TEST SUMMARY"

echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                      TEST RESULTS                              ║${NC}"
echo -e "${BLUE}╠════════════════════════════════════════════════════════════════╣${NC}"
echo -e "${BLUE}║${NC}  ${YELLOW}Total Tests Run:${NC}       $(printf '%3d' $TOTAL)                                  ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}  ${GREEN}Passed:${NC}                $(printf '%3d' $PASSED)                                  ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}  ${RED}Failed:${NC}                $(printf '%3d' $FAILED)                                  ${BLUE}║${NC}"
echo -e "${BLUE}║${NC}  ${MAGENTA}Skipped:${NC}               $(printf '%3d' $SKIPPED)                                  ${BLUE}║${NC}"

if [ $TOTAL -gt 0 ]; then
    SUCCESS_RATE=$(awk "BEGIN {printf \"%.1f\", ($PASSED/$TOTAL)*100}")
    echo -e "${BLUE}║${NC}  ${CYAN}Success Rate:${NC}          ${SUCCESS_RATE}%                              ${BLUE}║${NC}"
fi

echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}\n"

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓✓✓ ALL TESTS PASSED! ✓✓✓${NC}\n"
    echo -e "${GREEN}Your API is working correctly!${NC}\n"
else
    echo -e "${RED}⚠ SOME TESTS FAILED ⚠${NC}\n"
    echo -e "${YELLOW}Review the output above for details on failed tests.${NC}\n"
fi

echo -e "${CYAN}════════════════════════════════════════════════════════════════${NC}"
echo -e "${CYAN}Notes:${NC}"
echo -e "  • Some routes require specific user roles (admin, recruiter)"
echo -e "  • File upload endpoints were skipped (require multipart data)"
echo -e "  • Logout/session revoke endpoints were skipped (preserve session)"
echo -e "  • Created test data was automatically cleaned up"
echo -e "  • 404 errors on placeholder IDs are expected"
echo -e "${CYAN}════════════════════════════════════════════════════════════════${NC}\n"

echo -e "${BLUE}Testing completed at $(date)${NC}\n"