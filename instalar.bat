@echo off
setlocal
cd /d "%~dp0"

echo.
echo  ============================================
echo   Laboratorio Becomarca - Instalador (XAMPP)
echo  ============================================
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\instalar.ps1" %*

if errorlevel 1 (
    echo.
    echo  La instalacion termino con errores. Revisa el mensaje de arriba.
    echo.
    pause
    exit /b 1
)

echo.
pause
endlocal
