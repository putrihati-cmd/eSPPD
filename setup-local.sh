#!/bin/bash

# eSPPD Local Development Setup
# Usage: ./setup-local.sh

echo "=================================="
echo "  eSPPD LOCAL DEVELOPMENT SETUP"
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if in project directory
if [ ! -f "artisan" ]; then
    echo "❌ Run this script from the project root directory"
    exit 1
fi

echo -e "${YELLOW}Step 1: Installing PHP dependencies${NC}"
composer install
echo -e "${GREEN}✅ Done${NC}\n"

echo -e "${YELLOW}Step 2: Setting up .env file${NC}"
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
    echo -e "${GREEN}✅ .env created${NC}"
else
    echo -e "${GREEN}✅ .env already exists${NC}"
fi
echo ""

echo -e "${YELLOW}Step 3: Installing Node dependencies${NC}"
npm install
echo -e "${GREEN}✅ Done${NC}\n"

echo -e "${YELLOW}Step 4: Running migrations${NC}"
php artisan migrate
echo -e "${GREEN}✅ Done${NC}\n"

echo -e "${YELLOW}Step 5: Seeding test data (optional)${NC}"
read -p "Do you want to seed test data? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed
    echo -e "${GREEN}✅ Test data seeded${NC}"
else
    echo -e "${YELLOW}⏭️  Skipped seeding${NC}"
fi
echo ""

echo -e "${YELLOW}Step 6: Building frontend assets${NC}"
npm run dev
echo -e "${GREEN}✅ Done${NC}\n"

echo ""
echo -e "${GREEN}=================================="
echo "  ✅ SETUP COMPLETED"
echo "==================================${NC}"
echo ""
echo "Next steps:"
echo ""
echo "1. Start the Laravel development server:"
echo "   php artisan serve"
echo ""
echo "2. Start the Python microservice (in another terminal):"
echo "   cd document-service"
echo "   python -m uvicorn main:app --reload --port 8001"
echo ""
echo "3. (Optional) Watch frontend assets:"
echo "   npm run watch"
echo ""
echo "4. Open browser:"
echo "   http://localhost:8000"
echo ""
echo "5. Test integration:"
echo "   ./test-integration.sh  (Linux/Mac)"
echo "   test-integration.bat   (Windows)"
echo ""
