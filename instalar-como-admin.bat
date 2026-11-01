@echo off
setlocal
cd /d "%~dp0"

echo.
echo  ==========================================================
echo   LABORATORIO BECOMARCA - INSTALACION COMPLETA
echo   (pide permiso de administrador una sola vez)
echo  ==========================================================
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\elevar.ps1" %*

if errorlevel 1 (
    echo.
    echo  No se pudo completar la instalacion. Revisa el mensaje de arriba.
    echo.
    pause
    exit /b 1
)

echo.
echo  Instalacion finalizada. Cerra esta ventana cuando quieras.
pause
endlocal
