# Laboratorio Becomarca

Sistema interno del **Laboratorio de Anatomía Patológica "Dr. José R. Becker Reyes"**
(Becomarca): recepción de muestras, informes de biopsia y citología, emisión con PDF
archivado, reportes de producción y auditoría.

Laravel 11 + MariaDB/MySQL. Reemplaza al sistema original en **Microsoft Access 2000**,
cuyo histórico se migró completo (92.263 pacientes, 91.457 estudios).

## Stack

| Componente | Versión |
|---|---|
| PHP | 8.2 (XAMPP) |
| Laravel | 11.54 |
| Base de datos | MariaDB 10.4 / MySQL 8 (InnoDB) |
| Frontend | Blade + Bootstrap 5 (CDN, sin build) |
| PDF | dompdf |
| Tests | PHPUnit 11 + Laravel TestCase |

## Módulos

- **Pacientes**: alta, edición y búsqueda por cédula/nombre, número de biopsia (NB) o
  de citología (NC). Un paciente puede tener **varios estudios** (1:N), algo que el
  modelo de Access no permitía.
- **Estudios**: alta de biopsia o citología con **numeración asistida** (`NNNN` + año
  para biopsia, `NNNNN` + año para citología), corrección de número y fecha, y filtros
  por tipo, estado y año.
- **Informes**: editor a dos columnas con plantillas macro/micro/diagnóstico, catálogo
  de diagnósticos citológicos, anexos, índice hormonal y notas.
- **Emisión**: al guardar un informe completo pasa a **Entregado**, se sella la fecha
  y se habilita la impresión. Un informe emitido queda bloqueado; solo un administrador
  puede reabrirlo (auditado).
- **PDF archivado**: al emitir se genera el PDF en el servidor con **hash SHA-256** y
  versión, guardado en `storage/app/private/reports`.
- **Reportes**: producción mensual, pendientes por antigüedad, productividad por
  patólogo, procedencia y médicos que remiten.
- **Cuarentena**: bandeja para resolver las 4.245 anomalías detectadas en la migración
  (números duplicados, pacientes sin estudio, fechas corruptas, etc.) con nota y auditoría.
- **Usuarios** (solo admin): alta, edición, cambio de contraseña, baja y rol.
- **Auditoría**: quién creó, editó, emitió, reabrió, imprimió o respaldó, con IP y
  user-agent (`audit_events`, append-only).
- **Respaldos** (solo admin): generar y descargar respaldos comprimidos de la base.

## Roles

| Rol | Puede |
|---|---|
| `admin` | todo: operación diaria, reabrir emitidos, cuarentena, respaldos y usuarios |
| `operador` | pacientes, estudios, informes, emisión, impresión y reportes |

## Estados del estudio

```
recibido ──(primer guardado)──► en proceso ──(informe completo)──► ENTREGADO
                                      ▲                                 │
                                      └──────(reapertura, admin)────────┘
```

"Entregado" significa **informe terminado y habilitado para imprimir**, no entregado al
paciente. Los campos exigidos para emitir son los que el laboratorio realmente completa:
en biopsia, material + macro + diagnóstico microscópico; en citología, descripción +
diagnóstico + calidad. El detalle y su justificación están en
[docs/PUESTA_EN_MARCHA.md](docs/PUESTA_EN_MARCHA.md).

## Requisitos

- PHP 8.2 con `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`
- Composer 2
- MySQL/MariaDB
- Windows PowerShell 5.1 (solo para el ETL y los respaldos)

No requiere Node.js: los estilos vienen por CDN y no hay build de Vite en uso.

## Instalación

### Para no expertos: `instalar-como-admin.bat`

Con MySQL encendido en XAMPP, **doble clic en `instalar-como-admin.bat`** y aceptar el
aviso de administrador. Hace todo sin preguntas:

- instala dependencias, crea la base y el usuario administrador;
- configura **`http://laboratorio-cito-biop.test`**;
- deja Apache y MySQL arrancando **al encender la PC** (sobrevive a un corte de luz);
- crea la base de pruebas `lapt_test`.

Con datos: `instalar-como-admin.bat -Kit "D:\laboratorio-kit-XXXX.zip"`.

Si se rechaza el permiso de administrador no rompe nada; queda para usar `instalar.bat`.

### Equipo nuevo, sin clonar a mano: `instalar-desde-cero.bat`

Copia al pendrive estos tres archivos y ejecutá el `.bat` en el equipo nuevo:

