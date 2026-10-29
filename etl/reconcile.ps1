<#
.SYNOPSIS
    Ejecuta etl/reconcile.sql y genera el reporte de reconciliacion en Markdown + JSON.

.DESCRIPTION
    Convierte la salida TSV (seccion / metrica / valor) en un informe legible
    con tablas por seccion y KPIs calculados. Es la evidencia para decidir
    el modelo destino y los buckets de cuarentena.

.NOTES
    Ejecutar:
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File etl/reconcile.ps1
#>
[CmdletBinding()]
param(
    [string]$SqlPath,
    [string]$OutDir,
    [string]$MysqlExe = 'C:\xampp\mysql\bin\mysql.exe',
    [string]$User     = 'root',
    [string]$Password = '',
    [string]$Database = 'lapt_staging'
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $SqlPath) { $SqlPath = Join-Path $scriptDir 'reconcile.sql' }
if (-not $OutDir)  { $OutDir  = Join-Path $scriptDir 'out' }

if (-not (Test-Path -LiteralPath $SqlPath)) { throw "No existe: $SqlPath" }
if (-not (Test-Path -LiteralPath $OutDir))  { New-Item -ItemType Directory -Path $OutDir -Force | Out-Null }

$auth = "-u $User"
if ($Password) { $auth += " -p$Password" }

$tsvPath = Join-Path $OutDir 'reconciliation.tsv'
$runCmd  = Join-Path $OutDir 'run_reconcile.cmd'
@"
@echo off
"$MysqlExe" -B -N $auth $Database < "$SqlPath" > "$tsvPath" 2>&1
"@ | Set-Content -LiteralPath $runCmd -Encoding ASCII

& cmd.exe /c $runCmd
if ($LASTEXITCODE -ne 0) {
    if (Test-Path -LiteralPath $tsvPath) { Write-Host (Get-Content -LiteralPath $tsvPath -Raw -Encoding UTF8) }
    throw "Fallo reconcile.sql (exit $LASTEXITCODE)"
}

# --- Parsear TSV ----------------------------------------------------------------
$sections = [ordered]@{}
$flat = [ordered]@{}

foreach ($line in (Get-Content -LiteralPath $tsvPath -Encoding UTF8)) {
    if ([string]::IsNullOrWhiteSpace($line)) { continue }
    $parts = $line -split "`t"
    if ($parts.Count -lt 3) { continue }
    $sec = $parts[0].Trim(); $met = $parts[1].Trim(); $val = $parts[2].Trim()
    if (-not $sections.Contains($sec)) { $sections[$sec] = New-Object System.Collections.ArrayList }
    [void]$sections[$sec].Add([pscustomobject]@{ metrica = $met; valor = $val })
    $flat[$met] = $val
}

function V([string]$key) {
    if ($flat.Contains($key) -and $flat[$key] -ne '') { return [double]$flat[$key] }
    return 0
}
function Pct($num, $den) {
    if ($den -eq 0) { return 'n/d' }
    return ('{0:N2} %' -f (100 * $num / $den))
}
function Fmt([string]$v) {
    if ($v -match '^\d+$') { return ([double]$v).ToString('N0') }
    return $v
}

# --- KPIs calculados ------------------------------------------------------------
$pac        = V 'pacientes'
$bio        = V 'biopsias'
$cit        = V 'citologias'
$completados = V 'pacientes_completados'
$sinCompl   = V 'pacientes_sin_completar'
$sinNumero  = V 'sin_ninguno'
$ncDupFilas = V 'nc_dup_filas'
$nbDupFilas = V 'nb_dup_filas'
$bioSinPat  = V 'bio_sin_patologo1'
$citSinPat  = V 'cit_sin_patologo1'
$facturas   = V 'facturas'
$factSuma   = $flat['facturas_suma_total']

$kpis = @(
    [pscustomobject]@{ kpi = 'Pacientes';                        valor = Fmt $flat['pacientes'];                  nota = 'un registro por persona = por estudio en el modelo Access' }
    [pscustomobject]@{ kpi = 'Estudios con informe';             valor = Fmt ([double]($bio + $cit));             nota = 'biopsias + citologias' }
    [pscustomobject]@{ kpi = 'Completados (fecha_comp)';         valor = (Pct $completados ($completados + $sinCompl)); nota = 'el "status" real es la fecha de completacion' }
    [pscustomobject]@{ kpi = 'Sin numero de estudio';            valor = (Pct $sinNumero $pac);                   nota = 'no pueden pasar la validacion nueva' }
    [pscustomobject]@{ kpi = 'Citologias con numero duplicado';  valor = (Pct $ncDupFilas $pac);                  nota = 'la regla unique actual las rechaza' }
    [pscustomobject]@{ kpi = 'Biopsias con numero duplicado';    valor = (Pct $nbDupFilas $pac);                  nota = 'idem' }
    [pscustomobject]@{ kpi = 'Biopsias sin patologo 1';          valor = (Pct $bioSinPat $bio);                   nota = 'no pueden firmarse con la regla nueva' }
    [pscustomobject]@{ kpi = 'Citologias sin patologo 1';        valor = (Pct $citSinPat $cit);                   nota = 'idem' }
    [pscustomobject]@{ kpi = 'Facturas historicas';              valor = (Fmt $facturas);                         nota = ('modulo no migrado; suma ' + $factSuma) }
)

# --- Markdown -------------------------------------------------------------------
$md = New-Object System.Text.StringBuilder
[void]$md.AppendLine('# Reconciliacion ETL | Laboratorio de Anatomia Patologica')
[void]$md.AppendLine('')
[void]$md.AppendLine(('Generado: **{0}** | Fuente: `prog_lapt.mdb` -> `LAPT.mdb` | Destino: `{1}`' -f (Get-Date).ToString('yyyy-MM-dd HH:mm'), $Database))
[void]$md.AppendLine('')
[void]$md.AppendLine('## KPIs de decision')
[void]$md.AppendLine('')
[void]$md.AppendLine('| Indicador | Valor | Lectura |')
[void]$md.AppendLine('|---|---:|---|')
foreach ($k in $kpis) { [void]$md.AppendLine(('| {0} | {1} | {2} |' -f $k.kpi, $k.valor, $k.nota)) }
[void]$md.AppendLine('')

foreach ($sec in $sections.Keys) {
    [void]$md.AppendLine(('## {0}' -f $sec))
    [void]$md.AppendLine('')
    [void]$md.AppendLine('| Metrica | Valor |')
    [void]$md.AppendLine('|---|---:|')
    foreach ($row in $sections[$sec]) { [void]$md.AppendLine(('| {0} | {1} |' -f $row.metrica, (Fmt $row.valor))) }
    [void]$md.AppendLine('')
}

$mdPath = Join-Path $OutDir 'reconciliation.md'
Set-Content -LiteralPath $mdPath -Value $md.ToString() -Encoding UTF8

$jsonPath = Join-Path $OutDir 'reconciliation.json'
[pscustomobject]@{
    generated_at = (Get-Date).ToString('o')
    database     = $Database
    flat         = $flat
    kpis         = $kpis
} | ConvertTo-Json -Depth 6 | Set-Content -LiteralPath $jsonPath -Encoding UTF8

Write-Host ("Reporte: {0}" -f $mdPath)
Write-Host ("JSON:    {0}" -f $jsonPath)
Write-Host ''
foreach ($k in $kpis) { Write-Host ("  {0,-38} {1}" -f $k.kpi, $k.valor) }
