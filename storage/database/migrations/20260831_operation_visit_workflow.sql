-- Evolução compatível do fluxo de visitas. Preserva todos os registros existentes.
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS demand_id INT UNSIGNED NULL AFTER condominium_id;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS objective TEXT NULL AFTER title;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS summary TEXT NULL AFTER notes;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS ends_at DATETIME NULL AFTER scheduled_at;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS recurrence_rule VARCHAR(120) NULL AFTER ends_at;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS location VARCHAR(255) NULL AFTER recurrence_rule;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS checkin_latitude DECIMAL(10,7) NULL AFTER started_at;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS checkin_longitude DECIMAL(10,7) NULL AFTER checkin_latitude;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS checkin_accuracy DECIMAL(8,2) NULL AFTER checkin_longitude;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS checkin_device VARCHAR(255) NULL AFTER checkin_accuracy;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS checkout_latitude DECIMAL(10,7) NULL AFTER completed_at;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS checkout_longitude DECIMAL(10,7) NULL AFTER checkout_latitude;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS checkout_accuracy DECIMAL(8,2) NULL AFTER checkout_longitude;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS checkout_device VARCHAR(255) NULL AFTER checkout_accuracy;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS signature_required TINYINT(1) NOT NULL DEFAULT 0 AFTER summary;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS signature_name VARCHAR(180) NULL AFTER signature_required;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS signature_path VARCHAR(500) NULL AFTER signature_name;
ALTER TABLE operation_visits MODIFY visit_type ENUM('periodic','management','inspection','meeting','follow_up','technical','emergency','implementation','maintenance') NOT NULL DEFAULT 'inspection';

ALTER TABLE operation_visit_items ADD COLUMN IF NOT EXISTS area VARCHAR(120) NULL AFTER title;
ALTER TABLE operation_visit_items ADD COLUMN IF NOT EXISTS category VARCHAR(120) NULL AFTER area;
ALTER TABLE operation_visit_items ADD COLUMN IF NOT EXISTS priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal' AFTER category;
ALTER TABLE operation_visit_items ADD COLUMN IF NOT EXISTS photo_required_on_failure TINYINT(1) NOT NULL DEFAULT 0 AFTER priority;
ALTER TABLE operation_visit_items ADD COLUMN IF NOT EXISTS comment_required_on_failure TINYINT(1) NOT NULL DEFAULT 1 AFTER photo_required_on_failure;
ALTER TABLE operation_visit_items MODIFY result ENUM('pending','conforming','attention','nonconforming','not_applicable') NOT NULL DEFAULT 'pending';

ALTER TABLE operation_checklist_items ADD COLUMN IF NOT EXISTS area VARCHAR(120) NULL AFTER title;
ALTER TABLE operation_checklist_items ADD COLUMN IF NOT EXISTS category VARCHAR(120) NULL AFTER area;
ALTER TABLE operation_checklist_items ADD COLUMN IF NOT EXISTS frequency ENUM('every_visit','weekly','biweekly','monthly','quarterly','semiannual','annual','custom') NOT NULL DEFAULT 'every_visit' AFTER category;
ALTER TABLE operation_checklist_items ADD COLUMN IF NOT EXISTS frequency_rule VARCHAR(120) NULL AFTER frequency;
ALTER TABLE operation_checklist_items ADD COLUMN IF NOT EXISTS priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal' AFTER required;
ALTER TABLE operation_checklist_items ADD COLUMN IF NOT EXISTS photo_required_on_failure TINYINT(1) NOT NULL DEFAULT 0 AFTER priority;
ALTER TABLE operation_checklist_items ADD COLUMN IF NOT EXISTS comment_required_on_failure TINYINT(1) NOT NULL DEFAULT 1 AFTER photo_required_on_failure;
ALTER TABLE operation_checklists ADD COLUMN IF NOT EXISTS condominium_id INT UNSIGNED NULL AFTER id;
ALTER TABLE operation_checklists ADD COLUMN IF NOT EXISTS visit_type VARCHAR(40) NULL AFTER category;
ALTER TABLE operation_checklist_items ADD COLUMN IF NOT EXISTS asset_id INT UNSIGNED NULL AFTER category;

CREATE TABLE IF NOT EXISTS operation_visit_participants (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visit_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NULL,
 person_id INT UNSIGNED NULL,
 name VARCHAR(180) NULL,
 role VARCHAR(80) NULL,
 presence_status ENUM('invited','confirmed','present','absent') NOT NULL DEFAULT 'invited',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_visit_participant (visit_id,presence_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_visit_evidence (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visit_id INT UNSIGNED NOT NULL,
 visit_item_id INT UNSIGNED NULL,
 issue_id INT UNSIGNED NULL,
 file_path VARCHAR(500) NOT NULL,
 original_name VARCHAR(255) NOT NULL,
 mime_type VARCHAR(120) NULL,
 file_size INT UNSIGNED NOT NULL DEFAULT 0,
 caption VARCHAR(255) NULL,
 latitude DECIMAL(10,7) NULL,
 longitude DECIMAL(10,7) NULL,
 created_by INT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_visit_evidence (visit_id,visit_item_id),
 CONSTRAINT fk_visit_evidence_visit FOREIGN KEY (visit_id) REFERENCES operation_visits(id) ON DELETE CASCADE,
 CONSTRAINT fk_visit_evidence_item FOREIGN KEY (visit_item_id) REFERENCES operation_visit_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_visit_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visit_id INT UNSIGNED NOT NULL,
 event_type VARCHAR(60) NOT NULL,
 summary VARCHAR(255) NOT NULL,
 details_json LONGTEXT NULL,
 user_id INT UNSIGNED NULL,
 occurred_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_visit_event_timeline (visit_id,occurred_at),
 CONSTRAINT fk_visit_event_visit FOREIGN KEY (visit_id) REFERENCES operation_visits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_visit_sync_queue (
 id CHAR(36) PRIMARY KEY,
 visit_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 device_id VARCHAR(180) NULL,
 operation_type VARCHAR(60) NOT NULL,
 payload_json LONGTEXT NOT NULL,
 status ENUM('pending','processing','synced','failed') NOT NULL DEFAULT 'pending',
 attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 last_error VARCHAR(500) NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 synced_at DATETIME NULL,
 KEY idx_visit_sync (visit_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE studio_calendar_events ADD COLUMN IF NOT EXISTS recurrence_rule VARCHAR(120) NULL AFTER ends_at;
ALTER TABLE studio_calendar_events ADD COLUMN IF NOT EXISTS reminder_minutes SMALLINT UNSIGNED NULL AFTER recurrence_rule;
ALTER TABLE studio_calendar_events ADD COLUMN IF NOT EXISTS location VARCHAR(255) NULL AFTER reminder_minutes;

CREATE TABLE IF NOT EXISTS operation_calendar_participants (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 event_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 response ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_calendar_participant (event_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_comments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 entity_type VARCHAR(40) NOT NULL,
 entity_id INT UNSIGNED NOT NULL,
 comment TEXT NOT NULL,
 is_internal TINYINT(1) NOT NULL DEFAULT 1,
 created_by INT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_operation_comment (entity_type,entity_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO access_permissions(name,slug,group_name,description) VALUES
('Finalizar visita incompleta','operation.visits.override_required','Operacional','Permite finalizar visita com itens obrigatórios pendentes')
ON DUPLICATE KEY UPDATE name=VALUES(name),group_name=VALUES(group_name),description=VALUES(description);
INSERT IGNORE INTO access_role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM access_roles r JOIN access_permissions p ON p.slug='operation.visits.override_required'
WHERE r.slug IN ('developer','super_admin','client_admin');
