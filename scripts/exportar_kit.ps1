<#
.SYNOPSIS
    Arma un kit para llevar el sistema a otro equipo: base + PDFs + .env + instrucciones.

.DESCRIPTION
    Genera en storage\kit\laboratorio-kit-AAAAMMDD-HHMMSS\ :
      - base\lapt_core-*.sql.gz   (respaldo comprimido de la base operativa)
      - reports\*.pdf             (PDFs archivados; NO estan en la base ni en git)
      - .env                      (credenciales y APP_KEY; protegelo)
      - LEEME.txt                 (pasos para el equipo nuevo)
    y un .zip con todo, listo para copiar a un pendrive.

    Uso:
        powershell -File scripts/exportar_kit.ps1
        powershell -File scripts/exportar_kit.ps1 -Database lapt_core -ConRespaldos
#>
[CmdletBinding()]
param(
    [string]$Database = 'lapt_core',
    [string]$User = 'root',
    [string]$Password = '',
    [string]$OutRoot,
    [switch]$ConRespaldos
)

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
if (-not $OutRoot) { $OutRoot = Join-Path $root 'storage\kit' }

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$kit = Join-Path $OutRoot ("laboratorio-kit-" + $stamp)
$base = Join-Path $kit 'base'
$reportsDir = Join-Path $kit 'reports'

New-Item -ItemType Directory -Path $base, $reportsDir -Force | Out-Null

Write-Host "[1/4] Respaldando la base..."
$backup = Join-Path $PSScriptRoot 'backup_db.ps1'
if (-not (Test-Path $backup)) { throw 'No se encontro scripts/backup_db.ps1' }

$backupArgs = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $backup,
                '-Database', $Database, '-User', $User, '-OutDir', $base)
if ($Password) { $backupArgs += @('-Password', $Password) }

& powershell.exe @backupArgs
if ($LASTEXITCODE -ne 0) { throw 'Fallo el respaldo de la base' }

Write-Host "[2/4] Copiando PDFs archivados..."
$pdfSource = Join-Path $root 'storage\app\private\reports'
$pdfCount = 0
if (Test-Path $pdfSource) {
    Get-ChildItem $pdfSource -Filter *.pdf -File | ForEach-Object {
        Copy-Item $_.FullName -Destination $reportsDir -Force
        $pdfCount++
    }
}
Write-Host ("      {0} PDF(s)" -f $pdfCount)

Write-Host "[3/4] Copiando .env (contiene APP_KEY y credenciales)..."
$envFile = Join-Path $root '.env'
$envInfo = 'No se copio .env (no existe). Crea uno nuevo y anota DB_DATABASE/DB_USERNAME.'
if (Test-Path $envFile) {
    Copy-Item $envFile -Destination (Join-Path $kit '.env') -Force
    $envInfo = '.env copiado. Contiene APP_KEY: si lo reemplazas, las sesiones se invalidan pero los datos no se afectan.'
}

$readme = @"
KIT DE TRASLADO - LABORATORIO BECOMARCA
Generado: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

CONTENIDO
  base\            respaldo comprimido de la base operativa ($Database)
  reports\         PDFs archivados (no estan en la base ni en el repositorio)
  .env             credenciales y APP_KEY

EN EL EQUIPO NUEVO (Windows + XAMPP)
  1. Instalar XAMPP 8.2 con PHP y MySQL, y Composer 2.
  2. Clonar el codigo:
       git clone https://github.com/joseoho/laboratorio-cito-biop.git C:\xampp\htdocs\laboratorio-cito-biop
  3. Instalar el sistema (deja la base vacia y las dependencias):
       cd C:\xampp\htdocs\laboratorio-cito-biop
       instalar.bat -Si
  4. Restaurar la base desde este kit:
       powershell -File scripts\restore_db.ps1 -File "RUTA\base\lapt_core-XXXX.sql.gz"
  5. Copiar los PDFs:
       xcopy /E /I "RUTA\reports" "C:\xampp\htdocs\laboratorio-cito-biop\storage\app\private\reports"
  6. Copiar el .env del kit a la raiz del proyecto (o crearlo y ajustar DB_* y APP_URL).
  7. Limpiar caches y verificar:
       C:\xampp\php\php.exe artisan optimize:clear
       http://localhost/laboratorio-cito-biop/public/login
  8. (Opcional) regenerar la base de pruebas:
       powershell -File scripts\test_db.ps1

IMPORTANTE
  - El .env de este kit da acceso a la base: no lo dejes en un pendrive olvidado.
  - Sin la carpeta reports\, el boton PDF del sistema da 404 aunque la base tenga los registros.
  - No copies lapt_staging: es intermedia del ETL y se puede regenerar.
  - Si el equipo nuevo tiene otra URL o carpeta, ajusta APP_URL (siempre la raiz, sin subdirectorio).

$envInfo
"@

Set-Content -Path (Join-Path $kit 'LEEME.txt') -Value $readme -Encoding UTF8

Write-Host "[4/4] Comprimiendo el kit..."
$zip = $kit + '.zip'
Compress-Archive -Path (Join-Path $kit '*') -DestinationPath $zip -Force

$size = [math]::Round((Get-Item $zip).Length / 1MB, 2)
Write-Host ""
Write-Host ("OK  Kit listo: {0}  ({1} MB)" -f $zip, $size) -ForegroundColor Green
Write-Host "    Copia ese .zip al equipo nuevo y segui LEEME.txt."