| Archivo | Se genera en el equipo de origen con |
|---|---|
| `instalar-desde-cero.bat` | copiarlo de la raíz del proyecto |
| `laboratorio-codigo.zip` | `scripts\exportar_codigo.ps1` (≈0,7 MB) |
| `laboratorio-kit-XXXX.zip` | `scripts\exportar_kit.ps1` (≈7,5 MB) |

El lanzador **clona el repo** si hay Git y credenciales; si no (el repo es privado),
descomprime `laboratorio-codigo.zip`. Después detecta el kit y llama a
`instalar-como-admin.bat`: instala, carga datos y PDFs, configura el dominio `.test` y el
arranque automático. Requiere XAMPP instalado.

```bat
instalar-desde-cero.bat              :: clona (o usa el zip) + instala con datos
instalar-desde-cero.bat -SinGit      :: no intentar clonar
instalar-desde-cero.bat -SoloCodigo  :: solo obtener el código
```

### Automática con preguntas

Con MySQL encendido en XAMPP, doble clic en **`instalar.bat`**. Verifica requisitos,
crea `.env` y la clave, crea la base, instala dependencias, migra, crea el
administrador, prepara carpetas y (opcional) la base de pruebas.

```bat
instalar.bat                   :: interactivo
instalar.bat -Check            :: solo verifica requisitos
instalar.bat -Si               :: sin preguntas
instalar.bat -Si -Kit "D:\laboratorio-kit-XXXX.zip"   :: instala Y carga los datos
instalar.bat -DbPass "clave"   :: si MySQL tiene contrasena
instalar.bat -Si -SinVhost     :: no toca el dominio local
```

Con `-Kit` el instalador restaura la base, copia los PDFs archivados y deja el
sistema **con los datos del laboratorio**, no vacío (a menos que se pase
`-UsarEnvDelKit`, usa un `.env` nuevo y los usuarios solo deben volver a entrar).

Los pasos 11 y 12 configuran **dominio local** (`http://laboratorio-cito-biop.test`) y
**arranque automático** de Apache/MySQL. Requieren permisos de administrador para el
vhost y para el arranque al encender el equipo; si el instalador no los tiene, deja el
comando exacto para ejecutarlo aparte:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\setup-vhost.ps1   # hosts + vhost + reinicio
powershell -ExecutionPolicy Bypass -File scripts\autostart.ps1 -Modo Login      # al iniciar sesion
powershell -ExecutionPolicy Bypass -File scripts\autostart.ps1 -Modo Arranque   # al encender (admin)
```

Guía paso a paso, verificación y problemas comunes: **[docs/INSTALACION.md](docs/INSTALACION.md)**.

### Manual (resumen)

```powershell
composer install
copy .env.example .env
php artisan key:generate
# crear la base lapt_core y ajustar DB_* en .env
php artisan migrate --force
php artisan db:seed --force
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\test_db.ps1
```

### Con el histórico de Access

El orden importa: el ETL crea y carga las tablas clínicas; después las migraciones
agregan las columnas nuevas (`doctor_id`, `origin_id`, auditoría, PDF, etc.).

```powershell
# 1) Migrar el legado (ver etl/README.md para el detalle)
Get-Content etl/schema/001_staging.sql -Raw | & C:\xampp\mysql\bin\mysql.exe -u root
powershell -NoProfile -ExecutionPolicy Bypass -File etl/access_export.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File etl/staging_load.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File etl/transform.ps1

# 2) Migraciones de la aplicación (idempotentes: no recrean lo que ya existe)
php artisan migrate --force

# 3) Recuperar médico y procedencia del legado
Get-Content etl/transform/004_backfill_referrals.sql -Raw | & C:\xampp\mysql\bin\mysql.exe -u root

