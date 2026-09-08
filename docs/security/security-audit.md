# MovesOS — auditoria de segurança

Status: proposta de Discovery para revisão. Especializa a trilha geral para
identidade e controles de segurança; não cria eventos, tabelas ou alertas.

## Objetivo e fronteiras

Auditoria de segurança deve provar tentativas, decisões e mudanças que afetem
identidade, privilégio, sessão, segredo e controles, permitindo investigação sem
transformar logs técnicos ou histórico de login em evidência canônica.

Depende da [trilha de auditoria](audit-trail-strategy.md), do
[histórico de login](login-history.md), do [RBAC](rbac-strategy.md) e do
[threat model](threat-model.md). Herda envelope, integridade, retenção e
append-only da trilha geral; aqui são definidos catálogo, criticidade e resposta.

## Baseline e lacunas

`Source\Support\Audit` grava ações/entidades e diffs sanitizados em
`system_audit_logs`; models e controllers chamam o helper em pontos distintos.
`AppLogger` registra falhas de autenticação/autorização e `AppLog` registra
atividade. O painel permite consultar auditoria e gerenciar logs técnicos.

Não há catálogo completo de segurança, actor type, tenant/correlation/causation
canônicos, policy version, assurance, reason/outcome uniforme ou cobertura
provada de negações, sessões, MFA, segredos e break-glass. Chamadas manuais podem
deixar lacunas; CRUD/tabela não expressa necessariamente o fato de segurança.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| produtor de domínio | emitir fato completo junto à decisão |
| serviço de auditoria | validar schema, redigir, persistir e selar |
| segurança | catálogo, criticidade, alertas e investigação |
| auditor/compliance | consultar integridade e evidência autorizada |
| suporte | acesso mínimo vinculado a case e motivo |
| plataforma | storage, relógio, backup, retenção e disponibilidade |
| privacy/legal | finalidade, minimização, hold e descarte |

## Catálogo mínimo

### Autenticação e credenciais

Login sucedido/rejeitado relevante, rate limit/lockout, recuperação/reset, troca
de senha, credencial comprometida, MFA enroll/verify/recover/remove, step-up e
mudança de assurance. Falhas comuns podem ser agregadas; transições não.

### Sessões

Criação/rotação, logout, revogação individual/global, uso pós-revogação,
expiração por política, consulta/exportação de sessões e falha de propagação.

### Autorização e privilégios

Role/policy/permission criada ou alterada, atribuição/revogação/suspensão,
override, segregação negada, denied sensível, impersonation, break-glass e mudança
de módulo/capacidade. Leitura comum permitida não é auditada por padrão.

### Plataforma e dados sensíveis

Secret/configuração, service account, webhook, deploy/migration, backup/restore,
exportação, legal hold, retenção, acesso a documento sensível e administração da
própria auditoria.

Cada ação declara slug estável, gatilho, outcomes, severity, campos allowlisted,
owner, retenção, alerta, fail policy e testes. Ação nova sensível sem catálogo não
entra em produção.

## Envelope especializado

Além do envelope geral: `security_category`, `action`, `outcome`, `reason_code`,
`severity`, actor real/efetivo/type, target type/public ID, tenant/scope,
audience, assurance before/after, policy/release version, session public ID,
correlation/causation e incident/case ID quando houver.

Origem de rede/device é referência minimizada/classificada, não IP/UA bruto por
padrão. Senha, hash, token, cookie, MFA response, secret, body, documento, e-mail,
SQL/stack são proibidos. Descrição é gerada pelo servidor a partir do catálogo.

## Outcomes, severidade e estados

Outcomes: `succeeded`, `denied`, `rejected`, `failed`, `cancelled`, `unknown`.
Severidade deriva do risco e contexto, não só do outcome. Falha de senha comum é
baixa/agregável; elevação própria, quebra de cadeia ou break-glass é alta.

```text
captured → committed → sealed → retained → expired → disposed
               ↘ failed → reconciled
event → evaluated → linked_to_case → contained → closed
                    ↘ false_positive (evidência preservada)
```

Fechar caso não altera evento. Correção/anotação é novo registro append-only.
`unknown` requer owner e reconciliação; não é convertido em sucesso por timeout.

## Consistência e falha de auditoria

Ações críticas de controle — privilégio, segredo, break-glass, restore e
administração da auditoria — falham fechadas se o registro transacional/outbox não
puder ser confirmado. Ações comuns usam outbox atômica e podem concluir com
pipeline degradado, desde que backlog, alerta e reconciliação sejam garantidos.

