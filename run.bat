@echo off
title Laravel Network Access Starter
color 0a

echo ===========================================
echo     Laravel Network Access Starter
echo ===========================================

REM --- Cari alamat IP lokal (IPv4) ---
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr "IPv4" ^| findstr /v "127.0.0.1"') do set IP=%%a
set IP=%IP: =%

if "%IP%"=="" (
    echo Gagal mendeteksi IP lokal.
    echo Pastikan kamu terhubung ke Wi-Fi atau LAN.
    pause
    exit
)

echo IP lokal kamu: %IP%
echo ===========================================
echo Akses dari perangkat lain di alamat:
echo     http://%IP%:8000/
echo ===========================================

REM --- Jalankan Laravel ---
cd /d %~dp0
start "" http://%IP%:8000/
php artisan serve --host=%IP% --port=8000

pause
