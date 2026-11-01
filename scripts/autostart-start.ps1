<#
.SYNOPSIS
    Levanta Apache y MySQL si no estan corriendo. Pensado para ejecutarse al
    iniciar sesion o al arrancar Windows.

.DESCRIPTION
    Idempotente: si los procesos ya existen, no hace nada. Deja rastro en
    storage\logs\xampp-autostart.log.
#>
[CmdletBinding()]
param(
    [string]$Xampp = 'C:\xampp'
)

$ErrorActionPreference = 'Continue'

$root = Split-Path -Parent $PSScriptRoot
$logDir = Join-Path $root 'storage\logs'
if (-not (Test-Path $logDir)) { New-Item -ItemType Directory -Path $logDir -Force | Out-Null }
$log = Join-Path $logDir 'xampp-autostart.log'

function Escribir([string]$texto) {
    $linea = (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + '  ' + $texto
    Add-Content -Path $log -Value $linea -Encoding UTF8
}

Escribir '--- inicio de verificacion ---'

$apache = Get-Process -Name httpd -ErrorAction SilentlyContinue
if ($apache) {
    Escribir 'Apache ya estaba corriendo'
} else {
    $httpd = Join-Path $Xampp 'apache\bin\httpd.exe'
    if (Test-Path $httpd) {
        Start-Process -FilePath $httpd -WorkingDirectory (Join-Path $Xampp 'apache\bin') -WindowStyle Hidden
        Escribir 'Apache iniciado'
    } else {
        Escribir ('No se encontro ' + $httpd)
    }
}

$mysql = Get-Process -Name mysqld -ErrorAction SilentlyContinue
if ($mysql) {
    Escribir 'MySQL ya estaba corriendo'
} else {
    $mysqld = Join-Path $Xampp 'mysql\bin\mysqld.exe'
    $myIni = Join-Path $Xampp 'mysql\bin\my.ini'
    if (Test-Path $mysqld) {
        $args = @()
        if (Test-Path $myIni) { $args = @("--defaults-file=$myIni") }
        Start-Process -FilePath $mysqld -ArgumentList $args -WorkingDirectory (Join-Path $Xampp 'mysql\bin') -WindowStyle Hidden
        Escribir 'MySQL iniciado'
    } else {
        Escribir ('No se encontro ' + $mysqld)
    }
}

Start-Sleep -Seconds 3

$okApache = [bool](Get-Process -Name httpd -ErrorAction SilentlyContinue)
$okMysql = [bool](Get-Process -Name mysqld -ErrorAction SilentlyContinue)
Escribir ('estado final: apache=' + $okApache + ' mysql=' + $okMysql)
