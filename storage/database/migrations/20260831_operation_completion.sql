-- Conclusão do fluxo operacional: pauta inteligente, recorrência materializada e vínculos de ocorrências.
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS recurrence_parent_id INT UNSIGNED NULL AFTER recurrence_rule;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS recurrence_key VARCHAR(80) NULL AFTER recurrence_parent_id;
CREATE UNIQUE INDEX IF NOT EXISTS uq_operation_visit_recurrence ON operation_visits(recurrence_parent_id,recurrence_key);

CREATE TABLE IF NOT EXISTS operation_visit_agenda_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visit_id INT UNSIGNED NOT NULL,
 source_type VARCHAR(40) NOT NULL,
 source_id INT UNSIGNED NULL,
 title VARCHAR(180) NOT NULL,
 description TEXT NULL,
 priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
 status ENUM('pending','discussed','resolved','dismissed') NOT NULL DEFAULT 'pending',
 position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 generated_automatically TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_visit_agenda_source (visit_id,source_type,source_id),
 KEY idx_visit_agenda (visit_id,status,priority,position),
 CONSTRAINT fk_visit_agenda_visit FOREIGN KEY (visit_id) REFERENCES operation_visits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_visit_outcomes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 visit_id INT UNSIGNED NOT NULL,
 issue_id INT UNSIGNED NULL,
 outcome_type ENUM('issue','demand','ticket','task','record') NOT NULL,
 outcome_id INT UNSIGNED NULL,
 title VARCHAR(180) NOT NULL,
 created_by INT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_visit_outcome (visit_id,outcome_type,outcome_id),
 CONSTRAINT fk_visit_outcome_visit FOREIGN KEY (visit_id) REFERENCES operation_visits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
