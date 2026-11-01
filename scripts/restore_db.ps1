<#
.SYNOPSIS
    Restaura un respaldo (.sql o .sql.gz) en una base MySQL/MariaDB.

.DESCRIPTION
    - Acepta el .sql.gz que genera backup_db.ps1 (lo descomprime solo).
    - Crea la base si no existe.
    - Pide confirmacion si la base destino ya tiene pacientes, porque
      mysqldump incluye DROP TABLE y REEMPLAZA el contenido.

    Uso:
        powershell -File scripts/restore_db.ps1 -File "C:\ruta\lapt_core-20261101-200000.sql.gz"
        powershell -File scripts/restore_db.ps1 -File "...\respaldo.sql.gz" -Database lapt_nueva -Force
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$File,
    [string]$Database = 'lapt_core',
    [string]$User = 'root',
    [string]$Password = '',
    [string]$MysqlBin = 'C:\xampp\mysql\bin',
    [switch]$Force
)

$ErrorActionPreference = 'Stop'

$mysql = Join-Path $MysqlBin 'mysql.exe'
if (-not (Test-Path $mysql)) { throw "No existe $mysql" }
if (-not (Test-Path $File)) { throw "No existe el respaldo: $File" }

$auth = @('-u', $User)
if ($Password) { $auth += "-p$Password" }
$authStr = ($auth -join ' ')

# --- Aviso si la base destino ya tiene datos --------------------------------
# Se consulta con cmd y se silencia el error: si la base no existe, MySQL
# escribe en stderr y con ErrorActionPreference=Stop cortaria el script.
$exists = $false
$patients = 0

$probeSql = "SELECT COUNT(*) FROM ``$Database``.patients"
$probe = & cmd.exe /c "`"$mysql`" $authStr -N -e `"$probeSql`" 2>nul"
$probeText = ($probe | Out-String).Trim()

if ($probeText -match '^\d+$') {
    $exists = $true
    $patients = [int]$probeText
}

if ($exists -and $patients -gt 0 -and -not $Force) {
    Write-Host ""
    Write-Host ("  ATENCION: la base '{0}' ya tiene {1} pacientes." -f $Database, $patients) -ForegroundColor Yellow
    Write-Host "  La restauracion REEMPLAZA su contenido." -ForegroundColor Yellow
    $r = Read-Host "  Escribi SI para continuar"
    if ($r -ne 'SI') { Write-Host 'Cancelado.'; exit 1 }
}

# --- Descomprimir si hace falta ---------------------------------------------
$sql = $File
$tmp = $null

if ($File.ToLower().EndsWith('.gz')) {
    $tmp = Join-Path $env:TEMP ("restore_" + [guid]::NewGuid().ToString('N') + ".sql")
    $in = [IO.File]::OpenRead($File)
    $out = [IO.File]::Create($tmp)
    $gz = New-Object IO.Compression.GZipStream($in, [IO.Compression.CompressionMode]::Decompress)
    try {
        $gz.CopyTo($out)
    } finally {
        $gz.Dispose(); $out.Dispose(); $in.Dispose()
    }
    $sql = $tmp
}

try {
    & $mysql @auth -e "CREATE DATABASE IF NOT EXISTS ``$Database`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    if ($LASTEXITCODE -ne 0) { throw "No se pudo crear la base $Database" }

    $sw = [Diagnostics.Stopwatch]::StartNew()
    & cmd.exe /c "`"$mysql`" $($auth -join ' ') $Database < `"$sql`""
    if ($LASTEXITCODE -ne 0) { throw "Fallo la importacion en $Database" }
    $sw.Stop()

    $counts = & cmd.exe /c "`"$mysql`" $($auth -join ' ') -N -e `"SELECT (SELECT COUNT(*) FROM ``$Database``.patients), (SELECT COUNT(*) FROM ``$Database``.cases), (SELECT COUNT(*) FROM ``$Database``.users);`""
    $p = ($counts | Out-String).Trim() -split "`t"

    Write-Host ("OK  {0} restaurada en {1:N1}s" -f $Database, $sw.Elapsed.TotalSeconds) -ForegroundColor Green
    Write-Host ("    pacientes={0}  estudios={1}  usuarios={2}" -f $p[0], $p[1], $p[2])
}
finally {
    if ($tmp -and (Test-Path $tmp)) { Remove-Item $tmp -Force -ErrorAction SilentlyContinue }
}
