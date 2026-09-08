# MovesOS — filas e processamento assíncrono

Status: proposta de Discovery para revisão. Define contratos e testes; não
instala broker, cria tabela, altera worker ou agenda cron.

## Objetivo e princípios

Processamento assíncrono desacopla efeitos demorados/retryable do request sem
perder consistência, tenant, autorização ou rastreabilidade.

1. commit de negócio e intenção assíncrona usam outbox quando precisam atomicidade;
2. entrega pode ser ao menos uma vez; efeito deve ser idempotente;
3. mensagem não é fonte de verdade do agregado;
4. retry só ocorre para falha classificada como transitória;
5. item terminal possui owner, evidência e ação de reprocessamento;
6. worker revalida tenant, policy e pré-condições no consumo;
7. fila indisponível não fica silenciosa nem prende request indefinidamente.

Depende dos [eventos internos](internal-events-strategy.md), dos
[ambientes](environment-strategy.md), do [tratamento de erros](error-handling-strategy.md)
e da [observabilidade](observability-strategy.md).

## Baseline e lacunas

`Email::queue()` persiste `mail_queue`; `sendQueue()` seleciona pending/retry,
faz claim por update, envia, aplica backoff exponencial e termina em sent/failed.
Há jobs CLI para e-mail/certificado e `operation-tick.php` com exemplo de cron.
Operation também materializa recorrências e notificações em execuções periódicas.

Lacunas: contrato geral de job/envelope, outbox, lease/heartbeat, concorrência,
jitter, dead-letter/replay, tenant/correlation, shutdown/drain, scheduler HA e
garantia de autorização. Seleção antes do claim pode gerar disputa; processamento
preso em `processing` não tem recuperação canônica comprovada.

## Atores e componentes

| Componente | Responsabilidade |
| --- | --- |
| produtor | validar comando e gravar intenção/outbox |
| dispatcher | publicar outbox idempotentemente |
| queue | ordenar/entregar conforme contrato, sem regra de negócio |
| scheduler | materializar jobs de agenda sem duplicar |
| worker | claim, heartbeat, executar, classificar e confirmar |
| handler | efeito idempotente e tenant-scoped |
| operador | monitorar, pausar, drenar, reprocessar e reconciliar |

## Envelope canônico

Campos mínimos: `job_id`, `type`, `schema_version`, `created_at`, `available_at`,
`priority`, `attempt`, `max_attempts`, `timeout_ms`, `tenant_id`/scope confiável,
`actor`/service, `correlation_id`, `causation_id`, `idempotency_key`, payload
versionado e metadata limitada.

Payload contém public IDs/referências, não objeto inteiro, senha, token, cookie,
arquivo bruto ou PII desnecessária. Handler relê estado atual e valida schema.
Mensagem desconhecida vai para terminal/quarantine, não é descartada.

## Estados e transições

```text
created → pending → leased → processing → succeeded
              ↑        ↘ retry_wait ────────┘
              ↘ cancelled
                       processing → terminal → requeued → pending
                              ↘ expired
```

- `leased` possui worker, token e prazo; só o holder confirma;
- heartbeat renova lease de trabalho longo dentro do timeout;
- lease vencido permite novo claim após reconciliação;
- `retry_wait` tem próxima tentativa calculada;
- `terminal` preserva reason e payload minimizado;
- cancelamento é best effort antes do efeito irreversível;
- sucesso é terminal e reprocessamento cria novo comando ligado ao original.

Transições usam compare-and-swap/claim atômico. Mesmo job não fica ativo em dois
workers sem que o handler tolere redelivery.

## Idempotência e consistência

Produtor usa idempotency key + payload hash no escopo tenant/tipo; mesma key com
payload diferente conflita. Outbox é gravada na transação do negócio e despachada
ao menos uma vez. Inbox/dedup do consumidor e constraint do efeito impedem
duplicidade.

E-mail, webhook e API externa usam chave do fornecedor quando disponível e
guardam resultado/estado desconhecido para reconciliação. Timeout não significa
“não executado”. Compensação é comando separado, idempotente e auditado.

## Retry, timeout e terminal

Categorias transitórias: timeout confirmado, indisponibilidade, rate limit e
conflito recuperável. Validação, autorização, schema incompatível, recurso
terminal e bug não repetem automaticamente. Política por job declara tentativas,
backoff exponencial com jitter, deadline, timeout, circuit breaker e terminal.

`max_attempts` e números ficam por handler após baseline. Retry manual exige
capability, motivo, versão/payload visível de forma segura e dry-run quando amplo.
Não resetar contagem apaga evidência; novo job referencia o terminal.

## Prioridade, ordem e fairness

