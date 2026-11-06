@echo off
setlocal EnableExtensions EnableDelayedExpansion
chcp 65001 >nul
title Instalador - Laboratorio Becomarca

REM ============================================================
REM  INSTALADOR PARA WINDOWS + XAMPP
REM
REM  - Descarga el proyecto (git clone o ZIP).
REM  - Instala Composer si falta.
REM  - Crea la base de datos y ejecuta migraciones + seeder.
REM  - Opcional: restaura los datos del kit de traslado.
REM  - Configura el VirtualHost de Apache sin borrar los existentes.
REM  - Deja Apache y MySQL arrancando al encender el equipo.
REM
REM  Requisitos: XAMPP con PHP 8.2+ y MySQL.
REM  Git y Composer se intentan resolver automaticamente.
REM
REM  Uso:
REM    instalar.bat
REM    instalar.bat --kit "D:\laboratorio-kit-XXXX.zip"
REM    instalar.bat --sin-vhost
REM    instalar.bat --check
REM ============================================================

set "REPO=https://github.com/joseoho/laboratorio-cito-biop.git"
set "APP_HOST=laboratorio-cito-biop.test"
set "DB_NAME=lapt_core"
set "DB_USER=root"
set "DB_PASS="
set "XAMPP=C:\xampp"
set "TARGET_DIR=%XAMPP%\htdocs\laboratorio-cito-biop"
set "ADMIN_EMAIL=admin@laboratorio.local"
set "ADMIN_PASS=cambiar123"
set "KIT="
set "SIN_VHOST=0"
set "SIN_ADMIN=0"

:args
if "%~1"=="" goto :inicio
if /i "%~1"=="--check" goto :check
if /i "%~1"=="--kit" (
    set "KIT=%~2"
    shift
    shift
    goto :args
)
if /i "%~1"=="--sin-vhost" (
    set "SIN_VHOST=1"
    shift
    goto :args
)
if /i "%~1"=="--sin-admin" (
    set "SIN_ADMIN=1"
    shift
    goto :args
)
echo Opcion no reconocida: %~1
echo.
echo Uso: instalar.bat [--kit "ruta.zip"] [--sin-vhost] [--check]
pause
exit /b 1

:inicio
echo ============================================================
echo   INSTALADOR - LABORATORIO BECOMARCA
echo ============================================================
echo.

if "%SIN_ADMIN%"=="1" goto :saltar_admin

net session >nul 2>&1
if errorlevel 1 (
    echo [i] Se necesitan permisos de administrador para hosts y Apache.
    echo     Acepta el aviso de Windows...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -ArgumentList '%*' -Verb RunAs"
    exit /b
)

:saltar_admin
cd /d "%~dp0"

if not exist "%XAMPP%\php\php.exe" (
    echo [X] No se encontro XAMPP en %XAMPP%.
    echo     Instala XAMPP con PHP 8.2 o superior y vuelve a ejecutar.
    goto :fin_error
)

set "PHP=%XAMPP%\php\php.exe"
set "MYSQL=%XAMPP%\mysql\bin\mysql.exe"

REM ------------------------------------------------------------
REM 1) Obtener el proyecto
REM ------------------------------------------------------------
set "APP_DIR=%~dp0"
if exist "%~dp0artisan" goto :proyecto_listo

if exist "%TARGET_DIR%\artisan" (
    set "APP_DIR=%TARGET_DIR%"
    goto :proyecto_listo
)

echo [1/8] Descargando el proyecto en %TARGET_DIR%...
if not exist "%XAMPP%\htdocs" mkdir "%XAMPP%\htdocs"

if exist "%TARGET_DIR%" (
    echo [X] La carpeta ya existe pero no parece el proyecto:
    echo     %TARGET_DIR%
    echo     Renombrala o eliminala y vuelve a ejecutar.
    goto :fin_error
)

where git >nul 2>&1
if not errorlevel 1 (
    echo     Clonando con git...
    git clone "%REPO%" "%TARGET_DIR%"
)