A matriz exata fica no catálogo. O logger nunca é fallback como única evidência.
Duplicata usa event ID; falta/gap, relógio divergente, alteração e falha de selo
geram incidente. Restauração valida cadeia antes de liberar consulta.

## Detecção, alertas e casos

Regras correlacionam eventos por janelas e pseudônimos seguros: força bruta,
credential stuffing, impossible travel futuro, uso pós-revogação, escalada de
privilégio, bypass/override e exportação anormal. Detecção é projeção; não edita o
evento nem toma ação automática destrutiva sem comando/policy auditável.

Alerta declara owner, severidade, limiar, supressão, canal, runbook e recuperação.
Caso contém ID, responsável, escopo, evidências referenciadas, decisões e próxima
ação. E-mail não é único canal para incidente do próprio e-mail.

## Consulta, acesso e multi-tenancy

Filtros usam período limitado, action/category, outcome, severity, actor/target
opacos, tenant/scope, audience, correlation e case. Paginação é por cursor.
Exportação exige step-up, motivo, limites, expiração e marcação/auditoria.

Tenant vem do contexto confiável. Admin de condomínio vê apenas catálogo e
projeções expressamente tenant-scoped; não vê login global, outro tenant, stack ou
actor pseudônimo platform. Suporte usa case-scoped access. Break-glass registra
operador e sujeito e expira automaticamente.

## Privacidade, retenção e integridade

Finalidade e campo são minimizados no produtor. Redaction falha fechada e é
testada com canaries. Retenção varia por categoria/obrigação e fica `TBD` após
privacy/legal review. Legal hold suspende descarte elegível sem ampliar acesso.

Aplicação pode inserir via interface restrita, não atualizar/apagar eventos
selados. Serviço de leitura é separado. Hash chain/storage imutável e assinatura
são decisão de ADR após volume/ameaça; hash no mesmo banco não basta contra admin.

## Métricas e alertas operacionais

- eventos por categoria/outcome/severity/policy version;
- atraso, backlog, retry, duplicata, gap e falha de selo;
- eventos sem catálogo/campos rejeitados/redaction;
- consultas, exports, holds e break-glass;
- casos abertos, tempo de triagem/contenção e reabertura;
- tenant, actor, target e session não são labels irrestritas.

## Estratégia de testes

1. cada ação do catálogo emite outcomes e campos obrigatórios;
2. ação crítica falha fechada; comum usa outbox/reconciliação;
3. retry/event ID não duplica e correção não edita original;
4. segredo/PII em chave conhecida, neutra e profunda é redigido;
5. login, MFA, sessão, RBAC, secret e break-glass têm cobertura negativa;
6. actor real/efetivo, scope, assurance e policy version são corretos;
7. dois tenants não consultam, exportam ou correlacionam eventos;
8. suporte/case e export exigem capacidade, motivo e step-up;
9. gap, alteração, relógio e selo quebrado geram incidente;
10. retenção, hold, backup/restore preservam política e integridade;
11. detector correlaciona sem editar evidência ou vazar identidade;
12. painel escapa conteúdo e não mostra stack/log técnico;
13. indisponibilidade do sink não causa recursão/perda silenciosa;
14. integração usa ID 2 e nunca altera/audita artificialmente ID 1.

## Migração incremental

1. inventariar produtores, actions CRUD, logs e lacunas por controle;
2. publicar catálogo/schema e adapter do `Audit` atual;
3. adicionar IDs/contexto e shadow validation sem rejeitar legado;
4. migrar autenticação/sessões/RBAC por fatias testadas;
5. implementar outbox, integridade e reconciliação;
6. liberar consulta/casos/exportação com acesso restrito;
7. ativar detecções/alertas após baseline;
8. remover eventos genéricos apenas após equivalência observada.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| catálogo | fechado/versionado | lacunas iniciais | coverage matrix |
| integridade | mecanismo em ADR | adulteração | threat/PoC |
| detecção | projeção separada | falso positivo | baseline/runbook |
| retenção | TBD por categoria | excesso/lacuna | legal/privacy |
| falha | matriz por ação | indisponibilidade | fault tests |
| acesso | capability + case | investigação lenta | UX/security |

Antes de implementar, aprovar catálogo, matriz fail policy, integridade, retenção,
acesso, detecções e Issues pequenas. Exceção exige owner, ação/campos, motivo e
expiração. Esta Discovery não coleta, altera ou exporta eventos reais.
