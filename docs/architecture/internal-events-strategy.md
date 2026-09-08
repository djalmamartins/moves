# MovesOS — eventos internos

Status: proposta de Discovery para revisão. A dependência textual da Issue #13
repete “estratégia de eventos internos”; como não há outra Issue correspondente,
esta especificação é tratada como a própria decisão requerida. Nenhum broker,
outbox, evento ou consumidor é implementado aqui.

## Objetivo e princípios

Eventos internos comunicam fatos de negócio já ocorridos entre contextos do
monólito modular sem permitir escrita direta no estado privado de outro domínio.

1. evento é fato imutável no passado; não é pedido nem log técnico;
2. o domínio produtor possui nome, semântica e evolução do evento;
3. publicação é atômica com a mudança que originou o fato;
4. entrega é ao menos uma vez; consumidores são idempotentes;
5. ordem é garantida apenas dentro da chave/agregado declarados;
6. payload contém o mínimo necessário e respeita tenant e privacidade;
7. falha de consumidor não desfaz silenciosamente o fato já confirmado.

Esta proposta complementa as [convenções de domínio](domain-conventions.md), a
[estratégia de IDs](identifier-strategy.md), o
[tratamento de erros](error-handling-strategy.md) e o
[logging estruturado](logging-strategy.md).

## Conceitos e fronteiras

| Conceito | Semântica | Exemplo | Responsável |
| --- | --- | --- | --- |
| comando | intenção que pode ser rejeitada | `StartVisit` | caso de uso receptor |
| evento de domínio | fato do agregado no mesmo contexto | `VisitStarted` | domínio produtor |
| evento de integração | contrato estável para outro contexto | `operation.visit_started.v1` | produtor + governança |
| auditoria | evidência de ator/ação/resultado | `visit.start` | política de auditoria |
| log técnico | diagnóstico de execução | `event_publish_failed` | infraestrutura |
| notificação | comunicação ao usuário | “Visita iniciada” | Communication |

Um evento pode disparar auditoria ou notificação, mas não substitui nenhuma delas.
Eventos não retornam sucesso de negócio ao produtor e não carregam objetos/modelos
PHP, HTML, SQL ou detalhes de persistência.

## Baseline observado

O legado possui fatos registrados em `operation_visit_events`,
`studio_support_ticket_events`, `operation_activity`, `system_audit_logs`,
`notifications` e logs; filas como `mail_queue` e `operation_visit_sync_queue`
possuem estados próprios. Controllers ainda escrevem diretamente em várias dessas
tabelas e acionam notificações no mesmo fluxo.

Esses registros não são declarados automaticamente como barramento de eventos.
Precisam ser classificados por finalidade, owner, retenção e consumidor antes de
migração. As tabelas atuais permanecem fontes compatíveis enquanto adapters forem
necessários.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| produtor/domínio | emitir apenas fatos que possui, após validar invariantes |
| publicador/outbox | persistir, ordenar e entregar sem perder o commit |
| consumidor | filtrar, deduplicar, processar e registrar resultado |
| owner do contrato | schema, compatibilidade, documentação e depreciação |
| operação | lag, retry, dead letter, replay e incidentes |
| segurança/privacidade | classificação, acesso, minimização e retenção |

Todo evento e consumidor ativo tem owner e próxima ação operacional. Consumidor
desconhecido não autoriza breaking change.

## Envelope canônico

```json
{
  "event_id": "01J8M70ABCDEFGHJKMNPQRSTVW",
  "event_name": "operation.visit_started.v1",
  "occurred_at": "2026-09-08T07:00:00.123Z",
  "produced_at": "2026-09-08T07:00:00.130Z",
  "producer": "operation",
  "tenant_id": "01J8M700000000000000000000",
  "aggregate_type": "visit",
  "aggregate_id": "01J8M71ABCDEFGHJKMNPQRSTVW",
  "aggregate_version": 4,
  "actor": { "type": "user", "id": "01J8M72ABCDEFGHJKMNPQRSTVW" },
  "correlation_id": "936da01f-0000-4000-8000-000000000000",
  "causation_id": "01J8M6ZABCDEFGHJKMNPQRSTVW",
  "data": {}
}
```

