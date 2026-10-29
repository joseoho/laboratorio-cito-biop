-- =============================================================================
-- Reconciliacion del staging vs realidad operativa de Access
-- Salida: TSV (seccion \t metrica \t valor). La consume etl/reconcile.ps1
-- Uso: mysql -u root -B -N < etl/reconcile.sql
-- =============================================================================
USE lapt_staging;

SELECT 'VOLUMEN' AS seccion, 'pacientes' AS metrica, CAST(COUNT(*) AS CHAR) AS valor FROM legacy_pacientes
UNION ALL SELECT 'VOLUMEN','biopsias',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias
UNION ALL SELECT 'VOLUMEN','citologias',CAST(COUNT(*) AS CHAR) FROM legacy_citologia
UNION ALL SELECT 'VOLUMEN','medicos',CAST(COUNT(*) AS CHAR) FROM legacy_medico
UNION ALL SELECT 'VOLUMEN','procedencias',CAST(COUNT(*) AS CHAR) FROM legacy_procedencia
UNION ALL SELECT 'VOLUMEN','patologos',CAST(COUNT(*) AS CHAR) FROM legacy_patologo
UNION ALL SELECT 'VOLUMEN','plantillas',CAST(COUNT(*) AS CHAR) FROM legacy_plantillas
UNION ALL SELECT 'VOLUMEN','diagcito',CAST(COUNT(*) AS CHAR) FROM legacy_diagcito
UNION ALL SELECT 'VOLUMEN','anexos',CAST(COUNT(*) AS CHAR) FROM legacy_anexo
UNION ALL SELECT 'VOLUMEN','facturas',CAST(COUNT(*) AS CHAR) FROM legacy_facturas
UNION ALL SELECT 'VOLUMEN','detalle_examenes',CAST(COUNT(*) AS CHAR) FROM legacy_detalle_examenes
UNION ALL SELECT 'VOLUMEN','detalle_abonos',CAST(COUNT(*) AS CHAR) FROM legacy_detalle_abonos
UNION ALL SELECT 'VOLUMEN','examenes_catalogo',CAST(COUNT(*) AS CHAR) FROM legacy_examenes

-- -----------------------------------------------------------------------------
UNION ALL SELECT 'NUMERACION','nb_con_valor',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nb IS NOT NULL AND nb<>''
UNION ALL SELECT 'NUMERACION','nb_sin_valor',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nb IS NULL OR nb=''
UNION ALL SELECT 'NUMERACION','nc_con_valor',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nc IS NOT NULL AND nc<>''
UNION ALL SELECT 'NUMERACION','nc_sin_valor',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nc IS NULL OR nc=''
UNION ALL SELECT 'NUMERACION','con_ambos_nb_y_nc',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nb<>'' AND nc<>''
UNION ALL SELECT 'NUMERACION','sin_ninguno',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE (nb IS NULL OR nb='') AND (nc IS NULL OR nc='')
UNION ALL SELECT 'NUMERACION','nb_largo_6',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nb<>'' AND CHAR_LENGTH(nb)=6
UNION ALL SELECT 'NUMERACION','nb_otro_largo',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nb<>'' AND CHAR_LENGTH(nb)<>6
UNION ALL SELECT 'NUMERACION','nc_largo_7',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nc<>'' AND CHAR_LENGTH(nc)=7
UNION ALL SELECT 'NUMERACION','nc_otro_largo',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nc<>'' AND CHAR_LENGTH(nc)<>7
UNION ALL SELECT 'NUMERACION','nb_dup_grupos',CAST(COUNT(*) AS CHAR) FROM (SELECT nb FROM legacy_pacientes WHERE nb<>'' GROUP BY nb HAVING COUNT(*)>1) x
UNION ALL SELECT 'NUMERACION','nb_dup_filas',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes p INNER JOIN (SELECT nb FROM legacy_pacientes WHERE nb<>'' GROUP BY nb HAVING COUNT(*)>1) d ON p.nb=d.nb
UNION ALL SELECT 'NUMERACION','nc_dup_grupos',CAST(COUNT(*) AS CHAR) FROM (SELECT nc FROM legacy_pacientes WHERE nc<>'' GROUP BY nc HAVING COUNT(*)>1) x
UNION ALL SELECT 'NUMERACION','nc_dup_filas',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes p INNER JOIN (SELECT nc FROM legacy_pacientes WHERE nc<>'' GROUP BY nc HAVING COUNT(*)>1) d ON p.nc=d.nc
UNION ALL SELECT 'NUMERACION','nb_anio_fuera_rango',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nb<>'' AND (CAST(RIGHT(nb,2) AS UNSIGNED) NOT BETWEEN 10 AND 25)
UNION ALL SELECT 'NUMERACION','nc_anio_fuera_rango',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE nc<>'' AND (CAST(RIGHT(nc,2) AS UNSIGNED) NOT BETWEEN 10 AND 25)

