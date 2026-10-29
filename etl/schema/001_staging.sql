-- =============================================================================
-- ETL · LABORATORIO ANATOMIA PATOLOGICA
-- Staging crudo (todo texto) que replica 1:1 el back-end Access LAPT.mdb
-- Objetivo: cero fallos de carga, maxima fidelidad, reconciliacion auditable.
-- NO toca la base operativa de la aplicacion.
-- Ejecutar:  mysql -u root < etl/schema/001_staging.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS lapt_staging
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lapt_staging;

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- Pacientes (92k). OJO: sin status, sin mail, sin created_at.
-- El estado real del estudio lo marca fecha_comp.
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS legacy_pacientes;
CREATE TABLE legacy_pacientes (
  _row_id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_paciente      VARCHAR(40)  NULL,   -- IDPaciente (Long)
  fecha_apertura   VARCHAR(30)  NULL,   -- Date
  ci               VARCHAR(50)  NULL,
  nombre           VARCHAR(120) NULL,
  sexo             VARCHAR(5)   NULL,   -- Text(1)
  direccion        VARCHAR(200) NULL,   -- Dirección
  cod_telf         VARCHAR(10)  NULL,   -- CodTelf (Area)
  telefono         VARCHAR(20)  NULL,   -- Teléfono
  fecha_nac        VARCHAR(30)  NULL,   -- FechaNac (nunca usada: 0 filas)
  edad             VARCHAR(50)  NULL,   -- Text(50): "36 AÑOS"
  procedencia      VARCHAR(50)  NULL,
  dr               VARCHAR(50)  NULL,
  resto            VARCHAR(40)  NULL,   -- Currency
  abono            VARCHAR(40)  NULL,   -- Currency
  descuento        VARCHAR(40)  NULL,   -- Currency (columna "Desc")
  nb               VARCHAR(50)  NULL,
  nc               VARCHAR(50)  NULL,
  sms              VARCHAR(10)  NULL,   -- Integer NOT NULL en Access
  sms_status       VARCHAR(50)  NULL,
  fecha_comp       VARCHAR(30)  NULL,
  _loaded_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id),
  KEY ix_pac_id     (id_paciente),
  KEY ix_pac_ci     (ci),
  KEY ix_pac_nb     (nb),
  KEY ix_pac_nc     (nc),
  KEY ix_pac_fecha  (fecha_apertura),
  KEY ix_pac_comp   (fecha_comp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Biopsias (9k). Sin NB: el numero vive solo en pacientes.nb.
-- foto_1..3 y com_1..3 nunca se usaron (0 filas).
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS legacy_biopsias;
CREATE TABLE legacy_biopsias (
  _row_id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_paciente                VARCHAR(40)  NULL,
  fecha_recibido             VARCHAR(30)  NULL,
  fecha_envio                VARCHAR(30)  NULL,
  material_remitido          VARCHAR(100) NULL,
  diagnostico_clinico        VARCHAR(100) NULL,
  sitio_lesion               VARCHAR(100) NULL,
  descripcion_macroscopica   LONGTEXT     NULL,
  descripcion_microscopica   LONGTEXT     NULL,
  diagnostico_microscopico   LONGTEXT     NULL,
  notas                      LONGTEXT     NULL,
  medico_patologo_1          VARCHAR(50)  NULL,
  medico_patologo_2          VARCHAR(50)  NULL,
  foto_1                     VARCHAR(50)  NULL,
  foto_2                     VARCHAR(50)  NULL,
  foto_3                     VARCHAR(50)  NULL,
  com_1                      VARCHAR(50)  NULL,
  com_2                      VARCHAR(50)  NULL,
  com_3                      VARCHAR(50)  NULL,
  _loaded_at                 TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id),
  KEY ix_bio_pac (id_paciente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Citologia (82k). Sin NC: el numero vive solo en pacientes.nc.
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS legacy_citologia;
CREATE TABLE legacy_citologia (
  _row_id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_paciente                VARCHAR(40)  NULL,
  fecha_recibido             VARCHAR(30)  NULL,
  fecha_envio                VARCHAR(30)  NULL,
  material_remitido          VARCHAR(250) NULL,
  diagnostico_clinico        VARCHAR(60)  NULL,
  descripcion_macroscopica   LONGTEXT     NULL,
  descripcion_citologica     LONGTEXT     NULL,
  diagnostico_citologico     LONGTEXT     NULL,
  notas                      LONGTEXT     NULL,
  medico_patologo_1          VARCHAR(50)  NULL,
  medico_patologo_2          VARCHAR(50)  NULL,
  superficiales              VARCHAR(20)  NULL,
  intermedias                VARCHAR(20)  NULL,
  parabasales                VARCHAR(20)  NULL,
  conclusion                 LONGTEXT     NULL,
  calidad                    LONGTEXT     NULL,
  _loaded_at                 TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id),
  KEY ix_cit_pac (id_paciente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Catalogos
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS legacy_plantillas;
CREATE TABLE legacy_plantillas (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  modulo      VARCHAR(10) NULL,   -- 00 macro | 01 micro | 02 diagnostico | 99 "NO BORRAR"
  nombre      VARCHAR(50) NULL,
  contenido   LONGTEXT    NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id),
  KEY ix_pl_mod (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_diagcito;
CREATE TABLE legacy_diagcito (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod         VARCHAR(50) NULL,
  descripcion LONGTEXT    NULL,
  diagnostico LONGTEXT    NULL,
  orden       VARCHAR(20) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_anexo;
CREATE TABLE legacy_anexo (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod         VARCHAR(50) NULL,
  anexo       LONGTEXT    NULL,
  orden       VARCHAR(20) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_medico;
CREATE TABLE legacy_medico (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo      VARCHAR(50) NULL,
  nombre      VARCHAR(100) NULL,
  telefono    VARCHAR(50) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id),
  KEY ix_med_cod (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_procedencia;
CREATE TABLE legacy_procedencia (
  _row_id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo       VARCHAR(50) NULL,
  lista_precio VARCHAR(50) NULL,   -- NO existe columna "Nombre"
  _loaded_at   TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_patologo;
CREATE TABLE legacy_patologo (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  patologo    VARCHAR(100) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Facturacion (modulo NO migrado a Laravel: 2.275 facturas)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS legacy_facturas;
CREATE TABLE legacy_facturas (
  _row_id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  no_recibo      VARCHAR(40) NULL,
  id_paciente    VARCHAR(40) NULL,
  total          VARCHAR(40) NULL,
  fecha_hora     VARCHAR(30) NULL,
  dcto           VARCHAR(40) NULL,
  resto          VARCHAR(40) NULL,
  abono          VARCHAR(40) NULL,
  cancelado_por  VARCHAR(50) NULL,
  procedencia    VARCHAR(50) NULL,
  _loaded_at     TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id),
  KEY ix_fac_pac (id_paciente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_detalle_examenes;
CREATE TABLE legacy_detalle_examenes (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  no_recibo   VARCHAR(40) NULL,
  cod_exa     VARCHAR(50) NULL,
  monto       VARCHAR(40) NULL,
  dcto        VARCHAR(40) NULL,
  fecha_hora  VARCHAR(30) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_detalle_abonos;
CREATE TABLE legacy_detalle_abonos (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  no_recibo   VARCHAR(40) NULL,
  abono       VARCHAR(40) NULL,
  fecha_hora  VARCHAR(30) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_examenes;
CREATE TABLE legacy_examenes (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_exa     VARCHAR(50) NULL,
  descripcion VARCHAR(100) NULL,
  precio_1    VARCHAR(40) NULL,
  precio_2    VARCHAR(40) NULL,
  precio_3    VARCHAR(40) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_exadetails;
CREATE TABLE legacy_exadetails (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cod_exa     VARCHAR(50) NULL,
  item_name   VARCHAR(100) NULL,
  fraction    VARCHAR(40) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS legacy_verif_tabla;
CREATE TABLE legacy_verif_tabla (
  _row_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  numero      VARCHAR(50) NULL,
  problema    VARCHAR(100) NULL,
  _loaded_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (_row_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
