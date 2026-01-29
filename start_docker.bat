@echo off
cd /d "%~dp0"

title e-SPPD Docker Launcher
echo ==========================================
echo   e-SPPD Docker Environment Launcher
echo ==========================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not running!
    echo Please start Docker Desktop and try again.
    pause
    exit /b
)

echo [1/3] Setting up Environment...
if not exist .env (
    echo [INFO] Copying .env.docker to .env...
    copy .env.docker .env
)

echo.
echo [2/3] Building and Starting Containers...
echo This might take a while for the first time...
docker-compose up -d --build

echo.
echo [3/4] Initializing Application...
echo Generating App Key...
docker-compose exec app php artisan key:generate --show
echo Running Migrations and Seeding...
docker-compose exec app php artisan migrate --seed --force

echo.
echo [4/4] Checking Services...
docker-compose ps

echo.
echo ==========================================
echo   Application is running at:
echo   http://localhost:8000
echo ==========================================
echo.
echo To stop the server, run: docker-compose down
echo.
pause
