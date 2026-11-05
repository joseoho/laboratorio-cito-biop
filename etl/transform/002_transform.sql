-- =============================================================================
-- TRANSFORM: legacy_* (staging) -> modelo clinico (lapt_core)
-- Reglas:
--   * El numero de estudio se copia TAL CUAL (nunca se renumera).
--   * Nada se descarta: toda anomalia va a quarantine con payload.
--   * No se revalida el pasado: se marca con flags, no se rechaza.
--   * Idempotente: 001 recrea el esquema, este script solo inserta.
-- =============================================================================

USE lapt_staging;

DROP TABLE IF EXISTS work_dup_nb;
DROP TABLE IF EXISTS work_dup_nc;
DROP TABLE IF EXISTS work_multi;

CREATE TABLE work_dup_nb (code VARCHAR(50) NOT NULL, PRIMARY KEY (code));
INSERT INTO work_dup_nb
SELECT TRIM(nb) FROM legacy_pacientes WHERE COALESCE(TRIM(nb),'')<>'' GROUP BY TRIM(nb) HAVING COUNT(*)>1;

CREATE TABLE work_dup_nc (code VARCHAR(50) NOT NULL, PRIMARY KEY (code));
INSERT INTO work_dup_nc
SELECT TRIM(nc) FROM legacy_pacientes WHERE COALESCE(TRIM(nc),'')<>'' GROUP BY TRIM(nc) HAVING COUNT(*)>1;

CREATE TABLE work_multi (id_paciente VARCHAR(40) NOT NULL, src VARCHAR(20) NOT NULL, PRIMARY KEY (id_paciente, src));
INSERT INTO work_multi
SELECT id_paciente, 'biopsias' FROM legacy_biopsias GROUP BY id_paciente HAVING COUNT(*)>1
UNION ALL
SELECT id_paciente, 'citologia' FROM legacy_citologia GROUP BY id_paciente HAVING COUNT(*)>1;

-- =============================================================================
USE lapt_core;
SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- 1. Catalogos
-- -----------------------------------------------------------------------------
-- El catalogo Patologo del legado esta incompleto: los informes usan mas nombres
-- (Coromoto Molina, Mariluz Garcia, Karen Mendez, Gladys Lopez). Se construye la
-- union de catalogo + uso real, normalizando el punto final.
INSERT INTO pathologists (name)
SELECT nombre FROM (
  SELECT DISTINCT TRIM(TRAILING '.' FROM TRIM(patologo)) AS nombre
  FROM lapt_staging.legacy_patologo WHERE COALESCE(TRIM(patologo),'')<>''
  UNION SELECT DISTINCT TRIM(TRAILING '.' FROM TRIM(medico_patologo_1)) FROM lapt_staging.legacy_biopsias WHERE COALESCE(TRIM(medico_patologo_1),'')<>''
  UNION SELECT DISTINCT TRIM(TRAILING '.' FROM TRIM(medico_patologo_2)) FROM lapt_staging.legacy_biopsias WHERE COALESCE(TRIM(medico_patologo_2),'')<>''
  UNION SELECT DISTINCT TRIM(TRAILING '.' FROM TRIM(medico_patologo_1)) FROM lapt_staging.legacy_citologia WHERE COALESCE(TRIM(medico_patologo_1),'')<>''
  UNION SELECT DISTINCT TRIM(TRAILING '.' FROM TRIM(medico_patologo_2)) FROM lapt_staging.legacy_citologia WHERE COALESCE(TRIM(medico_patologo_2),'')<>''
) x
ORDER BY nombre;

INSERT INTO doctors (legacy_code, name, phone)
SELECT TRIM(codigo), COALESCE(MAX(NULLIF(TRIM(nombre),'')), TRIM(codigo)), MAX(NULLIF(TRIM(telefono),''))
FROM lapt_staging.legacy_medico
WHERE COALESCE(TRIM(codigo),'')<>''
GROUP BY TRIM(codigo);

INSERT INTO origins (legacy_code, price_list)
SELECT TRIM(codigo), MAX(NULLIF(TRIM(lista_precio),''))
FROM lapt_staging.legacy_procedencia
WHERE COALESCE(TRIM(codigo),'')<>''
GROUP BY TRIM(codigo);

