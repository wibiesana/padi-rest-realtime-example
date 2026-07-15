@echo off
set PUBLIC_ROOT=backend/public
set MERCURE_DB_PATH=backend/storage/mercure.db
echo Starting FrankenPHP in NORMAL mode...
echo Address: http://localhost:8085
frankenphp run --config "%~dp0backend\Caddyfile.standard"
if %errorlevel% neq 0 (
    echo.
    echo Server stopped with error.
    pause
)