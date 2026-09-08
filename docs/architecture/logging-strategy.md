# MovesOS — logging estruturado

Status: proposta de Discovery para revisão. Este documento define o contrato de
telemetria; não altera logger, tabelas, retenção, alertas ou infraestrutura.

## Objetivo e separação de responsabilidades

Logs devem permitir detectar, correlacionar e diagnosticar falhas sem expor dados
ou depender do componente que falhou. Três sinais não são intercambiáveis:

- **log técnico:** o que aconteceu em um processo/requisição;
- **auditoria:** quem realizou uma ação de segurança ou negócio e seu resultado;
- **métrica/trace:** tendência quantitativa e caminho temporal distribuído.

Um erro pode gerar os três, com IDs em comum, mas retenção, acesso e imutabilidade
são próprios. Esta proposta depende da
[configuração segura](../security/secure-configuration.md), do
[threat model](../security/threat-model.md) e do
[contrato de ambientes](environment-strategy.md). Métricas, traces, health,
dashboards e SLOs são definidos na
[estratégia de observabilidade](observability-strategy.md).

## Baseline observado

`Source\Support\AppLogger` já captura erros, exceções e fatals, gera request e
incident IDs, normaliza níveis/canais, persiste em `app_log`, agrupa fingerprint
recente, redige chaves sensíveis e possui fallback JSONL em `storage/logs`.
Incidentes novos de erro podem gerar notificação/e-mail. `Source\Support\Audit`
grava mudanças separadamente em `system_audit_logs` com valores sensíveis
redigidos.

Lacunas a tratar em Issues executáveis, sem invalidar os controles existentes:

- correlation/request ID não possui contrato comum de entrada/saída entre HTTP,
  jobs e integrações;
- logger e alertas dependem diretamente do banco da aplicação;
- limpeza amostral e retenções estão embutidas no caminho de escrita;
- fallback usa operações suprimidas e não confirma durabilidade/permissões;
- mensagem/trace podem conter segredo no valor mesmo com chave neutra;
- alerta e pequena execução da fila de e-mail ocorrem no encerramento da requisição.

## Atores

| Ator | Pode produzir | Pode consultar | Responsabilidade |
| --- | --- | --- | --- |
| aplicação/superfície | logs e correlação | não diretamente | evento seguro e semântico |
| worker/job | logs, tentativa e job ID | próprio estado | continuidade entre retries |
| operador do tenant | eventos autorizados do tenant | visão limitada | não acessar stack/PII global |
| suporte/engenharia | diagnóstico autorizado | escopo operacional | investigação e resolução |
| segurança/auditoria | eventos de segurança/audit | escopo aprovado | integridade e incidente |
| pipeline/plataforma | deploy/infra logs | ambiente próprio | disponibilidade e retenção |

Ser developer ou operador de plataforma não concede automaticamente acesso a
payloads de todos os tenants. Acesso a logs é capacidade específica e auditada.

## Envelope canônico

Cada registro técnico futuro contém campos estruturados e tipados:

| Campo | Regra |
| --- | --- |
| `timestamp` | ISO 8601 UTC com precisão suficiente |
| `level` | nível normalizado |
| `message` | resumo humano seguro, estável e curto |
| `event.name` | nome estável `snake_case`, não texto livre |
| `event.code` | código estável opcional |
| `service`, `surface`, `environment`, `version` | origem implantada |
| `request_id`/`correlation_id` | vínculo da borda à cadeia |
| `trace_id`, `span_id` | opcionais até tracing existir |
| `incident_id` | agrupamento/atendimento de falha |
| `actor_id`, `tenant_id`, `resource_type`, `resource_id` | IDs públicos quando autorizados |
| `http.method`, `http.route`, `http.status_code` | rota normalizada, nunca query sensível |
| `duration_ms`, `attempt`, `outcome` | números/tipos consistentes |
| `context` | mapa limitado por schema e tamanho |

Campos ausentes usam `null`/omissão conforme schema, nunca string vazia ambígua.
IDs são opacos; CPF, CNPJ, e-mail e nome não substituem `actor_id`/`resource_id`.

Exemplo sanitizado:

```json
{
  "timestamp": "2026-09-08T05:00:00.123Z",
  "level": "warning",
  "event": { "name": "operation_visit_transition_rejected", "code": "INVALID_STATE" },
  "service": "movesos", "surface": "operation", "environment": "staging",
  "correlation_id": "936da01f-0000-4000-8000-000000000000",
  "actor_id": "01J8M6Z7Y5K2F1R4D9QW3A8BCE",
  "tenant_id": "01J8M700000000000000000000",
  "outcome": "rejected",
  "context": { "from": "completed", "command": "start_visit" }
}
```

