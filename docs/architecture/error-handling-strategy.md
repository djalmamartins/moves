# MovesOS — tratamento central de erros

Status: proposta de Discovery para revisão. Este documento define taxonomia,
propagação e apresentação de falhas; não altera handlers, respostas ou fluxos.

## Objetivo e princípios

Uma falha deve produzir resposta previsível, estado consistente e diagnóstico
correlacionado, independentemente da superfície. O tratamento central traduz o
erro na borda; não esconde regra de domínio nem transforma toda falha em HTTP 500.

1. erro esperado é parte do contrato; exceção inesperada é incidente;
2. cada camada adiciona contexto seguro e preserva a causa;
3. transação falha por inteiro dentro da fronteira de consistência;
4. mensagens externas não revelam internals, PII, segredo ou outro tenant;
5. retry ocorre apenas quando a operação é idempotente e a falha é retryable;
6. HTML, JSON, CLI e jobs compartilham categorias, não necessariamente formato.

Esta proposta complementa a [estratégia da API](api-strategy.md), o
[logging estruturado](logging-strategy.md) e os
[padrões de código](code-standards.md).

## Baseline e lacunas observadas

`AppLogger` já registra erros PHP, exceções não capturadas e fatals, retornando
uma página 500 com código de incidente. Controllers, porém, ainda combinam
`echo json_encode`, mensagens HTML, redirects, status próprios e `catch` locais.
Models frequentemente comunicam falha por objeto de mensagem.

Esse legado permanece compatível por adapter. As lacunas são uniformidade de
status/schema, negociação HTML/JSON, fronteira transacional, classificação de
retry e testes de todos os caminhos. Este documento não afirma que um middleware
central ou exceções tipadas já existam.

## Atores e responsabilidades

| Ator/camada | Responsabilidade |
| --- | --- |
| domínio | invariantes, estados e erros sem HTTP/UI |
| caso de uso | transação, idempotência, dependências e resultado |
| adapter/repositório | traduz erro técnico preservando causa segura |
| borda HTTP/CLI/job | autentica, negocia formato e mapeia status/exit/retry |
| logger | registra incidente/correlação sem lançar nova falha |
| cliente/UI | apresenta ação possível e preserva dados seguros do formulário |
| operação/suporte | investiga pelo incident ID e atualiza lifecycle |

## Taxonomia canônica

| Categoria | Semântica | HTTP | Retry |
| --- | --- | --- | --- |
| `validation_error` | forma/campo inválido | 400/422 | não |
| `unauthenticated` | identidade ausente/inválida | 401 | após autenticar |
| `forbidden` | ação não permitida | 403 | não |
| `not_found` | recurso ausente ou invisível | 404 | não |
| `conflict` | estado, unicidade ou idempotência | 409 | após reconciliar |
| `precondition_failed` | versão/ETag divergente | 412 | reler e decidir |
| `rate_limited` | quota/limite excedido | 429 | após `Retry-After` |
| `dependency_timeout` | dependência excedeu prazo | 504/503 | condicionado |
| `dependency_unavailable` | serviço externo indisponível | 503 | condicionado |
| `storage_unavailable` | banco/filesystem/fila indisponível | 503 | condicionado |
| `internal_error` | falha inesperada/bug | 500 | não automático |

Códigos específicos são estáveis (`VISIT_INVALID_STATE`), enquanto mensagem pode
ser traduzida. Categoria não expõe classe PHP ou fornecedor. Um mesmo código tem
semântica única e owner de domínio.

## Modelo interno

Erros esperados carregam: categoria, código, mensagem segura, metadados tipados,
campo/ponteiro opcional e causa técnica restrita. Podem ser resultado tipado ou
exceção de aplicação conforme a fronteira; retorno `false`/`null` ambíguo não é
contrato novo.

Exceções técnicas de PDO, filesystem, HTTP client e vendor não atravessam até o
controller. Adapters convertem para falha de dependência/storage e preservam a
causa somente para logging. `catch (Throwable)` só é aceitável na borda, cleanup
ou tradução com rethrow/resultado explícito; nunca para continuar silenciosamente.

## Estados da execução

```text
received → validated → authorized → executing → committed → responded
    ↘ rejected   ↘ denied       ↘ failed/retryable
                                ↘ compensated → failed
```

- antes de `executing`, falha não produz efeito;
- dentro do agregado/transação, erro reverte todas as escritas;
- efeito externo após commit usa outbox/job ou compensação explícita;
- timeout desconhecido não é tratado como “não executado”;
- resposta perdida pode ser repetida somente por idempotency key;
- falha ao responder após commit retorna incidente, mas não desfaz por suposição.

## Borda HTTP e negociação

### API JSON

Usa `application/problem+json` conforme a estratégia da API, com `type`, `title`,
`status`, `code`, `detail`, `instance`, `correlation_id` e `errors[]` seguros.
Content-Type e status são definidos antes do corpo; serialização falha possui
fallback mínimo válido.

### AJAX legado

Adapter mantém chaves esperadas (`message`, `redirect`, `reload`) enquanto
adiciona status coerente e correlação quando compatível. HTML de mensagem não vira
contrato de API novo. A migração mede consumidores antes de remover formatos.

### Navegação HTML

Erro de campo retorna à tela com valores não sensíveis e mensagens por campo.
401 redireciona ao login somente em navegação segura, preservando destino local;
403, 404, 419/CSRF e 500 têm páginas próprias da superfície. Redirect não encobre
falha inesperada nem aceita destino externo não allowlisted.

## CLI, workers e jobs

