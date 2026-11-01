@echo off
setlocal
cd /d "%~dp0"
title Laboratorio Becomarca - Instalacion

echo.
echo  ==========================================================
echo   LABORATORIO BECOMARCA - INSTALACION
echo   No cierres esta ventana. Al final muestra el resumen.
echo  ==========================================================
echo.

if not exist "%~dp0scripts\instalar.ps1" (
    echo  ERROR: no se encuentra scripts\instalar.ps1
    echo  El proyecto no se copio completo. Volve a clonarlo o descargarlo entero.
    echo.
    pause
    exit /b 1
)

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\instalar.ps1" %*
set "CODE=%errorlevel%"

if not "%CODE%"=="0" (
    echo.
    echo  La instalacion termino con errores. Codigo %CODE%.
    echo  Revisa el mensaje de arriba y el archivo storage\logs\instalacion-*.log
    echo.
    pause
    exit /b 1
)

echo.
echo  Instalacion finalizada. Cerra esta ventana cuando quieras.
pause
endlocal
