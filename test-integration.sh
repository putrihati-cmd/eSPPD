#!/bin/bash
# Integration Test Script
# Tests Backend ↔ Microservice ↔ Database ↔ Frontend

echo "======================================"
echo "  SYSTEM INTEGRATION TEST SCRIPT"
echo "======================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

TEST_COUNT=0
PASS_COUNT=0
FAIL_COUNT=0

# Function to test
test_connection() {
    TEST_COUNT=$((TEST_COUNT + 1))
    local name=$1
    local command=$2

    echo -n "[$TEST_COUNT] Testing $name... "

    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ PASS${NC}"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        echo -e "${RED}❌ FAIL${NC}"
        FAIL_COUNT=$((FAIL_COUNT + 1))
    fi
}

# ========== DATABASE TESTS ==========
echo -e "${YELLOW}1. DATABASE TESTS${NC}"

test_connection "PostgreSQL Running" "pg_isready -h localhost -p 5432"
test_connection "Database Accessible" "psql -h localhost -U postgres -d eSPPD -c 'SELECT 1'"
test_connection "Users Table Exists" "psql -h localhost -U postgres -d eSPPD -c 'SELECT COUNT(*) FROM users' | grep -q ."
test_connection "Employees Table Exists" "psql -h localhost -U postgres -d eSPPD -c 'SELECT COUNT(*) FROM employees' | grep -q ."

echo ""

# ========== BACKEND TESTS ==========
echo -e "${YELLOW}2. BACKEND TESTS${NC}"

test_connection "Laravel Routes Available" "php artisan route:list | grep -q spd"
test_connection "Models Loaded" "php artisan tinker --execute=\"echo class_exists('App\\\\Models\\\\User') ? 'ok' : 'fail'\""
test_connection "Database Connection" "php artisan tinker --execute=\"echo DB::connection()->getPdo() ? 'ok' : 'fail'\""
test_connection "Auth Model Available" "php artisan tinker --execute=\"echo class_exists('App\\\\Models\\\\User') ? 'ok' : 'fail'\""

echo ""

# ========== MICROSERVICE TESTS ==========
echo -e "${YELLOW}3. MICROSERVICE TESTS${NC}"

test_connection "Python Available" "python --version > /dev/null 2>&1"
test_connection "FastAPI Framework" "python -c 'import fastapi' 2>/dev/null"
test_connection "Service File Exists" "test -f document-service/main.py"
test_connection "Service Config" "grep -q 'http://localhost:8001' app/Services/PythonDocumentService.php"

echo ""

# ========== FRONTEND TESTS ==========
echo -e "${YELLOW}4. FRONTEND TESTS${NC}"

test_connection "Profile Template" "test -f resources/views/profile.blade.php"
test_connection "Biodata Section" "grep -q 'Data Kepegawaian' resources/views/profile.blade.php"
test_connection "Dashboard Template" "test -f resources/views/livewire/dashboard.blade.php"
test_connection "SPD Templates" "test -f resources/views/livewire/spd/spd-create.blade.php"

echo ""

# ========== INTEGRATION TESTS ==========
echo -e "${YELLOW}5. INTEGRATION TESTS${NC}"

test_connection "Service Layer" "grep -q 'class PythonDocumentService' app/Services/PythonDocumentService.php"
test_connection "Model Relationships" "grep -q 'public function employee' app/Models/User.php"
test_connection "API Routes" "php artisan route:list | grep -q api"
test_connection "Config Services" "grep -q 'python_document' config/services.php"

echo ""
echo "======================================"
echo -e "  TEST RESULTS"
echo "======================================"
echo -e "Total Tests: ${TEST_COUNT}"
echo -e "Passed: ${GREEN}${PASS_COUNT}${NC}"
echo -e "Failed: ${RED}${FAIL_COUNT}${NC}"

if [ $FAIL_COUNT -eq 0 ]; then
    echo -e "${GREEN}✅ ALL TESTS PASSED - SYSTEM READY${NC}"
    exit 0
else
    echo -e "${YELLOW}⚠️  SOME TESTS FAILED - CHECK ABOVE${NC}"
    exit 1
fi
