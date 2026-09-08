# MovesOS — observabilidade base

Status: proposta de Discovery para revisão. Define contratos e gates; não
instala collector, agente, dashboard, alerta ou fornecedor.

## Objetivo e princípios

Observabilidade deve responder se o MovesOS está disponível, qual capacidade foi
afetada, para quais tenants, desde quando e por quê, com privacidade e custo
previsível.

1. métricas detectam tendências, logs explicam eventos e traces mostram caminhos;
2. auditoria prova ações relevantes e não é telemetria técnica;
3. alerta representa impacto acionável, não toda exceção;
4. sinais carregam ambiente, versão, superfície e correlação consistentes;
5. tenant é dimensão autorizada, nunca label irrestrita;
6. falha da telemetria não derruba o negócio nem fica silenciosa;
7. SLO numérico exige baseline e aprovação do owner.

Complementa [logging](logging-strategy.md), [tratamento de erros](error-handling-strategy.md),
[ambientes](environment-strategy.md) e [auditoria](../security/audit-trail-strategy.md).

## Baseline e lacunas

`Source\Support\AppLogger` já registra erros, exceções, fatals, duração e pico de
memória de requisições lentas; agrupa fingerprints, cria request/incident IDs e
usa banco com fallback JSONL. Existem auditoria separada, fila de e-mail com
tentativas/estados e filas/eventos do Operation.

Não há contrato implementado e comprovado para métricas, tracing, dashboards,
alertas, ownership, liveness/readiness ou sintéticos. O limite local de requisição
lenta não é SLO. Esta Discovery não declara endpoints de health existentes.

## Atores

| Ator | Responsabilidade |
| --- | --- |
| dono da capacidade | impacto, SLI, SLO futuro, degradação e runbook |
| engenharia | schema estável, instrumentação e correção |
| plataforma/operação | coleta, storage, retenção, alertas e resposta |
| segurança/privacidade | acesso, redaction e sinais sensíveis |
| suporte | correlação sem acesso indevido |
| produto | jornada e impacto real |
| worker/automação | execução, tentativa, atraso e resultado |

Alerta possui owner e substituto. Capacidade crítica sem owner não está pronta.

## Sinais e envelope comum

| Sinal | Pergunta |
| --- | --- |
| métrica | quanto, com que frequência e tendência? |
| log | qual evento ocorreu e por quê? |
| trace | onde a cadeia consumiu tempo/falhou? |
| auditoria | quem tentou qual ação e com que resultado? |
| evento de domínio | qual fato de negócio ocorreu? |

Todo sinal técnico futuro usa `service`, `surface`, `environment`, `version`,
`timestamp` e `outcome`. HTTP cria `request_id`; `correlation_id` atravessa
bordas; spans têm `trace_id`/`span_id`; jobs acrescentam `job_id`, `attempt` e
`queue`. Eventos/auditoria preservam `causation_id` conforme seus contratos.

Rotas, operações, dependências e códigos vêm de catálogo limitado. URL, SQL,
texto do usuário, resource ID e tenant não se tornam labels livres.

## SLIs e SLOs

As golden signals por superfície/capacidade são:

- **disponibilidade:** trabalhos elegíveis com resultado correto;
- **latência:** p50/p95/p99 na borda e dependências críticas;
- **tráfego:** taxa por operação/rota normalizada e volume de jobs;
- **erros:** proporção por categoria, sem rejeições esperadas;
- **saturação:** conexões, workers, fila, storage, CPU/memória disponíveis.

SLIs de jornada incluem login utilizável, agenda carregada, visita iniciada e
concluída, evidência persistida e fila entregue. Cada SLI declara população,
numerador, denominador, fonte, janela, exclusões e owner. Targets, error budget e
burn rate ficam `TBD` até baseline representativo; não se inventa compromisso.

## Health, readiness e estados

```text
unknown → healthy → degraded → unavailable → recovering → healthy
             ↘ draining → stopped
```

- liveness testa o processo sem todas as dependências;
- readiness confirma capacidade de receber tráfego/trabalho, migrations e
  dependências críticas;
- startup distingue inicialização lenta de travamento;
- deep health é autenticado e não participa de probes frequentes.

Checks têm timeout, não escrevem estado e não expõem host, schema, segredo ou
mensagem de fornecedor. Banco, identidade/sessão, schema incompatível e storage
indispensável impedem readiness. Dependência opcional produz degradação explícita.

## Dashboards e investigação

Visões iniciais cobrem plataforma, superfícies (Studio, ERP, Operation, portal e
APIs), dependências, jornadas críticas e incidentes. Drill-down segue métrica →
trace/log correlacionado → auditoria autorizada. Links preservam ambiente,
release e janela. Visão do tenant usa projeção filtrada; nome, e-mail e documento
não são dimensão de dashboard.

## Alertas e incidentes

```text
signal → firing → acknowledged → investigating → mitigated → resolved
                    ↘ suppressed (motivo + expiração)       ↘ reopened
```

Regra declara sinal/SLI, ambiente, owner, severidade, janela, limiar,
deduplicação, canal, escalada, runbook e recuperação. Com SLO, prefere burn rate
em janelas rápida/lenta; antes disso, limiares são provisórios e revisados.
Alerta da fila de e-mail não depende só de e-mail. Deploy é anotado. Silêncio sem
owner, alerta sem ação e supressão sem expiração são inválidos.

