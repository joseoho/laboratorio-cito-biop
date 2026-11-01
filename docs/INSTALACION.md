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
   Si ya tenés el kit de traslado del otro equipo, pasalo como parámetro y hace todo:
   `instalar.bat -Si -Kit "D:\laboratorio-kit-XXXX.zip"`.
3. Respondé dos preguntas:
   - ¿Crear la base de pruebas `lapt_test`? → **S/n** (recomendado S)
   - ¿Generar un respaldo ahora? → **s/N** (recomendado N la primera vez)
4. Al terminar vas a ver un resumen con la URL, las credenciales y si quedó **con datos o vacía**.

El instalador hace, en este orden:

| Paso | Qué hace |
|---|---|
| 1 | Verifica PHP, MySQL, conexión y Composer. Si falta algo, se detiene y lo dice. |
| 2 | Crea `.env`, ajusta `DB_DATABASE` al nombre pedido y genera `APP_KEY` si falta. |
| 3 | Crea la base `lapt_core` (utf8mb4) si no existe. |
| 4 | `composer install` (solo si falta `vendor/`). |
| 5 | `php artisan migrate --force` (idempotente). |
| 6 | `php artisan db:seed --force` (usuario administrador). |
| 7 | Prepara `storage/backups` y `storage/app/private/reports`. |
| 8 | **Si hay `-Kit`**: restaura la base, copia los PDFs y (opcional) el `.env`. |
| 9 | Clona la base operativa en `lapt_test` para los tests. |
| 10 | Respaldo inicial (opcional). |
| 11 | **Sitio local** `http://laboratorio-cito-biop.test` (si hay administrador). |
| 12 | **Arranque automático** de Apache y MySQL (opcional). |

### Dominio local y arranque automático

El instalador **pregunta** por el dominio y por el arranque, pero los dos requieren
acciones del sistema que se pueden hacer aparte:

**Dominio `http://laboratorio-cito-biop.test`** (requiere consola **como administrador**):

```powershell
powershell -ExecutionPolicy Bypass -File scripts\setup-vhost.ps1 -DryRun   # ver qué haría
powershell -ExecutionPolicy Bypass -File scripts\setup-vhost.ps1           # aplicar
powershell -ExecutionPolicy Bypass -File scripts\setup-vhost.ps1 -Remove   # quitar
```

Agrega la línea al `hosts`, habilita el `Include` de vhosts si está comentado y agrega
el `<VirtualHost>` con `DocumentRoot` a `...\public`. Respalda `hosts`, `httpd.conf` y
`httpd-vhosts.conf` como `*.bak-laboratorio` antes de tocar nada. **No borra** vhosts
existentes (en este equipo ya conviven `localhost`, `ganaderia_SaaS.test` y
`facturacion-inventario.test`). Después hay que reiniciar Apache; el script lo intenta y,
si no puede, avisa.

**Arranque de servicios** (para que el sistema vuelva solo después de un corte de luz):

```powershell
# Al iniciar sesión (no requiere administrador)
powershell -ExecutionPolicy Bypass -File scripts\autostart.ps1 -Modo Login

# Al ARRANCAR Windows, sin que nadie inicie sesión (requiere administrador)
powershell -ExecutionPolicy Bypass -File scripts\autostart.ps1 -Modo Arranque

# Quitar
powershell -ExecutionPolicy Bypass -File scripts\autostart.ps1 -Modo Login -Remove
powershell -ExecutionPolicy Bypass -File scripts\autostart.ps1 -Modo Arranque -Remove
```

| Modo | Cuándo arranca | Requiere admin | Sirve para corte de luz |
|---|---|---|---|
| `Login` | al iniciar sesión del usuario | no | solo si alguien inicia sesión |
| `Arranque` | al encender Windows (tarea como SYSTEM) | sí | **sí** |

En los dos casos el script es idempotente: si Apache o MySQL ya están corriendo, no hace
nada, y deja rastro en `storage\logs\xampp-autostart.log`.

