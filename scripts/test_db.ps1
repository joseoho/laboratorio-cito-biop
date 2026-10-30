<#
.SYNOPSIS
    Crea/actualiza la base de pruebas (lapt_test) como copia de la operativa.

.DESCRIPTION
    Los tests necesitan datos reales (pacientes y estudios existentes) pero NO deben
    tocar la base de produccion. Este script clona lapt_core en lapt_test.

    Ejecutar:
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts/test_db.ps1

    Despues, `php artisan test` usa lapt_test (phpunit.xml define DB_DATABASE).
#>
[CmdletBinding()]
param(
    [string]$Source    = 'lapt_core',
    [string]$Target    = 'lapt_test',
    [string]$User      = 'root',
    [string]$Password  = '',
    [string]$MysqlBin  = 'C:\xampp\mysql\bin'
)

$ErrorActionPreference = 'Stop'

$mysqldump = Join-Path $MysqlBin 'mysqldump.exe'
$mysql     = Join-Path $MysqlBin 'mysql.exe'

if (-not (Test-Path $mysqldump)) { throw "No existe $mysqldump" }
if (-not (Test-Path $mysql))     { throw "No existe $mysql" }

$auth = "-u $User"
if ($Password) { $auth += " -p$Password" }

# La base destino queda aislada: se recrea completa en cada corrida.
$create = "DROP DATABASE IF EXISTS ``$Target``; CREATE DATABASE ``$Target`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
& cmd.exe /c "`"$mysql`" $auth -e `"$create`""
if ($LASTEXITCODE -ne 0) { throw "No se pudo crear $Target" }

$dump = Join-Path $env:TEMP "lapt_test_$([guid]::NewGuid().ToString('N')).sql"

try {
    $sw = [Diagnostics.Stopwatch]::StartNew()

    # Volcado consistente de la operativa (sin bloquearla).
    & cmd.exe /c "`"$mysqldump`" --single-transaction --quick --routines --events --no-tablespaces --default-character-set=utf8mb4 $auth $Source > `"$dump`""
    if ($LASTEXITCODE -ne 0) { throw "Fallo mysqldump de $Source" }

    & cmd.exe /c "`"$mysql`" $auth $Target < `"$dump`""
    if ($LASTEXITCODE -ne 0) { throw "Fallo la importacion en $Target" }

    $sw.Stop()

    $counts = & cmd.exe /c "`"$mysql`" $auth -N -e `"SELECT (SELECT COUNT(*) FROM $Target.patients), (SELECT COUNT(*) FROM $Target.cases), (SELECT COUNT(*) FROM $Target.users);`""
    $parts = ($counts | Out-String).Trim() -split "`t"

    Write-Host ("OK  {0} recreada desde {1} en {2:N1}s" -f $Target, $Source, $sw.Elapsed.TotalSeconds)
    Write-Host ("    pacientes={0}  estudios={1}  usuarios={2}" -f $parts[0], $parts[1], $parts[2])
    Write-Host "    Los tests ya usan esta base (phpunit.xml define DB_DATABASE)."
}
finally {
    if (Test-Path $dump) { Remove-Item $dump -Force -ErrorAction SilentlyContinue }
}
