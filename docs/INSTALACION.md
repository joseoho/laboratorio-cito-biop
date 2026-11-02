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

### Un solo archivo: `instalar.bat`

1. Encendé **MySQL** desde el Panel de Control de XAMPP.
2. **Doble clic en `instalar.bat`** (en la raíz del proyecto o en el pendrive).
3. Windows va a pedir permiso de administrador: elegí **Sí**.
4. Esperá. No pregunta nada y no hay que escribir comandos.

Al terminar queda:

- la app instalada, con `.env`, `APP_KEY`, base `lapt_core` y usuario administrador;
- el dominio **`http://laboratorio-cito-biop.test`** funcionando;
- Apache y MySQL arrancando **solo al encender la PC** (sirve tras un corte de luz);
- si pasaste `--kit`, los datos del laboratorio y los PDFs archivados.

Con datos:

```bat
instalar.bat --kit "D:\laboratorio-kit-20261101-183016.zip"
```

### Opciones

| Opción | Para qué |
|---|---|
| `--kit "ruta"` | restaura la base y copia los PDFs del kit |
| `--sin-vhost` | no toca `hosts` ni Apache (deja la app en `/public`) |
| `--sin-arranque` | no programa el arranque automático |
| `--sin-admin` | no pide elevación (diagnóstico; el vhost y el arranque fallarán) |
| `--check` | **solo diagnostica**, no instala nada |

Si tu MySQL tiene contraseña, editá `DB_PASS` al inicio de `instalar.bat`.

### Qué hace, en orden

| Paso | Qué hace |
|---|---|
| 1 | Descarga el proyecto: `git clone`; si falla, `laboratorio-codigo.zip` o el ZIP del repo. |
| 2 | Prepara Composer (lo descarga como `composer.phar` si no está). |
| 3 | Crea `.env` desde `.env.example` (si no existe). |
| 4 | `composer install` y `APP_KEY`. |
| 5 | Verifica MySQL (lo arranca si hace falta) y crea `lapt_core`. |
| 6 | Configura `.env` (`APP_URL`, `DB_*`, zona horaria) y respalda si ya había datos. |
| 7 | `migrate --force`, `db:seed --force` y, si hay `--kit`, restaura datos y PDFs. |
| 8 | Configura el vhost `.test` (respalda y valida con `httpd -t`). |
| 9 | Programa el arranque de Apache/MySQL al encender el equipo. |

**Se puede volver a ejecutar** sin romper nada: omite lo que ya está hecho.

Si rechazás el permiso de administrador, no rompe nada: podés correr
`instalar.bat --sin-vhost --sin-arranque` y la app queda funcionando en
`http://localhost/laboratorio-cito-biop/public`.

### Dominio local y arranque automático por separado

El instalador ya los configura. Si preferís hacerlo a mano:

**Dominio `http://laboratorio-cito-biop.test`** (consola **como administrador**):

```powershell
powershell -ExecutionPolicy Bypass -File scripts\setup-vhost.ps1 -DryRun   # ver qué haría
powershell -ExecutionPolicy Bypass -File scripts\setup-vhost.ps1           # aplicar
powershell -ExecutionPolicy Bypass -File scripts\setup-vhost.ps1 -Remove   # quitar
```

Agrega la línea al `hosts`, habilita el `Include` de vhosts si está comentado y agrega
el `<VirtualHost>` con `DocumentRoot` a `...\public`. Respalda `hosts`, `httpd.conf` y
`httpd-vhosts.conf` como `*.bak-laboratorio` antes de tocar nada. **No borra** vhosts
existentes (en este equipo ya conviven `localhost`, `ganaderia_SaaS.test` y
`facturacion-inventario.test`). Valida con `httpd -t` y reinicia; si no puede reiniciar,
avisa.

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

Solo verificar requisitos, sin instalar ni pedir administrador:

