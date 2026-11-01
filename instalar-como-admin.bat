@echo off
setlocal
cd /d "%~dp0"
title Laboratorio Becomarca - Instalacion completa

echo.
echo  ==========================================================
echo   LABORATORIO BECOMARCA - INSTALACION COMPLETA
echo  ==========================================================
echo.
echo   Se va a abrir OTRA ventana con permisos de administrador.
echo   Acepta el aviso de Windows y NO cierres esa ventana.
echo   Esta ventana espera a que termine.
echo.

if not exist "%~dp0scripts\elevar.ps1" (
    echo  ERROR: no se encuentra scripts\elevar.ps1
    echo  El proyecto no se copio completo. Volve a clonarlo o descargarlo entero.
    echo.
    pause
    exit /b 1
)

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\elevar.ps1" %*
set "CODE=%errorlevel%"

if not "%CODE%"=="0" (
    echo.
    echo  No se pudo completar la instalacion. Codigo %CODE%.
    echo  Si rechazaste el permiso de administrador, proba con instalar.bat
    echo  (instalacion basica, sin dominio .test ni arranque automatico).
    echo.
    pause
    exit /b 1
)

echo.
echo  Instalacion finalizada. Cerra esta ventana cuando quieras.
pause
endlocal
