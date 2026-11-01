<#
.SYNOPSIS
    Ejecuta la instalacion completa pidiendo permisos de administrador una sola vez.

.DESCRIPTION
    Es el motor de instalar-como-admin.bat. Reabre el instalador con permisos
    elevados para poder configurar el dominio .test y el arranque al encender
    el equipo. Si ya esta elevado, no vuelve a pedir nada.

    Uso normal (doble clic en instalar-como-admin.bat):
        instalar-como-admin.bat
        instalar-como-admin.bat -Kit "D:\laboratorio-kit-XXXX.zip"

    Prueba sin elevacion (para diagnosticar):
        powershell -File scripts\elevar.ps1 -NoElevate -Check

.NOTES
    Sin acentos a proposito: Windows PowerShell 5.1 lee este archivo como ANSI.
#>
[CmdletBinding()]
param(
    [switch]$NoElevate,
    [Parameter(ValueFromRemainingArguments = $true)][string[]]$Rest
)

$ErrorActionPreference = 'Stop'

$instalar = Join-Path $PSScriptRoot 'instalar.ps1'
if (-not (Test-Path $instalar)) { throw 'No se encontro scripts/instalar.ps1' }

$id = [Security.Principal.WindowsIdentity]::GetCurrent()
$admin = (New-Object Security.Principal.WindowsPrincipal($id)).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

# Instalacion completa: sin preguntas, con arranque al iniciar sesion y al encender.
$baseArgs = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $instalar,
              '-Si', '-ConAutoarranque', '-AlArrancar')
if ($Rest) { $baseArgs += $Rest }

function Encomillar([string[]]$lista) {
    ($lista | ForEach-Object {
        if ($_ -match '\s') { '"' + $_ + '"' } else { $_ }
    }) -join ' '
}

if ($admin) {
    Write-Host 'Permisos de administrador: OK' -ForegroundColor Green
    & powershell.exe -NoExit @baseArgs
    exit $LASTEXITCODE
}

if ($NoElevate) {
    Write-Host 'MODO PRUEBA (-NoElevate): se ejecuta sin permisos de administrador.' -ForegroundColor Yellow
    Write-Host 'El dominio .test y el arranque al encender no se podran configurar.' -ForegroundColor Yellow
    & powershell.exe @baseArgs
    exit $LASTEXITCODE
}

Write-Host 'Se necesitan permisos de administrador para configurar el dominio y el arranque.' -ForegroundColor Cyan
Write-Host 'Windows va a mostrar un aviso: elegi "Si".' -ForegroundColor Cyan

try {
    $linea = Encomillar $baseArgs
    $proceso = Start-Process -FilePath 'powershell.exe' -ArgumentList $linea -Verb RunAs -PassThru
    $proceso.WaitForExit()
    exit $proceso.ExitCode
}
catch {
    Write-Host ''
    Write-Host 'No se concedio el permiso de administrador.' -ForegroundColor Red
    Write-Host 'Podes ejecutar igual la instalacion basica (sin dominio ni arranque):' -ForegroundColor Yellow
    Write-Host '    instalar.bat' -ForegroundColor Yellow
    exit 1
}
