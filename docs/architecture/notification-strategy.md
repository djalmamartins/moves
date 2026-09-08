# MovesOS — notificações

Status: proposta de Discovery para revisão. Define contrato multicanal e testes;
não envia mensagens, altera preferências, filas, banco ou templates.

## Objetivo e princípios

Notificações transformam fatos aprovados em comunicação útil, segura, deduplicada
e rastreável, respeitando tenant, preferências e urgência.

1. evento de domínio não é texto/template de notificação;
2. destinatários são resolvidos no envio a partir de regra versionada;
3. cada canal possui estados próprios; “enviado” não significa entregue/lido;
4. mensagem transacional obrigatória não vira marketing;
5. retry é idempotente e não duplica comunicação;
6. links são internos/allowlisted, curtos e nunca carregam segredo;
7. falha de um canal não declara sucesso nos demais.

Depende dos [eventos internos](internal-events-strategy.md), das
[filas](async-processing-strategy.md), do [storage](file-storage-strategy.md) e do
[isolamento multi-tenant](../security/multi-tenant-data-isolation.md).

## Baseline e lacunas

`Communication` entrega mensagens agendadas para canal system, email ou both,
resolve audiences por `users.level`/usuário, evita duplicata in-app/e-mail por
consultas e valida links internos. `Notification`, `NotificationMessage` e
`mail_queue` guardam mensagens, destinatários e estados. Studio lista, lê, exclui,
marca todas, agenda, cancela/reenvia e consulta filas/auditoria.

Lacunas: regra de audience tenant-scoped/RBAC, preferências/quiet hours, catálogo
de tipos/templates versionados, per-recipient/channel lifecycle, idempotency
canônica, bounce/complaint/unsubscribe, push/SMS futuro, digest, locale/timezone,
segurança de conteúdo e autorização revalidada. Marcar a mensagem como `sent`
após enfileirar não comprova entrega individual.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| produtor | emitir fato/intent com causation e tenant |
| policy resolver | decidir elegibilidade, prioridade e canais |
| recipient resolver | materializar destinatários autorizados |
| renderer | template/version/locale com dados allowlisted |
| dispatcher | criar deliveries idempotentes nas filas |
| channel adapter | enviar e traduzir resposta do provedor |
| usuário | preferências, leitura e ações próprias |
| operador | monitorar, pausar, reprocessar e reconciliar |

## Catálogo de tipos

Cada `notification_type` declara owner, finalidade (`security`, `transactional`,
`operational`, `reminder`, `digest`, `marketing`), evento/comando origem,
recipient rule, canais permitidos/obrigatórios, prioridade, template versions,
dados allowlisted, validade, dedup window, preference policy, fallback, métricas,
retenção e testes.

Segurança e transacional podem ignorar opt-out apenas quando necessário e
documentado; quiet hours ainda podem ceder a urgência aprovada. Marketing exige
consentimento/unsubscribe e pipeline separado. Tipo sem catálogo não envia.

## Modelo e envelope

Intent canônico: `notification_id`, type/version, tenant/scope, actor/service,
event/correlation/causation IDs, resource references, locale/timezone hints,
priority, available/expires at e idempotency key. Não contém texto final, segredo,
arquivo bruto ou lista de destinatários fornecida pelo cliente.

Delivery por destinatário/canal: public ID, notification ID, recipient public ID,
channel, address reference version, template version, status, attempts,
provider reference segura, scheduled/sent/delivered/read/failed timestamps e
reason code. Endereço direto é dado sensível com acesso/retention próprios.

## Estados

```text
intent: created → resolving → scheduled → dispatching → completed
                    ↘ suppressed/cancelled/expired
delivery: pending → queued → sending → sent → delivered → read
                       ↘ retry_wait   ↘ bounced/complained/failed
                       ↘ cancelled/expired
```

`completed` significa que todas as deliveries estão terminais conforme policy,
não que foram lidas. Canal sem receipt termina em `sent/unknown_delivery`.
Evento atrasado depois de `expires_at` vira expired; não envia informação velha.
Estados são monotônicos salvo correção explícita do provedor, que gera evento.

## Resolução de destinatários e tenant

Regra resolve memberships, papel, recurso e preferências no servidor. `all`,
`admins` e `level` legados não são audience segura futura. Snapshot de destinatário
é materializado com policy/version para auditoria; membership/RBAC é revalidada
antes de conteúdo sensível. Remoção posterior pode cancelar pending deliveries.

Um tenant não envia, consulta ou infere recipients de outro. Operação platform
exige capability, selector fechado, dry-run, aprovação e limites. Recipient
duplicado por múltiplos papéis gera uma delivery por tipo/canal/dedup key.

## Preferências, frequência e horário

Preferências são por type/category e canal, com defaults documentados e origem de
consentimento. Canais obrigatórios mostram isso claramente. Quiet hours usam fuso
do destinatário/tenant e DST testado; urgência aprovada pode bypassar com auditoria.

