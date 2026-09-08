# MovesOS — estratégia da API

Status: proposta de Discovery para revisão. Este contrato complementa as
[convenções de domínio](domain-conventions.md) e a
[estratégia de identificadores](identifier-strategy.md); não cria endpoints,
framework, migrations ou integrações.

## Objetivo e fronteira

A API pública do MovesOS será HTTP/JSON sobre HTTPS, orientada a recursos e
versionada sob `/api/v1`. Ela atende Web, App, ERP, Operation, Studio e
integrações autorizadas sem expor controllers, tabelas ou modelos internos.

- cada domínio possui seus recursos, comandos e regras;
- superfícies podem orquestrar domínios, mas não ganham uma API paralela;
- endpoints novos usam IDs públicos opacos e respostas JSON;
- rotas legadas permanecem em adaptadores até migração observável;
- contratos externos são compatíveis por padrão e mudam somente por versão.

## Atores e autenticação

| Cliente | Identidade | Escopo esperado |
| --- | --- | --- |
| Web/ERP/Operation/Studio | sessão do usuário na aplicação | capacidades e tenant ativos |
| App | token de acesso de curta duração | usuário, dispositivo e tenant |
| Integração | service account | scopes mínimos e tenant permitido |
| Job interno | workload identificável | comando específico e origem auditável |

O provedor e o protocolo de tokens serão definidos na especificação de
Identity & Access. Tokens não serão aceitos por query string. A API resolve
`actor_id`, `tenant_id` e capacidades de uma identidade confiável; o
`tenant_id` enviado pelo cliente nunca é autoridade. Troca de tenant exige
novo contexto autorizado e fica auditada.

## Contrato HTTP

- `GET` consulta sem efeitos; `POST` cria ou executa comando; `PATCH` altera
  parcialmente; `PUT` só substitui quando documentado; `DELETE` respeita o
  ciclo de vida do domínio.
- Sucesso usa `200`, `201` com `Location`, `202` para processamento assíncrono,
  `204` sem corpo; falhas usam status HTTP coerente e `application/problem+json`.
- IDs são strings. Instantes usam ISO 8601 em UTC; datas civis não recebem fuso.
- Dinheiro usa moeda ISO 4217 e valor decimal como string; ponto flutuante é
  proibido no transporte.
- Campos desconhecidos em escrita são rejeitados; expansão de campos de leitura
  é compatível e clientes devem ignorá-los.

Uma resposta de recurso não usa envelope artificial. Coleções usam:

```json
{
  "data": [],
  "page": { "next_cursor": null, "has_more": false },
  "links": { "self": "/api/v1/visits" }
}
```

Paginação é por cursor opaco, com limite máximo documentado. Filtros e ordenação
usam lista fechada por recurso; ordenação sempre inclui desempate estável. A API
não oferece consultas arbitrárias, nomes de coluna ou SQL indireto.

## Erros e validação

Falhas seguem o formato Problem Details (RFC 9457), com `type`, `title`,
`status`, `code`, `detail`, `instance` e `correlation_id`. Erros de campo podem
incluir `errors[]` com ponteiro seguro e código estável. `detail` é próprio para
o usuário e nunca contém SQL, stack trace, segredo ou existência de outro tenant.

| Categoria | Status usual | Comportamento |
| --- | --- | --- |
| `validation_error` | 400/422 | não altera estado |
| `unauthenticated` | 401 | informa esquema, não motivo sensível |
| `forbidden` | 403 | identidade sem capacidade |
| `not_found` | 404 | também para recurso invisível |
| `conflict` | 409 | unicidade, estado ou idempotência |
| `precondition_failed` | 412 | versão concorrente divergiu |
| `rate_limited` | 429 | informa `Retry-After` |
| `dependency_unavailable` | 503 | retry seguro quando aplicável |

## Concorrência, idempotência e comandos

Recursos mutáveis expõem `ETag`; atualizações concorrentes exigem `If-Match` e
falham com `412` sem efeitos parciais. Criações e comandos repetíveis aceitam
`Idempotency-Key`, escopada por tenant, ator e operação conforme a estratégia de
IDs. Mesma chave e payload retornam o resultado anterior; payload diferente
retorna conflito.

Ações que não cabem em CRUD usam comandos explícitos, por exemplo
`POST /api/v1/visits/{id}/start`. Uma transição inválida não altera o agregado.
Processamento demorado responde `202` e um recurso `operation` consultável, com
estados `pending`, `running`, `succeeded`, `failed` e `cancelled` quando houver
cancelamento seguro.

