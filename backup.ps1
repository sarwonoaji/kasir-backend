# Laravel Backup Script for Windows PowerShell
# This script runs the Laravel backup command

param(
    [string]$LaravelPath = "C:\xampp\htdocs\kasir-backend",
    [string]$PhpPath = "C:\xampp\php\php.exe"
)

Write-Host "Starting Laravel Database Backup..." -ForegroundColor Green
Write-Host ""

# Change to Laravel project directory
Set-Location -Path $LaravelPath

# Run the backup command
Write-Host "Running backup command..." -ForegroundColor Yellow
& $PhpPath artisan backup:run --only-db

# Check if backup was successful
if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "Backup completed successfully!" -ForegroundColor Green
    Write-Host "Backup files are located in: $LaravelPath\storage\app\private\Laravel" -ForegroundColor Cyan
} else {
    Write-Host ""
    Write-Host "Backup failed with error code: $LASTEXITCODE" -ForegroundColor Red
    Write-Host "Please check the Laravel logs for more details." -ForegroundColor Yellow
}

Write-Host ""
Read-Host "Press Enter to exit"