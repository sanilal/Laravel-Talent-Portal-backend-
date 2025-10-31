#!/bin/bash

# Quick Start & Test Script for Talent Marketplace
# This script helps you set up and test your application

echo "════════════════════════════════════════════════════════"
echo "   TALENT MARKETPLACE - QUICK START & TEST"
echo "════════════════════════════════════════════════════════"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if we're in the backend directory
if [ ! -f "artisan" ]; then
    print_error "Error: Not in Laravel backend directory!"
    echo "Please run this script from your Laravel backend root directory."
    exit 1
fi

echo "Step 1: Environment Check"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1)
    print_success "PHP installed: $PHP_VERSION"
else
    print_error "PHP not found! Please install PHP 8.1 or higher."
    exit 1
fi

# Check Composer
if command -v composer &> /dev/null; then
    print_success "Composer installed"
else
    print_error "Composer not found! Please install Composer."
    exit 1
fi

# Check if .env exists
if [ -f ".env" ]; then
    print_success ".env file exists"
else
    print_warning ".env file not found. Copying from .env.example..."
    cp .env.example .env
    print_info "Please update your .env file with database credentials"
    echo "Press Enter when ready to continue..."
    read
fi

echo ""
echo "Step 2: Database Setup"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

read -p "Do you want to reset the database with fresh data? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_info "Running migrations..."
    php artisan migrate:fresh
    
    if [ $? -eq 0 ]; then
        print_success "Database migrated successfully"
        
        # Copy seeder if not exists
        if [ ! -f "database/seeders/TestDataSeeder.php" ]; then
            if [ -f "TestDataSeeder.php" ]; then
                cp TestDataSeeder.php database/seeders/
                print_info "TestDataSeeder copied to database/seeders/"
            fi
        fi
        
        # Run seeder
        read -p "Do you want to seed test data? (y/n): " -n 1 -r
        echo ""
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            print_info "Seeding test data..."
            php artisan db:seed --class=TestDataSeeder
            print_success "Test data seeded!"
        fi
    else
        print_error "Migration failed! Please check your database configuration."
        exit 1
    fi
fi

echo ""
echo "Step 3: Storage Setup"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan storage:link
print_success "Storage linked"

echo ""
echo "Step 4: Cache Clear"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
print_success "Cache cleared"

echo ""
echo "Step 5: Start Servers"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

read -p "Do you want to start the Laravel server now? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_info "Starting Laravel server on http://localhost:8000..."
    print_warning "This will run in the foreground. Press Ctrl+C to stop."
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Test Accounts (Password: password123 for all):"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Talent 1: talent1@example.com"
    echo "Talent 2: talent2@example.com"
    echo "Recruiter: recruiter1@example.com"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Next Steps:"
    echo "1. Open another terminal"
    echo "2. Navigate to frontend directory"
    echo "3. Run: npm run dev"
    echo "4. Open http://localhost:3000"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    php artisan serve
else
    echo ""
    echo "════════════════════════════════════════════════════════"
    echo "   SETUP COMPLETE!"
    echo "════════════════════════════════════════════════════════"
    echo ""
    echo "To start testing:"
    echo ""
    echo "1. Start Backend Server:"
    echo "   $ php artisan serve"
    echo ""
    echo "2. Start Frontend Server (in another terminal):"
    echo "   $ cd ../frontend"
    echo "   $ npm run dev"
    echo ""
    echo "3. Run API Tests:"
    echo "   $ ./test-backend-simple.sh"
    echo ""
    echo "4. Test Accounts (Password: password123):"
    echo "   - Talent: talent1@example.com"
    echo "   - Designer: talent2@example.com"
    echo "   - Recruiter: recruiter1@example.com"
    echo ""
    echo "5. Check Documentation:"
    echo "   - TESTING_GUIDE.md"
    echo "   - FRONTEND_TESTING_CHECKLIST.md"
    echo ""
    echo "════════════════════════════════════════════════════════"
fi
