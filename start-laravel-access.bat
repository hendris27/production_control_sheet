@echo off
echo ==============================
echo  Laravel Local Access Starter
echo ==============================

REM --- Cari IP lokal (IPv4) ---
for /f "tokens=2 delims=:" %%A in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
    set IP=%%A
)
set IP=%IP: =%

echo Local IP detected: %IP%
echo.

REM --- Jalankan Laravel dengan host 0.0.0.0 dan port 8000 ---
echo Starting Laravel server at http://%IP%:8000 ...
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
