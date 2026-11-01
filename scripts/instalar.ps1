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
      8. (opcional) restaura datos y PDFs desde un kit de traslado
      9. (opcional) crea la base de pruebas lapt_test
     10. (opcional) genera el primer respaldo

    Uso:
        instalar.bat                                    (interactivo)
        instalar.bat -Check                             (solo verifica requisitos)
        instalar.bat -Si                                (sin preguntas)
        instalar.bat -Si -Kit "D:\kit\laboratorio-kit-XXXX.zip"
                                                        (instala y carga los datos)
        instalar.bat -DbName lapt_core -DbUser root -DbPass "clave"
        instalar.bat -Kit "...zip" -UsarEnvDelKit       (tambien copia el .env del kit)

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
    [string]$MysqlBin = 'C:\xampp\mysql\bin',
    [string]$Kit = '',
    [switch]$UsarEnvDelKit,
    [string]$Dominio = 'laboratorio-cito-biop.test',
    [switch]$SinVhost,
    [switch]$ConAutoarranque,
    [switch]$AlArrancar
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$mysql = Join-Path $MysqlBin 'mysql.exe'
$total = 12
$dataInfo = 'Base vacia: sin datos del laboratorio'
$urlFinal = 'http://localhost/laboratorio-cito-biop/public/login'

# Registro en archivo: si la ventana se cierra o algo falla, queda el detalle.
$script:logFile = Join-Path $root ('storage\logs\instalacion-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.log')
$script:transcript = $false
try {
    New-Item -ItemType Directory -Path (Split-Path $script:logFile) -Force | Out-Null
    Start-Transcript -Path $script:logFile -Force | Out-Null
    $script:transcript = $true
} catch {
    $script:transcript = $false
}

trap {
    if ($script:transcript) { try { Stop-Transcript | Out-Null } catch { } }
    Write-Host ''
    Write-Host (' ERROR: ' + $_.Exception.Message) -ForegroundColor Red
    Write-Host (' Log:   ' + $script:logFile)
    exit 1
}

function Paso([int]$n, [string]$texto) {
    Write-Host ''
    Write-Host ("[{0}/{1}] {2}" -f $n, $script:total, $texto) -ForegroundColor Cyan
}

function Ok([string]$texto) { Write-Host ("      OK  " + $texto) -ForegroundColor Green }
function Aviso([string]$texto) { Write-Host ("      !   " + $texto) -ForegroundColor Yellow }

function Ejecutar([string]$exe, [string[]]$argumentos, [string]$que) {
    & $exe @argumentos
    if ($LASTEXITCODE -ne 0) {
        throw ("Fallo " + $que + " (codigo " + $LASTEXITCODE + ").")
    }
}

function Test-Admin {
    $id = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($id)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

# ---------------------------------------------------------------------------
Paso 1 'Verificando requisitos'

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
    Write-Host (' Log: ' + $script:logFile)
    if ($script:transcript) { try { Stop-Transcript | Out-Null } catch { } }
    exit 0
}

# ---------------------------------------------------------------------------
Paso 2 'Preparando .env'
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
        Aviso (".env apuntaba a la base '" + $envDb.Trim() + "'; se cambia a '" + $DbName + "'.")
        Copy-Item '.env' '.env.bak' -Force
        $contenido = Get-Content '.env' -Raw
        $contenido = $contenido -replace '(?m)^DB_DATABASE=.*$', ("DB_DATABASE=" + $DbName)
        Set-Content '.env' -Value $contenido -Encoding ASCII -NoNewline
        Ok ("DB_DATABASE actualizado (respaldo en .env.bak)")
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
Paso 3 ("Creando la base de datos '" + $DbName + "'")
$sql = "CREATE DATABASE IF NOT EXISTS ``$DbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
& $mysql @mysqlArgs -e $sql
if ($LASTEXITCODE -ne 0) { throw "No se pudo crear la base $DbName." }
Ok ("Base '" + $DbName + "' lista")

# ---------------------------------------------------------------------------
Paso 4 'Instalando dependencias'
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
Paso 5 'Aplicando migraciones'
Push-Location $root
try {
    Ejecutar $PhpExe @('artisan', 'migrate', '--force') 'migrate'
    Ok 'Esquema al dia'
}
finally {
    Pop-Location
}

