-- Bootstrap idempotente de configuração e ACL.
-- Módulos autenticados começam desabilitados quando a configuração não existia;
-- a habilitação continua sendo uma decisão explícita do administrador.
INSERT INTO `settings`
(`id`,`mode`,`site_name`,`site_title`,`site_desc`,`site_lang`,`site_domain_ssl`,
 `view_theme`,`view_support`,`view_app`,`view_erp`,`view_admin`,`view_mail`,`view_upkeep`,
 `mail_name`,`mail_address`,`mail_lang`,`mail_html`,`mail_auth`,`mail_charset`,
 `pay_mode`,`pay_back`,`timezone_set`,`access_studio`,`access_erp`,`access_app`,`access_site`,`access_support`)
VALUES
(1,1,'MOVES','MOVES','Gestão condominial','pt_BR','https://localhost/erp',
 'default','support','default','default','default','default','default',
 'MOVES','no-reply@localhost','pt_BR','1','0','UTF-8',
 'test','/','America/Sao_Paulo',0,0,0,1,1)
ON DUPLICATE KEY UPDATE `id`=VALUES(`id`);

INSERT INTO `access_user_roles` (`user_id`,`role_id`,`assigned_by`)
SELECT u.id, r.id, NULL
FROM users u
JOIN access_roles r ON r.slug = CASE
    WHEN u.id = 1 THEN 'developer'
    WHEN u.level >= 10 THEN 'super_admin'
    WHEN u.level >= 5 THEN 'client_admin'
    WHEN u.level >= 2 THEN 'manager'
    ELSE 'user'
END
LEFT JOIN access_user_roles ur ON ur.user_id = u.id
WHERE u.status <> 'trash' AND ur.user_id IS NULL;
