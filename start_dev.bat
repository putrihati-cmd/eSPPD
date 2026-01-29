@echo off
REM Pindah ke direktori script berada (Project Root)
cd /d "%~dp0"

title e-SPPD Launcher
echo ==========================================
echo   e-SPPD Local Development Launcher
echo   Dir: %CD%
echo ==========================================
echo.

echo [INFO] Membersihkan file sampah dev server...
if exist "public\hot" (
    del "public\hot"
    echo [OK] File public/hot dihapus (Fix Layout Issue)
)

echo 1. Pastikan Redis dan PostgreSQL sudah berjalan (via Laragon)
echo.

echo [1/4] Starting Laravel Server...
start "Laravel Server" cmd /k "cd /d "%~dp0" && php artisan serve"

echo [2/4] Starting Vite Asset Server...
REM Cek apakah node_modules ada
if not exist "node_modules" (
    echo [INFO] Installing Node dependencies...
    call npm install
    echo [INFO] Building initial assets...
    call npm run build
)
REM Jalankan npm run build sekali untuk memastikan assets ada, baru run dev
if not exist "public\build\manifest.json" (
    echo [INFO] Building assets...
    call npm run build
)
start "Vite Assets" cmd /k "cd /d "%~dp0" && npm run dev"

echo [3/4] Starting Redis Queue Worker...
start "Queue Worker" cmd /k "cd /d "%~dp0" && php artisan queue:work"

echo [4/4] Starting Python Document Service...
if exist "document-service" (
    cd document-service
    
    if not exist "venv" (
        echo [INFO] Creating Python Virtual Environment...
        python -m venv venv
    )
    
    REM Install deps di window terpisah agar tidak blocking
    start "Python Service" cmd /k "cd /d "%~dp0document-service" && call venv\Scripts\activate && pip install -r requirements.txt && uvicorn main:app --reload --port 8001"
    
    cd ..
) else (
    echo [ERROR] Folder document-service not found!
)

echo.
echo ==========================================
echo   PERINTAH DILUNCURKAN
echo ==========================================
echo   Cek jendela-jendela CMD yang baru terbuka.
echo   Jika ada error merah, screenshot dan kirimkan.
echo ==========================================
echo   Web: http://localhost:8000
echo ==========================================
pause