```bat
instalar.bat --check
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
| Código | repo de GitHub | `instalar-desde-cero.bat` (clona) o `laboratorio-codigo.zip` |
| Base operativa (`lapt_core`) | MySQL, 135 MB | respaldo `.sql.gz` (7,5 MB) |
| PDFs archivados | `storage/app/private/reports` | carpeta tal cual (no están en la base ni en git) |
| `.env` | raíz del proyecto | copia manual (APP_KEY y credenciales) |

**No hace falta copiar**: `lapt_staging` (intermedia del ETL, se regenera), `lapt_test`
(se crea con `scripts/test_db.ps1`), `vendor/`, el `.mdb` original ni los respaldos viejos.

### Equipo nuevo desde el pendrive (recomendado)

El repositorio es **privado**: `git clone` pide usuario y contraseña. Por eso el pendrive
lleva tres archivos y el lanzador resuelve el resto:

| Archivo | Cómo se genera en el equipo de origen |
|---|---|
| `instalar-desde-cero.bat` | se copia tal cual desde la raíz del proyecto |
| `laboratorio-codigo.zip` | `powershell -File scripts\exportar_codigo.ps1` (≈0,7 MB) |
| `laboratorio-kit-XXXX.zip` | `powershell -File scripts\exportar_kit.ps1` (≈7,5 MB) |

En el equipo nuevo, **doble clic en `instalar-desde-cero.bat`**. El lanzador:

1. Verifica que XAMPP esté instalado.
2. Si hay Git y credenciales: `git clone` (o `git pull` si ya existe).
3. Si no hay Git, o el clon falla: descomprime `laboratorio-codigo.zip`.
4. Detecta el `laboratorio-kit-*.zip` que esté al lado y lo pasa como `--kit`.
5. Llama a `instalar.bat`, que pide administrador **una vez** e instala todo
   (dependencias, base, datos, PDFs, dominio `.test` y arranque al encender).

Opciones:

```bat
instalar-desde-cero.bat                       :: todo automático
instalar-desde-cero.bat -Kit "D:\otro.zip"    :: usar otro kit
instalar-desde-cero.bat -SinGit               :: no intentar clonar
instalar-desde-cero.bat -SoloCodigo           :: solo bajar/extraer el código
instalar-desde-cero.bat -Destino "D:\otra"    :: otra carpeta (default C:\xampp\htdocs\laboratorio-cito-biop)
```

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
instalar.bat --kit "D:\laboratorio-kit-20261101-183016.zip"

# 4) Limpiar cachés y probar
C:\xampp\php\php.exe artisan optimize:clear
# abrir http://laboratorio-cito-biop.test/login
```

El instalador informa al final la URL, las credenciales y si aplicó el kit.

Si preferís hacerlo manual (o el kit se descomprimió a una carpeta):

```powershell
instalar.bat --sin-vhost
powershell -File scripts\aplicar_kit.ps1 -Kit "RUTA\laboratorio-kit-XXXX.zip"
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
| `No se pudo conectar a MySQL` | MySQL apagado o clave distinta. Encendelo en XAMPP o editá `DB_PASS` al inicio de `instalar.bat`. |
| `No se encontro Composer` | Instalá desde getcomposer.org y reabrí la consola. |
| 404 en todas las rutas | Estás entrando por otra URL; debe incluir `/laboratorio-cito-biop/public/`. |
| 404 solo en los tests | `APP_URL` con subdirectorio en `.env`. Dejalo en `http://localhost`. |
| 500 al abrir | Mirá `storage/logs/laravel.log`. |
| `Unknown database 'lapt_test'` en tests | Corré `scripts\test_db.ps1`. |
| Cambiaste `.env` y no toma | `php artisan optimize:clear`. |
| "Class not found" | `composer dump-autoload`. |
| Login da 419 | `php artisan optimize:clear` y entrá siempre por el mismo host. |
| **No puedo entrar con el admin** | El kit trae los usuarios del equipo de origen. Reseteá la clave: `C:\xampp\php\php.exe scripts\reset_admin.php admin@laboratorio.local "NuevaClave123"`. Para ver qué usuarios hay: `scripts\reset_admin.php --list`. |

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