## Filas, jobs, migrations e dependências

Fila mede profundidade, idade do item mais antigo, entrada/saída, tentativas,
duração, retry, terminal/dead-letter e capacidade. Job registra agendamento,
início, término, resultado e atraso. Reprocessamento é idempotente e auditado.

Migration mede lock, duração, versão/checksum, resultado e compatibilidade sem SQL
sensível. Banco/integrações medem conexões, timeout, latência, categorias de erro
e circuit state; credenciais, payloads e queries brutas ficam fora.

## Browser e sintéticos

Frontend futuro cobre carregamento, erro JS, rede e ações essenciais com release
e correlação. Replay, DOM, teclas, formulário e upload ficam desabilitados até
avaliação de privacidade/consentimento. Sintéticos usam tenant e dados dedicados,
nunca destinatários ou negócios reais. RUM agregado não substitui sintético.

## Segurança, privacidade e multi-tenancy

- tenant/ator vêm de contexto confiável e são minimizados;
- métricas limitam cardinalidade; tenant não é label global;
- logs/traces seguem redaction e limites do contrato de logging;
- consulta/exportação aplicam ambiente, tenant, capacidade e auditoria;
- segredo, token, cookie, body, documento e SQL bruto são proibidos;
- break-glass tem motivo, aprovação, duração e trilha.

Agregações evitam inferência de outro tenant. Incidente global não concede ao
operador acesso a eventos dos demais.

## Falha, backpressure, retenção e custo

Adapter de telemetria nunca lança para o domínio. Buffer tem limite, timeout,
batch e descarte por classe; erro e auditoria crítica não são amostrados como
debug. Perda/atraso gera contador por canal alternativo. Collector indisponível
não cria retry infinito, disco ilimitado ou avalanche na recuperação.

Retenção, sampling e frequência seguem finalidade. Orçamento inclui volume,
cardinalidade, ingestão, consulta e storage. Redução de custo não elimina
evidência sob retenção legal.

## Estratégia de testes

1. schema e IDs em HTTP, CLI, worker, evento e integração;
2. cálculo de SLI sem contar rejeição esperada como indisponibilidade;
3. probes distinguem processo, dependência e migration;
4. latência, erro e saturação sob fault injection isolada;
5. fila/job mede atraso, retry, terminal e recuperação sem duplicar efeito;
6. dashboard preserva ambiente, versão, janela e escopo;
7. alerta dispara, deduplica, escala, recupera e liga ao runbook;
8. sink falho usa limite/fallback sem derrubar negócio ou encher disco;
9. canaries confirmam redaction em logs, traces, labels e links;
10. tenants não consultam nem inferem sinais entre si;
11. sintético não envia mensagem real nem altera negócio;
12. deploy anotado permite comparar regressão e rollback.

Teste de carga estabelece baseline antes dos targets. Fault injection tem abort
condition e nunca atinge produção.

## Migração incremental

1. inventariar superfícies, jornadas, jobs, filas e integrações;
2. publicar catálogo e adapter do envelope comum;
3. criar probes mínimos e classificar dependências por ambiente;
4. instrumentar uma jornada crítica ponta a ponta;
5. construir dashboards sem alertar e medir baseline/custo;
6. aprovar SLIs, targets e runbooks com owners;
7. ativar alertas progressivamente e testar recuperação;
8. ampliar tracing, filas, browser e sintéticos por risco;
9. remover legado só após equivalência medida.

Cada fase é reversível por configuração/feature flag. Fornecedor e padrão de
instrumentação exigem ADR após prova de conceito.

## Riscos, exceções e decisões reversíveis

| Tema | Decisão | Risco | Revisão |
| --- | --- | --- | --- |
| targets | TBD após baseline | falta inicial de budget | owner + dados |
| tracing | vendor-neutral | cobertura parcial | piloto de jornada |
| tenant | sem label irrestrita | investigação lenta | projeção autorizada |
| health | probes separados | custo/dependência | orçamento por check |
| frontend | sem replay | menos detalhe | avaliação de privacidade |
| alertas | impacto/ação | lacuna pré-baseline | limiar provisório |
| storage | fornecedor aberto | integração futura | ADR/PoC |

Exceção exige owner, ambiente, impacto, compensação, expiração e Issue. Vazamento
de segredo/tenant, capacidade crítica sem alerta e probe que altera estado não
são aceitáveis.

## Pontos abertos e gate

- inventariar superfícies, cron/jobs, filas e integrações implantadas;
- definir owners e jornadas críticas por ambiente;
- medir baseline de tráfego, latência, erros, saturação e custo;
- classificar dependências críticas/opcionais para readiness;
- escolher instrumentação, collector e storage via ADR/PoC;
- aprovar retenção, acesso, escalada e runbooks;
- transformar cada fatia em Issue pequena, testável e reversível.

Implementação só começa com owners e critérios verificáveis. Aprovar esta
Discovery não autoriza coleta nova, alteração de banco, endpoint público ou
contratação de fornecedor.
