CREATE TABLE IF NOT EXISTS operation_condominiums (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, name VARCHAR(180) NOT NULL, document VARCHAR(20) NULL,
 address VARCHAR(255) NULL, city VARCHAR(120) NULL, state CHAR(2) NULL, latitude DECIMAL(10,7) NULL,
 longitude DECIMAL(10,7) NULL, geofence_radius SMALLINT UNSIGNED NOT NULL DEFAULT 100,
 health_score TINYINT UNSIGNED NOT NULL DEFAULT 0, status ENUM('implementation','active','inactive') NOT NULL DEFAULT 'implementation',
 created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY(id), KEY idx_operation_condo_status(status,name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_visits (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, condominium_id INT UNSIGNED NOT NULL, title VARCHAR(180) NOT NULL,
 visit_type ENUM('inspection','management','implementation','maintenance') NOT NULL DEFAULT 'inspection',
 scheduled_at DATETIME NOT NULL, started_at DATETIME NULL, completed_at DATETIME NULL,
 status ENUM('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled', assigned_to INT UNSIGNED NULL,
 notes TEXT NULL, created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY(id), KEY idx_operation_visit_schedule(status,scheduled_at),
 CONSTRAINT fk_operation_visit_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_checklists (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, name VARCHAR(180) NOT NULL, category VARCHAR(100) NULL,
 description TEXT NULL, active TINYINT(1) NOT NULL DEFAULT 1, created_by INT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_operation_checklist_active(active,name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_checklist_items (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, checklist_id INT UNSIGNED NOT NULL, title VARCHAR(180) NOT NULL,
 instructions TEXT NULL, required TINYINT(1) NOT NULL DEFAULT 1, position SMALLINT UNSIGNED NOT NULL DEFAULT 1,
 PRIMARY KEY(id), KEY idx_operation_checklist_item(checklist_id,position),
 CONSTRAINT fk_operation_checklist_item FOREIGN KEY(checklist_id) REFERENCES operation_checklists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_visit_items (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, visit_id INT UNSIGNED NOT NULL, checklist_item_id INT UNSIGNED NULL,
 title VARCHAR(180) NOT NULL, result ENUM('pending','conforming','nonconforming','not_applicable') NOT NULL DEFAULT 'pending',
 notes TEXT NULL, checked_by INT UNSIGNED NULL, checked_at DATETIME NULL, PRIMARY KEY(id), KEY idx_operation_visit_item(visit_id,result),
 CONSTRAINT fk_operation_visit_item_visit FOREIGN KEY(visit_id) REFERENCES operation_visits(id) ON DELETE CASCADE,
 CONSTRAINT fk_operation_visit_item_template FOREIGN KEY(checklist_item_id) REFERENCES operation_checklist_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_issues (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, condominium_id INT UNSIGNED NOT NULL, visit_id INT UNSIGNED NULL,
 title VARCHAR(180) NOT NULL, description TEXT NULL, category VARCHAR(100) NULL,
 priority ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium', status ENUM('open','in_progress','waiting','resolved','cancelled') NOT NULL DEFAULT 'open',
 assigned_to INT UNSIGNED NULL, due_at DATETIME NULL, resolved_at DATETIME NULL, created_by INT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_operation_issue_queue(status,priority,due_at),
 CONSTRAINT fk_operation_issue_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE CASCADE,
 CONSTRAINT fk_operation_issue_visit FOREIGN KEY(visit_id) REFERENCES operation_visits(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_action_plans (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, issue_id INT UNSIGNED NOT NULL, title VARCHAR(180) NOT NULL, description TEXT NULL,
 status ENUM('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned', assigned_to INT UNSIGNED NULL,
 due_at DATETIME NULL, completed_at DATETIME NULL, created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY(id), KEY idx_operation_plan_status(status,due_at),
 CONSTRAINT fk_operation_plan_issue FOREIGN KEY(issue_id) REFERENCES operation_issues(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_assets (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, condominium_id INT UNSIGNED NOT NULL, name VARCHAR(180) NOT NULL,
 category VARCHAR(100) NULL, serial_number VARCHAR(120) NULL, location VARCHAR(180) NULL,
 status ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active', next_maintenance_at DATETIME NULL,
 created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY(id), KEY idx_operation_asset_status(condominium_id,status),
 CONSTRAINT fk_operation_asset_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_resident_requests (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, condominium_id INT UNSIGNED NOT NULL, title VARCHAR(180) NOT NULL,
 description TEXT NULL, status ENUM('submitted','reviewing','voting','approved','planned','rejected') NOT NULL DEFAULT 'submitted',
 votes INT UNSIGNED NOT NULL DEFAULT 0, created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY(id), KEY idx_operation_request_status(condominium_id,status),
 CONSTRAINT fk_operation_request_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_activity (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, entity_type VARCHAR(60) NOT NULL, entity_id INT UNSIGNED NOT NULL,
 action VARCHAR(60) NOT NULL, summary VARCHAR(255) NOT NULL, user_id INT UNSIGNED NULL,
 payload_json JSON NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_operation_activity(entity_type,entity_id,created_at), KEY idx_operation_activity_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