if not exist "%TARGET_DIR%\artisan" (
    if exist "%~dp0laboratorio-codigo.zip" (
        echo     Usando laboratorio-codigo.zip del pendrive...
        powershell -NoProfile -ExecutionPolicy Bypass -Command "Expand-Archive -Path '%~dp0laboratorio-codigo.zip' -DestinationPath '%TARGET_DIR%' -Force"
    ) else (
        echo     Descargando ZIP del repositorio...
        powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference='Stop'; $zip=Join-Path $env:TEMP 'laboratorio.zip'; $tmp=Join-Path $env:TEMP 'laboratorio_tmp'; if (Test-Path $zip) { Remove-Item $zip -Force }; if (Test-Path $tmp) { Remove-Item $tmp -Recurse -Force }; Invoke-WebRequest -Uri 'https://github.com/joseoho/laboratorio-cito-biop/archive/refs/heads/main.zip' -OutFile $zip; Expand-Archive -Path $zip -DestinationPath $tmp -Force; Move-Item (Join-Path $tmp 'laboratorio-cito-biop-main') '%TARGET_DIR%'; Remove-Item $zip -Force; Remove-Item $tmp -Recurse -Force"
    )
)

if not exist "%TARGET_DIR%\artisan" (
    echo [X] No se pudo obtener el proyecto en %TARGET_DIR%.
    echo     El repositorio es privado: usa laboratorio-codigo.zip
    echo     generado con scripts\exportar_codigo.ps1.
    goto :fin_error
)

set "APP_DIR=%TARGET_DIR%"

:proyecto_listo
if "%APP_DIR:~-1%"=="\" set "APP_DIR=%APP_DIR:~0,-1%"
cd /d "%APP_DIR%"
echo [OK] Proyecto en %APP_DIR%

if exist ".git" (
    where git >nul 2>&1
    if not errorlevel 1 (
        echo     Actualizando codigo con git pull...
        git pull --ff-only
        if errorlevel 1 echo [AVISO] No se pudo actualizar. Se continua con la version local.
    )
)

REM ------------------------------------------------------------
REM 2) Composer
REM ------------------------------------------------------------
echo [2/8] Preparando Composer...
set "COMPOSER_CMD="
where composer >nul 2>&1
if not errorlevel 1 set "COMPOSER_CMD=composer"

if not defined COMPOSER_CMD (
    if not exist "composer.phar" (
        echo     Descargando composer.phar...
        "%PHP%" -r "copy('https://getcomposer.org/installer','composer-setup.php');"
        "%PHP%" composer-setup.php --quiet
        if exist composer-setup.php del composer-setup.php >nul 2>&1
    )
    if not exist "composer.phar" (
        echo [X] No se pudo instalar Composer.
        echo     Instalalo desde https://getcomposer.org/download/
        goto :fin_error
    )
    set "COMPOSER_CMD=%PHP% composer.phar"
)

REM ------------------------------------------------------------
REM 3) .env inicial
REM ------------------------------------------------------------
if not exist ".env" (
    if not exist ".env.example" (
        echo [X] Falta .env.example en el proyecto.
        goto :fin_error
    )
    echo [3/8] Creando .env desde .env.example...
    copy /y ".env.example" ".env" >nul
) else (
    echo [3/8] .env ya existe, se conserva.
)

REM ------------------------------------------------------------
REM 4) Dependencias PHP
REM ------------------------------------------------------------
echo [4/8] Instalando dependencias PHP...
call %COMPOSER_CMD% install --no-interaction --prefer-dist --no-progress
if errorlevel 1 (
    echo [X] Fallo composer install.
    goto :fin_error
)

findstr /b /c:"APP_KEY=base64:" ".env" >nul 2>&1
if errorlevel 1 (
    "%PHP%" artisan key:generate --force
    if errorlevel 1 goto :fin_error
)

REM ------------------------------------------------------------
REM 5) MySQL y base de datos
REM ------------------------------------------------------------
echo [5/8] Preparando MySQL...
set "MYSQL_AUTH=-u %DB_USER%"
if not "%DB_PASS%"=="" set "MYSQL_AUTH=-u %DB_USER% -p%DB_PASS%"

"%MYSQL%" %MYSQL_AUTH% -e "SELECT 1" >nul 2>&1
if errorlevel 1 (
    echo     Arrancando MySQL...
    powershell -NoProfile -Command "Start-Process -FilePath '%XAMPP%\mysql\bin\mysqld.exe' -ArgumentList '--defaults-file=%XAMPP:\=/%/mysql/bin/my.ini','--standalone' -WindowStyle Hidden"
    timeout /t 8 /nobreak >nul
)

