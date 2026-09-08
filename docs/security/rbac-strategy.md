# MovesOS — RBAC e autorização

Status: proposta de Discovery para revisão. Define contrato e testes; não altera
papéis, permissões, usuários, migrations ou acessos em produção.

## Objetivo e princípios

RBAC deve decidir se um ator autenticado pode executar uma ação sobre um recurso
em um escopo, negando por padrão e preservando isolamento multi-tenant.

1. autenticação não concede autorização;
2. permissão expressa capacidade de negócio, não tela ou tabela;
3. papel agrupa permissões; contexto limita o alcance da concessão;
4. negação explícita prevalece e falha técnica não amplia privilégio;
5. frontend oculta ações, mas servidor sempre autoriza novamente;
6. mudanças são versionadas, auditadas, revisáveis e reversíveis;
7. contas privilegiadas exigem MFA/step-up conforme risco.

Depende das [convenções de domínio](../architecture/domain-conventions.md), da
[trilha de auditoria](audit-trail-strategy.md) e do [threat model](threat-model.md).

## Baseline e lacunas

`Source\Support\Access` consulta `access_roles`, `access_permissions`, vínculos e
overrides, com cache no processo. `deny` prevalece sobre `allow`; módulos podem
desabilitar acessos. Há fallback por `users.level` quando consulta/permissão
falha, e o papel `developer` recebe autorização irrestrita. Migrations criam
papéis/permissões e vinculam o usuário ID 1 a developer. Controllers usam
`Access::can()` em algumas bordas, mas parte do legado ainda compara `level`.

Lacunas críticas: fallback pode ampliar acesso durante falha; cache não carrega
tenant, versão ou resource scope; query considera apenas um papel (`LIMIT 1`);
developer bypassa módulos/negações; nomenclatura e cobertura não estão completas;
papel global e vínculo por tenant não estão separados no contrato.

## Modelo conceitual

| Conceito | Regra |
| --- | --- |
| actor | usuário, service account ou sistema identificado |
| role | conjunto nomeado/versionado de permissões |
| permission | ação estável `context.resource.verb` |
| assignment | papel concedido ao ator em escopo e vigência |
| scope | platform, administradora, condomínio ou recurso permitido |
| constraint | condição contextual explícita e testável |
| override | exceção temporária allow/deny com motivo e expiração |
| decision | allow/deny + código, policy version e correlation ID |

RBAC não incorpora ownership, workflow ou segregação complexa como strings
ad-hoc. Quando a decisão depende de atributo/estado, o policy service combina
papel com predicado de domínio testado, mantendo deny-by-default.

## Nomenclatura e catálogo

Permissões usam `<contexto>.<recurso>.<verbo>`, por exemplo
`operation.visit.view`, `operation.visit.start`, `identity.role.assign` e
`audit.event.export`. `manage` só é permitido quando seu conjunto fechado está
documentado; wildcard em runtime é proibido por padrão.

Cada entrada declara descrição, owner, escopos, risco, ações incluídas,
step-up/MFA, conflito de funções e testes. Slug é imutável; mudança semântica cria
novo slug/versão. Permissão obsoleta possui consumidores medidos e prazo.

## Papéis e atribuições

Papéis de sistema são templates versionados. Papel customizado futuro pertence a
um tenant e não pode conceder capacidade platform. Um usuário pode ter múltiplas
atribuições; a decisão calcula união de allows válidos, depois aplica denies,
constraints, módulo, estado e escopo. Ordem não depende da linha retornada.

```text
proposed → approved → active → suspended → active
                ↘ expired             ↘ revoked
                ↘ rejected
```

Atribuição contém ator, role version, scope, vigência, concedente, aprovação e
motivo. Concedente só delega permissões que possui e pode delegar. Ninguém aprova
a própria elevação sensível. Remoção/rebaixamento invalida cache/sessões conforme
risco e não apaga histórico.

## Algoritmo de decisão

Entrada canônica: actor autenticado, permission slug, resource type/public ID,
tenant/scope confiável e contexto mínimo. Ordem:

1. validar identidade, sessão, ambiente e entrada;
2. resolver tenant/scope pelo recurso no servidor, nunca pelo cliente apenas;
3. carregar policy version e atribuições ativas;
4. negar se capacidade/módulo/recurso não existir ou estiver fora do escopo;
5. aplicar deny explícito e segregação de funções;
6. avaliar allows e constraints de estado/ownership;
7. exigir step-up/MFA quando aplicável;
8. retornar decisão tipada e auditar eventos relevantes.

Recurso inexistente e cross-tenant são indistinguíveis. Falha do repositório,
policy inválida ou versão incompatível retorna deny seguro; nunca usa nível
legado para ampliar acesso. Decisão não revela papel necessário ou outro tenant.

## Overrides, break-glass e contas especiais

