@echo off
rem Set timeout for Composer to avoid premature termination
set COMPOSER_PROCESS_TIMEOUT=1200

rem Ensure php.exe path is correct for XAMPP
set PHP_EXE=C:\xampp\php\php.exe

rem Change to project root and run Composer
cd /d "%~dp0.."
%PHP_EXE% composer.phar require google/apiclient vlucas/phpdotenv razorpay/razorpay --prefer-dist

if %errorlevel% neq 0 (
    echo Error: Composer failed to install dependencies.
    exit /b %errorlevel%
) else (
    echo Dependencies installed successfully.
)

pause