## Segurança, privacidade e multi-tenancy

- toda consulta tenant-scoped filtra `tenant_id` junto ao ID público;
- autorização acontece no recurso e na ação, não apenas na rota;
- sessão por cookie exige proteção CSRF; bearer token não usa essa exceção;
- CORS mantém lista explícita de origens, métodos e cabeçalhos;
- uploads validam tamanho, tipo real, extensão, nome, malware e autorização;
- rate limiting combina identidade, tenant, operação e risco;
- logs mascaram PII e nunca recebem senha, token, cookie, arquivo ou chave
  idempotente bruta;
- respostas sensíveis usam política de cache explícita e falham fechadas.

Toda requisição recebe ou gera `X-Correlation-ID`. Escritas relevantes geram
auditoria com ator, tenant, ação, alvo, resultado, instante e correlação. O
cliente pode enviar correlação válida, mas não controlar IDs de auditoria.

## Integrações e eventos externos

Webhooks serão opt-in, versionados, assinados, entregues ao menos uma vez e
terão ID de evento, timestamp, tentativas com backoff e política de retenção.
Consumidores devem deduplicar por ID. O payload contém IDs públicos e o mínimo
de dados necessário. Detalhes de eventos internos e consistência ficam nas
Issues #13 e #40.

## Compatibilidade e evolução

Mudanças aditivas dentro de `v1` podem incluir campos opcionais, novos recursos,
novos eventos e novos valores somente quando o cliente já trata desconhecidos.
Remover/renomear campos, estreitar formato, alterar semântica ou autorização é
breaking change. Depreciações serão anunciadas por documentação e headers, com
telemetria e prazo; uma nova versão não elimina a anterior sem plano aprovado.

## Migração incremental do legado

1. inventariar rotas, respostas JSON, consumidores e IDs expostos;
2. publicar schemas e exemplos do primeiro recurso sem trocar o legado;
3. introduzir `/api/v1` atrás de política de autenticação e tenant;
4. executar testes de contrato e isolamento em paralelo;
5. migrar um consumidor por vez e medir erros/latência;
6. anunciar depreciação somente após ausência de consumidores desconhecidos;
7. remover adaptador em mudança própria e reversível.

Rollback mantém o adaptador anterior e desativa a rota nova por configuração;
nenhuma etapa exige reescrever todas as rotas ou chaves de uma vez.

## Estratégia de testes para a implementação futura

1. contrato/schema para sucesso e cada categoria de erro;
2. autenticação e autorização positiva/negativa por ator e capacidade;
3. isolamento entre dois tenants, inclusive enumeração por ID;
4. idempotência com payload igual e diferente;
5. `ETag`/`If-Match` sob duas escritas concorrentes;
6. paginação sem perda ou duplicação durante alterações;
7. validação de datas, dinheiro, IDs e campos desconhecidos;
8. limites, upload malicioso, CORS, CSRF e vazamento em logs;
9. indisponibilidade de dependência, retry e ausência de efeito parcial;
10. compatibilidade entre consumidor legado e `/api/v1` durante a migração.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Reversibilidade |
| --- | --- | --- | --- |
| versão | URL `/api/v1` | versões simultâneas | prazo e roteamento configuráveis |
| erros | RFC 9457 | adaptação do legado | adapter por superfície |
| paginação | cursor | filtros complexos | cursor evolui sem expor storage |
| concorrência | ETag/If-Match | clientes antigos | exigência gradual por recurso |
| assíncrono | recurso operation | retenção/custo | política por comando |
| autenticação | contrato abstrato | escolha prematura | provedor definido em ADR própria |

## Pontos abertos

- selecionar mecanismo de autenticação e rotação nas Issues #23–#39;
- catalogar origens CORS e consumidores reais;
- definir limites e retenção por operação;
- escolher representação final de schemas e geração de SDK;
- inventariar endpoints legados e classificar compatibilidade;
- detalhar eventos internos, webhooks e política de entrega;
- validar exigências LGPD de retenção e exportação por domínio.

## Gate para sair do Discovery

- convenções de domínio e identificadores aprovadas;
- contrato revisado por arquitetura, segurança, produto e consumidores;
- primeiro recurso e matriz de autorização escolhidos para prova;
- riscos e pontos abertos convertidos em Issues executáveis;
- nenhum endpoint ou framework tratado como implementado por este documento.