"%MYSQL%" %MYSQL_AUTH% -e "SELECT 1" >nul 2>&1
if errorlevel 1 (
    echo [X] No se pudo conectar a MySQL.
    echo     Abre XAMPP, pulsa Start en MySQL y reintenta.
    goto :fin_error
)

"%MYSQL%" %MYSQL_AUTH% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo [X] No se pudo crear la base de datos %DB_NAME%.
    goto :fin_error
)
echo [OK] Base de datos %DB_NAME% disponible.

REM ------------------------------------------------------------
REM 6) Configuracion del .env
REM ------------------------------------------------------------
echo [6/8] Configurando .env...
"%PHP%" scripts\set-env.php ".env" "APP_NAME=Laboratorio Becomarca" "APP_URL=http://%APP_HOST%" "APP_TIMEZONE=America/Caracas" "APP_LOCALE=es" "DB_CONNECTION=mysql" "DB_HOST=127.0.0.1" "DB_PORT=3306" "DB_DATABASE=%DB_NAME%" "DB_USERNAME=%DB_USER%" "DB_PASSWORD=%DB_PASS%"
if errorlevel 1 goto :fin_error

"%PHP%" artisan optimize:clear >nul 2>&1

set "HAS_TABLES=0"
for /f "usebackq" %%C in (`"%MYSQL%" %MYSQL_AUTH% -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='%DB_NAME%';" 2^>nul`) do set "HAS_TABLES=%%C"

set "BK_ARGS=-Database %DB_NAME% -User %DB_USER%"
if not "%DB_PASS%"=="" set "BK_ARGS=%BK_ARGS% -Password %DB_PASS%"

if not "!HAS_TABLES!"=="0" (
    echo     Respaldando datos antes de migrar...
    powershell -NoProfile -ExecutionPolicy Bypass -File "scripts\backup_db.ps1" !BK_ARGS!
    if errorlevel 1 echo [AVISO] No se pudo crear el respaldo. Se continua.
)

REM ------------------------------------------------------------
REM 7) Migraciones, datos iniciales y kit
REM ------------------------------------------------------------
echo [7/8] Migrando la base de datos...
"%PHP%" artisan migrate --force
if errorlevel 1 (
    echo [X] Fallaron las migraciones.
    goto :fin_error
)

echo     Cargando datos iniciales...
"%PHP%" artisan db:seed --force
if errorlevel 1 (
    echo [X] Fallaron los seeders.
    goto :fin_error
)

if not exist "storage\app\private\reports" mkdir "storage\app\private\reports"
if not exist "storage\backups" mkdir "storage\backups"

if defined KIT (
    echo     Aplicando kit de datos: !KIT!
    if "%DB_PASS%"=="" (
        powershell -NoProfile -ExecutionPolicy Bypass -File "scripts\aplicar_kit.ps1" -Kit "!KIT!" -Database "%DB_NAME%" -User "%DB_USER%"
    ) else (
        powershell -NoProfile -ExecutionPolicy Bypass -File "scripts\aplicar_kit.ps1" -Kit "!KIT!" -Database "%DB_NAME%" -User "%DB_USER%" -Password "%DB_PASS%"
    )
    if errorlevel 1 (
        echo [AVISO] No se pudo aplicar el kit. La base quedo con el esquema solamente.
    )
) else (
    echo     Sin kit: la base queda vacia, solo con el usuario administrador.
)

echo [OK] Migraciones y datos iniciales aplicados.

REM ------------------------------------------------------------
REM 8) VirtualHost
REM ------------------------------------------------------------
echo [8/8] Configurando Apache...
set "VHOST_OK=0"

if "%SIN_VHOST%"=="1" (
    echo     Omitido por --sin-vhost.
) else (
    powershell -NoProfile -ExecutionPolicy Bypass -File "scripts\setup-vhost.ps1" -Domain "%APP_HOST%"
    if errorlevel 1 (
        echo [AVISO] No se pudo configurar el vhost.
        echo     Revisa los permisos de %XAMPP%\apache o configura Apache a mano.
    ) else (
        set "VHOST_OK=1"
    )
)