# ---------------------------------------------------------------------------
Paso 6 'Creando el usuario administrador'
Push-Location $root
try {
    Ejecutar $PhpExe @('artisan', 'db:seed', '--force') 'db:seed'
    Ok 'Usuario inicial listo'
}
finally {
    Pop-Location
}

# ---------------------------------------------------------------------------
Paso 7 'Preparando carpetas'
foreach ($dir in @('storage\backups', 'storage\app\private\reports', 'storage\logs')) {
    $path = Join-Path $root $dir
    if (-not (Test-Path $path)) { New-Item -ItemType Directory -Path $path -Force | Out-Null }
}
Ok 'storage\backups y storage\app\private\reports'

# ---------------------------------------------------------------------------
Paso 8 'Restaurando datos del kit (opcional)'

if ($Kit) {
    if (-not (Test-Path $Kit)) { throw "No se encontro el kit: $Kit" }

    $extract = $Kit
    $tempExtract = $null

    if ((Get-Item $Kit).PSIsContainer -eq $false) {
        $tempExtract = Join-Path $env:TEMP ("kit_" + [guid]::NewGuid().ToString('N'))
        Expand-Archive -Path $Kit -DestinationPath $tempExtract -Force
        $extract = $tempExtract
        Ok 'Kit descomprimido'
    }

    $sqlFile = Get-ChildItem $extract -Recurse -Filter *.sql.gz -File | Select-Object -First 1
    if (-not $sqlFile) { throw 'El kit no contiene base\*.sql.gz' }

    $restore = Join-Path $PSScriptRoot 'restore_db.ps1'
    if (-not (Test-Path $restore)) { throw 'No se encontro scripts/restore_db.ps1' }

    $restoreArgs = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $restore,
                     '-File', $sqlFile.FullName, '-Database', $DbName, '-User', $DbUser, '-Force')
    if ($DbPass) { $restoreArgs += @('-Password', $DbPass) }

    & powershell.exe @restoreArgs
    if ($LASTEXITCODE -ne 0) { throw 'Fallo la restauracion de la base.' }

    $reportsSrc = Get-ChildItem $extract -Recurse -Directory -Filter 'reports' | Select-Object -First 1
    $pdfCount = 0
    if ($reportsSrc) {
        $reportsDst = Join-Path $root 'storage\app\private\reports'
        Get-ChildItem $reportsSrc.FullName -Filter *.pdf -File | ForEach-Object {
            Copy-Item $_.FullName -Destination $reportsDst -Force
            $pdfCount++
        }
    }
    Ok ("PDFs copiados: " + $pdfCount)

    if ($UsarEnvDelKit) {
        $kitEnv = Join-Path $extract '.env'
        if (Test-Path $kitEnv) {
            Copy-Item $kitEnv (Join-Path $root '.env') -Force
            $contenido = Get-Content (Join-Path $root '.env') -Raw
            $contenido = $contenido -replace '(?m)^DB_DATABASE=.*$', ("DB_DATABASE=" + $DbName)
            Set-Content (Join-Path $root '.env') -Value $contenido -Encoding ASCII -NoNewline
            Ok '.env del kit copiado (misma APP_KEY; DB_DATABASE ajustada)'
        } else {
            Aviso 'El kit no trae .env; se mantiene el generado.'
        }
    } else {
        Aviso 'Se mantiene el .env generado (las sesiones se reinician, los datos no se afectan).'
    }

    $dataInfo = 'Datos restaurados desde el kit: ' + $sqlFile.Name

    if ($tempExtract -and (Test-Path $tempExtract)) {
        Remove-Item $tempExtract -Recurse -Force -ErrorAction SilentlyContinue
    }
} else {
    Aviso 'Sin -Kit: la base queda vacia (solo esquema y usuario admin).'
}

# ---------------------------------------------------------------------------
Paso 9 'Base de pruebas (opcional)'
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
Paso 10 'Primer respaldo (opcional)'
$backup = ($Si -and $Kit -ne '')
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
Paso 11 ("Sitio local: http://" + $Dominio)

$esAdmin = Test-Admin
$hacerVhost = -not $SinVhost

if ($hacerVhost -and -not $Si) {
    $r = Read-Host '      Configurar el dominio local? (S/n)'
    if ($r -and $r.Trim().ToLower().StartsWith('n')) { $hacerVhost = $false }
}