## Níveis e canais

| Nível | Uso | Alerta padrão |
| --- | --- | --- |
| `debug` | diagnóstico temporário, fora de produção por padrão | não |
| `info` | marco de operação saudável e baixo volume | não |
| `notice` | evento relevante sem degradação | não |
| `warning` | degradação/condição anormal recuperável | por tendência |
| `error` | operação falhou, serviço ainda disponível | sim conforme regra |
| `critical` | capacidade crítica indisponível ou integridade em risco | imediato |
| `alert`/`emergency` | ação urgente/sistema amplamente indisponível | imediato/escalado |

Canais representam responsabilidade (`application`, `security`, `database`,
`mail`, `integration`, `performance`, `job`, `migration`), não nomes livres por
controller. Severidade descreve impacto, não a classe da exceção.

## Correlação e propagação

Na borda HTTP, um `X-Correlation-ID` válido pode ser aceito como correlação; um
`request_id` novo identifica a execução local. Valores inválidos/longos são
substituídos. A resposta retorna a correlação segura. Jobs, e-mails, eventos e
webhooks propagam `correlation_id` e criam seu próprio execution/request ID.

Tenant e ator vêm do contexto autenticado, não de headers confiados do cliente.
Uma cadeia assíncrona mantém correlação entre retries sem reutilizar incident ID
para falhas diferentes.

## Eventos e estados

Nomes descrevem fatos, por exemplo `auth_login_failed`, `mail_delivery_failed` e
`migration_checksum_mismatch`. Começar/concluir uma operação longa são eventos
distintos; não se loga cada linha/loop por padrão.

Ciclo de incidente técnico:

```text
detected → open → acknowledged → investigating → resolved
             ↘ duplicate/grouped          ↘ reopened
             ↘ ignored (motivo + expiração)
```

Agrupamento por fingerprint reduz ruído, mas preserva contagem, primeira/última
ocorrência e exemplos sanitizados. `ignored` exige ator, motivo e prazo; nova
versão, severidade ou padrão material pode reabrir. Resolução não apaga evidência.

## Erros, exceções e stack traces

A taxonomia, a propagação e a tradução por borda são detalhadas em
[`error-handling-strategy.md`](error-handling-strategy.md), sujeitas à revisão
da Issue #11.

Erros esperados de domínio usam códigos estáveis e não geram stack em nível error
por padrão. Exceções inesperadas registram classe, frame sanitizado e stack apenas
no sink restrito. Mensagem pública contém incident/correlation ID, nunca o detalhe.

O logger não lança falha para o fluxo de negócio. Recursão é bloqueada e fallback
é append-only local/stderr com limite, permissão e monitoramento. Falha do sink
primário produz um sinal fora dele; e-mail/notificação é processamento assíncrono,
sem bloquear encerramento da requisição.

## Privacidade, redaction e segurança

Proibidos em logs: senha, hash de senha, token, cookie, Authorization, CSRF,
secret, chave privada, certificado bruto, idempotency key bruta, body/arquivo,
dados bancários completos e documento pessoal desnecessário.

- sanitização ocorre antes de serializar ou persistir;
- catálogo de campos sensíveis complementa padrões por nome;
- strings livres têm detecção/canary e limite de tamanho/profundidade;
- URL registra rota normalizada sem query/fragmento sensível;
- IP e user-agent têm finalidade, acesso e retenção definidos;
- path remove raiz e dados de infraestrutura;
- acesso, exportação, resolução e deleção são auditados;
- logs são protegidos contra execução pública, alteração e leitura cross-tenant.

Redaction falha fechada: estrutura desconhecida ou profunda vira marcador, não
dump. Dados mascarados não podem ser reconstruídos pelo usuário do painel.

## Multi-tenancy

Eventos tenant-scoped carregam `tenant_id` obtido do contexto confiável. Consulta
normal filtra tenant no repositório. Incidentes globais têm escopo `platform` e
acesso separado. Um erro de outro tenant não aparece em contagem, busca, alerta ou
exportação do tenant atual.

Quando ainda não há contexto autenticado (login), o sistema usa IDs técnicos de
tentativa/sessão e dados minimizados, sem inferir tenant por entrada não validada.

## Volume, amostragem e backpressure