REM ------------------------------------------------------------
REM Resumen
REM ------------------------------------------------------------
for %%I in ("%APP_DIR%") do set "APP_FOLDER=%%~nxI"

set "URL=http://%APP_HOST%"
if not "!VHOST_OK!"=="1" (
    set "URL=http://localhost/%APP_FOLDER%/public"
    "%PHP%" scripts\set-env.php ".env" "APP_URL=http://localhost" >nul 2>&1
)

set "DATOS=Base vacia, solo con el usuario administrador"
if defined KIT set "DATOS=Kit aplicado: !KIT!"

echo.
echo ============================================================
echo   INSTALACION COMPLETADA
echo.
echo   Carpeta: %APP_DIR%
echo   URL:     !URL!
echo   Admin:   %ADMIN_EMAIL% / %ADMIN_PASS%
echo   Datos:   !DATOS!
echo.
echo   Cambia la contrasena al entrar.
echo ============================================================
echo.

start "" "!URL!"
pause
exit /b 0

REM ============================================================
REM  MODO DIAGNOSTICO:  instalar.bat --check
REM ============================================================
:check
echo Modo diagnostico, no instala nada
echo ------------------------------------------------

if exist "%XAMPP%\php\php.exe" (
    echo [OK] PHP encontrado en %XAMPP%\php:
    "%XAMPP%\php\php.exe" -v | findstr /b /c:"PHP "
) else (
    echo [X] PHP no encontrado en %XAMPP%\php
)

if exist "%XAMPP%\mysql\bin\mysql.exe" (
    echo [OK] MySQL cliente encontrado
    "%XAMPP%\mysql\bin\mysql.exe" -u %DB_USER% -e "SELECT 1" >nul 2>&1
    if errorlevel 1 (
        echo [AVISO] MySQL no responde. Arrancalo desde XAMPP.
    ) else (
        echo [OK] MySQL responde
    )
) else (
    echo [X] MySQL no encontrado en %XAMPP%\mysql
)

where composer >nul 2>&1
if not errorlevel 1 (
    echo [OK] Composer disponible:
    call composer --version
) else (
    echo [AVISO] Composer no esta en PATH: el instalador lo descarga solo
)

where git >nul 2>&1
if not errorlevel 1 (
    echo [OK] Git disponible
) else (
    echo [AVISO] Git no esta en PATH: se usara laboratorio-codigo.zip
)

if exist "%~dp0artisan" (
    echo [OK] Proyecto detectado en %~dp0
) else if exist "%TARGET_DIR%\artisan" (
    echo [OK] Proyecto ya instalado en %TARGET_DIR%
) else (
    echo [AVISO] El proyecto se descargara en %TARGET_DIR%
)

if exist "%~dp0laboratorio-codigo.zip" (
    echo [OK] laboratorio-codigo.zip presente
) else (
    echo [AVISO] Sin laboratorio-codigo.zip al lado del instalador
)

dir /b "%~dp0laboratorio-kit-*.zip" >nul 2>&1
if not errorlevel 1 (
    echo [OK] Kit de datos presente
) else (
    echo [AVISO] Sin kit de datos: la base quedara vacia
)

findstr /i /c:"%APP_HOST%" "%SystemRoot%\System32\drivers\etc\hosts" >nul 2>&1
if not errorlevel 1 (
    echo [OK] %APP_HOST% ya esta en el archivo hosts
) else (
    echo [AVISO] %APP_HOST% no esta en hosts: lo agregara el instalador
)

if exist "%XAMPP%\apache\conf\extra\httpd-vhosts.conf" (
    findstr /i /c:"%APP_HOST%" "%XAMPP%\apache\conf\extra\httpd-vhosts.conf" >nul 2>&1
    if not errorlevel 1 (
        echo [OK] VirtualHost ya configurado
    ) else (
        echo [AVISO] VirtualHost pendiente: lo agrega el instalador
    )
) else (
    echo [X] No se encontro httpd-vhosts.conf
)

echo ------------------------------------------------
echo Diagnostico terminado.
pause
exit /b 0

:fin_error
echo.
echo [X] La instalacion no termino. Revisa el mensaje anterior.
pause
exit /b 1