-- -----------------------------------------------------------------------------
UNION ALL SELECT 'FECHAS','apertura_min',COALESCE(MIN(fecha_apertura),'-') FROM legacy_pacientes
UNION ALL SELECT 'FECHAS','apertura_max',COALESCE(MAX(fecha_apertura),'-') FROM legacy_pacientes
UNION ALL SELECT 'FECHAS','completado_min',COALESCE(MIN(fecha_comp),'-') FROM legacy_pacientes
UNION ALL SELECT 'FECHAS','completado_max',COALESCE(MAX(fecha_comp),'-') FROM legacy_pacientes
UNION ALL SELECT 'FECHAS','pacientes_completados',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE fecha_comp IS NOT NULL AND fecha_comp<>''
UNION ALL SELECT 'FECHAS','pacientes_sin_completar',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE fecha_comp IS NULL OR fecha_comp=''
UNION ALL SELECT 'FECHAS','bio_recibido_min',COALESCE(MIN(fecha_recibido),'-') FROM legacy_biopsias
UNION ALL SELECT 'FECHAS','bio_recibido_max',COALESCE(MAX(fecha_recibido),'-') FROM legacy_biopsias
UNION ALL SELECT 'FECHAS','cit_recibido_max',COALESCE(MAX(fecha_recibido),'-') FROM legacy_citologia
UNION ALL SELECT 'FECHAS','bio_fecha_corrupta',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias WHERE CAST(LEFT(fecha_recibido,4) AS UNSIGNED)>2025 OR CAST(LEFT(fecha_recibido,4) AS UNSIGNED)<2000 OR CAST(LEFT(fecha_envio,4) AS UNSIGNED)>2025
UNION ALL SELECT 'FECHAS','cit_fecha_corrupta',CAST(COUNT(*) AS CHAR) FROM legacy_citologia WHERE CAST(LEFT(fecha_recibido,4) AS UNSIGNED)>2025 OR CAST(LEFT(fecha_recibido,4) AS UNSIGNED)<2000 OR CAST(LEFT(fecha_envio,4) AS UNSIGNED)>2025
UNION ALL SELECT 'FECHAS','bio_sin_fecha_envio',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias WHERE fecha_envio IS NULL OR fecha_envio=''
UNION ALL SELECT 'FECHAS','cit_sin_fecha_envio',CAST(COUNT(*) AS CHAR) FROM legacy_citologia WHERE fecha_envio IS NULL OR fecha_envio=''

-- -----------------------------------------------------------------------------
UNION ALL SELECT 'INTEGRIDAD','bio_huerfanas',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias b LEFT JOIN legacy_pacientes p ON p.id_paciente=b.id_paciente WHERE p.id_paciente IS NULL
UNION ALL SELECT 'INTEGRIDAD','cit_huerfanas',CAST(COUNT(*) AS CHAR) FROM legacy_citologia c LEFT JOIN legacy_pacientes p ON p.id_paciente=c.id_paciente WHERE p.id_paciente IS NULL
UNION ALL SELECT 'INTEGRIDAD','pacientes_con_2mas_biopsias',CAST(COUNT(*) AS CHAR) FROM (SELECT id_paciente FROM legacy_biopsias GROUP BY id_paciente HAVING COUNT(*)>1) x
UNION ALL SELECT 'INTEGRIDAD','pacientes_con_2mas_citologias',CAST(COUNT(*) AS CHAR) FROM (SELECT id_paciente FROM legacy_citologia GROUP BY id_paciente HAVING COUNT(*)>1) x
UNION ALL SELECT 'INTEGRIDAD','bio_paciente_sin_nb',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias b INNER JOIN legacy_pacientes p ON p.id_paciente=b.id_paciente WHERE p.nb IS NULL OR p.nb=''
UNION ALL SELECT 'INTEGRIDAD','cit_paciente_sin_nc',CAST(COUNT(*) AS CHAR) FROM legacy_citologia c INNER JOIN legacy_pacientes p ON p.id_paciente=c.id_paciente WHERE p.nc IS NULL OR p.nc=''
UNION ALL SELECT 'INTEGRIDAD','id_paciente_duplicado_grupos',CAST(COUNT(*) AS CHAR) FROM (SELECT id_paciente FROM legacy_pacientes GROUP BY id_paciente HAVING COUNT(*)>1) x
UNION ALL SELECT 'INTEGRIDAD','ci_duplicada_filas',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes p INNER JOIN (SELECT ci FROM legacy_pacientes WHERE ci<>'' GROUP BY ci HAVING COUNT(*)>1) d ON p.ci=d.ci

