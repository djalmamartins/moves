-- Operation integrado: amplia estruturas existentes sem apagar ou duplicar dados.
ALTER TABLE operation_condominiums ADD COLUMN IF NOT EXISTS app_condominium_id INT UNSIGNED NULL AFTER id;
CREATE UNIQUE INDEX IF NOT EXISTS uq_operation_condominium_app ON operation_condominiums(app_condominium_id);

INSERT INTO operation_condominiums (app_condominium_id,name,document,status,created_by)
SELECT c.id,COALESCE(NULLIF(c.fantasy_name,''),c.condominium_name),c.document,
       CASE WHEN c.status IN ('active','1','on') THEN 'active' ELSE 'implementation' END,
       COALESCE((SELECT id FROM users ORDER BY level DESC,id LIMIT 1),1)
FROM app_condominium c
LEFT JOIN operation_condominiums o ON o.app_condominium_id=c.id
WHERE o.id IS NULL;

CREATE TABLE IF NOT EXISTS operation_demands (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, protocol VARCHAR(24) NOT NULL, condominium_id INT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL, description TEXT NULL, category VARCHAR(100) NULL,
 priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
 status ENUM('new','analysis','in_progress','waiting_third_party','waiting_condominium','completed','cancelled') NOT NULL DEFAULT 'new',
 assigned_to INT UNSIGNED NULL, due_at DATETIME NULL, completed_at DATETIME NULL, source_type VARCHAR(40) NULL, source_id INT UNSIGNED NULL,
 created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY(id), UNIQUE KEY uq_operation_demand_protocol(protocol),
 KEY idx_operation_demand_queue(condominium_id,status,priority,due_at), KEY idx_operation_demand_assignee(assigned_to,status),
 CONSTRAINT fk_operation_demand_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_tasks (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, condominium_id INT UNSIGNED NOT NULL, demand_id INT UNSIGNED NULL,
 title VARCHAR(180) NOT NULL, description TEXT NULL, task_type ENUM('task','meeting','deadline','assembly','inspection') NOT NULL DEFAULT 'task',
 priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal', status ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
 starts_at DATETIME NULL, due_at DATETIME NULL, completed_at DATETIME NULL, assigned_to INT UNSIGNED NULL,
 created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_operation_task_day(assigned_to,status,due_at),
 CONSTRAINT fk_operation_task_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE RESTRICT,
 CONSTRAINT fk_operation_task_demand FOREIGN KEY(demand_id) REFERENCES operation_demands(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_suppliers (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, legal_name VARCHAR(180) NOT NULL, trade_name VARCHAR(180) NULL, document VARCHAR(20) NULL,
 category VARCHAR(120) NULL, contact_name VARCHAR(160) NULL, phone VARCHAR(30) NULL, email VARCHAR(190) NULL,
 address VARCHAR(255) NULL, city VARCHAR(120) NULL, state CHAR(2) NULL, status ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
 created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_operation_supplier_document(document), KEY idx_operation_supplier(category,status,trade_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_quotes (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, protocol VARCHAR(24) NOT NULL, condominium_id INT UNSIGNED NOT NULL, demand_id INT UNSIGNED NULL,
 visit_id INT UNSIGNED NULL, ticket_id INT UNSIGNED NULL, title VARCHAR(180) NOT NULL, description TEXT NULL,
 status ENUM('draft','requested','received','analysis','waiting_approval','approved','rejected','expired') NOT NULL DEFAULT 'draft',
 valid_until DATE NULL, assigned_to INT UNSIGNED NULL, created_by INT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_operation_quote_protocol(protocol), KEY idx_operation_quote_queue(condominium_id,status,valid_until),
 CONSTRAINT fk_operation_quote_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE RESTRICT,
 CONSTRAINT fk_operation_quote_demand FOREIGN KEY(demand_id) REFERENCES operation_demands(id) ON DELETE SET NULL,
 CONSTRAINT fk_operation_quote_visit FOREIGN KEY(visit_id) REFERENCES operation_visits(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_quote_offers (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, quote_id INT UNSIGNED NOT NULL, supplier_id INT UNSIGNED NOT NULL,
 amount DECIMAL(12,2) NULL, received_at DATETIME NULL, valid_until DATE NULL, document_path VARCHAR(500) NULL,
 notes TEXT NULL, status ENUM('requested','received','selected','rejected') NOT NULL DEFAULT 'requested',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_operation_quote_supplier(quote_id,supplier_id),
 CONSTRAINT fk_operation_offer_quote FOREIGN KEY(quote_id) REFERENCES operation_quotes(id) ON DELETE CASCADE,
 CONSTRAINT fk_operation_offer_supplier FOREIGN KEY(supplier_id) REFERENCES operation_suppliers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_documents (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, condominium_id INT UNSIGNED NOT NULL, demand_id INT UNSIGNED NULL,
 supplier_id INT UNSIGNED NULL, ticket_id INT UNSIGNED NULL, title VARCHAR(180) NOT NULL, category VARCHAR(100) NOT NULL,
 file_path VARCHAR(500) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(120) NULL, file_size INT UNSIGNED NOT NULL DEFAULT 0,
 document_date DATE NULL, valid_until DATE NULL, status ENUM('valid','expiring','expired','archived') NOT NULL DEFAULT 'valid',
 responsible_id INT UNSIGNED NULL, created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY(id), KEY idx_operation_document_expiry(condominium_id,status,valid_until),
 CONSTRAINT fk_operation_document_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE RESTRICT,
 CONSTRAINT fk_operation_document_demand FOREIGN KEY(demand_id) REFERENCES operation_demands(id) ON DELETE SET NULL,
 CONSTRAINT fk_operation_document_supplier FOREIGN KEY(supplier_id) REFERENCES operation_suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_people (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, name VARCHAR(180) NOT NULL, document VARCHAR(20) NULL, phone VARCHAR(30) NULL,
 email VARCHAR(190) NULL, status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_by INT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_operation_person_document(document), KEY idx_operation_person(name,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_person_links (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT, person_id INT UNSIGNED NOT NULL, condominium_id INT UNSIGNED NOT NULL,
 unit_label VARCHAR(80) NULL, block_label VARCHAR(80) NULL,
 relation_type ENUM('resident','owner','tenant','syndic','subsyndic','councillor') NOT NULL,
 starts_at DATE NULL, ends_at DATE NULL, status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_operation_person_condo(condominium_id,relation_type,status),
 CONSTRAINT fk_operation_person_link_person FOREIGN KEY(person_id) REFERENCES operation_people(id) ON DELETE CASCADE,
 CONSTRAINT fk_operation_person_link_condo FOREIGN KEY(condominium_id) REFERENCES operation_condominiums(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_relations (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, source_type VARCHAR(40) NOT NULL, source_id INT UNSIGNED NOT NULL,
 target_type VARCHAR(40) NOT NULL, target_id INT UNSIGNED NOT NULL, relation_type VARCHAR(60) NOT NULL DEFAULT 'related',
 created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_operation_relation(source_type,source_id,target_type,target_id,relation_type),
 KEY idx_operation_relation_target(target_type,target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS operation_attachments (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, entity_type VARCHAR(40) NOT NULL, entity_id INT UNSIGNED NOT NULL,
 file_path VARCHAR(500) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(120) NULL, file_size INT UNSIGNED NOT NULL DEFAULT 0,
 created_by INT UNSIGNED NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY idx_operation_attachment(entity_type,entity_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS demand_id INT UNSIGNED NULL AFTER condominium_id;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS objective TEXT NULL AFTER title;
ALTER TABLE operation_visits ADD COLUMN IF NOT EXISTS summary TEXT NULL AFTER notes;
ALTER TABLE studio_calendar_events ADD COLUMN IF NOT EXISTS condominium_id INT UNSIGNED NULL AFTER id;
ALTER TABLE studio_calendar_events ADD COLUMN IF NOT EXISTS operation_entity_type VARCHAR(40) NULL AFTER assigned_to;
ALTER TABLE studio_calendar_events ADD COLUMN IF NOT EXISTS operation_entity_id INT UNSIGNED NULL AFTER operation_entity_type;
ALTER TABLE studio_support_tickets ADD COLUMN IF NOT EXISTS condominium_id INT UNSIGNED NULL AFTER id;
ALTER TABLE studio_support_tickets ADD COLUMN IF NOT EXISTS demand_id INT UNSIGNED NULL AFTER condominium_id;
ALTER TABLE studio_support_tickets ADD COLUMN IF NOT EXISTS requester_person_id INT UNSIGNED NULL AFTER requester_id;

INSERT INTO access_roles(name,slug,level,description) VALUES
('Desenvolvedor','developer',100,'Controle técnico integral do MovesOS'),
('Super administrador','super_admin',90,'Administração global da plataforma'),
('Administrador do cliente','client_admin',70,'Administração completa da própria operação'),
('Gerente','manager',50,'Gestão de conteúdo, equipe e relatórios'),
('Operador','operator',30,'Execução das rotinas autorizadas'),
('Usuário','user',10,'Acesso aos próprios recursos')
ON DUPLICATE KEY UPDATE name=VALUES(name),level=VALUES(level),description=VALUES(description);

INSERT INTO access_permissions(name,slug,group_name,description) VALUES
('Acessar Operacional','operation.access','Operacional','Entrar no ambiente operacional'),
('Visualizar demandas','operation.demands.view','Operacional','Consultar demandas'),('Criar demandas','operation.demands.create','Operacional','Cadastrar demandas'),('Alterar demandas','operation.demands.update','Operacional','Editar e concluir demandas'),('Excluir demandas','operation.demands.delete','Operacional','Excluir demandas'),
('Gerenciar agenda','operation.agenda.manage','Operacional','Criar e alterar compromissos'),('Gerenciar visitas','operation.visits.manage','Operacional','Criar, executar e concluir visitas'),
('Gerenciar chamados operacionais','operation.tickets.manage','Operacional','Administrar chamados relacionados à operação'),('Gerenciar orçamentos','operation.quotes.manage','Operacional','Administrar orçamentos'),
('Gerenciar documentos operacionais','operation.documents.manage','Operacional','Administrar documentos'),('Gerenciar fornecedores','operation.suppliers.manage','Operacional','Administrar fornecedores'),
('Gerenciar pessoas','operation.people.manage','Operacional','Administrar moradores e síndicos'),('Visualizar relatórios operacionais','operation.reports.view','Operacional','Consultar relatórios operacionais'),
('Gerenciar condomínios operacionais','operation.condominiums.manage','Operacional','Administrar a carteira de condomínios')
ON DUPLICATE KEY UPDATE name=VALUES(name),group_name=VALUES(group_name),description=VALUES(description);

INSERT IGNORE INTO access_role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM access_roles r CROSS JOIN access_permissions p WHERE r.slug IN ('developer','super_admin','client_admin') AND p.slug LIKE 'operation.%';
INSERT IGNORE INTO access_role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM access_roles r JOIN access_permissions p ON p.slug IN ('operation.access','operation.demands.view','operation.demands.create','operation.demands.update','operation.agenda.manage','operation.visits.manage','operation.tickets.manage','operation.quotes.manage','operation.documents.manage','operation.suppliers.manage','operation.people.manage','operation.reports.view','operation.condominiums.manage') WHERE r.slug='manager';
INSERT IGNORE INTO access_role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM access_roles r JOIN access_permissions p ON p.slug IN ('operation.access','operation.demands.view','operation.demands.create','operation.demands.update','operation.agenda.manage','operation.visits.manage','operation.tickets.manage') WHERE r.slug='operator';

INSERT IGNORE INTO access_user_roles(user_id,role_id)
SELECT u.id,r.id FROM users u JOIN access_roles r ON r.slug=CASE WHEN u.level>=10 THEN 'developer' WHEN u.level>=5 THEN 'client_admin' WHEN u.level>=2 THEN 'manager' ELSE 'user' END;
