# Puesta en marcha

## Requisitos

| Pieza | Versión |
|---|---|
| PHP | 8.2 (XAMPP) con `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip` |
| MySQL / MariaDB | 10.4 (XAMPP) |
| Composer | 2.x |
| Apache | XAMPP, documento en `public/` |

## Instalación

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
```

`php artisan migrate` crea las tablas de Laravel (`users`, `cache`, `jobs`) y aplica la
migración del modelo clínico. Esa migración está **guardada por `Schema::hasTable`**:
si las tablas ya existen (porque las creó el ETL), no las toca ni borra datos.

## Base de datos

El sistema usa **una sola base operativa**: `lapt_core` (configurada en `.env`).

```
lapt_staging   staging crudo del ETL (no la usa la app)
lapt_core      pacientes, estudios, informes, catálogos, cuarentena  ← la app
```

## Credenciales iniciales

```
email:    admin@laboratorio.local
password: cambiar123
```

> Cambiar en producción: `ADMIN_EMAIL` y `ADMIN_PASSWORD` en `.env` antes de `db:seed`.
> El registro público está deshabilitado (`Auth::routes(['register' => false])`).

## Estructura

| Pieza | Ubicación |
|---|---|
| Modelos | `app/Models/` (`Patient`, `ClinicalCase`, `HistologyReport`, `CytologyReport`, …) |
| Enums | `app/Enums/` (`CaseType`, `CaseStatus`) |
| Controladores | `app/Http/Controllers/PatientController.php`, `CaseController.php` |
| Vistas | `resources/views/patients/`, `resources/views/cases/`, `layouts/core.blade.php` |
| Rutas | `routes/web.php` |
| Migración clínica | `database/migrations/2026_10_28_000001_create_clinical_core_tables.php` |
| Mensajes en español | `lang/es/` + `lang/es.json` |

## Pantallas

| Ruta | Nombre | Qué hace |
|---|---|---|
| `/pacientes` | `patients.index` | Buscar por CI/nombre, NB o NC; ver estudios por paciente |
| `/pacientes/crear` | `patients.create` | Alta de paciente (datos, contacto, médico y procedencia) |
| `/pacientes/{id}` | `patients.show` | Ficha + historial de estudios |
| `/pacientes/{id}/estudios/crear` | `cases.create` | Alta de estudio con número sugerido |
| `/estudios` | `cases.index` | Filtros por tipo, estado, año y texto |
| `/estudios/{id}` | `cases.show` | Detalle del informe |
| `/estudios/{id}/editar` | `cases.edit` | Editor de dos columnas con plantillas |
| `/estudios/{id}/imprimir` | `cases.print` | Informe listo para `window.print()` |

## Alta de pacientes y estudios

El alta está **separada** a propósito: en Access el paciente y el estudio eran la misma fila,
y eso impedía que una persona tuviera más de un estudio en su vida.

1. **Paciente** (`/pacientes/crear`): nombre obligatorio; cédula, sexo, edad, contacto,
   médico que remite y procedencia. Una cédula `NS`, `SN`, `MENOR`, `S/N`, `?` se guarda
   y se marca como *placeholder* (no se pierde el dato original).
2. **Estudio** (`/pacientes/{id}/estudios/crear`): tipo (biopsia/citología), número,
   fecha de recepción, material y diagnóstico clínico.

**Numeración** (`App\Services\CaseNumberGenerator`): el sistema propone el siguiente
número del año con el formato histórico.

| Tipo | Formato | Ejemplo |
|---|---|---|
| Biopsia | `NNNN` + `YY` | `083526` = biopsia 835 de 2026 |
| Citología | `NNNNN` + `YY` | `0360626` = citología 3606 de 2026 |

- El número se puede corregir a mano (el laboratorio a veces ya lo asignó).
- Se valida largo, formato y **unicidad por tipo**: un número nuevo no puede repetirse.
- Se crea el estudio **con su informe vacío** en la misma transacción, para que el editor
  tenga dónde escribir.
- Fecha de recepción no puede ser futura.

## Pruebas

```powershell
php artisan test
```

32 pruebas / 79 aserciones: renderizado de las pantallas contra datos reales,
búsqueda, alta de pacientes y estudios, numeración (formato, secuencia, unicidad),
guardado dentro de una transacción con rollback, validación de fechas y cosignante,
y redirección de invitados.

> `phpunit.xml` fija `APP_URL=http://localhost` a propósito: con un `APP_URL` que
> incluya subdirectorio (`/laboratorio-cito-biop/public`), el helper de tests
> antepone ese path y todas las rutas devuelven 404. En Apache la base se detecta
> del request, así que `APP_URL` debe ser la raíz.

## Pendiente (siguiente iteración)

- Firma: bloquear el paso a `delivered` si faltan campos (`missingForSignature()` ya existe).
- Adjuntos (fotos macro/micro) y PDF server-side.
- Roles y permisos (hoy solo autenticación).
- Edición de pacientes y estudios ya creados.
- Facturación: **fuera de alcance**, no se migra; se construirá con el modelo nuevo.
