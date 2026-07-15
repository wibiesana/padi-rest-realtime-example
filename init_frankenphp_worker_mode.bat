@echo off
set PUBLIC_ROOT=backend/public
set WORKER_INDEX_PATH=backend/public/index.php
set MERCURE_DB_PATH=backend/storage/mercure.db
echo Starting FrankenPHP in WORKER mode...
echo Address: http://localhost:8085
frankenphp run --config "%~dp0backend\Caddyfile.worker"
if %errorlevel% neq 0 (
    echo.
    echo Server stopped with error.
    pause
)