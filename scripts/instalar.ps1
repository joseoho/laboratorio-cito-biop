<#
.SYNOPSIS
    Instalador del Laboratorio Becomarca para XAMPP en Windows.

.DESCRIPTION
    Ejecuta los pasos en orden y de forma idempotente (se puede repetir sin romper nada):
      1. Verifica XAMPP, MySQL y Composer
      2. Prepara .env y la clave de la aplicacion
      3. Crea la base de datos
      4. Instala dependencias (composer) si faltan
      5. Aplica migraciones
      6. Crea el usuario administrador
      7. Prepara carpetas de PDF y respaldos
      8. (opcional) crea la base de pruebas lapt_test
      9. (opcional) genera el primer respaldo

    Uso:
        instalar.bat                  (interactivo)
        instalar.bat -Check           (solo verifica requisitos, no instala)
        instalar.bat -Si              (sin preguntas, valores por defecto)
        instalar.bat -DbName lapt_core -DbUser root -DbPass ""

.NOTES
    Sin acentos a proposito: Windows PowerShell 5.1 lee este archivo como ANSI.
#>
[CmdletBinding()]
param(
    [switch]$Check,
    [switch]$Si,
    [string]$DbName = 'lapt_core',
    [string]$DbUser = 'root',
    [string]$DbPass = '',
    [string]$PhpExe = 'C:\xampp\php\php.exe',
    [string]$MysqlBin = 'C:\xampp\mysql\bin'
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$mysql = Join-Path $MysqlBin 'mysql.exe'

function Paso([int]$n, [int]$total, [string]$texto) {
    Write-Host ''
    Write-Host ("[{0}/{1}] {2}" -f $n, $total, $texto) -ForegroundColor Cyan
}

function Ok([string]$texto) { Write-Host ("      OK  " + $texto) -ForegroundColor Green }
function Aviso([string]$texto) { Write-Host ("      !   " + $texto) -ForegroundColor Yellow }

function Ejecutar([string]$exe, [string[]]$argumentos, [string]$que) {
    & $exe @argumentos
    if ($LASTEXITCODE -ne 0) {
        throw ("Fallo " + $que + " (codigo " + $LASTEXITCODE + ").")
    }
}

$total = 9

# ---------------------------------------------------------------------------
Paso 1 $total 'Verificando requisitos'

if (-not (Test-Path $PhpExe)) {
    throw "No se encontro PHP en $PhpExe. Instala XAMPP o indica -PhpExe."
}
Ok ("PHP: " + (& $PhpExe -r 'echo PHP_VERSION;'))

if (-not (Test-Path $mysql)) {
    throw "No se encontro MySQL en $MysqlBin. Instala XAMPP o indica -MysqlBin."
}
Ok 'MySQL: encontrado'

$mysqlArgs = @('-u', $DbUser)
if ($DbPass) { $mysqlArgs += "-p$DbPass" }

& $mysql @mysqlArgs -N -e 'SELECT 1' 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) {
    throw "No se pudo conectar a MySQL con el usuario '$DbUser'. Inicia MySQL desde el panel de XAMPP y verifica la contrasena."
}
Ok 'Conexion a MySQL'

$composer = $null
$cmd = Get-Command composer -ErrorAction SilentlyContinue
if ($cmd) {
    $composer = $cmd.Source
} elseif (Test-Path 'C:\ProgramData\ComposerSetup\bin\composer.bat') {
    $composer = 'C:\ProgramData\ComposerSetup\bin\composer.bat'
}
if (-not $composer) {
    throw 'No se encontro Composer. Descargalo de https://getcomposer.org/download/ e intentalo de nuevo.'
}
Ok ("Composer: " + $composer)

if ($Check) {
    Write-Host ''
    Write-Host 'Verificacion completada. No se modifico nada (-Check).' -ForegroundColor Green
    exit 0
}

# ---------------------------------------------------------------------------
Paso 2 $total 'Preparando .env'
Push-Location $root
try {
    if (-not (Test-Path '.env')) {
        if (-not (Test-Path '.env.example')) { throw 'Falta .env.example en el proyecto.' }
        Copy-Item '.env.example' '.env'
        Ok '.env creado desde .env.example'
    } else {
        Ok '.env ya existia'
    }

    $envDb = (Select-String -Path '.env' -Pattern '^DB_DATABASE=(.*)$').Matches.Groups[1].Value
    if ($envDb -and $envDb.Trim() -ne $DbName) {
        Aviso (".env apunta a la base '" + $envDb.Trim() + "' y el instalador usara '" + $DbName + "'.")
        Aviso 'Si quieres otra base, edita DB_DATABASE en .env o pasa -DbName.'
    }

    $keyOk = (Select-String -Path '.env' -Pattern '^APP_KEY=base64:.+$') -ne $null
    if (-not $keyOk) {
        Ejecutar $PhpExe @('artisan', 'key:generate', '--force') 'key:generate'
        Ok 'APP_KEY generada'
    } else {
        Ok 'APP_KEY ya existia'
    }
}
finally {
    Pop-Location
}

