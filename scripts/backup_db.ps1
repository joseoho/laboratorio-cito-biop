<#
.SYNOPSIS
    Respaldo comprimido de la base operativa (lapt_core) con rotación.

.DESCRIPTION
    - mysqldump consistente (--single-transaction), sin bloquear la operación.
    - Comprime a .sql.gz en storage/backups.
    - Conserva los últimos N respaldos (por defecto 14).
    - Verifica que el archivo no quede vacío.

.NOTES
    Ejecutar:
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts/backup_db.ps1

    Programar en el Programador de tareas de Windows (diario, 20:00):
        schtasks /Create /TN "Laboratorio backup" /SC DAILY /ST 20:00 /TR "powershell.exe -NoProfile -ExecutionPolicy Bypass -File C:\xampp\htdocs\laboratorio-cito-biop\scripts\backup_db.ps1"
#>
[CmdletBinding()]
param(
    [string]$MysqlDump = 'C:\xampp\mysql\bin\mysqldump.exe',
    [string]$Database  = 'lapt_core',
    [string]$User      = 'root',
    [string]$Password  = '',
    [string]$OutDir,
    [int]$Keep         = 14
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $OutDir) { $OutDir = Join-Path (Split-Path -Parent $scriptDir) 'storage\backups' }
if (-not (Test-Path -LiteralPath $OutDir)) { New-Item -ItemType Directory -Path $OutDir -Force | Out-Null }

if (-not (Test-Path -LiteralPath $MysqlDump)) { throw "No existe mysqldump: $MysqlDump" }

$stamp  = Get-Date -Format 'yyyyMMdd-HHmmss'
$sql    = Join-Path $OutDir "lapt_core-$stamp.sql"
$gz     = "$sql.gz"
$runCmd = Join-Path $env:TEMP "_lapt_dump_$stamp.cmd"
$errLog = Join-Path $env:TEMP "_lapt_dump_$stamp.err"

$auth = "-u $User"
if ($Password) { $auth += " -p$Password" }

@"
@echo off
"$MysqlDump" --single-transaction --quick --routines --events --no-tablespaces --default-character-set=utf8mb4 $auth $Database > "$sql" 2> "$errLog"
"@ | Set-Content -LiteralPath $runCmd -Encoding ASCII

$sw = [Diagnostics.Stopwatch]::StartNew()
& cmd.exe /c $runCmd
$code = $LASTEXITCODE
$sw.Stop()

if ($code -ne 0) {
    $detail = if (Test-Path $errLog) { Get-Content $errLog -Raw } else { '' }
    throw "mysqldump falló (exit $code). $detail"
}

$sqlFile = Get-Item -LiteralPath $sql
if ($sqlFile.Length -lt 1024) { throw "El respaldo salió vacío: $sql" }

# Comprimir y borrar el .sql
$in  = [System.IO.File]::OpenRead($sql)
$out = [System.IO.File]::Create($gz)
$gzs = New-Object System.IO.Compression.GZipStream($out, [System.IO.Compression.CompressionLevel]::Optimal)
$in.CopyTo($gzs)
$gzs.Dispose(); $out.Dispose(); $in.Dispose()
Remove-Item -LiteralPath $sql -Force

$gzFile = Get-Item -LiteralPath $gz

# Rotación
$old = Get-ChildItem -LiteralPath $OutDir -Filter 'lapt_core-*.sql.gz' |
    Sort-Object LastWriteTime -Descending | Select-Object -Skip $Keep
foreach ($f in $old) { Remove-Item -LiteralPath $f.FullName -Force }

Write-Host ("OK  {0}  ({1:N1} MB, {2:N1}s)  |  respaldos conservados: {3}" -f `
    $gzFile.Name, ($gzFile.Length / 1MB), $sw.Elapsed.TotalSeconds,
    (Get-ChildItem -LiteralPath $OutDir -Filter 'lapt_core-*.sql.gz').Count)

Remove-Item -LiteralPath $runCmd, $errLog -Force -ErrorAction SilentlyContinue
