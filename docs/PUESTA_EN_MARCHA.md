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
| `/reportes` | `reports.index` | Producción, pendientes y productividad (admin, patólogo, auditor) |

## Reportes

`/reportes?anio=2023&tipo=biopsy` — filtrable por año y tipo:

- **Totales del período**: estudios, biopsias, citologías, emitidos y en proceso.
- **Producción por mes** con barra proporcional y total anual.
- **Pendientes por antigüedad**: hasta 7 días, 8–30 y más de 30.
- **Sin patólogo titular** (porcentaje del período).
- **Productividad por patólogo**: casos y emitidos (rol titular).
- **Procedencia** y **médicos que remiten**: top 10.

Todas las cifras salen de `cases.received_at`; el histórico de Access entra completo.
La página se imprime con el botón del navegador (`window.print()`).

## Cuarentena

`/cuarentena` (solo admin) — las **4.245 anomalías** que el ETL no corrigió, en 12 grupos:

| Grupo | Casos | Qué decidir |
|---|---:|---|
| `DUPLICADO_NC` | 1.496 | número de citología repetido |
| `CI_PLACEHOLDER` | 1.096 | cédula `NS`/`SN`/`MENOR`: completar o dejar |
| `SIN_ESTUDIO` | 835 | paciente sin biopsia ni citología |
| `DUPLICADO_NB` | 271 | número de biopsia repetido |
| `INFORME_SIN_NUMERO` | 200 | informe emitido sin número |
| `NUMERO_SIN_INFORME` | 186 | número asignado sin informe |
| `FECHA_CORRUPTA` | 97 | años 0222/5202/9202 |
| `SEXO_INVALIDO` | 36 | valores `A`, `?`, `}` |
| `NUMERO_FUERA_PATRON` | 19 | no cumple secuencia+año |
| `HUERFANO_*` | 6 | informe sin paciente |
| `AMBOS_NB_NC` | 3 | paciente con biopsia y citología juntas |

Cada fila se resuelve como **Revisado** (con nota) o **Ignorar**; queda registrado en
auditoría (`quarantine.reviewed` / `quarantine.ignored`) con el usuario y la fecha.
Cuando la clave corresponde a un paciente del legado, la fila enlaza a su ficha.

El módulo **no corrige solo**: no renumera, no fusiona pacientes ni inventa valores.
La decisión es del laboratorio y queda auditada.

## Alta de pacientes y estudios

El alta está **separada** a propósito: en Access el paciente y el estudio eran la misma fila,
y eso impedía que una persona tuviera más de un estudio en su vida.

1. **Paciente** (`/pacientes/crear`): nombre obligatorio; cédula, sexo, edad, contacto,
   médico que remite y procedencia. Una cédula `NS`, `SN`, `MENOR`, `S/N`, `?` se guarda
   y se marca como *placeholder* (no se pierde el dato original).
2. **Estudio** (`/pacientes/{id}/estudios/crear`): tipo (biopsia/citología), número,
   fecha de recepción. El material y el diagnóstico clínico se cargan **días después**,
   en el editor, junto con macro/micro y el diagnóstico.

## Ciclo de vida y emisión

```
recibido ──(primer guardado)──► en proceso ──(informe completo)──► ENTREGADO
                                      ▲                                 │
                                      └──────(se borra un campo)────────┘
```

**"Entregado" no significa entregado al paciente**: significa *informe terminado y
habilitado para imprimir*. Es el mismo criterio con el que la operadora veía activarse
el botón de imprimir en Access.

| Tipo | Campos exigidos para emitir |
|---|---|
| Biopsia | material remitido, descripción macroscópica, diagnóstico microscópico |
| Citología | descripción citológica, diagnóstico citológico, calidad de la muestra |

Por qué **no** se exigen (medido sobre 91.457 estudios reales):

| Campo | Uso histórico | Consecuencia |
|---|---|---|
| Patólogo 1 | 0 de 399 biopsias en 2023 | exigirlo bloquearía todo |
| Descripción microscópica (biopsia) | 6,9% | es opcional en la práctica |
| Macro (citología) | 2,3% | casi nunca se usa |
| Sitio de la lesión | 87% | opcional |
| Diagnóstico clínico | 76% (bio) / 51% (cit) | opcional |

Otras reglas:

- Al guardar completo: `status = entregado`, `completed_at = ahora`, y **redirige a la
  ficha del estudio** con la impresión ya activa.
