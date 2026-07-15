@echo off
echo Starting FrankenPHP in WORKER mode...
echo Address: http://localhost:8085
frankenphp run --config "%~dp0backend\Caddyfile.worker"
if %errorlevel% neq 0 (
    echo.
    echo Server stopped with error.
    pause
)