INSERT INTO templates (module, name, content)
SELECT NULLIF(TRIM(modulo),''), NULLIF(TRIM(nombre),''), contenido
FROM lapt_staging.legacy_plantillas;

INSERT INTO cytology_diagnosis_catalog (code, description, diagnosis, sort_order)
SELECT TRIM(cod), MAX(descripcion), MAX(diagnostico),
       MAX(CASE WHEN TRIM(orden) REGEXP '^[0-9]+$' THEN CAST(TRIM(orden) AS SIGNED) END)
FROM lapt_staging.legacy_diagcito
WHERE COALESCE(TRIM(cod),'')<>''
GROUP BY TRIM(cod);

INSERT INTO annexes (code, text, sort_order)
SELECT TRIM(cod), MAX(anexo),
       MAX(CASE WHEN TRIM(orden) REGEXP '^[0-9]+$' THEN CAST(TRIM(orden) AS SIGNED) END)
FROM lapt_staging.legacy_anexo
WHERE COALESCE(TRIM(cod),'')<>''
GROUP BY TRIM(cod);

-- -----------------------------------------------------------------------------
-- 2. Pacientes (una fila por IDPaciente del legado)
-- -----------------------------------------------------------------------------
INSERT INTO patients (
  legacy_id, document_number, document_is_placeholder, full_name, sex, legacy_sex_raw,
  age_at_entry, legacy_age_text, phone_code, phone, address, created_at, flags
)
SELECT
  CAST(NULLIF(TRIM(p.id_paciente),'') AS UNSIGNED),
  NULLIF(TRIM(p.ci),''),
  CASE WHEN UPPER(TRIM(p.ci)) IN ('NS','SN','S/N','?','MENOR','NC','N/A','NA') THEN 1 ELSE 0 END,
  NULLIF(TRIM(p.nombre),''),
  CASE
    WHEN p.sexo IS NULL OR TRIM(p.sexo)='' THEN NULL
    WHEN UPPER(TRIM(p.sexo))='F' THEN 'F'
    WHEN UPPER(TRIM(p.sexo))='M' THEN 'M'
    ELSE 'X'
  END,
  NULLIF(TRIM(p.sexo),''),
  CASE WHEN COALESCE(TRIM(p.edad),'')<>'' AND CAST(TRIM(p.edad) AS UNSIGNED) BETWEEN 1 AND 120
       THEN CAST(TRIM(p.edad) AS UNSIGNED) END,
  NULLIF(TRIM(p.edad),''),
  NULLIF(TRIM(p.cod_telf),''),
  NULLIF(TRIM(p.telefono),''),
  NULLIF(TRIM(p.direccion),''),
  CASE WHEN YEAR(p.fecha_apertura) BETWEEN 2000 AND 2026 THEN p.fecha_apertura END,
  CONCAT_WS(',',
    CASE WHEN COALESCE(TRIM(p.nb),'')='' AND COALESCE(TRIM(p.nc),'')='' THEN 'SIN_ESTUDIO' END,
    CASE WHEN COALESCE(TRIM(p.nb),'')<>'' AND COALESCE(TRIM(p.nc),'')<>'' THEN 'AMBOS' END,
    CASE WHEN COALESCE(TRIM(p.ci),'')='' THEN 'CI_VACIA'
         WHEN UPPER(TRIM(p.ci)) IN ('NS','SN','S/N','?','MENOR','NC','N/A','NA') THEN 'CI_PLACEHOLDER' END,
    CASE WHEN COALESCE(TRIM(p.sexo),'')<>'' AND UPPER(TRIM(p.sexo)) NOT IN ('F','M') THEN 'SEXO_INVALIDO' END,
    CASE WHEN COALESCE(TRIM(p.edad),'')<>'' AND CAST(TRIM(p.edad) AS UNSIGNED) NOT BETWEEN 1 AND 120
         THEN 'EDAD_INVALIDA' END
  )
FROM lapt_staging.legacy_pacientes p
WHERE COALESCE(TRIM(p.id_paciente),'')<>'';

-- -----------------------------------------------------------------------------
-- 3. Estudio BIOPSIA (una fila por registro de Biopsias)
--    El numero viene de pacientes.NB; el legado NO lo guarda en la biopsia.
-- -----------------------------------------------------------------------------
INSERT INTO cases (patient_id, type, case_code, sequence_no, year_no, status,
                   received_at, sent_at, completed_at, legacy_source, legacy_row_id, flags)