Rate limit, frequency cap e digest evitam fadiga. Agrupamento não esconde alerta
crítico nem mistura tenants. Alteração de preferência é versionada/auditada e vale
para novas deliveries; efeito sobre pending é definido por tipo.

## Templates, conteúdo e links

Template é imutável/versionado por type, channel e locale, com schema de variáveis
allowlisted. Renderização escapa por contexto (HTML, subject, text, URL) e falha
fechado em variável ausente/desconhecida. Conteúdo do usuário não controla HTML,
header, endereço ou URL.

Link é rota interna ou origem allowlisted, construída por public ID e autorizada
novamente ao abrir. Token de ação, quando indispensável, é único, curto, purpose-
bound, armazenado como digest e nunca vai para analytics/log. Attachment usa
storage autorizado; não copia path local para worker/provedor.

## Canais e fallback

- in-app: projeção por usuário, read/unread e expiração, sem polling que dispara;
- e-mail: fila, provider response, bounce/complaint e suppression list;
- push/SMS/WhatsApp futuro: consentimento, endereço verificado, provider e custo;
- canal de segurança crítico: fallback não depende do mesmo serviço afetado.

Fallback só ocorre se catálogo autorizar, sem duplicar mensagem já entregue. Falha
de e-mail não transforma in-app em entregue por e-mail. Provider é adapter e sua
taxonomia é traduzida para reason codes estáveis.

## Agendamento, cancelamento e idempotência

Scheduler usa UTC e occurrence/idempotency key; duas execuções criam uma intent.
Dispatch usa outbox/queue, delivery key e payload hash. Retry mantém delivery ID;
reenvio manual cria nova delivery ligada à anterior e preserva tentativas.

Cancelamento impede pending/queued quando possível; envio externo já aceito não é
revertido. Atualizar evento não edita mensagem já enviada; cria correção/cancelamento
conforme tipo. Bulk possui limite, checkpoint e reconciliação.

## Segurança, privacidade e auditoria

Auditar type/template/policy, operação platform, envio sensível, preferência,
consentimento, unsubscribe, cancel/replay e acesso/export de conteúdo. Não registrar
body, segredo, token, endereço completo, attachment ou PII desnecessária.

Prevenir header injection, template injection, XSS, open redirect, recipient
enumeration, confused deputy, unsubscribe forgery e provider webhook spoofing.
Webhook valida assinatura/replay, resolve provider ID seguro e é idempotente.

## Falhas e observabilidade

Queue/provider/template/recipient store falhos produzem estados distintos, retry
por categoria e terminal com owner. Poison template pausa apenas tipo/version,
não toda fila. Backlog aplica quota/fairness por tenant. Resposta pública não
confirma se endereço/usuário existe.

Métricas: intents/deliveries por type/channel/status, oldest age, latency,
attempts, bounce/complaint, suppression, preference, expiry, provider error,
template failure e read rate quando legítimo. Recipient/tenant/address não são
labels irrestritas. Alertas têm owner/runbook e recuperação.

## Estratégia de testes

1. evento duplicado produz uma intent/delivery por idempotency key;
2. recipient rule/RBAC não cruza tenant ou usa `level` global;
3. preferences, obrigatório, quiet hours, DST, cap e digest são corretos;
4. template/locale/schema escapam HTML/header/URL e rejeitam variável indevida;
5. link/ação expira, é purpose-bound e reautoriza recurso;
6. canais mantêm estados independentes e fallback autorizado;
7. retry/bounce/complaint/provider timeout não duplica envio;
8. scheduler/bulk concorrente usa occurrence/checkpoint;
9. cancelamento diferencia pending de provider accepted;
10. membership revogada cancela conteúdo sensível pendente;
11. webhook falso/replay é rejeitado e legítimo idempotente;
12. queue/provider indisponível gera backlog/alerta sem bloquear negócio;
13. logs/UI/auditoria não vazam body, token, address ou PII;
14. integração usa tenant/ID 2 e nunca notifica/altera ID 1.

## Migração incremental

1. inventariar produtores, audiences, templates, canais e estados atuais;
2. publicar catálogo/envelope e adapters para `Communication`/`Email`;
3. modelar delivery individual em shadow mode;
4. migrar um tipo in-app+email tenant-scoped;
5. adicionar preferências, locale, quiet hours e receipts;
6. integrar webhooks, suppression, cancel/replay e observabilidade;
7. migrar tipos restantes e remover `level/all` permissivo;
8. avaliar novos canais somente por ADR/consentimento.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| audience | rule tenant-scoped | migração ampla | shadow comparison |
| estado | por delivery/canal | volume | benchmark/retention |
| preferences | por tipo/canal | complexidade | UX/legal review |
| templates | versionados | operação | preview/rollback |
| providers | adapters | lock-in | ADR/PoC |
| tracking | mínimo por finalidade | privacidade | privacy review |

Antes de implementar: aprovar catálogo, recipient rules, estados, preferências,
templates, links, webhooks e Issues por tipo/canal. Exceção exige owner, tipo,
finalidade, público, prazo e auditoria. Esta Discovery não envia notificações.
