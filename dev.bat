@echo off
setlocal

if "%PORT%"=="" set PORT=8000
if "%HOST%"=="" set HOST=localhost

set "PHP_EXE=php"
where php >nul 2>nul
if errorlevel 1 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_EXE=C:\xampp\php\php.exe"
    ) else (
        echo php.exe not found in PATH and C:\xampp\php\php.exe missing.
        echo Install XAMPP or add php.exe to PATH.
        exit /b 1
    )
)

"%PHP_EXE%" -m | findstr /I pdo_mysql >nul
if errorlevel 1 (
    echo pdo_mysql extension missing in %PHP_EXE%
    exit /b 1
)

echo.
echo   SleepTrack -^> http://%HOST%:%PORT%
echo   Make sure Apache + MySQL are running in XAMPP.
echo.

"%PHP_EXE%" -S %HOST%:%PORT% -t .