SELECT
  pat.id, 'biopsy',
  NULLIF(TRIM(p.nb),''),
  CASE WHEN TRIM(p.nb) REGEXP '^[0-9]{6}$' AND CAST(RIGHT(TRIM(p.nb),2) AS UNSIGNED) BETWEEN 10 AND 99
       THEN CAST(LEFT(TRIM(p.nb),4) AS UNSIGNED) END,
  CASE WHEN TRIM(p.nb) REGEXP '^[0-9]{6}$' AND CAST(RIGHT(TRIM(p.nb),2) AS UNSIGNED) BETWEEN 10 AND 99
       THEN 2000 + CAST(RIGHT(TRIM(p.nb),2) AS UNSIGNED) END,
  'received',
  CASE WHEN YEAR(b.fecha_recibido) BETWEEN 2000 AND 2026 THEN b.fecha_recibido END,
  CASE WHEN YEAR(b.fecha_envio)    BETWEEN 2000 AND 2026 THEN b.fecha_envio    END,
  CASE WHEN YEAR(p.fecha_comp)     BETWEEN 2000 AND 2026 THEN p.fecha_comp     END,
  'biopsias', b._row_id,
  CONCAT_WS(',',
    CASE WHEN COALESCE(TRIM(p.nb),'')='' THEN 'SIN_NUMERO' END,
    CASE WHEN COALESCE(TRIM(p.nb),'')<>'' AND NOT (TRIM(p.nb) REGEXP '^[0-9]{6}$'
              AND CAST(RIGHT(TRIM(p.nb),2) AS UNSIGNED) BETWEEN 10 AND 99) THEN 'NUMERO_FUERA_PATRON' END,
    CASE WHEN (COALESCE(TRIM(b.fecha_recibido),'')<>'' AND YEAR(b.fecha_recibido) NOT BETWEEN 2000 AND 2026)
           OR (COALESCE(TRIM(b.fecha_envio),'')<>''   AND YEAR(b.fecha_envio)    NOT BETWEEN 2000 AND 2026)
         THEN 'FECHA_CORRUPTA' END,
    CASE WHEN dnb.code IS NOT NULL THEN 'DUPLICADO' END,
    CASE WHEN m.id_paciente IS NOT NULL THEN 'MULTIPLE' END
  )
FROM lapt_staging.legacy_biopsias b
JOIN lapt_staging.legacy_pacientes p ON p.id_paciente = b.id_paciente
JOIN patients pat ON pat.legacy_id = CAST(NULLIF(TRIM(p.id_paciente),'') AS UNSIGNED)
LEFT JOIN lapt_staging.work_dup_nb dnb ON dnb.code = TRIM(p.nb)
LEFT JOIN lapt_staging.work_multi m ON m.id_paciente = b.id_paciente AND m.src = 'biopsias';

-- -----------------------------------------------------------------------------
-- 4. Estudio CITOLOGIA (una fila por registro de Citología)
-- -----------------------------------------------------------------------------
INSERT INTO cases (patient_id, type, case_code, sequence_no, year_no, status,
                   received_at, sent_at, completed_at, legacy_source, legacy_row_id, flags)