-- -----------------------------------------------------------------------------
UNION ALL SELECT 'CALIDAD','ci_vacia',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE ci IS NULL OR ci=''
UNION ALL SELECT 'CALIDAD','ci_placeholder',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE UPPER(TRIM(ci)) IN ('NS','SN','S/N','?','MENOR','NC','N/A','NA')
UNION ALL SELECT 'CALIDAD','sexo_vacio',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE sexo IS NULL OR sexo=''
UNION ALL SELECT 'CALIDAD','edad_vacia',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE edad IS NULL OR edad=''
UNION ALL SELECT 'CALIDAD','edad_no_numerica',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE edad<>'' AND edad NOT REGEXP '^[0-9]+$'
UNION ALL SELECT 'CALIDAD','bio_sin_patologo1',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias WHERE medico_patologo_1 IS NULL OR medico_patologo_1=''
UNION ALL SELECT 'CALIDAD','cit_sin_patologo1',CAST(COUNT(*) AS CHAR) FROM legacy_citologia WHERE medico_patologo_1 IS NULL OR medico_patologo_1=''
UNION ALL SELECT 'CALIDAD','bio_sin_diagnostico',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias WHERE diagnostico_microscopico IS NULL OR diagnostico_microscopico=''
UNION ALL SELECT 'CALIDAD','cit_sin_diagnostico',CAST(COUNT(*) AS CHAR) FROM legacy_citologia WHERE diagnostico_citologico IS NULL OR diagnostico_citologico=''
UNION ALL SELECT 'CALIDAD','bio_sin_material',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias WHERE material_remitido IS NULL OR material_remitido=''
UNION ALL SELECT 'CALIDAD','cit_sin_calidad',CAST(COUNT(*) AS CHAR) FROM legacy_citologia WHERE calidad IS NULL OR calidad=''

-- -----------------------------------------------------------------------------
UNION ALL SELECT 'CAMPOS_MUERTOS','bio_con_foto',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias WHERE COALESCE(foto_1,'')<>'' OR COALESCE(foto_2,'')<>'' OR COALESCE(foto_3,'')<>''
UNION ALL SELECT 'CAMPOS_MUERTOS','bio_con_comentario',CAST(COUNT(*) AS CHAR) FROM legacy_biopsias WHERE COALESCE(com_1,'')<>'' OR COALESCE(com_2,'')<>'' OR COALESCE(com_3,'')<>''
UNION ALL SELECT 'CAMPOS_MUERTOS','fecha_nac_llena',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE fecha_nac IS NOT NULL AND fecha_nac<>''
UNION ALL SELECT 'CAMPOS_MUERTOS','sms_activo',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE sms='1'
UNION ALL SELECT 'CAMPOS_MUERTOS','abono_distinto_cero',CAST(COUNT(*) AS CHAR) FROM legacy_pacientes WHERE abono IS NOT NULL AND abono<>'' AND CAST(abono AS DECIMAL(14,2))<>0

-- -----------------------------------------------------------------------------
UNION ALL SELECT 'CATALOGOS','plantillas_mod_00',CAST(COUNT(*) AS CHAR) FROM legacy_plantillas WHERE modulo='00'
UNION ALL SELECT 'CATALOGOS','plantillas_mod_01',CAST(COUNT(*) AS CHAR) FROM legacy_plantillas WHERE modulo='01'
UNION ALL SELECT 'CATALOGOS','plantillas_mod_02',CAST(COUNT(*) AS CHAR) FROM legacy_plantillas WHERE modulo='02'
UNION ALL SELECT 'CATALOGOS','plantillas_otros_modulos',CAST(COUNT(*) AS CHAR) FROM legacy_plantillas WHERE modulo NOT IN ('00','01','02')
UNION ALL SELECT 'CATALOGOS','patologos_distintos',CAST(COUNT(DISTINCT patologo) AS CHAR) FROM legacy_patologo
UNION ALL SELECT 'CATALOGOS','patologos_vacios',CAST(COUNT(*) AS CHAR) FROM legacy_patologo WHERE patologo IS NULL OR patologo=''
UNION ALL SELECT 'CATALOGOS','medicos_distintos',CAST(COUNT(DISTINCT codigo) AS CHAR) FROM legacy_medico
UNION ALL SELECT 'CATALOGOS','procedencias_distintas',CAST(COUNT(DISTINCT codigo) AS CHAR) FROM legacy_procedencia
UNION ALL SELECT 'CATALOGOS','diagcito_codigos',CAST(COUNT(DISTINCT cod) AS CHAR) FROM legacy_diagcito
UNION ALL SELECT 'CATALOGOS','anexos_codigos',CAST(COUNT(DISTINCT cod) AS CHAR) FROM legacy_anexo

-- -----------------------------------------------------------------------------
UNION ALL SELECT 'FACTURACION','facturas_total',CAST(COUNT(*) AS CHAR) FROM legacy_facturas
UNION ALL SELECT 'FACTURACION','facturas_suma_total',CAST(COALESCE(SUM(CAST(NULLIF(total,'') AS DECIMAL(14,2))),0) AS CHAR) FROM legacy_facturas
UNION ALL SELECT 'FACTURACION','facturas_suma_abonos',CAST(COALESCE(SUM(CAST(NULLIF(abono,'') AS DECIMAL(14,2))),0) AS CHAR) FROM legacy_facturas
UNION ALL SELECT 'FACTURACION','facturas_fecha_min',COALESCE(MIN(fecha_hora),'-') FROM legacy_facturas
UNION ALL SELECT 'FACTURACION','facturas_fecha_max',COALESCE(MAX(fecha_hora),'-') FROM legacy_facturas
;