- error/critical e eventos de segurança relevantes não são amostrados;
- debug/info de alto volume podem ser amostrados por regra versionada;
- limites preservam contagem agregada de registros descartados;
- fingerprint, sampling e rate limit não misturam tenants indevidamente;
- sink lento/indisponível não esgota memória, worker ou conexão HTTP;
- fila possui capacidade, retenção, retry e dead-letter observáveis.

Sampling nunca decide auditoria. O custo por evento e cardinalidade de labels é
medido antes de habilitar campos livres em métricas.

## Retenção e acesso

Política é definida por finalidade e classe: diagnóstico curto, segurança e
auditoria podem exigir prazos distintos. Retenção não roda probabilisticamente no
caminho da requisição; usa job observável e idempotente. Legal hold suspende apenas
a eliminação autorizada e é auditado.

Exportações são minimizadas, têm expiração e marca d'água/metadados quando útil.
Backup respeita a maior sensibilidade dos registros e possui restauração testada.

## Alertas e ownership

Alerta nasce de impacto/SLI, não de cada linha `error`. Regra declara owner,
severidade, janela, limiar, deduplicação, canal, escalada, runbook e condição de
recuperação. Notificar a própria fila de e-mail por e-mail é evitado; existe canal
alternativo para falha do mecanismo de alerta.

Todo incidente aberto tem responsável e próxima ação. Alertas sem ação repetida
são corrigidos ou removidos, não simplesmente ignorados indefinidamente.

## Estratégia de testes

1. schema e tipos obrigatórios para HTTP, CLI, worker e migration;
2. propagação correlation/request/job IDs entre fronteiras;
3. níveis/canais/códigos normalizados e desconhecidos recusados;
4. exceção inesperada retorna resposta segura e registra incidente;
5. erro de domínio não gera stack/alerta indevido;
6. canary secrets em chave conhecida, neutra, string, URL, header e objeto profundo;
7. dois tenants não consultam, agrupam nem alertam registros entre si;
8. sink de banco indisponível aciona fallback sem recursão ou perda silenciosa;
9. volume excedido aplica backpressure/sampling e registra contagem;
10. fingerprint agrupa duplicata e separa falhas materialmente diferentes;
11. lifecycle open/ack/resolved/ignored/reopened com autorização/auditoria;
12. retenção remove apenas registros elegíveis e respeita legal hold;
13. alerta deduplica, escala e usa fallback quando o canal principal falha;
14. painel/exportação escapam conteúdo e não revelam stack/PII indevidos.

Testes usam canaries e bancos/sinks isolados; nenhuma falha deliberada atinge
produção ou destinatário real.

## Migração incremental

1. catalogar produtores, campos, canais, tabelas e arquivos atuais;
2. definir schema e adapter compatível para `AppLogger`/`Audit`;
3. adicionar correlação nas bordas e propagar a jobs/eventos;
4. introduzir sink desacoplado mantendo fallback existente testado;
5. mover alertas e retenção para processamento próprio;
6. validar redaction/canaries e isolamento antes de ampliar acesso;
7. migrar painel/consultas e medir campos/canais legados;
8. remover contratos antigos somente após telemetria sem uso.

Cada etapa preserva rollback por adapter/configuração e não reescreve histórico
sensível sem plano separado.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| formato | JSON estruturado | custo de migração | adapter para tabelas atuais |
| correlação | UUID opaco | cliente injeta cardinalidade | validação/novo request ID |
| sinks | contrato abstrato | fornecedor indefinido | piloto antes da ADR |
| retenção | job por política | backlog de limpeza | métricas e lotes |
| tracing | campos opcionais | falsa sensação de cobertura | ativar após instrumentação |
| alerta | assíncrono/por regra | atraso controlado | severidade e canal alternativo |

## Pontos abertos

- inventariar todos os `error_log`, handlers e gravações diretas;
- definir schema versionado e lista de event names/canais;
- escolher sink/collector, disponibilidade e estratégia offline;
- medir volume, cardinalidade, retenção e custo por ambiente;
- classificar IP/user-agent e requisitos LGPD;
- separar alertas/retention do shutdown HTTP;
- alinhar trilha de auditoria detalhada à Issue #12;
- definir SLIs e observabilidade base na Issue #20.

## Gate para sair do Discovery

- contrato revisado por engenharia, segurança, operação e privacidade;
- produtores e consumidores atuais inventariados;
- canary/redaction e isolamento tenant definidos como testes executáveis;
- fallback, retenção, alertas e ownership possuem plano;
- nenhuma integração/sink declarada ativa sem implementação e evidência.
