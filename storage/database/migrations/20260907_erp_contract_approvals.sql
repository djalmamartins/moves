CREATE TABLE IF NOT EXISTS erp_contracts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  condominium_id INT UNSIGNED NOT NULL,
  supplier_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  number VARCHAR(80) NULL,
  starts_at DATE NOT NULL,
  ends_at DATE NULL,
  monthly_amount DECIMAL(15,2) NULL,
  notice_days SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  status ENUM('draft','pending_approval','active','rejected','expiring','expired','cancelled') NOT NULL DEFAULT 'draft',
  owner_id INT UNSIGNED NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_erp_contract_expiry (condominium_id,status,ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS erp_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  condominium_id INT UNSIGNED NOT NULL,
  entity_type VARCHAR(60) NULL,
  entity_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  category VARCHAR(80) NULL,
  file_path VARCHAR(255) NULL,
  expires_at DATE NULL,
  visibility ENUM('internal','managers','residents','public') NOT NULL DEFAULT 'internal',
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_erp_document_scope (condominium_id,category,expires_at),
  KEY idx_erp_document_entity (entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS erp_approval_steps (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(60) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  condominium_id INT UNSIGNED NOT NULL,
  sequence_no SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  approver_id INT UNSIGNED NULL,
  status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  note TEXT NULL,
  decided_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_erp_approval_sequence (entity_type,entity_id,sequence_no),
  KEY idx_erp_approval_queue (condominium_id,status,sequence_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE erp_contracts
  ADD COLUMN IF NOT EXISTS created_by INT UNSIGNED NULL AFTER owner_id,
  MODIFY status ENUM('draft','pending_approval','active','rejected','expiring','expired','cancelled') NOT NULL DEFAULT 'draft';

ALTER TABLE erp_documents
  ADD INDEX IF NOT EXISTS idx_erp_document_entity (entity_type,entity_id);

ALTER TABLE erp_approval_steps
  ADD UNIQUE INDEX IF NOT EXISTS uq_erp_approval_sequence (entity_type,entity_id,sequence_no);