if (-not $hacerVhost) {
    Aviso 'Dominio local omitido.'
} elseif (-not $esAdmin) {
    Aviso 'Requiere administrador. Ejecuta en una consola como administrador:'
    Write-Host ('      powershell -ExecutionPolicy Bypass -File "' + (Join-Path $PSScriptRoot 'setup-vhost.ps1') + '"')
} else {
    & powershell.exe -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'setup-vhost.ps1') -Domain $Dominio
    if ($LASTEXITCODE -eq 0) {
        $contenido = Get-Content (Join-Path $root '.env') -Raw
        $contenido = $contenido -replace '(?m)^APP_URL=.*$', ('APP_URL=http://' + $Dominio)
        Set-Content (Join-Path $root '.env') -Value $contenido -Encoding ASCII -NoNewline
        Ok ('APP_URL actualizado a http://' + $Dominio)
        $urlFinal = 'http://' + $Dominio + '/login'
    } else {
        Aviso 'El vhost no se pudo configurar.'
    }
}

# ---------------------------------------------------------------------------
Paso 12 'Arranque automatico de servicios (opcional)'

$auto = $ConAutoarranque -or $AlArrancar
if (-not $Si -and -not $auto) {
    $r = Read-Host '      Arrancar Apache y MySQL al iniciar sesion? (s/N)'
    if ($r -and $r.Trim().ToLower().StartsWith('s')) { $auto = $true }
}

if ($auto) {
    & powershell.exe -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'autostart.ps1') -Modo Login
    if ($LASTEXITCODE -eq 0) { Ok 'Arranque al iniciar sesion configurado' }

    if ($esAdmin) {
        $crearArranque = $AlArrancar
        if (-not $crearArranque -and -not $Si) {
            $r2 = Read-Host '      Tambien al arrancar Windows, sin iniciar sesion? (s/N)'
            if ($r2 -and $r2.Trim().ToLower().StartsWith('s')) { $crearArranque = $true }
        }

        if ($crearArranque) {
            & powershell.exe -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'autostart.ps1') -Modo Arranque
            if ($LASTEXITCODE -eq 0) { Ok 'Tarea al arrancar Windows creada (sobrevive a un corte de luz)' }
        }
    } else {
        Aviso 'Para arrancar sin iniciar sesion hace falta administrador (autostart.ps1 -Modo Arranque).'
    }
} else {
    Aviso 'Arranque automatico omitido.'
}

# ---------------------------------------------------------------------------
# Estado final de la base (para el resumen)
if (-not $Kit) {
    $authStr = ($mysqlArgs -join ' ')
    $conteo = (& cmd.exe /c "`"$mysql`" $authStr -N -e `"SELECT COUNT(*) FROM ``$DbName``.patients`" 2>nul" | Out-String).Trim()
    if ($conteo -match '^\d+$' -and [int]$conteo -gt 0) {
        $dataInfo = ('Base existente con ' + $conteo + ' pacientes')
    }
}

Write-Host ''
Write-Host '============================================================' -ForegroundColor Green
Write-Host ' Instalacion terminada' -ForegroundColor Green
Write-Host '============================================================' -ForegroundColor Green
Write-Host ''
Write-Host ("  Base de datos: " + $DbName)
Write-Host ("  Datos:         " + $dataInfo)
Write-Host ''
Write-Host ("  Abrir:      " + $urlFinal)
Write-Host '  Usuario:    admin@laboratorio.local'
Write-Host '  Clave:      cambiar123'
Write-Host ''
Write-Host '  Siguientes pasos:'
Write-Host '   1. Cambiar la clave del administrador (Usuarios -> Editar).'
Write-Host '   2. Crear los usuarios operadores (Usuarios -> Nuevo usuario).'
Write-Host '   3. Programar el respaldo diario (ver docs/INSTALACION.md).'
Write-Host ''
Write-Host '  Tests:      php artisan test    (usa lapt_test, no la base operativa)'
Write-Host '  Respaldo:   scripts\backup_db.ps1'
Write-Host ''
Write-Host ("  Log:        " + $script:logFile)
Write-Host ''

if ($script:transcript) { try { Stop-Transcript | Out-Null } catch { } }
