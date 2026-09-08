# MovesOS — isolamento de dados multi-tenant

Status: proposta de Discovery para revisão. Define invariantes, ameaças e testes;
não escolhe persistência física, altera schema, dados ou acessos.

## Objetivo e modelo organizacional

Todo dado de negócio deve pertencer a escopo explícito e ser acessado somente por
ator com vínculo e capacidade naquele escopo. O modelo inicial diferencia:

```text
platform
  └─ administradora (tenant)
       └─ condomínio
            └─ unidade/recursos operacionais
```

Condomínio pode mudar de administradora apenas por processo de transferência;
isso não muda owner silenciosamente nem mistura históricos. Recursos platform são
raros e catalogados. Ausência de `tenant_id` não significa global por padrão.

Depende das [convenções de domínio](../architecture/domain-conventions.md), do
[threat model](threat-model.md), do [RBAC](rbac-strategy.md) e da
[auditoria de segurança](security-audit.md).

## Baseline e lacunas

O legado representa administradora por corporation, condomínio por registros
próprios e usa `corporations_id`, `condominium_id`, sessão `authCondo` e relações
em várias tabelas. Controllers frequentemente montam filtros diretamente; models
genéricos não impõem tenant. `Access::can()` não inclui tenant/resource no cache
ou decisão atual. Operation possui controles e relações próprios.

Lacunas: fonte canônica do tenant ativo, classificação global/tenant/condominium,
filtro obrigatório em repositórios, vínculos temporais, jobs/cache/search/files,
unicidade por escopo, proteção de agregados/exportações e testes negativos
sistemáticos. IDs sequenciais podem facilitar enumeração mesmo com 404.

## Atores e vínculos

| Ator | Escopo possível |
| --- | --- |
| platform operator | funções platform explicitamente concedidas |
| administradora | seu tenant e condomínios vinculados |
| gestor/operador | assignments nos tenants/condomínios permitidos |
| síndico/conselho | condomínio e recursos autorizados |
| morador/fornecedor | unidade/chamado/visita expressamente relacionados |
| service account | tenant/scope mínimo e finalidade fixa |
| suporte | case-scoped ou break-glass temporário |

Membership tem actor, tenant, role, status, início/fim, origem, aprovador e versão.
Identidade global não concede acesso a todos os tenants onde o e-mail aparece.

## Classificação de dados

Cada entidade/tabela/projeção/arquivo/evento declara uma classe:

- `platform`: configuração global catalogada e acesso restrito;
- `tenant`: pertence à administradora;
- `condominium`: pertence ao tenant e condomínio;
- `unit/resource`: herda tenant/condomínio por relação imutável/validada;
- `shared_reference`: catálogo comum somente leitura, sem dado de tenant;
- `derived`: projeção que preserva o escopo das fontes;
- `unclassified`: bloqueada para nova funcionalidade até classificação.

Relação indireta deve ser resolvida no mesmo query/repositório, não por objeto já
carregado do cliente. Tabela associativa carrega/valida ambos os escopos quando
necessário. Unicidade inclui tenant quando a regra não é global.

## Contexto confiável e estados

Tenant ativo deriva da sessão autenticada + membership ativa e é revalidado no
servidor. Host/path/header/body podem solicitar seleção, nunca atestar acesso.

```text
unselected → requested → membership_validated → active → switched
                    ↘ denied              ↘ suspended/expired
active → draining_transfer → transferred → active(new tenant)
```

Troca de tenant limpa caches/filtros/form state, rotaciona contexto quando o risco
exigir e é auditada. Uma requisição possui um contexto principal; operações
cross-tenant são comandos platform/lote explícitos, não omissão do filtro.

## Invariantes por camada

### Borda e aplicação

Rota resolve resource public ID e contexto; input `tenant_id` é ignorado ou
comparado, nunca fonte única. Caso de uso recebe `TenantContext` tipado. Erros para
ausente e cross-tenant são indistinguíveis. Redirect/link não troca tenant sem
reautorização.

### Repositório e banco

Todo método tenant-scoped exige contexto e inclui predicado na leitura, escrita,
update/delete e contagem. `findById` genérico não é aceito para recurso tenant.
Foreign keys/constraints compostas ou política equivalente impedem relação entre
tenants. Operação sem filtro falha em teste/gate; banco/RLS futuro é defesa em
profundidade, não substitui domínio.

### Cache, busca e filas

Chave de cache contém ambiente + tenant/scope + versão; invalidation preserva o
mesmo escopo. Índice de busca particiona/filtra antes do ranking. Job/evento leva
tenant e actor/causation confiáveis, e consumidor revalida; dead-letter não expõe
payload de outro tenant.

### Arquivos, exportações e backups

Objeto/metadata carregam tenant e owner; path/URL não autoriza download. Signed URL
é curta, audience-bound e emitida após autorização. Exportação filtra na query,
tem capability, limite, expiração e auditoria. Backup pode conter múltiplos tenants,
mas restore/extract exige processo platform e testes de isolamento.

