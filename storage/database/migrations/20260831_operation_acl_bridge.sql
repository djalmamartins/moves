-- Permissões-base exigidas pelos controllers compartilhados de Agenda e Help Desk.
INSERT INTO access_permissions(name,slug,group_name,description) VALUES
('Acessar Studio','studio.access','Sistema','Entrar no painel administrativo'),
('Visualizar dashboard','dashboard.view','Sistema','Consultar indicadores do painel'),
('Gerenciar suporte','support.manage','Suporte','Administrar chamados e central de suporte'),
('Visualizar relatórios','reports.view','Gestão','Consultar relatórios'),
('Gerenciar usuários','users.manage','Segurança','Criar usuários e definir acessos'),
('Alterar configurações','settings.manage','Sistema','Editar configurações gerais'),
('Visualizar logs','logs.view','Segurança','Consultar logs do sistema')
ON DUPLICATE KEY UPDATE name=VALUES(name),group_name=VALUES(group_name),description=VALUES(description);

INSERT IGNORE INTO access_role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM access_roles r CROSS JOIN access_permissions p WHERE r.slug IN ('developer','super_admin','client_admin');
INSERT IGNORE INTO access_role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM access_roles r JOIN access_permissions p ON p.slug IN ('studio.access','dashboard.view','support.manage','reports.view') WHERE r.slug='manager';
INSERT IGNORE INTO access_role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM access_roles r JOIN access_permissions p ON p.slug IN ('studio.access','dashboard.view','support.manage') WHERE r.slug='operator';
