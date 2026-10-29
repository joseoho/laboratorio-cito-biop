<#
.SYNOPSIS
    Exporta las tablas de Access (prog_lapt.mdb -> LAPT.mdb) a CSV para staging.

.DESCRIPTION
    - Lee SIEMPRE una copia del .mdb (nunca el original): no genera .ldb ni bloquea Access.
    - Conexion ACE OLEDB en Mode=Read.
    - CSV UTF-8 sin BOM, comillas dobles, NULL como \N (sin comillas).
    - Fechas en ISO 'yyyy-MM-dd HH:mm:ss' (preserva anios corruptos tipo 4202).
    - No se normaliza nada aqui: eso es el transform.
    - Genera out/export_manifest.json con tabla, columnas, filas y ruta del CSV.

.NOTES
    Requiere Windows PowerShell 5.1 (System.Data.OleDb) + Microsoft ACE OLEDB 12.0.
    Ejecutar:
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File etl/access_export.ps1
#>
[CmdletBinding()]
param(
    [string]$AccessPath,
    [string]$OutDir,
    [string]$Provider = 'Microsoft.ACE.OLEDB.12.0'
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $AccessPath) { $AccessPath = Join-Path $env:USERPROFILE 'Dropbox\MedixLab\prog_lapt.mdb' }
if (-not $OutDir)     { $OutDir     = Join-Path $scriptDir 'out' }

if ($PSVersionTable.PSEdition -ne 'Desktop') {
    throw 'Este script necesita Windows PowerShell 5.1: System.Data.OleDb no esta disponible en PowerShell 7.'
}

# --- Tablas: patron de nombre en Access -> tabla destino en staging / CSV -------
# Se usa patron porque PowerShell 5.1 lee este archivo como ANSI: nada de acentos en literales.
$map = @(
    @{ match = 'Pacientes';         table = 'legacy_pacientes';        file = 'pacientes.csv' },
    @{ match = 'Biopsias';          table = 'legacy_biopsias';         file = 'biopsias.csv' },
    @{ match = 'Citolog*';          table = 'legacy_citologia';        file = 'citologia.csv' },
    @{ match = 'DiagCito';          table = 'legacy_diagcito';         file = 'diagcito.csv' },
    @{ match = 'Anexo';             table = 'legacy_anexo';            file = 'anexo.csv' },
    @{ match = 'Medico';            table = 'legacy_medico';           file = 'medico.csv' },
    @{ match = 'Procedencia';       table = 'legacy_procedencia';      file = 'procedencia.csv' },
    @{ match = 'Patologo';          table = 'legacy_patologo';         file = 'patologo.csv' },
    @{ match = 'Plantillas';        table = 'legacy_plantillas';       file = 'plantillas.csv' },
    @{ match = 'Facturas';          table = 'legacy_facturas';         file = 'facturas.csv' },
    @{ match = 'Detalle Examenes';  table = 'legacy_detalle_examenes'; file = 'detalle_examenes.csv' },
    @{ match = 'Detalle Abonos';    table = 'legacy_detalle_abonos';   file = 'detalle_abonos.csv' },
    @{ match = 'Examenes';          table = 'legacy_examenes';         file = 'examenes.csv' },
    @{ match = 'exadetails';        table = 'legacy_exadetails';       file = 'exadetails.csv' },
    @{ match = 'VerifTabla';        table = 'legacy_verif_tabla';      file = 'verif_tabla.csv' }
)

$csvDir = Join-Path $OutDir 'csv'
if (-not (Test-Path $csvDir)) { New-Item -ItemType Directory -Path $csvDir -Force | Out-Null }

# --- Copia de trabajo: nunca abrimos el original -------------------------------
if (-not (Test-Path -LiteralPath $AccessPath)) { throw "No existe el archivo Access: $AccessPath" }
$workCopy = Join-Path $env:TEMP 'prog_lapt_etl_copy.mdb'
Copy-Item -LiteralPath $AccessPath -Destination $workCopy -Force
Write-Host ("[1/3] Copia de trabajo: {0} ({1:N1} MB)" -f $workCopy, ((Get-Item $workCopy).Length / 1MB))