CLI escreve resultado humano seguro em stderr e retorna exit codes documentados:
sucesso `0`, uso/configuração `2`, falha operacional `1` (valores adicionais só
com contrato). Job registra tentativa e termina como `succeeded`, `retryable` ou
`terminal`; não captura erro e confirma mensagem como sucesso.

Retry usa backoff+jitter, limite e deadline. Erros de validação, autorização,
conflito permanente e bug não são retryable. Dead-letter/estado terminal mantém
payload minimizado, owner e ação de reprocessamento auditada.

## Transações, idempotência e dependências

- a transação pertence ao caso de uso, não ao controller;
- erro de banco faz rollback em `finally`/abstração segura;
- chamadas externas não ficam dentro de transação longa;
- outbox liga commit local ao evento assíncrono;
- mesma idempotency key/payload retorna o resultado já produzido;
- key igual com payload diferente é `conflict`;
- circuit breaker/timeout/bulkhead são decisões por dependência e risco;
- compensação é comando auditável, idempotente e pode falhar explicitamente.

## Segurança, privacidade e multi-tenancy

Recurso inexistente e recurso de outro tenant retornam `not_found` indistinguível.
Autorização negada não inclui papel necessário, owner do recurso ou existência
cross-tenant. Erro público não contém SQL, stack, path, host interno, segredo,
token, documento, e-mail ou payload bruto.

Tenant e ator do log vêm do contexto confiável. Erros de parsing anteriores à
autenticação usam apenas correlação e dados minimizados. Validação de upload não
repete nome/path fornecido sem sanitização. Página de erro sempre escapa incident
ID e qualquer texto contextual.

## Logging, auditoria e alertas

Erro esperado de usuário não vira incidente operacional por padrão. Falha
inesperada registra categoria, código, classe técnica, frame sanitizado, versão,
ambiente e correlação. Eventos repetidos podem agrupar por fingerprint sem
misturar tenants nem perder contagem.

Falha relevante de autorização, configuração, migration ou alteração de estado
gera auditoria própria. Alerta depende de impacto/tendência; o handler não envia
e-mail síncrono. Falha do logger não substitui nem altera o resultado do negócio.

## Experiência do usuário

Mensagem responde: o que não foi concluído, o que o usuário pode fazer e como
obter suporte. Não culpa o usuário, não promete sucesso inexistente e não perde
entrada não sensível. Botão de retry só aparece para ação segura. Estados loading,
empty, validation, offline/degraded, forbidden e unexpected são distintos.

Acessibilidade exige foco no resumo/primeiro campo inválido, associação entre
mensagem e campo, live region apropriada e texto além de cor/ícone.

## Estratégia de testes

1. cada categoria mapeia para status, schema e mensagem segura;
2. HTML, API, AJAX legado, CLI e job negociam o formato correto;
3. validação múltipla associa campos sem refletir payload malicioso;
4. 401/403/404 não permitem enumeração de tenant/recurso;
5. exceção inesperada gera um incident/correlation ID e resposta genérica;
6. transação falha sem escrita parcial e libera lock/conexão;
7. timeout antes/depois do commit não duplica efeito sob retry;
8. idempotency key igual/diferente segue o contrato;
9. dependência indisponível aplica limite, backoff e estado terminal;
10. falha do logger/sink usa fallback sem recursão;
11. mensagens, logs, headers e redirects não vazam segredo/PII;
12. dois tenants recebem respostas indistinguíveis e logs isolados;
13. páginas de erro escapam conteúdo e cumprem acessibilidade;
14. adapters legados preservam consumidores durante a migração.

Testes usam dependências e dados isolados; fault injection nunca atinge produção.

## Migração incremental

1. inventariar respostas, status, redirects, catches e mensagens por superfície;
2. publicar taxonomia/códigos e objetos internos sem trocar controllers;
3. criar tradutores centrais para API, HTML, AJAX, CLI e job;
4. migrar um fluxo de alto risco com testes de contrato;
5. mover transação e regra para caso de uso quando necessário;
6. medir formatos/códigos legados e migrar consumidores;
7. remover catches/respostas duplicadas somente após cobertura;
8. tornar lint/arquitetura gate depois do baseline limpo.

Cada etapa mantém adapter/feature flag e rollback compatível. Não se reescrevem
todos os controllers num único PR.

## Exceções

Formato legado temporário exige rota/consumidor, owner, risco, teste, métrica e
condição de remoção. Captura silenciosa, exposição de stack em produção, escrita
parcial ou acesso cross-tenant não são exceções aceitáveis.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| erros internos | tipos/códigos estáveis | abstração excessiva | piloto em fluxo real |
| API | Problem Details | clientes legados | adapter por rota |
| HTML | páginas por superfície | duplicação visual | componente compartilhado |
| retry | explícito por categoria | classificação errada | fault injection/métricas |
| transação | caso de uso | legado no controller | migração incremental |
| ferramenta | handler abstrato | framework não escolhido | ADR após prova |

## Pontos abertos

- inventariar todos os formatos JSON/HTML, status e `catch` existentes;
- definir classes/interfaces internas após piloto sem acoplar framework;
- catalogar códigos por domínio e política de tradução;
- decidir uso de 419 versus 403 para CSRF no contrato futuro;
- mapear transações e efeitos externos que exigem outbox/compensação;
- definir retry/circuit breaker por dependência;
- criar Issues por superfície para migração dos adapters.

## Gate para sair do Discovery

- taxonomia revisada por domínios, frontend, segurança e operação;
- matriz de formato/status/código aceita;
- um fluxo representativo desenhado de ponta a ponta;
- testes negativos, de transação e tenancy convertidos em critérios executáveis;
- nenhuma centralização declarada implementada por este documento.