**Se puede volver a ejecutar** sin romper nada: omite lo que ya está hecho.

Solo verificar requisitos, sin instalar:

```bat
instalar.bat -Check
```

Sin preguntas:

```bat
instalar.bat -Si
```

Con datos del laboratorio (kit generado con `scripts\exportar_kit.ps1`):

```bat
instalar.bat -Si -Kit "D:\laboratorio-kit-20261101-183016.zip"
```

Opcional: copiar también el `.env` del kit (misma `APP_KEY`, sesiones intactas):

```bat
instalar.bat -Si -Kit "D:\kit.zip" -UsarEnvDelKit
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

## Llevar el sistema a otro equipo

La data **no viaja en el código**: vive en MySQL. Para que el sistema funcione igual en
otra máquina hacen falta cuatro cosas.

| Qué | Dónde está | Cómo viaja |
|---|---|---|
| Código | repo de GitHub | `git clone` |
| Base operativa (`lapt_core`) | MySQL, 135 MB | respaldo `.sql.gz` (7,5 MB) |
| PDFs archivados | `storage/app/private/reports` | carpeta tal cual (no están en la base ni en git) |
| `.env` | raíz del proyecto | copia manual (APP_KEY y credenciales) |

**No hace falta copiar**: `lapt_staging` (intermedia del ETL, se regenera), `lapt_test`
(se crea con `scripts/test_db.ps1`), `vendor/`, el `.mdb` original ni los respaldos viejos.

### Kit automático

En el equipo actual:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\exportar_kit.ps1
```

Genera `storage\kit\laboratorio-kit-AAAAMMDD-HHMMSS.zip` con la base, los PDFs, el `.env`
y un `LEEME.txt` con los pasos. Copiá ese `.zip` a un pendrive.

> El `.env` del kit da acceso a la base y contiene la `APP_KEY`: tratalo como una credencial.

### En el equipo nuevo, en orden

```powershell
# 1) XAMPP 8.2 + Composer instalados y MySQL encendido

# 2) Código
git clone https://github.com/joseoho/laboratorio-cito-biop.git C:\xampp\htdocs\laboratorio-cito-biop
cd C:\xampp\htdocs\laboratorio-cito-biop

# 3) TODO en un paso: dependencias, base, admin, datos del kit y PDFs
instalar.bat -Si -Kit "D:\laboratorio-kit-20261101-183016.zip"

# 4) Limpiar cachés y probar
C:\xampp\php\php.exe artisan optimize:clear
# abrir http://localhost/laboratorio-cito-biop/public/login
```

El instalador informa al final `Datos: Datos restaurados desde el kit: ...` y el
conteo de pacientes/estudios.

Si preferís hacerlo manual (o el kit se descomprimió a una carpeta):

```powershell
instalar.bat -Si
powershell -File scripts\restore_db.ps1 -File "RUTA\base\lapt_core-XXXX.sql.gz"
xcopy /E /I "RUTA\reports" "storage\app\private\reports"
C:\xampp\php\php.exe artisan optimize:clear
```

### Cuidados

- La restauración **reemplaza** las tablas (`mysqldump` incluye `DROP TABLE`). No la corras
  sobre una base con datos nuevos: usá una base limpia o hacé respaldo antes.
- Si copiás el `.env` del equipo viejo, las sesiones siguen válidas y la `APP_KEY` es la misma.
  Si generás una nueva, los usuarios solo tienen que volver a entrar (no hay columnas cifradas).
- **Sin la carpeta `reports`**, el botón PDF devuelve 404 aunque la base tenga los registros,
  y los hashes guardados no coinciden con ningún archivo.
- Restaurá en MariaDB 10.4+ o MySQL 8; evitar una versión más vieja que la de origen.
- Si cambia la carpeta o el dominio, ajustá `APP_URL` (siempre la raíz, sin subdirectorio).

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
