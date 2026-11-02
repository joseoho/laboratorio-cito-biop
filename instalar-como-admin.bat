@echo off
setlocal
cd /d "%~dp0"

REM Alias de compatibilidad: el instalador nuevo ya se eleva solo.
REM Se mantiene este nombre para los accesos directos y la documentacion viejos.

if not exist "%~dp0instalar.bat" (
    echo  ERROR: no se encuentra instalar.bat
    echo  El proyecto no se copio completo.
    echo.
    pause
    exit /b 1
)

call "%~dp0instalar.bat" %*
exit /b %errorlevel%
