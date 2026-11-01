<#
.SYNOPSIS
    Genera un ZIP con el codigo del proyecto (sin .git, vendor ni storage).

.DESCRIPTION
    Sirve para instalar en un equipo sin Git o sin credenciales de GitHub,
    porque el repositorio es privado.

    El ZIP se coloca al lado de instalar-desde-cero.bat en el pendrive.
    Ese lanzador lo usa como respaldo si el clonado falla.

    Uso:
        powershell -ExecutionPolicy Bypass -File scripts\exportar_codigo.ps1
        powershell -ExecutionPolicy Bypass -File scripts\exportar_codigo.ps1 -Out "D:\laboratorio-codigo.zip"
#>
[CmdletBinding()]
param(
    [string]$Out
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
if (-not $Out) {
    $Out = Join-Path $root 'storage\kit\laboratorio-codigo.zip'
}

New-Item -ItemType Directory -Path (Split-Path $Out) -Force | Out-Null
if (Test-Path $Out) { Remove-Item $Out -Force }

Push-Location $root
try {
    & git archive --format=zip --output="$Out" HEAD
    if ($LASTEXITCODE -ne 0) { throw 'Fallo git archive. Estas en la raiz del repositorio?' }
}
finally {
    Pop-Location
}

$file = Get-Item $Out
Write-Host ("OK  {0}  ({1:N2} MB)" -f $file.FullName, ($file.Length / 1MB)) -ForegroundColor Green
Write-Host '    Copia ese .zip junto a instalar-desde-cero.bat en el pendrive.'