Override é exceção, não modelo principal. Possui permission exata, scope, efeito,
motivo, aprovador, início, expiração e ticket/incidente. Deny de segurança não
pode ser neutralizado por allow comum. Break-glass exige conta nominal, MFA forte,
aprovação/alerta, duração curta, justificativa e revisão posterior.

Nenhum papel — inclusive developer — possui bypass invisível. Capacidade platform
é catálogo explícito. O usuário ID 1 continua protegido durante migração e nunca
é alterado por testes; essa proteção operacional não se torna backdoor permanente.

## Segregação de funções e step-up

A matriz inicial deve impedir que o mesmo ator solicite e aprove pagamento,
conceda e aprove privilégio próprio, ou altere e valide trilha/backup. Conflitos
consideram atribuições simultâneas e temporárias. Exceção requer dois aprovadores
quando risco exigir.

Administrador, financeiro e autorizador de pagamentos exigirão MFA; ações como
papel privilegiado, exportação sensível, segredo, pagamento e break-glass exigem
step-up recente. RBAC retorna `step_up_required`, não simula `allow`.

## Cache, concorrência e consistência

Cache inclui actor, tenant/scope, permission, policy version e contexto estável;
TTL sozinho não basta para revogação crítica. Mudança publica invalidação após
commit. Falha de invalidação mantém deny/versão segura e gera alerta.

Comandos de atribuição usam versão/idempotency key. Concorrência não perde deny,
aprovação ou revogação. Autorização ocorre próximo da operação e novamente no
worker consumidor; permissão na criação do job não garante execução futura.

## Segurança, auditoria e multi-tenancy

Auditar assign/revoke/suspend, role/policy change, override, break-glass, negação
sensível, exportação e falha do policy store. Evento contém ator real/efetivo,
scope, permission, target opaco, outcome/reason code, policy version e correlação;
não contém PII, segredo ou payload do recurso.

Toda query de atribuição/permissão inclui scope. Papel homônimo em tenants
distintos não compartilha ID ou concessão. Administrador de condomínio não
administra administradora/plataforma. Contagens, busca, cache e exportação também
respeitam o isolamento.

## UI, API, jobs e integrações

Menu/ação desabilitada usa a mesma matriz para UX, mas não é controle de segurança.
API responde 401 para ausência de autenticação e 403/404 conforme política sem
detalhar grants. Bulk autoriza cada item ou rejeita atomicamente conforme contrato.

Jobs preservam actor/service, tenant, causation e policy version, revalidando no
consumo. Service account recebe capacidades mínimas, sem papel humano ou login
interativo. Webhooks e integrações mapeiam escopos próprios, não um superusuário.

## Estratégia de testes

1. sem atribuição/permissão/recurso válido resulta em deny;
2. múltiplos papéis produzem união determinística e deny prevalece;
3. módulo desabilitado e constraint de estado negam no servidor;
4. resource/tenant vindo do cliente não atravessa escopo;
5. inexistente e cross-tenant geram resposta indistinguível;
6. falha do banco/cache/policy nunca cai em allow legado;
7. assign/revoke concorrente respeita versão e invalida decisão;
8. ator não concede além do delegável nem aprova elevação própria;
9. conflito de funções e step-up/MFA são aplicados;
10. override expira, break-glass alerta e ambos são auditados;
11. UI, API, HTML, job e integração aplicam decisão equivalente;
12. dois tenants não consultam atribuições, cache ou auditoria entre si;
13. testes de matriz cobrem cada role × permission × scope × estado;
14. testes integrados usam ID 2 e nunca modificam ID 1.

## Migração incremental

1. inventariar comparações de `level`, guards e capacidades atuais;
2. publicar catálogo e matriz de papéis com owners;
3. introduzir policy service em modo comparativo com decisões legadas;
4. corrigir divergências e tornar falha deny-by-default;
5. modelar assignments por scope/tenant e versionar cache;
6. migrar uma superfície/fluxo crítico com testes negativos;
7. ativar SoD, step-up e contas de serviço;
8. remover fallback/bypass legado após equivalência observada;
9. renomear tabelas somente pela estratégia de migrations.

Cada etapa usa feature flag e relatório de divergência sem expor dados. Rollback
mantém policy version anterior e nunca restaura grant já revogado automaticamente.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| modelo | RBAC + constraints explícitas | virar ABAC informal | catálogo/review |
| múltiplos papéis | união + deny | conflito inesperado | matriz gerada |
| escopo | assignment contextual | migração do legado | shadow evaluation |
| developer | sem bypass implícito | perder suporte | break-glass testado |
| cache | versionado/invalidation | acesso revogado ativo | teste concorrente |
| custom role | futuro e tenant-scoped | explosão de combinações | limites/ADR |

Antes de implementar: aprovar catálogo, hierarquia de escopos, matriz inicial,
delegação, SoD, step-up, estratégia de cache e migração do `level`. Exceção requer
owner, permission/scope exatos, motivo, aprovação e expiração. Esta Discovery não
autoriza mudar papel, permissão ou acesso de qualquer usuário.