## Leituras, escritas e concorrência

Create injeta tenant do contexto. Update/delete usa `resource_id + tenant_id +
version`; zero linhas pode ser not-found/conflito sem revelar outro tenant. Bulk
materializa e autoriza cada alvo ou rejeita atomicamente conforme contrato.

Transferência de condomínio usa estado/lock, snapshot de vínculos, data efetiva e
reconciliação. Histórico mantém tenant no instante do evento. Escritas concorrentes
durante `draining_transfer` são bloqueadas ou roteadas por regra explícita; nunca
divididas silenciosamente entre owners.

## Agregações, relatórios e analytics

Contagens, autocomplete, filtros, paginação, totais, erros e tempos não podem
inferir outro tenant. Agregação platform exige capability e limiar/supressão.
Materialized views e data warehouse preservam tenant lineage e política de acesso.

Dados anonimizados só deixam de ser tenant-scoped após análise formal de
reidentificação. Telemetria evita tenant como label irrestrita e usa projeção
autorizada para investigação.

## Segurança, privacidade e auditoria

Riscos centrais: IDOR, mass assignment de tenant, join sem filtro, cache poisoning,
search leak, export/arquivo, job atrasado, backup/restore e suporte/break-glass.
Deny ocorre no servidor e é uniforme; UI não é controle.

Auditar seleção/troca, membership, cross-tenant negado relevante, transferência,
exportação, acesso sensível, comando platform, break-glass e mudança de classificação.
Evento registra tenant/scope confiável, actor real/efetivo, target opaco, outcome,
policy version e correlação; não copia payload/PII/segredo.

## Falhas e operações platform

Contexto ausente, membership store indisponível, policy inválida ou filtro não
comprovado falha fechado. Não há fallback para corporation/condominium default
(especialmente ID 1). Jobs sem tenant vão para quarantine/dead-letter com owner.

Operação platform declara propósito, query/selector fechado, aprovação, dry-run,
limite, checkpoint, auditoria e reconciliação. Suporte usa case e expiração.
Rollback não restaura grant/ownership anterior automaticamente.

## Métricas e alertas

- decisões tenant permitidas/negadas por surface/reason seguro;
- contexto ausente/inválido e tentativa cross-tenant agregada;
- queries/jobs/events sem classificação/tenant;
- cache/search/export/file denials e divergências;
- transferências, lotes parciais e reconciliação;
- testes canary e policy version por release.

Tenant, actor e resource não são labels globais. Alertas têm owner/runbook e
canal independente da função afetada.

## Estratégia de testes

1. matriz A/B: cada leitura, escrita, count, search e export nega tenant B;
2. ID conhecido/sequencial e recurso ausente têm resposta indistinguível;
3. mass assignment/header/host não alteram tenant confiável;
4. create injeta tenant; update/delete incluem tenant + version;
5. join/associação/foreign key não conectam tenants distintos;
6. cache key/invalidation não retornam dado após troca de tenant;
7. busca, autocomplete, agregação e paginação não vazam contagem;
8. jobs/retries/dead-letter preservam scope e revalidam membership;
9. arquivos/signed URLs/exports negam outro tenant e expiram;
10. logs, traces, erros e auditoria não revelam dados cross-tenant;
11. transferência bloqueia corrida e preserva histórico temporal;
12. falha do membership/policy store não usa tenant default;
13. operações platform exigem capability, dry-run e auditoria;
14. testes integrados usam dois tenants sintéticos/ID 2, nunca alteram ID 1.

Testes de arquitetura/lint impedem novos repositórios tenant-scoped sem contexto,
e testes de propriedade geram combinações de actor × tenant × resource.

## Migração incremental

1. inventariar tabelas, relações, rotas, queries, arquivos, caches e jobs;
2. classificar tudo como platform/tenant/condominium/derived/unclassified;
3. introduzir `TenantContext` e shadow checks nas bordas;
4. migrar repositórios de um agregado crítico com testes A/B;
5. adicionar constraints/índices por expand-backfill-contract;
6. migrar cache, busca, arquivos, eventos e exports;
7. implementar transferência e operações platform;
8. remover defaults/filtros legados após equivalência observada.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| persistência | não definida | falsa segurança | ADR após inventário |
| contexto | objeto obrigatório | legado amplo | shadow migration |
| RLS | defesa futura possível | bypass/conexão | PoC/ADR |
| tenant raiz | administradora | vínculos históricos | domain review |
| transferência | workflow explícito | corrida/ownership | testes concorrentes |
| operação platform | selector fechado | blast radius | aprovação/dry-run |

Antes de implementar: aprovar catálogo de dados, hierarquia/vínculos, contexto,
transferência, estratégia física e Issues por agregado. Exceção exige owner,
recurso, motivo, compensação e expiração. Esta Discovery não move ou expõe dados.