# 4) Usuario inicial
php artisan db:seed --force
```

Usuario del seeder: `admin@laboratorio.local` / `cambiar123` (cambiar al entrar).
Los demás usuarios se crean desde **Usuarios** (solo admin).

## Base de datos

| Base | Uso |
|---|---|
| `lapt_core` | operativa: pacientes, estudios, informes, PDFs, auditoría |
| `lapt_test` | copia aislada para los tests (`scripts/test_db.ps1`) |
| `lapt_staging` | staging crudo del ETL (la aplicación no la usa) |

## Tests

```powershell
C:\xampp\php\php.exe artisan test
```

**85 pruebas / 241 aserciones**: pantallas, alta y edición, numeración, emisión,
bloqueo y reapertura, PDF con hash, roles, usuarios, auditoría, reportes, cuarentena,
respaldos, validación de fechas y redirección de invitados.

> `phpunit.xml` fija `DB_DATABASE=lapt_test`: los tests corren sobre una **copia aislada**
> de la operativa, creada con `scripts/test_db.ps1`. Nunca tocan `lapt_core`, y las
> escrituras van en transacciones con rollback. Si falta la base, el test falla con
> `Unknown database 'lapt_test'`.

## Respaldos

Desde la app: **Respaldos → Generar respaldo ahora** (solo admin), con descarga.

Por consola:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/backup_db.ps1
```

Genera `storage/backups/lapt_core-AAAAMMDD-HHMMSS.sql.gz` (≈7,5 MB), conserva los
últimos 14 y verifica que no salga vacío. Para programarlo a diario:

```powershell
schtasks /Create /TN "Laboratorio backup" /SC DAILY /ST 20:00 /TR "powershell.exe -NoProfile -ExecutionPolicy Bypass -File C:\xampp\htdocs\laboratorio-cito-biop\scripts\backup_db.ps1"
```

Restaurar:

```powershell
gunzip -c storage\backups\lapt_core-XXXX.sql.gz | C:\xampp\mysql\bin\mysql.exe -u root lapt_core
```

> Un respaldo en el mismo equipo no protege contra un daño del disco: cópialo a otro
> medio y **probá la restauración** al menos una vez.

Para llevar el sistema a otro equipo (base + PDFs + `.env` + instrucciones):

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\exportar_kit.ps1
```

Genera un `.zip` en `storage\kit` y en el equipo nuevo se restaura con
`scripts\restore_db.ps1`. Detalle en
[docs/INSTALACION.md](docs/INSTALACION.md#llevar-el-sistema-a-otro-equipo).

## Seguridad

- CSRF en todos los formularios y sesiones con `APP_KEY`.
- Autorización por rol (`admin` / `operador`) en rutas, más comprobaciones en vistas.
- Registro público deshabilitado; altas de usuario solo por administrador.
- Informes emitidos bloqueados; reapertura exclusiva del administrador y auditada.
- PDF del informe con hash SHA-256 para verificar la copia archivada.
- Consultas con Eloquent y salida escapada por Blade.
- `.env` fuera del repositorio.

Antes de producción falta: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` real,
contraseña para el usuario de MySQL, HTTPS (o red cerrada), respaldo programado, y
actualizar dependencias (`composer audit` reporta avisos, la mayoría en Guzzle).

## Comandos útiles

```powershell
php artisan migrate --force      # aplicar migraciones
php artisan db:seed --force      # usuario inicial (idempotente)
php artisan optimize             # caché de config, rutas y vistas
php artisan optimize:clear       # limpiar cachés
php artisan test                 # suite completa
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/backup_db.ps1
```

## Estructura

```
app/
  Enums/                 CaseType, CaseStatus
  Http/Controllers/      Patients, Cases, Reports, Quarantine, Backups, Users
  Http/Middleware/       EnsureUserHasRole
  Http/Requests/         StoreCaseRequest
  Models/                Patient, ClinicalCase, informes, catálogos, auditoría, PDFs
  Services/              CaseCreator, CaseNumberGenerator, ReportPdfService,
                         AuditLogger, BackupService
database/migrations/     esquema reproducible del modelo clínico y de la app
etl/                     migración Access → MySQL (export, staging, transform, verificación)
resources/views/         Blade por módulo (layouts/core.blade.php)
scripts/backup_db.ps1    respaldo comprimido con rotación
docs/PUESTA_EN_MARCHA.md instalación, flujo clínico, roles, auditoría y despliegue
tests/Feature/           pruebas HTTP contra datos reales
```

## Migración desde Access

El paquete `etl/` lee el `.mdb` original (copia de trabajo, sin tocar el archivo), lo
carga en `lapt_staging`, lo transforma a `lapt_core` y reporta la reconciliación.
Detalles y decisiones en [etl/README.md](etl/README.md).

## Documentación

- [docs/PUESTA_EN_MARCHA.md](docs/PUESTA_EN_MARCHA.md) — instalación, flujo, roles,
  auditoría, respaldos y checklist de producción.
- [etl/README.md](etl/README.md) — pipeline de migración y hallazgos del legado.