- IDs seguem a estratégia pública e são strings;
- `occurred_at` representa o fato; `produced_at`, sua materialização;
- `event_name` inclui versão major do schema;
- `aggregate_version` apoia ordem/concorrência, não autorização;
- `actor` pode ser `user`, `service` ou `system`, sempre identificável;
- `correlation_id` liga a jornada; `causation_id`, o comando/evento anterior;
- `data` é schema fechado/versionado e não replica toda a entidade.

## Nomes, schemas e evolução

Nomes seguem `<contexto>.<agregado>_<fato>.v<major>`, em inglês técnico alinhado
ao glossário. Fatos usam passado (`visit_started`, não `start_visit`).

Mudanças compatíveis adicionam campo opcional/default sem mudar semântica.
Remoção, renome, mudança de tipo/semântica ou maior restrição cria nova major.
Produtor pode publicar duas versões durante migração; prazo, consumidores e
telemetria são explícitos. Schema e exemplos são versionados com o código.

Valores de enum novos só são aditivos quando consumidores já tratam desconhecidos.
Timestamp, dinheiro e IDs seguem os contratos da API.

## Estados da publicação e entrega

```text
recorded → pending → claimed → delivered → acknowledged
                ↘ retryable ↗        ↘ retryable
                ↘ terminal/dead_letter → replayed
```

- `recorded`: fato e outbox persistidos na transação do agregado;
- `pending`: disponível ao dispatcher;
- `claimed`: lease temporário impede trabalho concorrente duplicado excessivo;
- `delivered`: transporte aceitou, ainda sujeito a redelivery;
- `acknowledged`: consumidor concluiu e registrou deduplicação/efeito;
- `retryable`: erro transitório com tentativa e próxima execução;
- `dead_letter`: limite/deadline atingido, com owner e ação;
- `replayed`: nova tentativa auditada preservando `event_id` original e replay ID.

Estado terminal não descarta payload silenciosamente. Retry e replay não criam
um novo fato de domínio.

## Consistência, outbox e transações

Produtor grava alteração do agregado e registro outbox na mesma transação local.
Dispatcher lê outbox após commit. Dual write direto banco+barramento é proibido
quando puder deixar apenas um lado confirmado.

Consumidor grava efeito e inbox/deduplicação na mesma transação quando possível.
Efeitos externos usam idempotency key derivada de contrato seguro e registro de
tentativa. Saga/process manager coordena fluxos longos; não existe transação
distribuída implícita.

## Ordem, idempotência e concorrência

- ordem é por `tenant_id + aggregate_type + aggregate_id` quando requerida;
- consumidores comparam `aggregate_version` e definem política para lacuna/atraso;
- evento duplicado retorna o resultado anterior sem repetir efeito;
- handlers não dependem de ordem global ou tempo de entrega;
- concorrência usa claim/lease e atualização condicional;
- mesma mensagem pode chegar após uma versão posterior; ignorar, aguardar ou
  reconciliar é decisão documentada do consumidor;
- idempotência tem retenção maior que a janela de redelivery/replay.

## Falhas, retry e dead letter

Falhas são `retryable` (timeout, dependência temporária), `terminal` (schema
inválido, autorização estrutural) ou `unknown` (resultado externo incerto).
Retry usa backoff exponencial, jitter, limite e deadline. Poison message não
bloqueia a partição indefinidamente.

Dead letter registra evento, consumidor, tentativa, código seguro, first/last
failure, próxima ação e owner. Reprocessamento exige correção/justificativa e é
auditado; payload não é editado manualmente para “passar”.

## Segurança, privacidade e multi-tenancy

- tenant vem do contexto confiável do produtor e acompanha roteamento/consulta;
- consumidor revalida o escopo necessário; evento não concede autorização;
- tópicos/filas, cache, inbox e dead letter não misturam tenants sem controle;
- payload exclui senha, token, cookie, chave, documento e conteúdo bruto;
- referência por ID público é preferida a replicar PII;
- dados necessários têm finalidade, classificação, retenção e acesso;
- eventos são autenticados/integridade protegida quando cruzam processo/host;
- service accounts possuem scopes mínimos e rotação.

Evento de outro tenant não aparece em métricas detalhadas, painel ou replay do
tenant atual. `not_found`/falha não revela existência cross-tenant.

## Observabilidade e auditoria

Métricas mínimas: pending/lag por tipo e consumidor, throughput, duração,
retry, dead letters, idade mais antiga e dedup hits. Logs usam event ID,
correlation ID, consumidor, tentativa e resultado sem payload bruto.