# --- Helpers -------------------------------------------------------------------
function Convert-ToCsvField {
    param($Value)

    if ($null -eq $Value -or $Value -is [System.DBNull]) { return '\N' }

    if ($Value -is [datetime]) {
        return '"' + $Value.ToString('yyyy-MM-dd HH:mm:ss', [Globalization.CultureInfo]::InvariantCulture) + '"'
    }
    if ($Value -is [decimal] -or $Value -is [double] -or $Value -is [single]) {
        return '"' + ([decimal]$Value).ToString([Globalization.CultureInfo]::InvariantCulture) + '"'
    }

    $s = [string]$Value
    if ($s.Length -eq 0) { return '""' }

    # MySQL interpreta '\' con ESCAPED BY: hay que duplicarlo para que llegue literal
    $s = $s.Replace('\', '\\')
    # Un solo estilo de salto de linea (MySQL acepta LF dentro de campo entrecomillado)
    $s = $s.Replace("`r`n", "`n").Replace("`r", "`n")
    # NUL fuera
    $s = $s.Replace([string][char]0, '')
    # Comilla doble -> \" (lo que MySQL desescapa con ESCAPED BY '\\')
    $s = $s.Replace('"', '\"')

    return '"' + $s + '"'
}

# --- Conexion ------------------------------------------------------------------
$connString = "Provider=$Provider;Data Source=$workCopy;Mode=Read;Persist Security Info=False;"
$cn = New-Object System.Data.OleDb.OleDbConnection $connString
$cn.Open()
Write-Host "[2/3] Conectado a Access (Mode=Read)"

$allColumns = $cn.GetSchema('Columns')
$manifest = @()
$totalRows = 0

foreach ($t in $map) {
    $schemaRows = @($allColumns.Rows | Where-Object { [string]$_['TABLE_NAME'] -like $t.match })
    if ($schemaRows.Count -eq 0) {
        Write-Warning ("  ! {0}: tabla no encontrada, se omite" -f $t.match)
        continue
    }

    $realName = [string]$schemaRows[0]['TABLE_NAME']
    $cols = @($schemaRows |
        Sort-Object { [int]$_['ORDINAL_POSITION'] } |
        ForEach-Object { [string]$_['COLUMN_NAME'] })

    $selectList = ($cols | ForEach-Object { '[' + $_ + ']' }) -join ','
    $cmd = $cn.CreateCommand()
    $cmd.CommandText = "SELECT $selectList FROM [$realName]"
    $cmd.CommandTimeout = 900

    $path = Join-Path $csvDir $t.file
    $sw = New-Object System.IO.StreamWriter($path, $false, (New-Object System.Text.UTF8Encoding($false)), 65536)
    $swInd = [Diagnostics.Stopwatch]::StartNew()

    $rd = $cmd.ExecuteReader()
    $n = 0
    $buf = New-Object System.Text.StringBuilder 8192

    while ($rd.Read()) {
        [void]$buf.Clear()
        for ($i = 0; $i -lt $cols.Count; $i++) {
            if ($i -gt 0) { [void]$buf.Append(',') }
            [void]$buf.Append((Convert-ToCsvField $rd.GetValue($i)))
        }
        $sw.Write($buf.ToString())
        $sw.Write("`n")
        $n++
    }
    $rd.Close()
    $sw.Close()

    $totalRows += $n
    Write-Host ("  {0,-22} -> {1,-26} {2,7} filas  ({3:N1}s)" -f $realName, $t.table, $n, $swInd.Elapsed.TotalSeconds)

    $manifest += [pscustomobject]@{
        access  = $realName
        table   = $t.table
        file    = $t.file
        columns = $cols
        rows    = $n
        bytes   = (Get-Item $path).Length
    }
}

$cn.Close()

# --- Manifest ------------------------------------------------------------------
$manifestPath = Join-Path $OutDir 'export_manifest.json'
[pscustomobject]@{
    generated_at = (Get-Date).ToString('o')
    source_mdb   = $AccessPath
    work_copy    = $workCopy
    provider     = $Provider
    total_rows   = $totalRows
    tables       = $manifest
} | ConvertTo-Json -Depth 6 | Set-Content -Path $manifestPath -Encoding UTF8

Write-Host ("[3/3] Manifest: {0}  |  Total filas: {1}" -f $manifestPath, $totalRows)
