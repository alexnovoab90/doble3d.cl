@echo off
title n8n - doble3d.cl
REM Lanzador manual de n8n. Deja esta ventana abierta mientras uses n8n.
REM La interfaz queda en: http://localhost:5678

set "PATH=C:\nvm4w\nodejs;%APPDATA%\npm;%PATH%"

REM Si ya hay una instancia corriendo, avisar y abrir el navegador
curl -s -o nul --max-time 3 http://localhost:5678
if %errorlevel%==0 (
    echo n8n ya esta corriendo. Abriendo el navegador...
    start http://localhost:5678
    timeout /t 5 >nul
    exit /b
)

echo Iniciando n8n... la interfaz estara en http://localhost:5678
start "" /min cmd /c "timeout /t 15 >nul && start http://localhost:5678"
n8n start
pause