Criação/alteração de subscription, replay, descarte e acesso a payload sensível
são auditados. O evento de negócio não é reescrito para acrescentar diagnóstico.

## Eventos candidatos iniciais

| Produtor | Evento candidato | Consumidores possíveis | Observação |
| --- | --- | --- | --- |
| Identity | `identity.user_authenticated.v1` | Audit/segurança | minimizar IP/dispositivo |
| Tenancy | `tenancy.membership_changed.v1` | Identity/Audit | revogar cache/sessão |
| Operation | `operation.visit_scheduled.v1` | Agenda/Communication | substitui acoplamento gradual |
| Operation | `operation.visit_completed.v1` | Documents/Reports/Audit | não carregar evidências |
| Service Desk | `service_desk.ticket_opened.v1` | Communication/SLA | adapter para notificações atuais |
| Documents | `documents.document_expiring.v1` | Communication/Operation | job produtor idempotente |
| Finance | `finance.invoice_approved.v1` | Audit/Communication | dados financeiros mínimos |

São candidatos para validação, não contratos publicados. Cada um exige Issue,
schema, owner, segurança e consumidores confirmados.

## Estratégia de testes

1. schema/envelope e compatibilidade entre versões;
2. commit do agregado inclui outbox; rollback não publica;
3. dispatcher concorrente não perde mensagem e lease expira com segurança;
4. redelivery não duplica efeito/inbox;
5. mesma chave de agregado preserva ordem declarada;
6. evento atrasado/lacuna de versão segue política do consumidor;
7. retry aplica backoff/limite e chega à dead letter;
8. replay é auditado e preserva identidade do fato;
9. falha após efeito externo é reconciliada sem repetição indevida;
10. tenant A não consome, consulta ou reprocessa evento do tenant B;
11. payload/log/dead letter não contém segredo ou PII proibida;
12. consumidor desconhecido não quebra ao receber campo/enum aditivo;
13. adapter legado continua notificações/atividade durante migração;
14. métricas refletem lag, retry, dead letter e deduplicação.

Testes usam relógio e transporte controlados, banco isolado e nenhum destinatário
ou endpoint real.

## Migração incremental

1. inventariar tabelas/eventos, produtores, consumidores e finalidade atuais;
2. escolher um fato de baixo risco e publicar schema/adapters;
3. criar outbox/inbox em migration própria, sem broker obrigatório;
4. dual-run do consumidor novo com efeito desativado e comparar resultados;
5. ativar um consumidor idempotente e observar lag/retry;
6. migrar notificações/auditoria sem duplicar efeitos;
7. adotar transporte externo somente se volume/disponibilidade justificarem;
8. remover escrita legada após telemetria e rollback aprovado.

O monólito pode começar com dispatcher local sobre outbox. Escolher broker antes
de medir necessidade é fora de escopo.

## Exceções

Chamada síncrona entre módulos só é aceita quando resposta imediata faz parte do
caso de uso, com contrato e timeout; não é disfarçada de evento. Publicação
best-effort sem outbox exige risco, owner e prazo, e não serve para fato crítico.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| entrega | ao menos uma vez | duplicatas | inbox/idempotência |
| consistência | outbox local | atraso eventual | SLI por fluxo |
| ordem | por agregado | consumidor complexo | somente onde necessário |
| schema | JSON versionado | drift | registry/review futuro |
| transporte | abstrato/local primeiro | escala | broker após evidência |
| retenção | por evento/consumidor | custo/privacidade | catálogo e métricas |

## Pontos abertos

- inventariar registros atuais e separar evento, auditoria, log e notificação;
- selecionar primeiro evento/consumidor para prova;
- definir schema storage/registry e compatibilidade automatizada;
- escolher retenção, particionamento, lease e limites de retry;
- detalhar inbox/outbox e disaster recovery;
- definir governança de replay e acesso a dead letters;
- alinhar notificações/filas nas Issues #14 e #16;
- alinhar consistência entre contextos à Issue #40.

## Gate para sair do Discovery

- semântica, envelope e ownership revisados pelos domínios;
- evento piloto, produtores e consumidores confirmados;
- garantias de transação, ordem, retry e deduplicação testáveis;
- segurança, tenant, retenção e observabilidade aprovados;
- transporte físico permanece decisão reversível até prova de carga/necessidade.
