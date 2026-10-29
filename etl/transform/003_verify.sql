-- =============================================================================
-- Verificacion del transform: no perdida, numeracion, estado, cuarentena
-- Salida TSV: seccion \t metrica \t valor
-- =============================================================================
USE lapt_core;

SELECT 'VOLUMEN' AS seccion, 'patients' AS metrica, CAST(COUNT(*) AS CHAR) AS valor FROM patients
UNION ALL SELECT 'VOLUMEN','cases',CAST(COUNT(*) AS CHAR) FROM cases
UNION ALL SELECT 'VOLUMEN','cases_biopsy',CAST(COUNT(*) AS CHAR) FROM cases WHERE type='biopsy'
UNION ALL SELECT 'VOLUMEN','cases_cytology',CAST(COUNT(*) AS CHAR) FROM cases WHERE type='cytology'
UNION ALL SELECT 'VOLUMEN','histology_reports',CAST(COUNT(*) AS CHAR) FROM histology_reports
UNION ALL SELECT 'VOLUMEN','cytology_reports',CAST(COUNT(*) AS CHAR) FROM cytology_reports
UNION ALL SELECT 'VOLUMEN','case_assignments',CAST(COUNT(*) AS CHAR) FROM case_assignments
UNION ALL SELECT 'VOLUMEN','quarantine',CAST(COUNT(*) AS CHAR) FROM quarantine
UNION ALL SELECT 'VOLUMEN','doctors',CAST(COUNT(*) AS CHAR) FROM doctors
UNION ALL SELECT 'VOLUMEN','origins',CAST(COUNT(*) AS CHAR) FROM origins
UNION ALL SELECT 'VOLUMEN','pathologists',CAST(COUNT(*) AS CHAR) FROM pathologists
UNION ALL SELECT 'VOLUMEN','templates',CAST(COUNT(*) AS CHAR) FROM templates
UNION ALL SELECT 'VOLUMEN','cytology_catalog',CAST(COUNT(*) AS CHAR) FROM cytology_diagnosis_catalog
UNION ALL SELECT 'VOLUMEN','annexes',CAST(COUNT(*) AS CHAR) FROM annexes

-- Conservacion: todo lo que estaba en staging esta en core o en cuarentena ---------
UNION ALL SELECT 'CONSERVACION','pacientes_staging',CAST(COUNT(*) AS CHAR) FROM lapt_staging.legacy_pacientes
UNION ALL SELECT 'CONSERVACION','pacientes_core',CAST(COUNT(*) AS CHAR) FROM patients
UNION ALL SELECT 'CONSERVACION','biopsias_staging',CAST(COUNT(*) AS CHAR) FROM lapt_staging.legacy_biopsias
UNION ALL SELECT 'CONSERVACION','biopsias_en_core',CAST(COUNT(*) AS CHAR) FROM cases WHERE legacy_source='biopsias'
UNION ALL SELECT 'CONSERVACION','biopsias_huerfanas',CAST(COUNT(*) AS CHAR) FROM quarantine WHERE bucket='HUERFANO_BIOPSIA'
UNION ALL SELECT 'CONSERVACION','citologias_staging',CAST(COUNT(*) AS CHAR) FROM lapt_staging.legacy_citologia
UNION ALL SELECT 'CONSERVACION','citologias_en_core',CAST(COUNT(*) AS CHAR) FROM cases WHERE legacy_source='citologia'
UNION ALL SELECT 'CONSERVACION','citologias_huerfanas',CAST(COUNT(*) AS CHAR) FROM quarantine WHERE bucket='HUERFANO_CITOLOGIA'
UNION ALL SELECT 'CONSERVACION','suma_legacy_id_core',CAST(SUM(legacy_id) AS CHAR) FROM patients
UNION ALL SELECT 'CONSERVACION','suma_id_paciente_staging',CAST(SUM(CAST(id_paciente AS UNSIGNED)) AS CHAR) FROM lapt_staging.legacy_pacientes
UNION ALL SELECT 'CONSERVACION','casos_sin_informe',CAST(COUNT(*) AS CHAR) FROM cases c WHERE NOT EXISTS (SELECT 1 FROM histology_reports h WHERE h.case_id=c.id) AND NOT EXISTS (SELECT 1 FROM cytology_reports y WHERE y.case_id=c.id)
UNION ALL SELECT 'CONSERVACION','informes_sin_caso',CAST(COUNT(*) AS CHAR) FROM histology_reports h LEFT JOIN cases c ON c.id=h.case_id WHERE c.id IS NULL

