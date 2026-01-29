@echo off
REM Laravel Backup Script for Windows
REM This script runs the Laravel backup command

echo Starting Laravel Database Backup...
echo.

REM Set the path to your Laravel project
set LARAVEL_PATH=C:\xampp\htdocs\kasir-backend

REM Set the path to PHP executable
set PHP_PATH=C:\xampp\php\php.exe

REM Change to Laravel project directory
cd /d %LARAVEL_PATH%

REM Run the backup command
echo Running backup command...
%PHP_PATH% artisan backup:run --only-db

REM Check if backup was successful
if %ERRORLEVEL% EQU 0 (
    echo.
    echo Backup completed successfully!
    echo Backup files are located in: %LARAVEL_PATH%\storage\app\private\Laravel
) else (
    echo.
    echo Backup failed with error code: %ERRORLEVEL%
    echo Please check the Laravel logs for more details.
)

echo.
echo Press any key to exit...
pause > nul