SELECT
  pat.id, 'cytology',
  NULLIF(TRIM(p.nc),''),
  CASE WHEN TRIM(p.nc) REGEXP '^[0-9]{7}$' AND CAST(RIGHT(TRIM(p.nc),2) AS UNSIGNED) BETWEEN 10 AND 99
       THEN CAST(LEFT(TRIM(p.nc),5) AS UNSIGNED) END,
  CASE WHEN TRIM(p.nc) REGEXP '^[0-9]{7}$' AND CAST(RIGHT(TRIM(p.nc),2) AS UNSIGNED) BETWEEN 10 AND 99
       THEN 2000 + CAST(RIGHT(TRIM(p.nc),2) AS UNSIGNED) END,
  'received',
  CASE WHEN YEAR(c.fecha_recibido) BETWEEN 2000 AND 2026 THEN c.fecha_recibido END,
  CASE WHEN YEAR(c.fecha_envio)    BETWEEN 2000 AND 2026 THEN c.fecha_envio    END,
  CASE WHEN YEAR(p.fecha_comp)     BETWEEN 2000 AND 2026 THEN p.fecha_comp     END,
  'citologia', c._row_id,
  CONCAT_WS(',',
    CASE WHEN COALESCE(TRIM(p.nc),'')='' THEN 'SIN_NUMERO' END,
    CASE WHEN COALESCE(TRIM(p.nc),'')<>'' AND NOT (TRIM(p.nc) REGEXP '^[0-9]{7}$'
              AND CAST(RIGHT(TRIM(p.nc),2) AS UNSIGNED) BETWEEN 10 AND 99) THEN 'NUMERO_FUERA_PATRON' END,
    CASE WHEN (COALESCE(TRIM(c.fecha_recibido),'')<>'' AND YEAR(c.fecha_recibido) NOT BETWEEN 2000 AND 2026)
           OR (COALESCE(TRIM(c.fecha_envio),'')<>''   AND YEAR(c.fecha_envio)    NOT BETWEEN 2000 AND 2026)
         THEN 'FECHA_CORRUPTA' END,
    CASE WHEN dnc.code IS NOT NULL THEN 'DUPLICADO' END,
    CASE WHEN m.id_paciente IS NOT NULL THEN 'MULTIPLE' END
  )
FROM lapt_staging.legacy_citologia c
JOIN lapt_staging.legacy_pacientes p ON p.id_paciente = c.id_paciente
JOIN patients pat ON pat.legacy_id = CAST(NULLIF(TRIM(p.id_paciente),'') AS UNSIGNED)
LEFT JOIN lapt_staging.work_dup_nc dnc ON dnc.code = TRIM(p.nc)
LEFT JOIN lapt_staging.work_multi m ON m.id_paciente = c.id_paciente AND m.src = 'citologia';

-- -----------------------------------------------------------------------------
-- 5. Informes
-- -----------------------------------------------------------------------------
INSERT INTO histology_reports (case_id, material, clinical_diagnosis, lesion_site,
                               macro_description, micro_description, micro_diagnosis, notes)
SELECT c.id, NULLIF(TRIM(b.material_remitido),''), NULLIF(TRIM(b.diagnostico_clinico),''),
       NULLIF(TRIM(b.sitio_lesion),''), b.descripcion_macroscopica, b.descripcion_microscopica,
       b.diagnostico_microscopico, b.notas
FROM lapt_staging.legacy_biopsias b
JOIN cases c ON c.legacy_source='biopsias' AND c.legacy_row_id = b._row_id;

INSERT INTO cytology_reports (case_id, material, clinical_diagnosis, macro_description,
                              cyto_description, cyto_diagnosis, quality, notes,
                              superficial, intermediate, parabasal, conclusion)
SELECT c.id, NULLIF(TRIM(k.material_remitido),''), NULLIF(TRIM(k.diagnostico_clinico),''),
       k.descripcion_macroscopica, k.descripcion_citologica, k.diagnostico_citologico,
       k.calidad, k.notas,
       CASE WHEN TRIM(k.superficiales) REGEXP '^[0-9]+$' THEN CAST(TRIM(k.superficiales) AS SIGNED) END,
       CASE WHEN TRIM(k.intermedias)   REGEXP '^[0-9]+$' THEN CAST(TRIM(k.intermedias)   AS SIGNED) END,
       CASE WHEN TRIM(k.parabasales)   REGEXP '^[0-9]+$' THEN CAST(TRIM(k.parabasales)   AS SIGNED) END,
       k.conclusion
FROM lapt_staging.legacy_citologia k
JOIN cases c ON c.legacy_source='citologia' AND c.legacy_row_id = k._row_id;

-- -----------------------------------------------------------------------------
-- 6. Asignacion de patologos (texto original + FK cuando existe)
-- -----------------------------------------------------------------------------
INSERT INTO case_assignments (case_id, pathologist_id, pathologist_name, role)
SELECT c.id, pa.id, TRIM(TRAILING '.' FROM TRIM(b.medico_patologo_1)), 1
FROM lapt_staging.legacy_biopsias b
JOIN cases c ON c.legacy_source='biopsias' AND c.legacy_row_id=b._row_id
LEFT JOIN pathologists pa ON pa.name = TRIM(TRAILING '.' FROM TRIM(b.medico_patologo_1))
WHERE COALESCE(TRIM(b.medico_patologo_1),'')<>'';