-- Numeracion: el codigo se copio tal cual -----------------------------------------
UNION ALL SELECT 'NUMERACION','nb_con_valor_staging',CAST(COUNT(*) AS CHAR) FROM lapt_staging.legacy_pacientes WHERE COALESCE(TRIM(nb),'')<>''
UNION ALL SELECT 'NUMERACION','biopsias_con_code',CAST(COUNT(*) AS CHAR) FROM cases WHERE type='biopsy' AND case_code IS NOT NULL
UNION ALL SELECT 'NUMERACION','biopsias_sin_code',CAST(COUNT(*) AS CHAR) FROM cases WHERE type='biopsy' AND case_code IS NULL
UNION ALL SELECT 'NUMERACION','nc_con_valor_staging',CAST(COUNT(*) AS CHAR) FROM lapt_staging.legacy_pacientes WHERE COALESCE(TRIM(nc),'')<>''
UNION ALL SELECT 'NUMERACION','citologias_con_code',CAST(COUNT(*) AS CHAR) FROM cases WHERE type='cytology' AND case_code IS NOT NULL
UNION ALL SELECT 'NUMERACION','citologias_sin_code',CAST(COUNT(*) AS CHAR) FROM cases WHERE type='cytology' AND case_code IS NULL
UNION ALL SELECT 'NUMERACION','biopsias_sin_anio',CAST(COUNT(*) AS CHAR) FROM cases WHERE type='biopsy' AND case_code IS NOT NULL AND year_no IS NULL
UNION ALL SELECT 'NUMERACION','citologias_sin_anio',CAST(COUNT(*) AS CHAR) FROM cases WHERE type='cytology' AND case_code IS NOT NULL AND year_no IS NULL
UNION ALL SELECT 'NUMERACION','years_biopsy',CAST(COUNT(DISTINCT year_no) AS CHAR) FROM cases WHERE type='biopsy' AND year_no IS NOT NULL
UNION ALL SELECT 'NUMERACION','years_cytology',CAST(COUNT(DISTINCT year_no) AS CHAR) FROM cases WHERE type='cytology' AND year_no IS NOT NULL
UNION ALL SELECT 'NUMERACION','codigos_duplicados_en_core',CAST(COUNT(*) AS CHAR) FROM (SELECT type, case_code FROM cases WHERE case_code IS NOT NULL GROUP BY type, case_code HAVING COUNT(*)>1) d
UNION ALL SELECT 'NUMERACION','numeros_sin_informe',CAST(COUNT(*) AS CHAR) FROM quarantine WHERE bucket='NUMERO_SIN_INFORME'
UNION ALL SELECT 'NUMERACION','numeros_sin_informe_NB',CAST(COUNT(*) AS CHAR) FROM quarantine WHERE bucket='NUMERO_SIN_INFORME' AND detail LIKE 'Paciente con NB%'
UNION ALL SELECT 'NUMERACION','numeros_sin_informe_NC',CAST(COUNT(*) AS CHAR) FROM quarantine WHERE bucket='NUMERO_SIN_INFORME' AND detail LIKE 'Paciente con NC%'

-- Estado derivado de FechaComp ----------------------------------------------------
UNION ALL SELECT 'ESTADO','delivered',CAST(COUNT(*) AS CHAR) FROM cases WHERE status='delivered'
UNION ALL SELECT 'ESTADO','in_progress',CAST(COUNT(*) AS CHAR) FROM cases WHERE status='in_progress'
UNION ALL SELECT 'ESTADO','received',CAST(COUNT(*) AS CHAR) FROM cases WHERE status='received'
UNION ALL SELECT 'ESTADO','cancelled',CAST(COUNT(*) AS CHAR) FROM cases WHERE status='cancelled'
UNION ALL SELECT 'ESTADO','con_completed_at',CAST(COUNT(*) AS CHAR) FROM cases WHERE completed_at IS NOT NULL
UNION ALL SELECT 'ESTADO','sin_completed_at',CAST(COUNT(*) AS CHAR) FROM cases WHERE completed_at IS NULL

-- Asignaciones --------------------------------------------------------------------
UNION ALL SELECT 'ASIGNACIONES','casos_sin_patologo',CAST(COUNT(*) AS CHAR) FROM cases c WHERE NOT EXISTS (SELECT 1 FROM case_assignments a WHERE a.case_id=c.id)
UNION ALL SELECT 'ASIGNACIONES','asignaciones_sin_fk',CAST(COUNT(*) AS CHAR) FROM case_assignments WHERE pathologist_id IS NULL
UNION ALL SELECT 'ASIGNACIONES','asignaciones_rol1',CAST(COUNT(*) AS CHAR) FROM case_assignments WHERE role=1
UNION ALL SELECT 'ASIGNACIONES','asignaciones_rol2',CAST(COUNT(*) AS CHAR) FROM case_assignments WHERE role=2

-- Calidad del paciente -------------------------------------------------------------
UNION ALL SELECT 'CALIDAD','sexo_X',CAST(COUNT(*) AS CHAR) FROM patients WHERE sex='X'
UNION ALL SELECT 'CALIDAD','sexo_null',CAST(COUNT(*) AS CHAR) FROM patients WHERE sex IS NULL
UNION ALL SELECT 'CALIDAD','edad_null',CAST(COUNT(*) AS CHAR) FROM patients WHERE age_at_entry IS NULL
UNION ALL SELECT 'CALIDAD','ci_placeholder',CAST(COUNT(*) AS CHAR) FROM patients WHERE document_is_placeholder=1
UNION ALL SELECT 'CALIDAD','casos_con_flag_SIN_NUMERO',CAST(COUNT(*) AS CHAR) FROM cases WHERE flags LIKE '%SIN_NUMERO%'
UNION ALL SELECT 'CALIDAD','casos_con_flag_FECHA_CORRUPTA',CAST(COUNT(*) AS CHAR) FROM cases WHERE flags LIKE '%FECHA_CORRUPTA%'
UNION ALL SELECT 'CALIDAD','casos_con_flag_DUPLICADO',CAST(COUNT(*) AS CHAR) FROM cases WHERE flags LIKE '%DUPLICADO%'
UNION ALL SELECT 'CALIDAD','casos_con_flag_MULTIPLE',CAST(COUNT(*) AS CHAR) FROM cases WHERE flags LIKE '%MULTIPLE%'
UNION ALL SELECT 'CALIDAD','casos_con_flag_NUMERO_FUERA_PATRON',CAST(COUNT(*) AS CHAR) FROM cases WHERE flags LIKE '%NUMERO_FUERA_PATRON%'

-- Cuarentena por bucket -------------------------------------------------------------
UNION ALL SELECT 'CUARENTENA', bucket, CAST(COUNT(*) AS CHAR) FROM quarantine GROUP BY bucket;
