# Instalación

Guía explícita y en orden. Si algo falla, cada paso dice qué deberías ver.

---

## 0. Antes de empezar

| Necesitás | Cómo comprobarlo |
|---|---|
| XAMPP con **PHP 8.2** | existe `C:\xampp\php\php.exe` |
| **MySQL** de XAMPP encendido | panel de XAMPP → MySQL → Start |
| **Composer 2** | en una consola: `composer --version` |
| El proyecto en `C:\xampp\htdocs\laboratorio-cito-biop` | esta carpeta |

> No hace falta Node.js ni compilar assets: los estilos vienen por CDN.

---

## Opción A — Instalador automático (recomendada)

1. Abrí el Panel de Control de XAMPP y **encendé MySQL** (Apache no hace falta todavía).
2. Doble clic en **`instalar.bat`** (en la raíz del proyecto).
3. Respondé dos preguntas:
   - ¿Crear la base de pruebas `lapt_test`? → **S/n** (recomendado S)
   - ¿Generar un respaldo ahora? → **s/N** (recomendado N la primera vez)
4. Al terminar vas a ver un resumen con la URL y las credenciales.

El instalador hace, en este orden:

| Paso | Qué hace |
|---|---|
| 1 | Verifica PHP, MySQL, conexión y Composer. Si falta algo, se detiene y lo dice. |
| 2 | Crea `.env` desde `.env.example` y genera `APP_KEY` si falta. |
| 3 | Crea la base `lapt_core` (utf8mb4) si no existe. |
| 4 | `composer install` (solo si falta `vendor/`). |
| 5 | `php artisan migrate --force` (idempotente). |
| 6 | `php artisan db:seed --force` (usuario administrador). |
| 7 | Prepara `storage/backups` y `storage/app/private/reports`. |
| 8 | Clona la base operativa en `lapt_test` para los tests. |
| 9 | Respaldo inicial (opcional). |

**Se puede volver a ejecutar** sin romper nada: omite lo que ya está hecho.

Solo verificar requisitos, sin instalar:

```bat
instalar.bat -Check
```

Sin preguntas:

```bat
instalar.bat -Si
```

Si tu MySQL tiene contraseña:

```bat
instalar.bat -DbPass "tu_clave"
```

---

## Opción B — Manual, paso a paso

Ejecutá desde `C:\xampp\htdocs\laboratorio-cito-biop`. Reemplazá `php` por
`C:\xampp\php\php.exe` si no está en el PATH.

### 1. Dependencias

```powershell
composer install
```

Deberías ver `Generating optimized autoload files` y la lista de paquetes.

### 2. Configuración

```powershell
copy .env.example .env
C:\xampp\php\php.exe artisan key:generate
```

Deberías ver `Application key set successfully`.

Editá `.env` y revisá:

```
APP_NAME="Laboratorio Becomarca"
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lapt_core
DB_USERNAME=root
DB_PASSWORD=
APP_TIMEZONE=America/Caracas
APP_LOCALE=es
```

> `APP_URL` debe ser la **raíz**, sin `/laboratorio-cito-biop/public`. Si le ponés el
> subdirectorio, los tests fallan con 404 y algunas URLs salen duplicadas.
> En Apache la base se detecta sola desde el request.

### 3. Base de datos

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS lapt_core CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Si tu MySQL tiene clave, agregá `-p`.

### 4. Esquema

```powershell
C:\xampp\php\php.exe artisan migrate --force
```

Deberías ver 8 migraciones (`users`, `cache`, `jobs`, modelo clínico, referencia médica,
auditoría, roles, PDFs, cuarentena). Si alguna tabla ya existe (por el ETL), la migración
la respeta.

### 5. Usuario inicial

```powershell
C:\xampp\php\php.exe artisan db:seed --force
```

Crea `admin@laboratorio.local` / `cambiar123` (o `ADMIN_EMAIL` / `ADMIN_PASSWORD` si los
definiste en `.env`).

### 6. Carpetas

```powershell
New-Item -ItemType Directory -Force storage\backups, storage\app\private\reports | Out-Null
```

