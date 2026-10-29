-- =============================================================================
-- MODELO DESTINO (core clinico) - transform del legado Access
-- Base: lapt_core   (independiente de lapt_staging y de la app Laravel)
-- Principio: el legado se importa TAL CUAL en `case_code`; nada se renumera.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS lapt_core
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lapt_core;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS quarantine;
DROP TABLE IF EXISTS case_assignments;
DROP TABLE IF EXISTS cytology_reports;
DROP TABLE IF EXISTS histology_reports;
DROP TABLE IF EXISTS cases;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS annexes;
DROP TABLE IF EXISTS cytology_diagnosis_catalog;
DROP TABLE IF EXISTS templates;
DROP TABLE IF EXISTS origins;
DROP TABLE IF EXISTS doctors;
DROP TABLE IF EXISTS pathologists;
DROP TABLE IF EXISTS etl_runs;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- Persona (una vez por IDPaciente). El legado NO tiene email ni fecha de nacimiento.
-- -----------------------------------------------------------------------------
CREATE TABLE patients (
  id                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  legacy_id                BIGINT UNSIGNED NULL,
  document_number          VARCHAR(50)  NULL,          -- CI tal cual (puede ser NS/SN/MENOR)
  document_is_placeholder  TINYINT(1)   NOT NULL DEFAULT 0,
  full_name                VARCHAR(120) NULL,
  sex                      ENUM('F','M','X') NULL,
  legacy_sex_raw           VARCHAR(5)   NULL,
  age_at_entry             SMALLINT UNSIGNED NULL,     -- parseado de "36 AÑOS"
  legacy_age_text          VARCHAR(50)  NULL,
  birth_date               DATE         NULL,
  phone_code               VARCHAR(10)  NULL,
  phone                    VARCHAR(20)  NULL,
  address                  VARCHAR(200) NULL,
  email                    VARCHAR(150) NULL,          -- legado: 100% nulo
  created_at               DATETIME     NULL,          -- FechaApertura
  flags                    VARCHAR(255) NULL,          -- SIN_ESTUDIO, AMBOS, CI_PLACEHOLDER...
  updated_at               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_patients_legacy (legacy_id),
  KEY ix_patients_doc  (document_number),
  KEY ix_patients_name (full_name),
  KEY ix_patients_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Estudio (1:N con paciente). Reemplaza el 1:1 pacientes<->biopsia del legado.
-- case_code conserva el numero original (6 digitos biopsia / 7 citologia).
-- -----------------------------------------------------------------------------
CREATE TABLE cases (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id    BIGINT UNSIGNED NOT NULL,
  type          ENUM('biopsy','cytology') NOT NULL,
  case_code     VARCHAR(20)  NULL,
  sequence_no   INT UNSIGNED NULL,
  year_no       SMALLINT UNSIGNED NULL,
  status        ENUM('received','in_progress','delivered','cancelled') NOT NULL DEFAULT 'received',
  received_at   DATETIME NULL,
  sent_at       DATETIME NULL,
  completed_at  DATETIME NULL,
  legacy_source VARCHAR(20)  NOT NULL,
  legacy_row_id BIGINT UNSIGNED NOT NULL,
  flags         VARCHAR(255) NULL,   -- SIN_NUMERO, FECHA_CORRUPTA, DUPLICADO, MULTIPLE, NUMERO_FUERA_PATRON
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cases_legacy (legacy_source, legacy_row_id),
  KEY ix_cases_patient (patient_id),
  KEY ix_cases_number  (type, year_no, sequence_no),
  KEY ix_cases_code    (case_code),
  KEY ix_cases_status  (status),
  KEY ix_cases_received (received_at),
  CONSTRAINT fk_cases_patient FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Informes
-- -----------------------------------------------------------------------------
CREATE TABLE histology_reports (
  case_id             BIGINT UNSIGNED NOT NULL,
  material            VARCHAR(100) NULL,
  clinical_diagnosis  VARCHAR(100) NULL,
  lesion_site         VARCHAR(100) NULL,
  macro_description   LONGTEXT NULL,
  micro_description   LONGTEXT NULL,
  micro_diagnosis     LONGTEXT NULL,
  notes               LONGTEXT NULL,
  PRIMARY KEY (case_id),
  CONSTRAINT fk_hist_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cytology_reports (
  case_id             BIGINT UNSIGNED NOT NULL,
  material            VARCHAR(250) NULL,
  clinical_diagnosis  VARCHAR(60)  NULL,
  macro_description   LONGTEXT NULL,
  cyto_description    LONGTEXT NULL,
  cyto_diagnosis      LONGTEXT NULL,
  quality             LONGTEXT NULL,
  notes               LONGTEXT NULL,
  superficial         SMALLINT NULL,
  intermediate        SMALLINT NULL,
  parabasal           SMALLINT NULL,
  conclusion          LONGTEXT NULL,
  PRIMARY KEY (case_id),
  CONSTRAINT fk_cyt_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Catalogos
-- -----------------------------------------------------------------------------
CREATE TABLE pathologists (
  id   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pathologists_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE case_assignments (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  case_id          BIGINT UNSIGNED NOT NULL,
  pathologist_id   BIGINT UNSIGNED NULL,
  pathologist_name VARCHAR(100) NULL,     -- se conserva el texto original
  role             TINYINT UNSIGNED NOT NULL,  -- 1 = titular, 2 = cosignante
  PRIMARY KEY (id),
  KEY ix_assign_case (case_id),
  KEY ix_assign_path (pathologist_id),
  CONSTRAINT fk_assign_case FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE CASCADE,
  CONSTRAINT fk_assign_path FOREIGN KEY (pathologist_id) REFERENCES pathologists (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE doctors (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  legacy_code VARCHAR(50)  NOT NULL,
  name        VARCHAR(100) NULL,
  phone       VARCHAR(50)  NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_doctors_code (legacy_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE origins (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  legacy_code VARCHAR(50) NOT NULL,
  price_list  VARCHAR(50) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_origins_code (legacy_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE templates (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  module     VARCHAR(10) NULL,     -- 00 macro | 01 micro | 02 diagnostico | 99 sentinel | NULL
  name       VARCHAR(50) NULL,
  content    LONGTEXT    NULL,
  PRIMARY KEY (id),
  KEY ix_templates_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cytology_diagnosis_catalog (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code        VARCHAR(50) NOT NULL,
  description LONGTEXT NULL,
  diagnosis   LONGTEXT NULL,
  sort_order  INT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cdc_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE annexes (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code       VARCHAR(50) NOT NULL,
  text       LONGTEXT NULL,
  sort_order INT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_annexes_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Cuarentena: nada se descarta en silencio. Cada anomalia es una decision pendiente.
-- -----------------------------------------------------------------------------
CREATE TABLE quarantine (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  bucket        VARCHAR(40)  NOT NULL,
  legacy_table  VARCHAR(40)  NOT NULL,
  legacy_row_id BIGINT UNSIGNED NULL,
  legacy_key    VARCHAR(60)  NULL,
  detail        VARCHAR(255) NULL,
  payload       JSON NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_quar_bucket (bucket),
  KEY ix_quar_key (legacy_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Bitacora de corridas
-- -----------------------------------------------------------------------------
CREATE TABLE etl_runs (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  run_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  source     VARCHAR(100) NOT NULL,
  stats      JSON NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
