@echo off
setlocal
title n8n - doble3d.cl

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\healthcheck.ps1" >nul 2>&1
if errorlevel 1 (
    echo n8n no responde. Iniciando la tarea programada Doble3D-n8n...
    powershell.exe -NoProfile -NonInteractive -Command "Start-ScheduledTask -TaskName 'Doble3D-n8n'"
    if errorlevel 1 (
        echo No se pudo iniciar Doble3D-n8n. Revisa RUNBOOK.md y los logs.
        exit /b 1
    )
    timeout /t 15 /nobreak >nul
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\healthcheck.ps1"
    if errorlevel 1 (
        echo n8n no supero la comprobacion de salud. Revisa logs\n8n.log.
        exit /b 1
    )
)

start "" http://localhost:5678
exit /b 0