- Un informe **emitido queda bloqueado**: no se puede editar. Solo un administrador
  puede **reabrirlo**, y la reapertura queda auditada.
- **Casos históricos**: no se revalidan; siguen imprimibles aunque estén "en proceso".
- La impresión de un estudio nuevo sin emitir redirige al editor con un mensaje claro.

## Edición

| Qué | Ruta | Quién |
|---|---|---|
| Paciente (datos, contacto, médico, procedencia) | `/pacientes/{id}/editar` | admin, operador |
| Cabecera del estudio (número y fecha) | `/estudios/{id}/datos` | admin, operador |
| Informe | `/estudios/{id}/editar` | admin, operador |

El **tipo** del estudio no se puede cambiar: el informe ya tiene esa estructura.
Corregir el número recalcula secuencia y año, y valida que no exista otro igual.

## Roles

El laboratorio usa **dos roles**:

| Rol | Puede |
|---|---|
| `admin` | todo: operación diaria, reabrir informes emitidos, cuarentena y respaldos |
| `operador` | alta y edición de pacientes y estudios, informes, emisión, impresión y reportes |

El registro público está deshabilitado. Los usuarios se crean por consola:

```powershell
php artisan tinker --execute="App\Models\User::create(['name'=>'Operador','email'=>'operador@laboratorio.local','password'=>bcrypt('cambiar123'),'role'=>'operador','email_verified_at'=>now()]);"
```

## PDF archivado

Al emitirse un informe se genera el **PDF en el servidor** (dompdf) y se archiva en
`storage/app/private/reports` con:

- **hash SHA-256** del archivo (verificable),
- **número de versión** (cada reemisión crea una nueva),
- usuario que lo generó y fecha.

La ficha del estudio muestra el botón **PDF** (versión y hash). Es la copia fiel del
informe emitido; la impresión desde el navegador sigue disponible como antes.

## Auditoría

Cada acción importante queda en `audit_events` (append-only):

`patient.created`, `patient.updated`, `case.created`, `case.updated`, `case.emitted`,
`case.reverted`, `case.header_updated`, `case.reopened`, `case.printed`,
`case.pdf_generated`, `quarantine.reviewed`, `backup.created`.

Se guarda actor, acción, registro afectado, cambios, IP y user-agent. El historial se
ve al final de la ficha del estudio (últimos 20 eventos).

## Respaldo

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File scripts/backup_db.ps1
```

Genera `storage/backups/lapt_core-AAAAMMDD-HHMMSS.sql.gz` (≈7,5 MB, 2 s), conserva los
últimos 14 y verifica que no salga vacío. Para programarlo a diario a las 20:00:

```powershell
schtasks /Create /TN "Laboratorio backup" /SC DAILY /ST 20:00 /TR "powershell.exe -NoProfile -ExecutionPolicy Bypass -File C:\xampp\htdocs\laboratorio-cito-biop\scripts\backup_db.ps1"
```

Restaurar:

```powershell
gunzip -c storage\backups\lapt_core-XXXX.sql.gz | C:\xampp\mysql\bin\mysql.exe -u root lapt_core
```

## Numeración
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

75 pruebas / 203 aserciones: renderizado de pantallas contra datos reales, búsqueda,
alta y **edición** de pacientes y estudios, numeración (formato, secuencia, unicidad),
emisión del informe, **PDF archivado con hash**, **bloqueo y reapertura**, **roles**,
**auditoría**, **reportes**, **cuarentena**, **respaldos**, validación de fechas y
cosignante, y redirección de invitados.

> `phpunit.xml` fija `APP_URL=http://localhost` a propósito: con un `APP_URL` que
> incluya subdirectorio (`/laboratorio-cito-biop/public`), el helper de tests
> antepone ese path y todas las rutas devuelven 404. En Apache la base se detecta
> del request, así que `APP_URL` debe ser la raíz.

## Pendiente (siguiente iteración)

- Cargar el tramo 2023-09 → 2026-09 cuando llegue el archivo del laboratorio.
- Adjuntos (fotos macro/micro).
- CI en GitHub (requiere primero tests independientes de los datos reales).
- Endurecer dependencias: `composer audit` reporta avisos en Guzzle y otros (actualizar).
- Facturación: **fuera de alcance**, no se migra; se construirá con el modelo nuevo.
