# ETL · Migración Access 2000 → MySQL (staging)

Extrae los datos del sistema legado (`prog_lapt.mdb` → back-end `LAPT.mdb`) a una base
de **staging cruda** en MySQL/MariaDB, sin tocar la aplicación ni la base operativa.

## Por qué existe

La app Laravel arranca vacía y el laboratorio tiene **13 años de historia** en Access.
Antes de escribir una sola migración del modelo nuevo hay que ver los datos reales:

- 92.263 pacientes · 82.375 citologías · 9.088 biopsias
- numeración `secuencia + año` (no correlativa simple)
- 1.496 citologías y 271 biopsias con número duplicado
- 835 pacientes sin número de estudio
- 69 fechas corruptas (años 0222, 5202, 9202…)
- 2.275 facturas de un módulo que la app nueva no tiene

## Requisitos

| Pieza | Versión usada |
|---|---|
| Windows PowerShell | **5.1** (necesita `System.Data.OleDb`; no sirve PowerShell 7) |
| Proveedor Access | `Microsoft.ACE.OLEDB.12.0` (64 bits) |
| MySQL / MariaDB | MariaDB 10.4.32 (XAMPP) |
| `mysql.exe` | `C:\xampp\mysql\bin\mysql.exe` |

## Pipeline (3 pasos)

```powershell
# 1. Crear la base y las 15 tablas legacy_*
Get-Content etl/schema/001_staging.sql -Raw | & C:\xampp\mysql\bin\mysql.exe -u root

# 2. Exportar Access -> CSV  (lee una COPIA del .mdb, nunca el original)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File etl/access_export.ps1

# 3. Cargar CSV -> MySQL + conciliar conteos
powershell.exe -NoProfile -ExecutionPolicy Bypass -File etl/staging_load.ps1

# 4. Reporte de reconciliación (KPIs + 8 secciones)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File etl/reconcile.ps1
```

Salida esperada: `etl/out/reconciliation.md` + `reconciliation.json`.

## Diseño (decisiones que importan)

1. **Staging todo texto.** Ninguna columna se tipa al cargar. Cero fallos por
   `"36 AÑOS"` en `Edad`, fechas año 5202 o `CI = "NS"`. El tipado es un paso
   posterior y explícito (`transform`), no un efecto colateral de la carga.
2. **Copia de trabajo.** El export copia el `.mdb` a `%TEMP%` y abre esa copia en
   `Mode=Read`. No genera `.ldb`, no bloquea Access, no modifica el original.
3. **CSV explícito.** UTF-8 sin BOM, comillas dobles, `NULL` como `\N` sin comillas,
   `\` duplicado y `"` escapada como `\"` (lo que MySQL desescapa con `ESCAPED BY`).
   Fechas ISO `yyyy-MM-dd HH:mm:ss`, preservando años corruptos para auditarlos.
4. **Mapa posicional** `etl/schema/column_map.json`: columnas de Access → columnas
   de staging. Se valida el conteo contra el manifest; si no cuadra, el load aborta.
5. **Verificación doble.** Conteo por tabla (Access vs MySQL) + suma de IDs
   (`SUM(IDPaciente)`) en pacientes/biopsias/citologías.

## Verificación de fidelidad (ejecutada)

| Prueba | Resultado |
|---|---|
| 15 tablas cargadas | 15 OK, 0 diferencias |
| Filas totales | 194.105 |
| `SUM(IDPaciente)` pacientes | `4.257.954.042` en ambos lados |
| `SUM(IDPaciente)` biopsias/citologías | idénticas |
| Acentos UTF-8 (`C3B3` = ó, `C391` = Ñ) | preservados (4.496 nombres, 371 plantillas) |
| Saltos de línea en memos | 196 plantillas con LF real, 0 con `\n` literal |
| Comillas dobles en texto | 11 biopsias, cargadas sin warning |

### Anomalía conocida (abierta)

`SELECT COUNT(*) FROM Pacientes` en Access devuelve **92.264**, pero el proveedor
solo entrega **92.263** filas. La suma de IDs coincide exactamente, así que **no hay
pérdida de información clínica**: es un registro fantasma/borrado que Jet cuenta y no
materializa. Pendiente: compactar una copia del `.mdb` con Access y verificar la
tabla `Pacientes` a mano.

## Transform (`lapt_staging` → `lapt_core`)

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File etl/transform.ps1

# una vez, tras el transform: recupera médico y procedencia del legado
Get-Content etl/transform/004_backfill_referrals.sql -Raw | & C:\xampp\mysql\bin\mysql.exe -u root
```

Crea el modelo clínico destino y carga 13 años de historia en **40 s**:

```
patients 1─N cases 1─1 histology_reports
                 └─1 cytology_reports
                 └─N  case_assignments → pathologists
```

| Tabla | Filas |
|---|---|
| `patients` | 92.263 |
| `cases` (biopsia 9.083 / citología 82.374) | 91.457 |
| `histology_reports` / `cytology_reports` | 9.083 / 82.374 |
| `case_assignments` | 155.125 (0 sin FK) |
| `pathologists` / `doctors` / `origins` | 9 / 1.475 / 34 |
| `quarantine` | 4.245 |

Decisiones del transform:

- **El número se copia tal cual** (`case_code`), nunca se renumera. `sequence_no` y
  `year_no` se derivan para indexar; si el formato no cumple, quedan `NULL` + flag.
- **`status` se deriva de `fecha_comp`**: 33.169 `delivered`, 58.288 `in_progress`.
- **Nada se descarta**: cada anomalía va a `quarantine` con `payload` JSON.
- **No se revalida el pasado**: se marca con `flags`, no se rechaza.

### Cuarentena (decisiones pendientes de la operadora)

| Bucket | Filas | Qué hay que decidir |
|---|---:|---|
| `DUPLICADO_NC` | 1.496 | número repetido: cuál es el válido |
| `CI_PLACEHOLDER` | 1.096 | `NS`/`SN`/`MENOR`: normalizar o dejar |
| `SIN_ESTUDIO` | 835 | pacientes sin NB ni NC |
| `DUPLICADO_NB` | 271 | idem biopsias |
| `INFORME_SIN_NUMERO` | 200 | informe emitido sin número |
| `NUMERO_SIN_INFORME` | 186 | número emitido sin informe (30 NB / 156 NC) |
| `FECHA_CORRUPTA` | 97 | años 0222/5202/9202 |
| `SEXO_INVALIDO` | 36 | valores `A`, `?`, `}`… |
| `NUMERO_FUERA_PATRON` | 19 | 14 en casos reales |
| `HUERFANO_*` | 6 | informe sin paciente |
| `AMBOS_NB_NC` | 3 | paciente con biopsia y citología |

### Hallazgo crítico: 9 patólogos, no 5

El catálogo `Patologo` del legado tiene 5 nombres, pero los informes usan **9**.
Faltan en el formulario Laravel: **Coromoto Molina (15.903), Mariluz García (10.223),
Karen Méndez (774) y Gladys López (3)** — el 29% de las firmas históricas. El
transform los reconstruye desde el uso real, no desde el catálogo.

## Siguiente paso

1. **Documento de paridad** para que la operadora valide los cambios (pendiente).
2. **Decisión de facturación**: 2.275 facturas siguen solo en staging.
3. **Conectar la app** al modelo nuevo (migraciones + modelos + controllers).

