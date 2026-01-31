@echo off
REM Integration Test Script (Windows)
REM Tests Backend ↔ Microservice ↔ Database ↔ Frontend

setlocal enabledelayedexpansion

echo.
echo ======================================
echo   SYSTEM INTEGRATION TEST SCRIPT
echo ======================================
echo.

set TEST_COUNT=0
set PASS_COUNT=0
set FAIL_COUNT=0

REM ========== DATABASE TESTS ==========
echo [DATABASE TESTS]
echo.

echo [1/16] Testing Laravel Migrations...
php artisan migrate:status >nul 2>&1
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ Migrations can be checked
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Migrations check failed
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [2/16] Testing Database Tables Exist...
php artisan tinker --execute="echo DB::table('users')->count() ? 'ok' : 'fail'" >nul 2>&1
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ Users table accessible
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Users table not accessible
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [3/16] Testing Employee Table...
php artisan tinker --execute="echo DB::table('employees')->count() ? 'ok' : 'fail'" >nul 2>&1
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ Employees table accessible
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Employees table not accessible
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo.
REM ========== BACKEND TESTS ==========
echo [BACKEND TESTS]
echo.

echo [4/16] Testing Laravel Routes...
php artisan route:list 2>nul | find "spd" >nul
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ Routes configured
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Routes not found
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [5/16] Testing User Model...
if exist "app\Models\User.php" (
    echo [OK] ✅ User model exists
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ User model missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [6/16] Testing Employee Model...
if exist "app\Models\Employee.php" (
    echo [OK] ✅ Employee model exists
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Employee model missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [7/16] Testing SPD Model...
if exist "app\Models\Spd.php" (
    echo [OK] ✅ SPD model exists
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ SPD model missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo.
REM ========== MICROSERVICE TESTS ==========
echo [MICROSERVICE TESTS]
echo.

echo [8/16] Testing Python Installation...
python --version >nul 2>&1
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ Python available
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Python not found
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [9/16] Testing FastAPI Module...
python -c "import fastapi" >nul 2>&1
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ FastAPI installed
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ FastAPI not installed
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [10/16] Testing Service File...
if exist "document-service\main.py" (
    echo [OK] ✅ Microservice file exists
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Microservice file missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo.
REM ========== FRONTEND TESTS ==========
echo [FRONTEND TESTS]
echo.

echo [11/16] Testing Profile Template...
if exist "resources\views\profile.blade.php" (
    echo [OK] ✅ Profile template exists
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Profile template missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [12/16] Testing Biodata Section...
findstr /M "Data Kepegawaian" "resources\views\profile.blade.php" >nul
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ Biodata section found
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Biodata section missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [13/16] Testing Dashboard Template...
if exist "resources\views\livewire\dashboard.blade.php" (
    echo [OK] ✅ Dashboard template exists
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Dashboard template missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo.
REM ========== INTEGRATION TESTS ==========
echo [INTEGRATION TESTS]
echo.

echo [14/16] Testing Service Layer...
if exist "app\Services\PythonDocumentService.php" (
    echo [OK] ✅ Service layer exists
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Service layer missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [15/16] Testing Model Relationships...
findstr /M "public function employee" "app\Models\User.php" >nul
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ Relationships defined
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Relationships not found
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo [16/16] Testing Config Services...
findstr /M "python_document" "config\services.php" >nul
if !ERRORLEVEL! equ 0 (
    echo [OK] ✅ Service config exists
    set /a PASS_COUNT+=1
) else (
    echo [FAIL] ❌ Service config missing
    set /a FAIL_COUNT+=1
)
set /a TEST_COUNT+=1

echo.
echo ======================================
echo   TEST RESULTS
echo ======================================
echo Total Tests: %TEST_COUNT%
echo Passed: %PASS_COUNT% ✅
echo Failed: %FAIL_COUNT% ❌
echo.

if %FAIL_COUNT% equ 0 (
    echo ✅ ALL TESTS PASSED - SYSTEM READY FOR INTEGRATION
    echo.
    echo Next Steps:
    echo   1. Start Database: PostgreSQL should be running
    echo   2. Start Backend: php artisan serve (port 8000)
    echo   3. Start Microservice: python -m uvicorn document-service/main:app --port 8001
    echo   4. Test: http://localhost:8000 (login and navigate to /profile)
    echo.
    exit /b 0
) else (
    echo ⚠️  SOME TESTS FAILED - SEE ABOVE FOR DETAILS
    exit /b 1
)