Prioridade é enumeração limitada, não número arbitrário do produtor. Ordem estrita
só quando requisito de negócio, usando partition key por agregado/tenant. FIFO
global não é prometido. Fairness e quota evitam que um tenant monopolize workers.

Starvation, idade máxima e backlog por classe são observáveis. Job crítico não
divide pool ilimitadamente com PDF/e-mail em massa sem bulkhead aprovado.

## Scheduler, concorrência e deploy

Agenda recorrente usa occurrence key determinística + lock/leader election ou
constraint equivalente. Dois schedulers materializam uma ocorrência. Horário é
UTC com fuso/regra versionados na definição; DST é testado.

Worker inicia somente após readiness de banco/schema/queue, recebe SIGTERM,
interrompe novos claims, renova/finaliza leases seguros e encerra dentro do grace
period. Deploy mantém compatibilidade N/N-1 de payload/handler; migration não
remove schema enquanto mensagens antigas existirem.

## Segurança, autorização e multi-tenancy

Tenant/actor vêm do contexto produtor e são validados pelo consumidor contra o
recurso. Job não herda superuser do worker. Service account tem permissions por
tipo/scope. Mudança de membership/RBAC entre enqueue e consumo pode negar e
terminalizar/compensar conforme contrato.

Queue UI, pause, cancel, replay, payload view/export e mudança de prioridade são
capacidades auditadas. Payload/log não contém segredo/PII indevida. Criptografia,
retenção e acesso consideram que a fila replica dados. Um tenant não consulta
status, contagem, timing ou terminal de outro.

## Operação e observabilidade

Métricas: depth, oldest age, enqueue/claim/success/failure rate, duration,
attempts, retries, lease expired, terminal, scheduler lag, outbox lag, saturation
e fairness. Labels são tipo/fila/outcome/environment, não job/tenant irrestrito.

Cada tipo possui owner, SLO futuro, dashboard, alertas e runbook. Correlation liga
request → outbox → job → dependência. Logs registram transição e reason seguro;
auditoria cobre comandos administrativos/efeitos relevantes.

## Falhas e recuperação

- queue indisponível: outbox acumula com backpressure/alerta;
- banco indisponível: worker não confirma e lease expira seguramente;
- worker crash: lease/retry recupera sem assumir efeito ausente;
- poison message: limite rápido, terminal e owner;
- backlog: admission control, quota e degradação explícita;
- clock drift: alerta e cálculo server-side consistente;
- perda/corrupção: reconciliar fonte de verdade/outbox/inbox e backup testado.

## Estratégia de testes

1. outbox e commit são atômicos; rollback não publica;
2. dispatcher/redelivery duplicado produz um único efeito;
3. claim concorrente tem um lease holder válido;
4. crash antes/depois do efeito reconcilia sem duplicar;
5. retry aplica categorias, backoff+jitter, deadline e terminal;
6. timeout externo fica unknown/reconciliado, não repete cegamente;
7. scheduler concorrente/DST gera uma occurrence;
8. cancel/pause/drain/replay respeitam estados e autorização;
9. deploy N/N-1 processa schema versionado e rejeita desconhecido;
10. tenant/RBAC revogado antes do consumo é revalidado;
11. dois tenants não consultam nem monopolizam fila;
12. payload/log/UI não vazam segredo/PII;
13. queue/store indisponível aplica backpressure e alerta;
14. load test mede throughput, oldest age e saturation;
15. integração usa tenant/ID 2 e nunca altera ID 1.

## Migração incremental

1. inventariar jobs, cron, tabelas, estados, handlers e efeitos externos;
2. publicar envelope, taxonomia de falha e adapter;
3. endurecer `mail_queue` com claim/lease/idempotência em shadow metrics;
4. introduzir outbox/inbox para uma jornada crítica;
5. criar worker runner com readiness/drain/telemetria;
6. migrar schedulers e Operation por tipo;
7. liberar terminal/replay UI com RBAC/auditoria;
8. escolher broker somente via ADR/PoC após medir volume.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| entrega | at-least-once | duplicidade | idempotency tests |
| backend | abstrato/DB atual | escala/lock | benchmark + ADR |
| ordem | por partition quando exigida | complexidade | requisito explícito |
| retries | por categoria | tempestade | fault/load tests |
| payload | referência versionada | estado mudou | handler revalida |
| autorização | no consumo | job terminal inesperado | UX/runbook |

Antes de implementar: aprovar inventário, envelope, estados, policies por handler,
leases, outbox/inbox, quotas e Issues pequenas. Exceção exige owner, fila/tipo,
motivo, compensação e expiração. Esta Discovery não processa mensagens reais.
