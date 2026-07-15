@echo off
echo Starting FrankenPHP in NORMAL mode...
echo Address: http://localhost:8085
frankenphp run --config "%~dp0backend\Caddyfile.standard"
if %errorlevel% neq 0 (
    echo.
    echo Server stopped with error.
    pause
)