# Catálogo do banco de dados MOVES

> Gerado de `storage/database/baseline/20260831_schema.sql`. Não contém dados de produção.

- Baseline: `20260831`
- Fingerprint: `a8149241bd8309939c9382a367b7288f92760958dd04e1f3ca9f767718328877`
- Tabelas catalogadas: 92
- Relações com foreign key: 43

## Política

Owner indica a área responsável. Retenção é a regra operacional mínima proposta e deve respeitar obrigações legais, bloqueios de auditoria e solicitações válidas de titulares. Exclusões nunca devem ignorar relações ou trilhas de auditoria.

## Tabelas

| Tabela | Owner | Model/serviço | Telas/uso | Relações | Retenção |
|---|---|---|---|---|---|
| `access_permissions` | Core/Security | SQL/serviço direto | Administração e auditoria | — | 5 anos; proteção legal pode ampliar |
| `access_role_permissions` | Core/Security | SQL/serviço direto | Administração e auditoria | `permission_id` → `access_permissions.id`<br>`role_id` → `access_roles.id` | 5 anos; proteção legal pode ampliar |
| `access_roles` | Core/Security | SQL/serviço direto | Administração e auditoria | — | 5 anos; proteção legal pode ampliar |
| `access_user_overrides` | Core/Security | SQL/serviço direto | Administração e auditoria | `permission_id` → `access_permissions.id`<br>`user_id` → `users.id` | 5 anos; proteção legal pode ampliar |
| `access_user_roles` | Core/Security | SQL/serviço direto | Administração e auditoria | `role_id` → `access_roles.id`<br>`user_id` → `users.id` | 5 anos; proteção legal pode ampliar |
| `app_accountable` | ERP | SQL/serviço direto | /erp e /app | `sub_of` → `app_condominium.id`<br>`users_id` → `users.id` | 5 anos após término do vínculo |
| `app_address` | ERP | `source/Models/Address.php` | /erp e /app | — | 5 anos após término do vínculo |
| `app_categories` | ERP | `source/Models/Erp/AppCategory.php` | /erp e /app | `sub_of` → `app_categories.id` | 5 anos após término do vínculo |
| `app_condominium` | ERP | `source/Models/Corporation/AppCondominium.php` | /erp e /app | — | 5 anos após término do vínculo |
| `app_corporations` | ERP | `source/Models/Corporation/AppCorporations.php` | /erp e /app | — | 5 anos após término do vínculo |
| `app_credit_cards` | ERP | SQL/serviço direto | /erp e /app | — | 5 anos após término do vínculo |
| `app_invoices` | ERP/Financeiro | `source/Models/Erp/AppInvoice.php` | /erp | — | 10 anos após exercício |
| `app_log` | ERP | `source/Models/Session/AppLog.php` | /erp e /app | — | 5 anos após término do vínculo |
| `app_orders` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `app_owner` | ERP | `source/Models/Corporation/AppOwner.php` | /erp e /app | — | 5 anos após término do vínculo |
| `app_plans` | ERP | SQL/serviço direto | /erp e /app | — | 5 anos após término do vínculo |
| `app_session` | Core/Session | `source/Models/Session/AppSession.php` | Autenticação e presença | — | 30 dias após expiração |
| `app_subscriptions` | ERP | SQL/serviço direto | /erp e /app | — | 5 anos após término do vínculo |
| `app_units` | ERP | `source/Models/Corporation/AppUnits.php` | /erp e /app | — | 5 anos após término do vínculo |
| `app_wallets` | ERP/Financeiro | `source/Models/Erp/AppWallet.php` | /erp | — | 10 anos após exercício |
| `brief` | Core/Legado | `source/Models/Brief/AppBrief.php` | Uso interno/legado | — | Revisão anual; não excluir sem auditoria |
| `categories_slide` | Core/Legado | `source/Models/Slide/SlideCategory.php`<br>`source/Models/Slide/CategorySlide.php` | Uso interno/legado | — | Revisão anual; não excluir sem auditoria |
| `categories` | Studio/Conteúdo | `source/Models/Post/Category.php` | /studio e site público | — | Enquanto publicado + histórico |
| `erp_activity_feed` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_approval_steps` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_attention_items` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_bank_transactions` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_blocks` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_communications` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_contracts` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_documents` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_financial_entries` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_meetings` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_payments` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `erp_suppliers` | ERP/Financeiro | SQL/serviço direto | /erp | — | 10 anos após exercício |
| `faq_channels` | Studio/Conteúdo | `source/Models/Faq/Channel.php` | /studio e site público | — | Enquanto publicado + histórico |
| `faq_questions` | Studio/Conteúdo | `source/Models/Faq/Question.php` | /studio e site público | — | Enquanto publicado + histórico |
| `mail_queue` | Core/Comunicação | SQL/serviço direto | Notificações e e-mail | — | 2 anos após envio |
| `module_migrations` | Core/Legado | SQL/serviço direto | Uso interno/legado | — | Revisão anual; não excluir sem auditoria |
| `modules` | Core/Legado | SQL/serviço direto | Uso interno/legado | — | Revisão anual; não excluir sem auditoria |
| `movesos_versions` | Core/Legado | SQL/serviço direto | Uso interno/legado | — | Revisão anual; não excluir sem auditoria |
| `notification_messages` | Core/Comunicação | `source/Models/Notification/NotificationMessage.php` | Notificações e e-mail | — | 2 anos após envio |
| `notifications_categories` | Core/Comunicação | `source/Models/Notification/NotificationCategory.php` | Notificações e e-mail | — | 2 anos após envio |
| `notifications` | Core/Comunicação | `source/Models/Notification/Notification.php` | Notificações e e-mail | — | 2 anos após envio |
| `operation_action_plans` | Operation | SQL/serviço direto | /operation | `issue_id` → `operation_issues.id` | 5 anos após encerramento |
| `operation_activity` | Operation | SQL/serviço direto | /operation | — | 5 anos após encerramento |
| `operation_assets` | Operation | SQL/serviço direto | /operation | `condominium_id` → `operation_condominiums.id` | 5 anos após encerramento |
| `operation_attachments` | Operation | SQL/serviço direto | /operation | — | 5 anos após encerramento |
| `operation_calendar_participants` | Operation | SQL/serviço direto | /operation | — | 5 anos após encerramento |
| `operation_checklist_items` | Operation | SQL/serviço direto | /operation | `checklist_id` → `operation_checklists.id` | 5 anos após encerramento |
| `operation_checklists` | Operation | SQL/serviço direto | /operation | — | 5 anos após encerramento |
| `operation_comments` | Operation | SQL/serviço direto | /operation | — | 5 anos após encerramento |
| `operation_condominiums` | Operation | `source/Models/Operation/CondominiumProfile.php` | /operation | — | 5 anos após encerramento |
| `operation_demands` | Operation | `source/Models/Operation/Demand.php` | /operation | `condominium_id` → `operation_condominiums.id` | 5 anos após encerramento |
| `operation_documents` | Operation | `source/Models/Operation/Document.php` | /operation | `condominium_id` → `operation_condominiums.id`<br>`demand_id` → `operation_demands.id`<br>`supplier_id` → `operation_suppliers.id` | 5 anos após encerramento |
| `operation_issues` | Operation | SQL/serviço direto | /operation | `condominium_id` → `operation_condominiums.id`<br>`visit_id` → `operation_visits.id` | 5 anos após encerramento |
| `operation_people` | Operation | `source/Models/Operation/Person.php` | /operation | — | 5 anos após encerramento |
| `operation_person_links` | Operation | SQL/serviço direto | /operation | `condominium_id` → `operation_condominiums.id`<br>`person_id` → `operation_people.id` | 5 anos após encerramento |
| `operation_quote_offers` | Operation | SQL/serviço direto | /operation | `quote_id` → `operation_quotes.id`<br>`supplier_id` → `operation_suppliers.id` | 5 anos após encerramento |
| `operation_quotes` | Operation | `source/Models/Operation/Quote.php` | /operation | `condominium_id` → `operation_condominiums.id`<br>`demand_id` → `operation_demands.id`<br>`visit_id` → `operation_visits.id` | 5 anos após encerramento |
| `operation_relations` | Operation | SQL/serviço direto | /operation | — | 5 anos após encerramento |
| `operation_resident_requests` | Operation | SQL/serviço direto | /operation | `condominium_id` → `operation_condominiums.id` | 5 anos após encerramento |
| `operation_suppliers` | Operation | `source/Models/Operation/Supplier.php` | /operation | — | 5 anos após encerramento |
| `operation_tasks` | Operation | `source/Models/Operation/Task.php` | /operation | `condominium_id` → `operation_condominiums.id`<br>`demand_id` → `operation_demands.id` | 5 anos após encerramento |
| `operation_visit_agenda_items` | Operation | SQL/serviço direto | /operation | `visit_id` → `operation_visits.id` | 5 anos após encerramento |
| `operation_visit_events` | Operation | SQL/serviço direto | /operation | `visit_id` → `operation_visits.id` | 5 anos após encerramento |
| `operation_visit_evidence` | Operation | SQL/serviço direto | /operation | `visit_item_id` → `operation_visit_items.id`<br>`visit_id` → `operation_visits.id` | 5 anos após encerramento |
| `operation_visit_items` | Operation | SQL/serviço direto | /operation | `checklist_item_id` → `operation_checklist_items.id`<br>`visit_id` → `operation_visits.id` | 5 anos após encerramento |
| `operation_visit_outcomes` | Operation | SQL/serviço direto | /operation | `visit_id` → `operation_visits.id` | 5 anos após encerramento |
| `operation_visit_participants` | Operation | SQL/serviço direto | /operation | — | 5 anos após encerramento |
| `operation_visit_sync_queue` | Operation | SQL/serviço direto | /operation | — | 5 anos após encerramento |
| `operation_visits` | Operation | `source/Models/Operation/Visit.php` | /operation | `condominium_id` → `operation_condominiums.id` | 5 anos após encerramento |
| `pages` | Studio/Conteúdo | `source/Models/Post/Page.php` | /studio e site público | — | Enquanto publicado + histórico |
| `posts` | Studio/Conteúdo | `source/Models/Post/Post.php` | /studio e site público | — | Enquanto publicado + histórico |
| `proposal_responses` | Core/Legado | `source/Models/Proposal/ProposalResponse.php` | Uso interno/legado | — | Revisão anual; não excluir sem auditoria |
| `proposals` | Core/Legado | `source/Models/Proposal/Proposal.php` | Uso interno/legado | — | Revisão anual; não excluir sem auditoria |
| `report_access` | Observabilidade | `source/Models/Report/Access.php` | Relatórios e logs | — | 13 meses |
| `report_online` | Core/Session | `source/Models/Report/Online.php` | Autenticação e presença | — | 30 dias após expiração |
| `settings` | Core/Legado | `source/Models/Settings/Settings.php` | Uso interno/legado | — | Revisão anual; não excluir sem auditoria |
| `slides` | Studio/Conteúdo | `source/Models/Slide/AppSlides.php`<br>`source/Models/Slide/AppSlide.php` | /studio e site público | — | Enquanto publicado + histórico |
| `studio_calendar_events` | Studio | SQL/serviço direto | /studio | — | Enquanto publicado + histórico |
| `studio_support_templates` | Help Desk | SQL/serviço direto | /helpdesk e /suporte | `created_by` → `users.id` | 5 anos após encerramento |
| `studio_support_ticket_attachments` | Help Desk | SQL/serviço direto | /helpdesk e /suporte | `message_id` → `studio_support_ticket_messages.id`<br>`ticket_id` → `studio_support_tickets.id`<br>`user_id` → `users.id` | 5 anos após encerramento |
| `studio_support_ticket_events` | Help Desk | SQL/serviço direto | /helpdesk e /suporte | `ticket_id` → `studio_support_tickets.id`<br>`user_id` → `users.id` | 5 anos após encerramento |
| `studio_support_ticket_messages` | Help Desk | SQL/serviço direto | /helpdesk e /suporte | — | 5 anos após encerramento |
| `studio_support_tickets` | Help Desk | SQL/serviço direto | /helpdesk e /suporte | — | 5 anos após encerramento |
| `support_article_votes` | Help Desk | SQL/serviço direto | /helpdesk e /suporte | — | 5 anos após encerramento |
| `support_articles` | Help Desk | `source/Models/Support/SupportArticle.php` | /helpdesk e /suporte | — | 5 anos após encerramento |
| `support_categories` | Help Desk | `source/Models/Support/SupportCategory.php` | /helpdesk e /suporte | — | 5 anos após encerramento |
| `system_audit_logs` | Core/Security | `source/Models/Notification/AuditLog.php` | Administração e auditoria | — | 5 anos; proteção legal pode ampliar |
| `system_protected_users` | Core/Security | SQL/serviço direto | Administração e auditoria | `user_id` → `users.id` | 5 anos; proteção legal pode ampliar |
| `users` | Core/Identidade | `source/Models/Auth.php`<br>`source/Models/User.php` | Todos os ambientes | — | Vínculo ativo + 5 anos |
