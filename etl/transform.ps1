<#
.SYNOPSIS
    Ejecuta el transform legacy_* -> lapt_core y genera el reporte de verificacion.

.DESCRIPTION
    1) 001_core_schema.sql : recrea el modelo clinico destino
    2) 002_transform.sql   : carga pacientes, estudios, informes, asignaciones y cuarentena
    3) 003_verify.sql      : conciliacion (no perdida, numeracion, estado, cuarentena)

.NOTES
    Ejecutar:
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File etl/transform.ps1
#>
[CmdletBinding()]
param(
    [string]$MysqlExe = 'C:\xampp\mysql\bin\mysql.exe',
    [string]$User     = 'root',
    [string]$Password = ''
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$sqlDir    = Join-Path $scriptDir 'transform'
$outDir    = Join-Path $scriptDir 'out'
if (-not (Test-Path -LiteralPath $outDir)) { New-Item -ItemType Directory -Path $outDir -Force | Out-Null }

$auth = "-u $User"
if ($Password) { $auth += " -p$Password" }

function Invoke-SqlFile {
    param([string]$Path, [string]$LogPath)
    $cmdPath = [System.IO.Path]::ChangeExtension($LogPath, '.cmd')
    @"
@echo off
"$MysqlExe" --default-character-set=utf8mb4 -B -N $auth < "$Path" > "$LogPath" 2>&1
"@ | Set-Content -LiteralPath $cmdPath -Encoding ASCII
    & cmd.exe /c $cmdPath
    if ($LASTEXITCODE -ne 0) {
        if (Test-Path -LiteralPath $LogPath) { Write-Host (Get-Content -LiteralPath $LogPath -Raw -Encoding UTF8) }
        throw ("Fallo {0} (exit {1})" -f (Split-Path -Leaf $Path), $LASTEXITCODE)
    }
}

# --- 1. Esquema ----------------------------------------------------------------
Write-Host '[1/3] Recreando modelo destino (lapt_core)...'
Invoke-SqlFile -Path (Join-Path $sqlDir '001_core_schema.sql') -LogPath (Join-Path $outDir 'transform_001.log')

# --- 2. Transform ---------------------------------------------------------------
Write-Host '[2/3] Transformando datos legacy...'
$sw = [Diagnostics.Stopwatch]::StartNew()
Invoke-SqlFile -Path (Join-Path $sqlDir '002_transform.sql') -LogPath (Join-Path $outDir 'transform_002.log')
$sw.Stop()
Write-Host ("      completado en {0:N1}s" -f $sw.Elapsed.TotalSeconds)

# --- 3. Verificacion -------------------------------------------------------------
Write-Host '[3/3] Verificando...'
$tsvPath = Join-Path $outDir 'transform_verify.tsv'
Invoke-SqlFile -Path (Join-Path $sqlDir '003_verify.sql') -LogPath $tsvPath

$sections = [ordered]@{}
$flat = @{}
foreach ($line in (Get-Content -LiteralPath $tsvPath -Encoding UTF8)) {
    if ([string]::IsNullOrWhiteSpace($line)) { continue }
    $parts = $line -split "`t"
    if ($parts.Count -lt 3) { continue }
    $sec = $parts[0].Trim(); $met = $parts[1].Trim(); $val = $parts[2].Trim()
    if (-not $sections.Contains($sec)) { $sections[$sec] = New-Object System.Collections.ArrayList }
    [void]$sections[$sec].Add([pscustomobject]@{ metrica = $met; valor = $val })
    $flat[$met] = $val
}

function V([string]$k) { if ($flat.ContainsKey($k) -and $flat[$k] -ne '') { return [double]$flat[$k] } return 0 }
function Fmt([string]$v) { if ($v -match '^\d+$') { return ([double]$v).ToString('N0') } return $v }

# --- Conciliaciones duras (PASS / FAIL) ------------------------------------------
$checks = @()
function Add-Check([string]$name, $left, $right) {
    $ok = ([double]$left -eq [double]$right)
    $script:checks += [pscustomobject]@{ check = $name; core = $left; staging = $right; ok = $ok }
}

Add-Check 'Pacientes staging = core'                (V 'pacientes_core')        (V 'pacientes_staging')
Add-Check 'Biopsias = en core + huerfanas'          ((V 'biopsias_en_core') + (V 'biopsias_huerfanas')) (V 'biopsias_staging')
Add-Check 'Citologias = en core + huerfanas'        ((V 'citologias_en_core') + (V 'citologias_huerfanas')) (V 'citologias_staging')
Add-Check 'Suma legacy_id = suma staging'           (V 'suma_legacy_id_core')   (V 'suma_id_paciente_staging')
Add-Check 'Informes huerfanos = 0'                  (V 'informes_sin_caso')     0
Add-Check 'Casos sin informe = 0'                   (V 'casos_sin_informe')     0
Add-Check 'Biopsias con codigo + sin codigo = core'  ((V 'biopsias_con_code') + (V 'biopsias_sin_code'))   (V 'biopsias_en_core')
Add-Check 'Citologias con codigo + sin codigo = core' ((V 'citologias_con_code') + (V 'citologias_sin_code')) (V 'citologias_en_core')

$failed = @($checks | Where-Object { -not $_.ok })

# --- Reporte ----------------------------------------------------------------------
$md = New-Object System.Text.StringBuilder
[void]$md.AppendLine('# Transform Access -> modelo clinico (lapt_core)')
[void]$md.AppendLine('')
[void]$md.AppendLine(('Generado: **{0}** | Destino: `lapt_core` | Fuente: `lapt_staging`' -f (Get-Date).ToString('yyyy-MM-dd HH:mm')))
[void]$md.AppendLine('')
[void]$md.AppendLine('## Conciliaciones (todas deben ser OK)')
[void]$md.AppendLine('')
[void]$md.AppendLine('| Control | Core | Staging | Estado |')
[void]$md.AppendLine('|---|---:|---:|:---:|')
foreach ($c in $checks) {
    $state = if ($c.ok) { 'OK' } else { '**FALLA**' }
    [void]$md.AppendLine(('| {0} | {1} | {2} | {3} |' -f $c.check, (Fmt ([string]$c.core)), (Fmt ([string]$c.staging)), $state))
}
[void]$md.AppendLine('')

foreach ($sec in $sections.Keys) {
    [void]$md.AppendLine(('## {0}' -f $sec))
    [void]$md.AppendLine('')
    [void]$md.AppendLine('| Metrica | Valor |')
    [void]$md.AppendLine('|---|---:|')
    foreach ($row in $sections[$sec]) { [void]$md.AppendLine(('| {0} | {1} |' -f $row.metrica, (Fmt $row.valor))) }
    [void]$md.AppendLine('')
}

$mdPath = Join-Path $outDir 'transform_report.md'
Set-Content -LiteralPath $mdPath -Value $md.ToString() -Encoding UTF8

Write-Host ''
foreach ($c in $checks) {
    $state = if ($c.ok) { 'OK  ' } else { 'FALLA' }
    Write-Host ("  {0} {1,-42} core={2,10} staging={3,10}" -f $state, $c.check, (Fmt ([string]$c.core)), (Fmt ([string]$c.staging)))
}
Write-Host ''
Write-Host ("Reporte: {0}" -f $mdPath)
if ($failed.Count -gt 0) { exit 2 }