INSERT INTO case_assignments (case_id, pathologist_id, pathologist_name, role)
SELECT c.id, pa.id, TRIM(TRAILING '.' FROM TRIM(b.medico_patologo_2)), 2
FROM lapt_staging.legacy_biopsias b
JOIN cases c ON c.legacy_source='biopsias' AND c.legacy_row_id=b._row_id
LEFT JOIN pathologists pa ON pa.name = TRIM(TRAILING '.' FROM TRIM(b.medico_patologo_2))
WHERE COALESCE(TRIM(b.medico_patologo_2),'')<>'';

INSERT INTO case_assignments (case_id, pathologist_id, pathologist_name, role)
SELECT c.id, pa.id, TRIM(TRAILING '.' FROM TRIM(k.medico_patologo_1)), 1
FROM lapt_staging.legacy_citologia k
JOIN cases c ON c.legacy_source='citologia' AND c.legacy_row_id=k._row_id
LEFT JOIN pathologists pa ON pa.name = TRIM(TRAILING '.' FROM TRIM(k.medico_patologo_1))
WHERE COALESCE(TRIM(k.medico_patologo_1),'')<>'';

INSERT INTO case_assignments (case_id, pathologist_id, pathologist_name, role)
SELECT c.id, pa.id, TRIM(TRAILING '.' FROM TRIM(k.medico_patologo_2)), 2
FROM lapt_staging.legacy_citologia k
JOIN cases c ON c.legacy_source='citologia' AND c.legacy_row_id=k._row_id
LEFT JOIN pathologists pa ON pa.name = TRIM(TRAILING '.' FROM TRIM(k.medico_patologo_2))
WHERE COALESCE(TRIM(k.medico_patologo_2),'')<>'';

-- -----------------------------------------------------------------------------
-- 7. Estado derivado (el legado no tiene status: lo marca FechaComp)
-- -----------------------------------------------------------------------------
UPDATE cases SET status='delivered'   WHERE completed_at IS NOT NULL;
UPDATE cases SET status='in_progress' WHERE completed_at IS NULL
  AND (id IN (SELECT case_id FROM histology_reports) OR id IN (SELECT case_id FROM cytology_reports));

-- -----------------------------------------------------------------------------
-- 8. Cuarentena: anomalias que requieren decision humana
-- -----------------------------------------------------------------------------
INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'SIN_ESTUDIO','legacy_pacientes', p._row_id, p.id_paciente,
       'Paciente sin NB ni NC',
       JSON_OBJECT('id_paciente',p.id_paciente,'nombre',p.nombre,'ci',p.ci,
                   'fecha_apertura',p.fecha_apertura,'fecha_comp',p.fecha_comp)
FROM lapt_staging.legacy_pacientes p
WHERE COALESCE(TRIM(p.nb),'')='' AND COALESCE(TRIM(p.nc),'')='';

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'AMBOS_NB_NC','legacy_pacientes', p._row_id, p.id_paciente,
       'Paciente con NB y NC a la vez',
       JSON_OBJECT('id_paciente',p.id_paciente,'nombre',p.nombre,'nb',p.nb,'nc',p.nc)
FROM lapt_staging.legacy_pacientes p
WHERE COALESCE(TRIM(p.nb),'')<>'' AND COALESCE(TRIM(p.nc),'')<>'';

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'HUERFANO_BIOPSIA','legacy_biopsias', b._row_id, b.id_paciente,
       'Biopsia sin paciente en Pacientes',
       JSON_OBJECT('id_paciente',b.id_paciente,'fecha_recibido',b.fecha_recibido,
                   'diagnostico',LEFT(COALESCE(b.diagnostico_microscopico,''),200))
FROM lapt_staging.legacy_biopsias b
LEFT JOIN lapt_staging.legacy_pacientes p ON p.id_paciente=b.id_paciente
WHERE p.id_paciente IS NULL;

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'HUERFANO_CITOLOGIA','legacy_citologia', k._row_id, COALESCE(k.id_paciente,''),
       'Citologia sin paciente en Pacientes',
       JSON_OBJECT('id_paciente',k.id_paciente,'fecha_recibido',k.fecha_recibido,
                   'diagnostico',LEFT(COALESCE(k.diagnostico_citologico,''),200))
