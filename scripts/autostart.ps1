<#
.SYNOPSIS
    Programa el arranque automatico de Apache y MySQL.

.DESCRIPTION
    Dos modos:
      - Login    : acceso directo en la carpeta Inicio del usuario. Corre al
                   iniciar sesion (no requiere administrador).
      - Arranque : tarea programada AL ARRANCAR Windows, como SYSTEM. Corre
                   aunque nadie inicie sesion (requiere administrador).
                   Es el modo recomendado para un corte de luz.

    Uso:
        powershell -File scripts\autostart.ps1 -Modo Login
        powershell -File scripts\autostart.ps1 -Modo Arranque
        powershell -File scripts\autostart.ps1 -Modo Login -DryRun
        powershell -File scripts\autostart.ps1 -Modo Login -Remove
        powershell -File scripts\autostart.ps1 -Modo Arranque -Remove

.NOTES
    Sin acentos a proposito: Windows PowerShell 5.1 lee este archivo como ANSI.
#>
[CmdletBinding()]
param(
    [ValidateSet('Login', 'Arranque')]
    [string]$Modo = 'Login',
    [switch]$Remove,
    [switch]$DryRun,
    [string]$Xampp = 'C:\xampp'
)

$ErrorActionPreference = 'Stop'

$worker = Join-Path $PSScriptRoot 'autostart-start.ps1'
if (-not (Test-Path $worker)) { throw 'No se encontro scripts/autostart-start.ps1' }

$ps = Join-Path $env:SystemRoot 'System32\WindowsPowerShell\v1.0\powershell.exe'
$nombre = 'Laboratorio Becomarca (XAMPP)'
$tarea = 'Laboratorio Becomarca XAMPP'

function Test-Admin {
    $id = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($id)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

Write-Host ''
Write-Host (" Modo: " + $Modo)
if ($DryRun) { Write-Host ' Simulacion (-DryRun): no se modifica nada' -ForegroundColor Yellow }
Write-Host ''

if ($Modo -eq 'Login') {
    $inicio = [Environment]::GetFolderPath('Startup')
    $lnk = Join-Path $inicio ($nombre + '.lnk')

    if ($Remove) {
        if (Test-Path $lnk) {
            if ($DryRun) { Write-Host (' (simulacion) eliminaria ' + $lnk) }
            else { Remove-Item $lnk -Force; Write-Host ' Acceso directo eliminado' }
        } else {
            Write-Host ' No habia acceso directo'
        }
        exit 0
    }

    if ($DryRun) {
        Write-Host (' (simulacion) crearia ' + $lnk)
        Write-Host ('   destino: ' + $ps + ' -File "' + $worker + '"')
        exit 0
    }

    $shell = New-Object -ComObject WScript.Shell
    $acceso = $shell.CreateShortcut($lnk)
    $acceso.TargetPath = $ps
    $acceso.Arguments = '-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "' + $worker + '" -Xampp "' + $Xampp + '"'
    $acceso.WorkingDirectory = Split-Path -Parent $PSScriptRoot
    $acceso.WindowStyle = 7
    $acceso.Description = 'Levanta Apache y MySQL al iniciar sesion'
    $acceso.Save()

    Write-Host (' Listo: ' + $lnk) -ForegroundColor Green
    Write-Host ' Se ejecuta al iniciar sesion. Para quitarlo: -Remove'
    exit 0
}

# --- Modo Arranque (tarea programada como SYSTEM) --------------------------
if (-not $DryRun -and -not (Test-Admin)) {
    throw 'El modo Arranque requiere administrador. Abri PowerShell como administrador.'
}

if ($Remove) {
    $cmd = 'schtasks /Delete /TN "' + $tarea + '" /F'
    if ($DryRun) { Write-Host (' (simulacion) ' + $cmd) }
    else {
        & cmd.exe /c $cmd 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) { Write-Host ' Tarea eliminada' }
        else { Write-Host ' No habia tarea programada' }
    }
    exit 0
}

$q = '\"'
$comando = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File ' + $q + $worker + $q + ' -Xampp ' + $q + $Xampp + $q
$crear = 'schtasks /Create /TN "' + $tarea + '" /SC ONSTART /RU SYSTEM /RL HIGHEST /TR "' + $comando + '" /F'

Write-Host ' Creando tarea al arrancar Windows (como SYSTEM)...'
if ($DryRun) {
    Write-Host (' (simulacion) ' + $crear)
    exit 0
}

& cmd.exe /c $crear
if ($LASTEXITCODE -ne 0) { throw 'No se pudo crear la tarea programada.' }

Write-Host ' Listo: Apache y MySQL arrancan con Windows, sin necesidad de iniciar sesion.' -ForegroundColor Green
Write-Host ' La tarea se llama "Laboratorio Becomarca XAMPP".'
Write-Host ' Registro: storage\logs\xampp-autostart.log'
