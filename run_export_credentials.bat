@echo off
REM Batch script to run Python export credentials with venv

cd /d "%~dp0"

REM Activate venv dan run Python
call .venv\Scripts\activate.bat
python python_scripts/export_credentials.py --output "%1"

endlocal