FROM lapt_staging.legacy_citologia k
LEFT JOIN lapt_staging.legacy_pacientes p ON p.id_paciente=k.id_paciente
WHERE p.id_paciente IS NULL;

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'FECHA_CORRUPTA','legacy_biopsias', b._row_id, b.id_paciente,
       CONCAT('recibido=',b.fecha_recibido,' envio=',b.fecha_envio),
       JSON_OBJECT('id_paciente',b.id_paciente,'fecha_recibido',b.fecha_recibido,'fecha_envio',b.fecha_envio)
FROM lapt_staging.legacy_biopsias b
WHERE (COALESCE(TRIM(b.fecha_recibido),'')<>'' AND YEAR(b.fecha_recibido) NOT BETWEEN 2000 AND 2026)
   OR (COALESCE(TRIM(b.fecha_envio),'')<>''    AND YEAR(b.fecha_envio)    NOT BETWEEN 2000 AND 2026);

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'FECHA_CORRUPTA','legacy_citologia', k._row_id, k.id_paciente,
       CONCAT('recibido=',k.fecha_recibido,' envio=',k.fecha_envio),
       JSON_OBJECT('id_paciente',k.id_paciente,'fecha_recibido',k.fecha_recibido,'fecha_envio',k.fecha_envio)
FROM lapt_staging.legacy_citologia k
WHERE (COALESCE(TRIM(k.fecha_recibido),'')<>'' AND YEAR(k.fecha_recibido) NOT BETWEEN 2000 AND 2026)
   OR (COALESCE(TRIM(k.fecha_envio),'')<>''    AND YEAR(k.fecha_envio)    NOT BETWEEN 2000 AND 2026);

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'INFORME_SIN_NUMERO','legacy_biopsias', b._row_id, COALESCE(b.id_paciente,''),
       'Biopsia de paciente sin NB',
       JSON_OBJECT('id_paciente',b.id_paciente,'nc_paciente',p.nc,'fecha_recibido',b.fecha_recibido)
FROM lapt_staging.legacy_biopsias b
JOIN lapt_staging.legacy_pacientes p ON p.id_paciente=b.id_paciente
WHERE COALESCE(TRIM(p.nb),'')='';

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'INFORME_SIN_NUMERO','legacy_citologia', k._row_id, COALESCE(k.id_paciente,''),
       'Citologia de paciente sin NC',
       JSON_OBJECT('id_paciente',k.id_paciente,'nb_paciente',p.nb,'fecha_recibido',k.fecha_recibido)
FROM lapt_staging.legacy_citologia k
JOIN lapt_staging.legacy_pacientes p ON p.id_paciente=k.id_paciente
WHERE COALESCE(TRIM(p.nc),'')='';

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'DUPLICADO_NB','legacy_pacientes', p._row_id, TRIM(p.nb),
       'Numero de biopsia repetido',
       JSON_OBJECT('id_paciente',p.id_paciente,'nombre',p.nombre,'nb',p.nb)
FROM lapt_staging.legacy_pacientes p
JOIN lapt_staging.work_dup_nb d ON d.code = TRIM(p.nb);

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'DUPLICADO_NC','legacy_pacientes', p._row_id, TRIM(p.nc),
       'Numero de citologia repetido',
       JSON_OBJECT('id_paciente',p.id_paciente,'nombre',p.nombre,'nc',p.nc)
FROM lapt_staging.legacy_pacientes p
JOIN lapt_staging.work_dup_nc d ON d.code = TRIM(p.nc);

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'NUMERO_FUERA_PATRON','legacy_pacientes', p._row_id,
       COALESCE(NULLIF(TRIM(p.nb),''), TRIM(p.nc)),
       'Numero no cumple formato secuencia+anio',
       JSON_OBJECT('id_paciente',p.id_paciente,'nb',p.nb,'nc',p.nc)
