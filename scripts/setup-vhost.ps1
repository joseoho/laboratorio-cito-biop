<#
.SYNOPSIS
    Configura http://laboratorio-cito-biop.test apuntando a la carpeta public del proyecto.

.DESCRIPTION
    Hace tres cosas, con respaldo previo y sin borrar configuracion existente:
      1. Agrega "127.0.0.1 laboratorio-cito-biop.test" al archivo hosts (si falta).
      2. Habilita el include de httpd-vhosts.conf en httpd.conf (si esta comentado).
      3. Agrega un <VirtualHost> con DocumentRoot a ...\laboratorio-cito-biop\public.

    Requiere una consola ABIERTA COMO ADMINISTRADOR (hosts y Apache).

    Uso:
        powershell -File scripts\setup-vhost.ps1 -DryRun     (muestra que haria)
        powershell -File scripts\setup-vhost.ps1             (aplica)
        powershell -File scripts\setup-vhost.ps1 -Remove     (quita el vhost y la linea del hosts)

.NOTES
    Sin acentos a proposito: Windows PowerShell 5.1 lee este archivo como ANSI.
#>
[CmdletBinding()]
param(
    [string]$Domain = 'laboratorio-cito-biop.test',
    [string]$AppPath,
    [string]$ApacheRoot = 'C:\xampp\apache',
    [switch]$Remove,
    [switch]$DryRun
)

$ErrorActionPreference = 'Stop'

if (-not $AppPath) {
    $AppPath = Split-Path -Parent $PSScriptRoot
}

$docRoot = Join-Path $AppPath 'public'
$hostsFile = Join-Path $env:SystemRoot 'System32\drivers\etc\hosts'
$httpdConf = Join-Path $ApacheRoot 'conf\httpd.conf'
$vhostConf = Join-Path $ApacheRoot 'conf\extra\httpd-vhosts.conf'
$apacheBin = Join-Path $ApacheRoot 'bin'
$begin = '# BEGIN laboratorio-cito-biop'
$end = '# END laboratorio-cito-biop'

