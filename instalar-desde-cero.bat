@echo off
setlocal EnableDelayedExpansion
cd /d "%~dp0"
title Laboratorio Becomarca - Instalacion desde cero

set "REPO=https://github.com/joseoho/laboratorio-cito-biop.git"
set "DEST=C:\xampp\htdocs\laboratorio-cito-biop"
set "KIT="
set "SOLO=0"
set "SINGIT=0"

:parse
if "%~1"=="" goto inicio
if /I "%~1"=="-Destino" (
    set "DEST=%~2"
    shift
    shift
    goto parse
)
if /I "%~1"=="-Kit" (
    set "KIT=%~2"
    shift
    shift
    goto parse
)
if /I "%~1"=="-SoloCodigo" (
    set "SOLO=1"
    shift
    goto parse
)
if /I "%~1"=="-SinGit" (
    set "SINGIT=1"
    shift
    goto parse
)
echo  Opcion no reconocida: %~1
echo  Uso: instalar-desde-cero.bat [-Destino "C:\ruta"] [-Kit "D:\kit.zip"] [-SinGit] [-SoloCodigo]
pause
exit /b 1

:inicio
echo.
echo  ==========================================================
echo   LABORATORIO BECOMARCA - INSTALACION DESDE CERO
echo  ==========================================================
echo.
echo   Carpeta destino: %DEST%
echo.

if not exist "C:\xampp\php\php.exe" (
    echo  ERROR: no se encontro XAMPP ^(C:\xampp\php\php.exe^).
    echo  Instala XAMPP 8.2 primero y volve a ejecutar.
    echo.
    pause
    exit /b 1
)

rem ---------------------------------------------------------------- codigo
set "GIT="
if "%SINGIT%"=="0" (
    where git >nul 2>&1 && set "GIT=1"
)

if defined GIT (
    if exist "%DEST%\.git" (
        echo [1/3] Actualizando el codigo existente...
        git -C "%DEST%" pull --ff-only
    ) else (
        echo [1/3] Clonando el repositorio...
        git clone "%REPO%" "%DEST%"
    )
) else (
    echo [1/3] Sin Git: se usara la copia del pendrive.
)

if not exist "%DEST%\instalar-como-admin.bat" (
    echo [1/3] Buscando la copia del codigo junto a este archivo...
    set "CODEZIP="
    for %%F in ("laboratorio-codigo*.zip") do set "CODEZIP=%%~fF"
    if not exist "%DEST%" mkdir "%DEST%"
    if defined CODEZIP (
        echo       usando !CODEZIP!
        powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Expand-Archive -Path '!CODEZIP!' -DestinationPath '%DEST%' -Force"
    ) else (
        echo.
        echo  ERROR: no se pudo obtener el codigo.
        echo    - El repositorio es privado: git pide usuario y contrasena.
        echo    - O no hay un laboratorio-codigo*.zip al lado de este archivo.
        echo.
        echo  Solucion: en el equipo de origen ejecuta
        echo      scripts\exportar_codigo.ps1
        echo  y copia laboratorio-codigo.zip junto a este .bat.
        echo.
        pause
        exit /b 1
    )
)

if not exist "%DEST%\instalar-como-admin.bat" (
    echo  ERROR: el codigo quedo incompleto en %DEST%.
    echo.
    pause
    exit /b 1
)
echo       Codigo listo.

rem ---------------------------------------------------------------- kit
if "%KIT%"=="" (
    for %%F in ("laboratorio-kit-*.zip") do set "KIT=%%~fF"
)

if "%SOLO%"=="1" (
    echo.
    echo  Codigo preparado en %DEST% ^(-SoloCodigo^).
    echo.
    pause
    exit /b 0
)

rem ---------------------------------------------------------------- instalacion
echo.
if defined KIT (
    echo [2/3] Datos del kit: !KIT!
    echo [3/3] Lanzando la instalacion completa...
    call "%DEST%\instalar-como-admin.bat" -Kit "!KIT!"
) else (
    echo [2/3] Sin kit: la base quedara vacia.
    echo [3/3] Lanzando la instalacion completa...
    call "%DEST%\instalar-como-admin.bat"
)

pause
endlocal