### 7. Base de pruebas (para `php artisan test`)

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\test_db.ps1
```

Clona `lapt_core` en `lapt_test` (≈25 s). Los tests la usan por `phpunit.xml`.

---

## Verificación (5 minutos)

1. **Abrí** `http://localhost/laboratorio-cito-biop/public/login`
   → debe verse el login en español, con el nombre "Laboratorio Becomarca".
2. **Entrá** con `admin@laboratorio.local` / `cambiar123`.
3. **Pacientes** → buscá `CARLEZ` → debe aparecer una ficha con estudios.
4. **Estudios** → abrí uno → **Editar** → guardá → debe quedar **Entregado** y aparecer
   el botón **PDF**.
5. **Reportes** → debe cargar el año en curso.
6. **Respaldos** → *Generar respaldo ahora* → debe listar un `.sql.gz` de ≈7,5 MB.
7. **Tests**:

```powershell
C:\xampp\php\php.exe artisan test
```

Esperado: **85 pruebas / 241 aserciones** en verde.

---

## Legado (Access) — solo si vas a cargar el histórico

El orden es obligatorio: el ETL crea y carga las tablas clínicas; después las
migraciones agregan columnas nuevas. Si invertís el orden, se pierden `doctor_id` y
`origin_id`.

```powershell
# 1) Staging + export + carga
Get-Content etl\schema\001_staging.sql -Raw | & C:\xampp\mysql\bin\mysql.exe -u root
powershell -NoProfile -ExecutionPolicy Bypass -File etl\access_export.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File etl\staging_load.ps1

# 2) Transform al modelo clínico
powershell -NoProfile -ExecutionPolicy Bypass -File etl\transform.ps1

# 3) Migraciones (agregan columnas nuevas sin borrar datos)
C:\xampp\php\php.exe artisan migrate --force

# 4) Recuperar médico y procedencia
Get-Content etl\transform\004_backfill_referrals.sql -Raw | & C:\xampp\mysql\bin\mysql.exe -u root

# 5) Usuario inicial
C:\xampp\php\php.exe artisan db:seed --force
```

Antes de correrlo, copiá `prog_lapt.mdb` y `LAPT.mdb` juntos en la misma carpeta y cerrá
Access. El detalle completo está en [../etl/README.md](../etl/README.md).

---

## Actualizar la base de pruebas

Cuando cambien los datos o el esquema:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\test_db.ps1
```

Nunca apuntes `lapt_test` a producción ni corras los tests contra `lapt_core`:
`phpunit.xml` ya fija `DB_DATABASE=lapt_test`.

---

## Problemas comunes

| Síntoma | Causa / solución |
|---|---|
| `No se pudo conectar a MySQL` | MySQL apagado o clave distinta. Encendelo en XAMPP y pasá `-DbPass`. |
| `No se encontro Composer` | Instalá desde getcomposer.org y reabrí la consola. |
| 404 en todas las rutas | Estás entrando por otra URL; debe incluir `/laboratorio-cito-biop/public/`. |
| 404 solo en los tests | `APP_URL` con subdirectorio en `.env`. Dejalo en `http://localhost`. |
| 500 al abrir | Mirá `storage/logs/laravel.log`. |
| `Unknown database 'lapt_test'` en tests | Corré `scripts\test_db.ps1`. |
| Cambiaste `.env` y no toma | `php artisan optimize:clear`. |
| "Class not found" | `composer dump-autoload`. |
| Login da 419 | `php artisan optimize:clear` y entrá siempre por el mismo host. |

---

## Pasar a producción

Falta un paquete de configuración (no lo hace el instalador):

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` real.
- Usuario de MySQL con contraseña (hoy `root` sin clave).
- HTTPS o red cerrada.
- Respaldo **programado** (ver abajo) y una restauración probada.
- Actualizar dependencias (`composer audit` reporta avisos).

Programar el respaldo diario a las 20:00:

```powershell
schtasks /Create /TN "Laboratorio backup" /SC DAILY /ST 20:00 /TR "powershell.exe -NoProfile -ExecutionPolicy Bypass -File C:\xampp\htdocs\laboratorio-cito-biop\scripts\backup_db.ps1"
```

Checklist completo en [PUESTA_EN_MARCHA.md](PUESTA_EN_MARCHA.md).