function Test-Admin {
    $id = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($id)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

function Respaldo([string]$path, [string]$suffix) {
    if ($DryRun) {
        Write-Host ("      (simulacion) respaldaria " + $path)
        return
    }
    $bak = $path + '.bak-laboratorio'
    if ((Test-Path $path) -and -not (Test-Path $bak)) {
        Copy-Item $path $bak -Force
        Write-Host ("      respaldo: " + $bak)
    }
}

if (-not (Test-Path $docRoot)) { throw "No existe $docRoot" }
if (-not (Test-Path $httpdConf)) { throw "No se encontro $httpdConf" }

Write-Host ""
Write-Host (" Dominio:    " + $Domain)
Write-Host (" DocumentRoot: " + $docRoot)
Write-Host (" Apache:     " + $ApacheRoot)
if ($DryRun) { Write-Host " MODO SIMULACION (-DryRun): no se modifica nada" -ForegroundColor Yellow }
Write-Host ""

if (-not $DryRun -and -not (Test-Admin)) {
    throw 'Se requieren permisos de administrador. Abri PowerShell como administrador y volve a ejecutar.'
}

# ---------------------------------------------------------------------------
if ($Remove) {
    Write-Host "[1/3] Quitando el vhost..."
    if (Test-Path $vhostConf) {
        $texto = Get-Content $vhostConf -Raw
        if ($texto -match [regex]::Escape($begin)) {
            $nuevo = [regex]::Replace($texto, [regex]::Escape($begin) + '[\s\S]*?' + [regex]::Escape($end), '')
            if ($DryRun) { Write-Host '      (simulacion) se quitaria el bloque del vhost' }
            else { Set-Content $vhostConf $nuevo -Encoding ASCII; Write-Host '      bloque del vhost quitado' }
        } else {
            Write-Host '      no habia bloque del vhost'
        }
    }

    Write-Host "[2/3] Quitando la linea del hosts..."
    $lineas = Get-Content $hostsFile | Where-Object { $_ -notmatch ('^\s*127\.0\.0\.1\s+' + [regex]::Escape($Domain) + '\s*$') }
    if ($DryRun) { Write-Host '      (simulacion) se quitaria la linea del hosts' }
    else { Respaldo $hostsFile 'hosts'; Set-Content $hostsFile $lineas -Encoding ASCII; Write-Host '      hosts actualizado' }

    Write-Host "[3/3] Reiniciando Apache..."
    if ($DryRun) { Write-Host '      (simulacion) httpd -k restart' }
    else { & (Join-Path $apacheBin 'httpd.exe') -k restart 2>&1 | Out-Null; Write-Host '      Apache reiniciado' }
    exit 0
}

# ---------------------------------------------------------------------------
Write-Host "[1/4] Archivo hosts..."
$hosts = Get-Content $hostsFile
$yaEsta = $hosts | Where-Object { $_ -match ('^\s*127\.0\.0\.1\s+' + [regex]::Escape($Domain) + '\s*$') }
if ($yaEsta) {
    Write-Host '      la linea ya existia'
} else {
    Respaldo $hostsFile 'hosts'
    if ($DryRun) {
        Write-Host ('      (simulacion) agregaria: 127.0.0.1 ' + $Domain)
    } else {
        Add-Content -Path $hostsFile -Value ("`r`n127.0.0.1 " + $Domain) -Encoding ASCII
        Write-Host ('      agregado: 127.0.0.1 ' + $Domain)
    }
}

Write-Host "[2/4] Include de vhosts en httpd.conf..."
$conf = Get-Content $httpdConf -Raw
$comentado = '# Include conf/extra/httpd-vhosts.conf'
$activo = 'Include conf/extra/httpd-vhosts.conf'

if ($conf -match [regex]::Escape($activo)) {
    Write-Host '      ya estaba habilitado'
} elseif ($conf -match [regex]::Escape($comentado)) {
    Respaldo $httpdConf 'httpd'
    if ($DryRun) {
        Write-Host '      (simulacion) descomentaria el Include de httpd-vhosts.conf'
    } else {
        $conf = $conf.Replace($comentado, $activo)
        Set-Content $httpdConf $conf -Encoding ASCII -NoNewline
        Write-Host '      Include habilitado'
    }
} else {
    Write-Host '      no se encontro la linea del Include; agregala a mano si el vhost no carga'
}

Write-Host "[3/4] VirtualHost..."
if (-not (Test-Path $vhostConf)) {
    if ($DryRun) { Write-Host '      (simulacion) crearia httpd-vhosts.conf' }
    else { Set-Content $vhostConf "`r`n" -Encoding ASCII }
}

$vhosts = Get-Content $vhostConf -Raw
if ($vhosts -match [regex]::Escape('ServerName ' + $Domain)) {
    Write-Host '      el vhost ya existia'
} else {
    Respaldo $vhostConf 'vhosts'
    $doc = $docRoot.Replace('\', '/')
    $bloque = @"

$begin
<VirtualHost *:80>
    DocumentRoot "$doc"
    ServerName $Domain
    <Directory "$doc">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/laboratorio-cito-biop-error.log"
    CustomLog "logs/laboratorio-cito-biop-access.log" common
</VirtualHost>
$end

"@
    if ($DryRun) {
        Write-Host '      (simulacion) agregaria el bloque:'
        Write-Host $bloque
    } else {
        Add-Content -Path $vhostConf -Value $bloque -Encoding ASCII
        Write-Host '      bloque agregado'
    }
}

Write-Host "[4/4] Reiniciando Apache..."
if ($DryRun) {
    Write-Host '      (simulacion) httpd -k restart'
} else {
    & (Join-Path $apacheBin 'httpd.exe') -k restart 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) { Write-Host '      Apache reiniciado' }
    else { Write-Host '      no se pudo reiniciar: hace-lo desde el panel de XAMPP' -ForegroundColor Yellow }
}

Write-Host ""
if ($DryRun) {
    Write-Host 'Simulacion terminada. Ejecuta sin -DryRun y como administrador para aplicar.' -ForegroundColor Yellow
} else {
    Write-Host ('Listo: http://' + $Domain + '/login') -ForegroundColor Green
    Write-Host 'Si no resuelve, ejecuta: ipconfig /flushdns'
}