FROM lapt_staging.legacy_pacientes p
WHERE (COALESCE(TRIM(p.nb),'')<>'' AND NOT (TRIM(p.nb) REGEXP '^[0-9]{6}$'
         AND CAST(RIGHT(TRIM(p.nb),2) AS UNSIGNED) BETWEEN 10 AND 99))
   OR (COALESCE(TRIM(p.nc),'')<>'' AND NOT (TRIM(p.nc) REGEXP '^[0-9]{7}$'
         AND CAST(RIGHT(TRIM(p.nc),2) AS UNSIGNED) BETWEEN 10 AND 99));

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'SEXO_INVALIDO','legacy_pacientes', p._row_id, p.id_paciente,
       CONCAT('sexo=',COALESCE(p.sexo,'NULL')),
       JSON_OBJECT('id_paciente',p.id_paciente,'sexo',p.sexo,'nombre',p.nombre)
FROM lapt_staging.legacy_pacientes p
WHERE COALESCE(TRIM(p.sexo),'')<>'' AND UPPER(TRIM(p.sexo)) NOT IN ('F','M');

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'CI_PLACEHOLDER','legacy_pacientes', p._row_id, p.id_paciente,
       CONCAT('ci=',p.ci),
       JSON_OBJECT('id_paciente',p.id_paciente,'ci',p.ci,'nombre',p.nombre)
FROM lapt_staging.legacy_pacientes p
WHERE UPPER(TRIM(p.ci)) IN ('NS','SN','S/N','?','MENOR','NC','N/A','NA');

-- Paciente con numero asignado pero SIN el registro de estudio correspondiente.
-- No es un error del transform: es un numero emitido que nunca tuvo informe.
INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'NUMERO_SIN_INFORME','legacy_pacientes', p._row_id, TRIM(p.nb),
       'Paciente con NB pero sin registro en Biopsias',
       JSON_OBJECT('id_paciente',p.id_paciente,'nb',p.nb,'nombre',p.nombre,'fecha_apertura',p.fecha_apertura)
FROM lapt_staging.legacy_pacientes p
WHERE COALESCE(TRIM(p.nb),'')<>''
  AND NOT EXISTS (SELECT 1 FROM lapt_staging.legacy_biopsias b WHERE b.id_paciente = p.id_paciente);

INSERT INTO quarantine (bucket, legacy_table, legacy_row_id, legacy_key, detail, payload)
SELECT 'NUMERO_SIN_INFORME','legacy_pacientes', p._row_id, TRIM(p.nc),
       'Paciente con NC pero sin registro en Citologia',
       JSON_OBJECT('id_paciente',p.id_paciente,'nc',p.nc,'nombre',p.nombre,'fecha_apertura',p.fecha_apertura)
FROM lapt_staging.legacy_pacientes p
WHERE COALESCE(TRIM(p.nc),'')<>''
  AND NOT EXISTS (SELECT 1 FROM lapt_staging.legacy_citologia c WHERE c.id_paciente = p.id_paciente);

-- -----------------------------------------------------------------------------
-- 9. Bitacora
-- -----------------------------------------------------------------------------
INSERT INTO etl_runs (source, stats)
SELECT 'LAPT.mdb (prog_lapt.mdb) -> lapt_core', JSON_OBJECT(
  'patients',        (SELECT COUNT(*) FROM patients),
  'cases',           (SELECT COUNT(*) FROM cases),
  'cases_biopsy',    (SELECT COUNT(*) FROM cases WHERE type='biopsy'),
  'cases_cytology',  (SELECT COUNT(*) FROM cases WHERE type='cytology'),
  'histology_reports', (SELECT COUNT(*) FROM histology_reports),
  'cytology_reports',  (SELECT COUNT(*) FROM cytology_reports),
  'assignments',     (SELECT COUNT(*) FROM case_assignments),
  'doctors',         (SELECT COUNT(*) FROM doctors),
  'origins',         (SELECT COUNT(*) FROM origins),
  'pathologists',    (SELECT COUNT(*) FROM pathologists),
  'templates',       (SELECT COUNT(*) FROM templates),
  'quarantine',      (SELECT COUNT(*) FROM quarantine)
);

DROP TABLE IF EXISTS lapt_staging.work_dup_nb;
DROP TABLE IF EXISTS lapt_staging.work_dup_nc;
DROP TABLE IF EXISTS lapt_staging.work_multi;
