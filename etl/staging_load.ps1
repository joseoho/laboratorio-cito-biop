<#
.SYNOPSIS
    Carga los CSV del export de Access en las tablas legacy_* de MySQL (lapt_staging).

.DESCRIPTION
    - TRUNCATE + LOAD DATA LOCAL INFILE (rapido y transaccional por tabla).
    - Verifica filas cargadas contra el manifest del export.
    - No toca ninguna base operativa: solo lapt_staging.

.NOTES
    Ejecutar:
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File etl/staging_load.ps1
#>
[CmdletBinding()]
param(
    [string]$ManifestPath,
    [string]$MysqlExe   = 'C:\xampp\mysql\bin\mysql.exe',
    [string]$User       = 'root',
    [string]$Password   = '',
    [string]$Database   = 'lapt_staging'
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $ManifestPath) { $ManifestPath = Join-Path $scriptDir 'out\export_manifest.json' }

if (-not (Test-Path -LiteralPath $ManifestPath)) { throw "No existe el manifest: $ManifestPath. Ejecuta access_export.ps1 primero." }
if (-not (Test-Path -LiteralPath $MysqlExe))     { throw "No existe mysql.exe en: $MysqlExe" }

$manifest = Get-Content -LiteralPath $ManifestPath -Raw -Encoding UTF8 | ConvertFrom-Json
$outDir   = Split-Path -Parent $ManifestPath
$sqlPath  = Join-Path $outDir 'load_staging.sql'

$colMapPath = Join-Path (Join-Path $scriptDir 'schema') 'column_map.json'
if (-not (Test-Path -LiteralPath $colMapPath)) { throw "Falta el mapa de columnas: $colMapPath" }
$colMap = Get-Content -LiteralPath $colMapPath -Raw -Encoding UTF8 | ConvertFrom-Json

# --- Generar load_staging.sql ---------------------------------------------------
$sb = New-Object System.Text.StringBuilder
[void]$sb.AppendLine('SET NAMES utf8mb4;')
[void]$sb.AppendLine('SET FOREIGN_KEY_CHECKS = 0;')
[void]$sb.AppendLine('SET UNIQUE_CHECKS = 0;')

foreach ($t in $manifest.tables) {
    $file = Join-Path (Join-Path $outDir 'csv') $t.file
    if (-not (Test-Path -LiteralPath $file)) { throw "Falta el CSV: $file" }

    # Mapeo posicional Access -> staging (el orden del CSV es el orden del esquema Access)
    $stagingCols = @($colMap.($t.table))
    $accessCols  = @($t.columns)
    if ($stagingCols.Count -ne $accessCols.Count) {
        throw ("Mapa de columnas inconsistente en {0}: access={1} staging={2}" -f $t.table, $accessCols.Count, $stagingCols.Count)
    }

    # MySQL en Windows: barras normales; comillas simples escapadas por si acaso
    $path = ($file -replace '\\', '/') -replace "'", "''"
    $cols = @($stagingCols | ForEach-Object { '`' + $_ + '`' }) -join ','

    [void]$sb.AppendLine("TRUNCATE TABLE ``$($t.table)``;")
    [void]$sb.AppendLine("LOAD DATA LOCAL INFILE '$path'")
    [void]$sb.AppendLine("INTO TABLE ``$($t.table)``")
    [void]$sb.AppendLine("CHARACTER SET utf8mb4")
    [void]$sb.AppendLine("FIELDS TERMINATED BY ',' ENCLOSED BY '""' ESCAPED BY '\\'")
    [void]$sb.AppendLine("LINES TERMINATED BY '\n'")
    [void]$sb.AppendLine("($cols);")
}

[void]$sb.AppendLine('SET UNIQUE_CHECKS = 1;')
[void]$sb.AppendLine('SET FOREIGN_KEY_CHECKS = 1;')
Set-Content -LiteralPath $sqlPath -Value $sb.ToString() -Encoding ASCII

Write-Host ("[1/3] Script de carga generado: {0}" -f $sqlPath)

# --- Ejecutar la carga -----------------------------------------------------------
$auth = "-u $User"
if ($Password) { $auth += " -p$Password" }

$logPath = Join-Path $outDir 'load_staging.log'
$cmdPath = Join-Path $outDir 'run_load.cmd'
@"
@echo off
"$MysqlExe" --local-infile=1 --default-character-set=utf8mb4 $auth $Database < "$sqlPath" > "$logPath" 2>&1
"@ | Set-Content -LiteralPath $cmdPath -Encoding ASCII

$sw = [Diagnostics.Stopwatch]::StartNew()
& cmd.exe /c $cmdPath
$code = $LASTEXITCODE
$sw.Stop()

if ($code -ne 0) {
    if (Test-Path -LiteralPath $logPath) { Write-Host (Get-Content -LiteralPath $logPath -Raw -Encoding UTF8) }
    throw "La carga fallo (exit $code). Ver: $logPath"
}
Write-Host ("[2/3] Carga completada en {0:N1}s" -f $sw.Elapsed.TotalSeconds)

# --- Verificacion de conteos -----------------------------------------------------
$verifyParts = @()
$i = 0
foreach ($t in $manifest.tables) {
    $i++
    $verifyParts += "SELECT $i, '$($t.table)', CAST(COUNT(*) AS CHAR) FROM $($t.table)"
}
$verifySql = ($verifyParts -join ' UNION ALL ') + ';'
$verifyPath = Join-Path $outDir '_verify.sql'
Set-Content -LiteralPath $verifyPath -Value ("USE $Database;`n" + $verifySql) -Encoding ASCII

$verifyOut = Join-Path $outDir '_verify.tsv'
$verifyCmd = Join-Path $outDir 'run_verify.cmd'
@"
@echo off
"$MysqlExe" -B -N $auth $Database < "$verifyPath" > "$verifyOut" 2>&1
"@ | Set-Content -LiteralPath $verifyCmd -Encoding ASCII

& cmd.exe /c $verifyCmd
if ($LASTEXITCODE -ne 0) {
    if (Test-Path -LiteralPath $verifyOut) { Write-Host (Get-Content -LiteralPath $verifyOut -Raw -Encoding UTF8) }
    throw "Fallo la verificacion de conteos (exit $LASTEXITCODE)"
}

$loaded = @{}
foreach ($line in (Get-Content -LiteralPath $verifyOut -Encoding UTF8)) {
    if ([string]::IsNullOrWhiteSpace($line)) { continue }
    $parts = $line -split "`t"
    if ($parts.Count -ge 3) { $loaded[$parts[1]] = [int]$parts[2] }
}

$ok = 0; $bad = 0
Write-Host "[3/3] Conciliacion de conteos (Access -> MySQL):"
foreach ($t in $manifest.tables) {
    $exp = [int]$t.rows
    $got = if ($loaded.ContainsKey($t.table)) { $loaded[$t.table] } else { -1 }
    if ($exp -eq $got) {
        $ok++
        Write-Host ("      OK   {0,-26} {1,7}" -f $t.table, $got)
    } else {
        $bad++
        Write-Host ("      DIFF {0,-26} access={1,7}  mysql={2,7}" -f $t.table, $exp, $got)
    }
}
Write-Host ("      -> {0} tablas OK, {1} con diferencia" -f $ok, $bad)

if ($bad -gt 0) { exit 2 }
