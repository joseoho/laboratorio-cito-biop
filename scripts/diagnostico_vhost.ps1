<#
.SYNOPSIS
    Diagnostico del dominio local: hosts, vhost, Apache y respuesta HTTP.

.DESCRIPTION
    Solo lee, no modifica nada. Sirve cuando un dominio .test abre la app equivocada
    o no abre.

    Uso:
        powershell -ExecutionPolicy Bypass -File scripts\diagnostico_vhost.ps1
        powershell -ExecutionPolicy Bypass -File scripts\diagnostico_vhost.ps1 -Domain otro.test
#>
[CmdletBinding()]
param(
    [string]$Domain = 'laboratorio-cito-biop.test',
    [string]$ApacheRoot = 'C:\xampp\apache'
)

$ErrorActionPreference = 'Continue'

$hostsFile = Join-Path $env:SystemRoot 'System32\drivers\etc\hosts'
$vhostConf = Join-Path $ApacheRoot 'conf\extra\httpd-vhosts.conf'
$httpdConf = Join-Path $ApacheRoot 'conf\httpd.conf'
$httpd = Join-Path $ApacheRoot 'bin\httpd.exe'
$appRoot = Split-Path -Parent $PSScriptRoot

function Titulo([string]$t) { Write-Host ''; Write-Host ('== ' + $t) -ForegroundColor Cyan }
function Bien([string]$t) { Write-Host ('   OK   ' + $t) -ForegroundColor Green }
function Mal([string]$t) { Write-Host ('   MAL  ' + $t) -ForegroundColor Red }
function Ojo([string]$t) { Write-Host ('   !    ' + $t) -ForegroundColor Yellow }

Titulo '1) Archivo hosts'
$linea = Select-String -Path $hostsFile -Pattern ('^\s*127\.0\.0\.1\s+' + [regex]::Escape($Domain) + '\s*$')
if ($linea) {
    Bien ('127.0.0.1 ' + $Domain + ' presente (linea ' + $linea.LineNumber + ')')
} else {
    Mal ('no hay una linea exacta "127.0.0.1 ' + $Domain + '"')
    Select-String -Path $hostsFile -Pattern ([regex]::Escape($Domain)) | ForEach-Object {
        Ojo ('linea ' + $_.LineNumber + ': ' + $_.Line.Trim() + '  <- revisar comas, espacios o http://')
    }
}

Titulo '2) Resolucion del nombre'
try {
    $ips = [System.Net.Dns]::GetHostAddresses($Domain) | ForEach-Object { $_.IPAddressToString }
    if ($ips -contains '127.0.0.1') { Bien ($Domain + ' resuelve a 127.0.0.1') }
    else { Mal ($Domain + ' resuelve a ' + ($ips -join ', ')) }
} catch {
    Mal ($Domain + ' no resuelve. Proba: ipconfig /flushdns')
}

Titulo '3) VirtualHost'
if (-not (Test-Path $vhostConf)) {
    Mal ('no existe ' + $vhostConf)
} else {
    $texto = Get-Content $vhostConf -Raw
    if ($texto -match [regex]::Escape('ServerName ' + $Domain)) {
        Bien ('hay un VirtualHost con ServerName ' + $Domain)
        $i = $texto.IndexOf('ServerName ' + $Domain)
        $inicio = $texto.LastIndexOf('<VirtualHost', $i)
        $fin = $texto.IndexOf('</VirtualHost>', $i)
        if ($inicio -ge 0 -and $fin -gt $inicio) {
            $bloque = $texto.Substring($inicio, $fin - $inicio + 14)
            $doc = [regex]::Match($bloque, 'DocumentRoot\s+"([^"]+)"')
            if ($doc.Success) {
                $ruta = $doc.Groups[1].Value.Replace('/', '\')
                if (Test-Path $ruta) { Bien ('DocumentRoot existe: ' + $ruta) }
                else { Mal ('DocumentRoot NO existe: ' + $ruta) }
                if ($ruta.TrimEnd('\') -eq (Join-Path $appRoot 'public').TrimEnd('\')) { Bien 'apunta a la carpeta public del proyecto' }
                else { Ojo ('apunta a otra carpeta: ' + $ruta) }
            }
        }
    } else {
        Mal ('NO hay VirtualHost con ServerName ' + $Domain)
        Ojo 'por eso Apache usa el PRIMER vhost del archivo y abre otra app'
        Ojo 'solucion: powershell -ExecutionPolicy Bypass -File scripts\setup-vhost.ps1  (como administrador)'
    }

    # Primer vhost REAL (ignora los ejemplos comentados con ##).
    $enBloque = $false
    $primer = $null
    foreach ($l in (Get-Content $vhostConf)) {
        if ($l -match '^\s*<VirtualHost') { $enBloque = $true; continue }
        if ($l -match '^\s*</VirtualHost>') { $enBloque = $false; continue }
        if ($enBloque -and $l -match '^\s*ServerName\s+(\S+)') { $primer = $matches[1]; break }
    }
    if ($primer) {
        Ojo ('el primer vhost del archivo es: ' + $primer + '  (es el que atiende los dominios sin coincidencia)')
    }
}

Titulo '4) Include de vhosts en httpd.conf'
if (Test-Path $httpdConf) {
    if (Select-String -Path $httpdConf -Pattern '^\s*Include\s+conf/extra/httpd-vhosts\.conf' -Quiet) {
        Bien 'Include habilitado'
    } else {
        Mal 'Include comentado: Apache no lee httpd-vhosts.conf'
    }
} else {
    Mal ('no existe ' + $httpdConf)
}

Titulo '5) Quien atiende el puerto 80'
$conexiones = Get-NetTCPConnection -LocalPort 80 -State Listen -ErrorAction SilentlyContinue
if ($conexiones) {
    foreach ($c in $conexiones) {
        $proc = Get-Process -Id $c.OwningProcess -ErrorAction SilentlyContinue
        if ($proc) {
            Ojo ('pid ' + $proc.Id + ': ' + $proc.Path)
            if ($proc.Path -and $proc.Path.ToLower().Contains('\xampp\')) { Bien 'es el Apache de XAMPP' }
            else { Mal 'NO es el Apache de XAMPP: puede ser Laragon u otro. Detenelo o configura ahi el dominio.' }
        }
    }
} else {
    Mal 'nadie escucha en el puerto 80: Apache esta apagado'
}

Titulo '6) Configuracion de Apache'
if (Test-Path $httpd) {
    $test = & $httpd -t 2>&1
    if ($LASTEXITCODE -eq 0) { Bien 'httpd -t: configuracion valida' }
    else { Mal 'httpd -t fallo:'; $test | ForEach-Object { Write-Host ('        ' + $_) } }
} else {
    Mal ('no existe ' + $httpd)
}

Titulo '7) Respuesta HTTP'
try {
    $r = Invoke-WebRequest -Uri ('http://' + $Domain + '/login') -UseBasicParsing -TimeoutSec 10
    $titulo = [regex]::Match($r.Content, '<title>(.*?)</title>').Groups[1].Value
    if ($r.StatusCode -eq 200 -and $r.Content -match 'Laboratorio|cambiar|Correo') {
        Bien ('HTTP 200 y parece la app del laboratorio. Titulo: ' + $titulo)
    } else {
        Mal ('HTTP ' + $r.StatusCode + ' pero parece OTRA app. Titulo: ' + $titulo)
        Ojo 'el vhost del laboratorio no esta tomando el dominio'
    }
} catch {
    Mal ('no responde: ' + $_.Exception.Message)
}

Write-Host ''
Write-Host 'Diagnostico terminado.' -ForegroundColor Cyan
