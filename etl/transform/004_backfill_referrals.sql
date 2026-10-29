-- =============================================================================
-- Backfill de referencia medica y procedencia desde el legado.
-- El transform inicial no las llevó a `patients`; se recuperan por código.
--
-- Rendimiento: el join directo contra legacy_pacientes.id_paciente (texto)
-- obliga a castear y pierde el índice de patients.legacy_id => O(n^2).
-- Solución: materializar el mapeo en una tabla temporal con PK numérica.
-- Idempotente: solo completa lo que está en NULL.
-- =============================================================================
USE lapt_core;

DROP TEMPORARY TABLE IF EXISTS tmp_ref;

CREATE TEMPORARY TABLE tmp_ref (
  legacy_id BIGINT UNSIGNED NOT NULL,
  dr        VARCHAR(60) NULL,
  proc      VARCHAR(60) NULL,
  PRIMARY KEY (legacy_id)
) ENGINE=InnoDB;

INSERT INTO tmp_ref (legacy_id, dr, proc)
SELECT CAST(TRIM(id_paciente) AS UNSIGNED),
       NULLIF(TRIM(dr), ''),
       NULLIF(TRIM(procedencia), '')
FROM lapt_staging.legacy_pacientes
WHERE COALESCE(TRIM(id_paciente), '') <> '';

UPDATE patients p
INNER JOIN tmp_ref t ON t.legacy_id = p.legacy_id
INNER JOIN doctors d ON d.legacy_code = t.dr
SET p.doctor_id = d.id
WHERE p.doctor_id IS NULL
  AND t.dr IS NOT NULL;

UPDATE patients p
INNER JOIN tmp_ref t ON t.legacy_id = p.legacy_id
INNER JOIN origins o ON o.legacy_code = t.proc
SET p.origin_id = o.id
WHERE p.origin_id IS NULL
  AND t.proc IS NOT NULL;

SELECT CONCAT('pacientes_con_medico=', COUNT(*)) AS resultado FROM patients WHERE doctor_id IS NOT NULL
UNION ALL
SELECT CONCAT('pacientes_con_procedencia=', COUNT(*)) FROM patients WHERE origin_id IS NOT NULL;