# ---------------------------------------------------------------------------
Paso 3 $total ("Creando la base de datos '" + $DbName + "'")
$sql = "CREATE DATABASE IF NOT EXISTS ``$DbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
& $mysql @mysqlArgs -e $sql
if ($LASTEXITCODE -ne 0) { throw "No se pudo crear la base $DbName." }
Ok ("Base '" + $DbName + "' lista")

# ---------------------------------------------------------------------------
Paso 4 $total 'Instalando dependencias'
Push-Location $root
try {
    if (Test-Path 'vendor\autoload.php') {
        Ok 'vendor ya existe (se omite composer install)'
    } else {
        & cmd.exe /c "`"$composer`" install --no-interaction --prefer-dist --no-progress"
        if ($LASTEXITCODE -ne 0) { throw 'Fallo composer install.' }
        Ok 'Dependencias instaladas'
    }
}
finally {
    Pop-Location
}

# ---------------------------------------------------------------------------
Paso 5 $total 'Aplicando migraciones'
Push-Location $root
try {
    Ejecutar $PhpExe @('artisan', 'migrate', '--force') 'migrate'
    Ok 'Esquema al dia'
}
finally {
    Pop-Location
}

# ---------------------------------------------------------------------------
Paso 6 $total 'Creando el usuario administrador'
Push-Location $root
try {
    Ejecutar $PhpExe @('artisan', 'db:seed', '--force') 'db:seed'
    Ok 'Usuario inicial listo'
}
finally {
    Pop-Location
}

# ---------------------------------------------------------------------------
Paso 7 $total 'Preparando carpetas'
foreach ($dir in @('storage\backups', 'storage\app\private\reports', 'storage\logs')) {
    $path = Join-Path $root $dir
    if (-not (Test-Path $path)) { New-Item -ItemType Directory -Path $path -Force | Out-Null }
}
Ok 'storage\backups y storage\app\private\reports'

# ---------------------------------------------------------------------------
Paso 8 $total 'Base de pruebas (opcional)'
$crearTest = $true
if (-not $Si) {
    $r = Read-Host '      Crear la base de pruebas lapt_test? (S/n)'
    if ($r -and $r.Trim().ToLower().StartsWith('n')) { $crearTest = $false }
}
if ($crearTest) {
    $testScript = Join-Path $PSScriptRoot 'test_db.ps1'
    if (Test-Path $testScript) {
        $testArgs = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $testScript,
                      '-Source', $DbName, '-Target', 'lapt_test', '-User', $DbUser)
        if ($DbPass) { $testArgs += @('-Password', $DbPass) }
        & powershell.exe @testArgs
        if ($LASTEXITCODE -ne 0) { Aviso 'No se pudo crear lapt_test; los tests no correran hasta crearla.' }
    } else {
        Aviso 'No se encontro scripts/test_db.ps1.'
    }
} else {
    Aviso 'Base de pruebas omitida.'
}

# ---------------------------------------------------------------------------
Paso 9 $total 'Primer respaldo (opcional)'
$backup = $false
if (-not $Si) {
    $r = Read-Host '      Generar un respaldo ahora? (s/N)'
    if ($r -and $r.Trim().ToLower().StartsWith('s')) { $backup = $true }
}
if ($backup) {
    $bk = Join-Path $PSScriptRoot 'backup_db.ps1'
    $bkArgs = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $bk,
                '-Database', $DbName, '-User', $DbUser)
    if ($DbPass) { $bkArgs += @('-Password', $DbPass) }
    & powershell.exe @bkArgs
    if ($LASTEXITCODE -ne 0) { Aviso 'El respaldo fallo; puedes reintentarlo desde la app (Respaldos).' }
} else {
    Aviso 'Respaldo omitido.'
}

# ---------------------------------------------------------------------------
Write-Host ''
Write-Host '============================================================' -ForegroundColor Green
Write-Host ' Instalacion terminada' -ForegroundColor Green
Write-Host '============================================================' -ForegroundColor Green
Write-Host ''
Write-Host '  Abrir:      http://localhost/laboratorio-cito-biop/public/login'
Write-Host '  Usuario:    admin@laboratorio.local'
Write-Host '  Clave:      cambiar123'
Write-Host ''
Write-Host '  Siguientes pasos:'
Write-Host '   1. Cambiar la clave del administrador (Usuarios -> Editar).'
Write-Host '   2. Crear los usuarios operadores (Usuarios -> Nuevo usuario).'
Write-Host '   3. Programar el respaldo diario (ver docs/INSTALACION.md).'
Write-Host '   4. Si hay datos del laboratorio: seguir docs/INSTALACION.md, seccion Legado.'
Write-Host ''
Write-Host '  Tests:      php artisan test    (usa lapt_test, no la base operativa)'
Write-Host '  Respaldo:   scripts\backup_db.ps1'
Write-Host ''
