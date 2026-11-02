<#
.SYNOPSIS
    Aplica un kit de traslado: restaura la base y copia los PDFs archivados.

.DESCRIPTION
    Acepta el .zip que genera exportar_kit.ps1 o un .sql.gz suelto.
    Si es .zip: descomprime, restaura base\*.sql.gz y copia reports\*.pdf.
    Si es .sql.gz: solo restaura la base.

    Uso:
        powershell -File scripts\aplicar_kit.ps1 -Kit "D:\laboratorio-kit-XXXX.zip"
        powershell -File scripts\aplicar_kit.ps1 -Kit "D:\lapt_core.sql.gz" -Database lapt_core
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$Kit,
    [string]$Database = 'lapt_core',
    [string]$User = 'root',
    [string]$Password = ''
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$restore = Join-Path $PSScriptRoot 'restore_db.ps1'

if (-not (Test-Path $Kit)) {
    # Windows suele ocultar la extension: probamos con .zip y .sql.gz.
    if (Test-Path ($Kit + '.zip')) {
        $Kit = $Kit + '.zip'
    } elseif (Test-Path ($Kit + '.sql.gz')) {
        $Kit = $Kit + '.sql.gz'
    } else {
        throw ("No existe el kit: " + $Kit + "`n" +
               "Verifica el nombre completo, con extension. Para listar los kits del pendrive:`n" +
               "    dir D:\laboratorio-kit-*.zip")
    }
}
if (-not (Test-Path $restore)) { throw 'No se encontro scripts/restore_db.ps1' }

$reportsDst = Join-Path $root 'storage\app\private\reports'
if (-not (Test-Path $reportsDst)) { New-Item -ItemType Directory -Path $reportsDst -Force | Out-Null }

$tmp = $null
$origen = $Kit

if ($Kit.ToLower().EndsWith('.zip')) {
    $tmp = Join-Path $env:TEMP ('kit_' + [guid]::NewGuid().ToString('N'))
    Expand-Archive -Path $Kit -DestinationPath $tmp -Force
    $origen = $tmp
    Write-Host ('      kit descomprimido en ' + $tmp)
}

$sql = Get-ChildItem $origen -Recurse -File -Include '*.sql.gz', '*.sql' -ErrorAction SilentlyContinue |
       Sort-Object Length -Descending |
       Select-Object -First 1

if (-not $sql) { throw 'El kit no contiene ningun archivo .sql.gz o .sql' }

$restoreArgs = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $restore,
                 '-File', $sql.FullName, '-Database', $Database, '-User', $User, '-Force')
if ($Password) { $restoreArgs += @('-Password', $Password) }

& powershell.exe @restoreArgs
if ($LASTEXITCODE -ne 0) { throw 'Fallo la restauracion de la base.' }

$pdfs = 0
if ($tmp) {
    $reportsSrc = Get-ChildItem $tmp -Recurse -Directory -Filter 'reports' -ErrorAction SilentlyContinue |
                  Select-Object -First 1
    if ($reportsSrc) {
        Get-ChildItem $reportsSrc.FullName -Filter *.pdf -File | ForEach-Object {
            Copy-Item $_.FullName -Destination $reportsDst -Force
            $pdfs++
        }
    }
    Remove-Item $tmp -Recurse -Force -ErrorAction SilentlyContinue
}

Write-Host ("      PDFs copiados: " + $pdfs)
Write-Host ('      base restaurada desde ' + $sql.Name)
