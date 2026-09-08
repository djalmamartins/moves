# MovesOS — permissões por tenant

Status: proposta de Discovery para revisão. Especializa RBAC por escopo; não
altera papéis, memberships, usuários, banco ou acessos reais.

## Objetivo e princípios

Uma permissão só é válida quando o ator possui atribuição ativa no tenant/escopo
do recurso. O mesmo usuário pode exercer papéis diferentes em administradoras e
condomínios distintos sem que uma concessão vaze para outro vínculo.

Depende do [RBAC](rbac-strategy.md) e do
[isolamento multi-tenant](multi-tenant-data-isolation.md). Herda deny-by-default,
catálogo de capabilities, contexto confiável, respostas indistinguíveis e
auditoria daquelas estratégias.

## Baseline e lacunas

`Access::can()` consulta papéis/permissions/overrides por usuário, sem tenant,
resource ou membership na assinatura/cache. A query usa `LIMIT 1`, developer
bypassa regras e falha pode cair em `users.level`. Sessões mantêm `authCondo`, e
controllers aplicam corporation/condominium de formas diferentes.

Migrations atuais vinculam papel global ao usuário e distribuem permissões por
role. Não há atribuição temporal por tenant, delegação, role version, escopo de
condomínio/unidade, deny contextual ou invalidação comprovada após troca de tenant.

## Modelo de autorização contextual

```text
Decision = Actor + Membership + Assignment + Permission + ResourceScope
           + Constraints + Assurance + PolicyVersion
```

| Conceito | Regra |
| --- | --- |
| membership | vínculo do actor ao tenant, com status/vigência |
| assignment | role concedido dentro de tenant/subescopo |
| permission | ação estável do catálogo RBAC |
| resource scope | tenant, condomínio, unidade ou recurso resolvido no servidor |
| constraint | ownership, estado, valor, horário ou SoD explicitamente testado |
| assurance | MFA/step-up necessário para ação sensível |
| decision | allow/deny/challenge + reason seguro e policy version |

Identity é global; membership e assignment são contextuais. Papel platform nunca
é criado por administrador de tenant. Papel tenant não concede operação platform.

## Hierarquia e herança

Escopos formam hierarquia explícita: administradora → condomínio → unidade/recurso.
Assignment no tenant pode alcançar descendentes somente quando a role/permission
declara `inherit_down=true`. Assignment de condomínio não sobe para administradora
nem alcança condomínio irmão. Recurso resolve ancestralidade na fonte de verdade.

Herança não atravessa transferência de condomínio automaticamente. Vínculos
históricos preservam validade temporal para auditoria, não acesso atual. Recursos
compartilhados exigem relação explícita multi-scope e policy própria.

## Estados de membership e assignment

```text
invited → active → suspended → active
   ↘ expired     ↘ revoked
proposed → approved → active → expired/revoked
              ↘ rejected
```

Membership inativo nega todas as assignments dependentes. Assignment contém
actor, role/version, tenant, subescopo opcional, vigência, delegante, aprovador,
reason e versão. Revogação é terminal; nova concessão cria novo registro.

Convite não autoriza até identidade/verificação aprovadas. Suspensão de tenant não
desativa identidade global. Datas usam servidor UTC; job de expiração é auxiliar,
pois cada decisão verifica vigência.

## Ordem da decisão

1. autenticar sessão/audience e obter actor/assurance;
2. resolver recurso e tenant/subescopo no servidor;
3. validar contexto ativo e membership vigente;
4. carregar assignments/policy version aplicáveis;
5. negar capability, módulo, ancestry ou estado inválido;
6. aplicar denies, segregação e constraints;
7. combinar allows explícitos/herdados;
8. retornar `step_up_required` quando assurance for insuficiente;
9. auditar decisão relevante e executar caso de uso no mesmo contexto.

Ausência, recurso de outro tenant e membership inválida retornam 404/deny
indistinguível conforme borda. A resposta não revela papel, tenant ou grant faltante.

## Delegação e administração

Delegante precisa `identity.assignment.grant`, possuir a permission delegável no
mesmo escopo e não exceder sua profundidade/validade. Não pode conceder platform,
aprovar a própria elevação, contornar SoD ou criar role com capabilities superiores.

Mudança sensível usa maker-checker, MFA/step-up e justificativa. Admin de
administradora gerencia condomínios conforme catálogo; admin de condomínio não
gerencia tenant/irmãos. Suporte usa case-scoped override temporário; break-glass é
platform, nominal, aprovado, alertado e expirável.

## Overrides e negações

Override contém permission exata, actor, tenant/subescopo, effect, motivo,
aprovador, vigência e ticket. Deny explícito prevalece sobre allow herdado.
Override allow não neutraliza suspensão, ausência de membership, SoD ou policy
platform proibitiva.

Exceções em lote são proibidas sem selector fechado/dry-run. Wildcards e “todas
as permissões” não são permitidos para roles customizadas; templates system são
versionados e revisados.

## Troca de tenant e sessões

Tenant solicitado é validado contra memberships atuais. Troca limpa cache e
estado de formulário, muda contexto de sessão por comando auditado e reavalia
audience/RBAC. Aba antiga recebe conflito/reload ou mantém contexto explicitamente
isolado por request; nunca aplica silenciosamente o tenant da última aba.

Revogar/suspender membership invalida decisões e sessões/scopes afetados dentro de
objetivo mensurável. Sessão pode permanecer para outro tenant, mas request ao
escopo revogado nega imediatamente pela fonte/versão confiável.

## Cache, jobs e integrações

Cache key inclui actor, tenant, subescopo/resource class, permission, membership
version, role/policy version e assurance. Invalidation acompanha commit; falha ou
versão desconhecida produz deny, não fallback para `level`.

Job/evento carrega tenant e causation confiáveis, snapshot mínimo da decisão e
revalida membership/permission antes do efeito. Service account tem assignment
por tenant/finalidade, sem role humano. Integração não recebe superuser compartilhado.

## Segurança, auditoria e privacidade

Auditar membership/assignment/role/override, delegação, tenant switch, deny
sensível, step-up, break-glass, falha do policy store e exportação. Registrar
actor real/efetivo, tenant/scope, permission, target opaco, outcome/reason, policy
version e correlação; não payload, segredo ou PII.

Listas, counts, autocomplete, logs e erros respeitam tenant. Admin não descobre
memberships globais pelo e-mail. Busca de usuário para convite usa fluxo neutro e
consentimento/política, não enumeração de identidades existentes.

## Concorrência e falhas

Assign/revoke/suspend usam versionamento e idempotency key. Comandos concorrentes
não perdem deny, aprovação ou data efetiva. Operação iniciada antes da revogação
revalida antes de efeito sensível; commit já concluído não é desfeito por suposição.

Membership/policy store indisponível, cache divergente, tenant não classificado ou
ancestry inválida falha fechado. Operação administrativa parcial entra em
reconciliação com owner; rollback não reativa assignment revogada.

## Métricas e alertas

- decisões allow/deny/challenge por permission class/reason/policy version;
- memberships/assignments ativos, expirados e revogados agregados;
- invalidação atrasada, cache divergente e uso pós-revogação;
- delegação/SoD/step-up negados e break-glass;
- jobs sem contexto ou revalidação falha;
- tenant, actor, resource e assignment não são labels irrestritas.

## Estratégia de testes

1. mesmo actor tem papéis diferentes em tenants A/B sem vazamento;
2. assignment tenant herda somente a descendentes permitidos;
3. condomínio/unidade não sobe nem alcança irmão;
4. membership ausente/suspensa/expirada nega todos os grants;
5. deny prevalece e override não contorna SoD/platform;
6. delegante não concede além do próprio/delegável nem autoaprova;
7. recurso ausente/cross-tenant tem resposta indistinguível;
8. troca de tenant/abas/cache não reutiliza decisão anterior;
9. revogação concorrente invalida request/job antes de efeito sensível;
10. policy store falho não cai em `level`/developer permissivo;
11. MFA/step-up é exigido por papel, ação e assurance;
12. service account e suporte ficam no scope/finalidade/case;
13. auditoria/UI/export não vazam membership de outro tenant;
14. integração usa dois tenants e ID 2, nunca altera ID 1.

Testes de matriz geram actor × membership × role × permission × scope × state;
testes de arquitetura proíbem `can(permission)` sem contexto em recurso tenant.

## Migração incremental

1. inventariar roles, permissions, overrides, levels e guards por superfície;
2. aprovar catálogo, hierarchy, templates, SoD e delegação;
3. modelar membership/assignment versionados por expand migration;
4. executar decisões novas em shadow mode comparadas ao legado;
5. migrar um tenant/agregado crítico com matriz A/B;
6. habilitar invalidation, jobs e tenant switch;
7. migrar roles/overrides e remover fallback permissivo;
8. contract migration só após equivalência observada.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| herança | explícita por role/permission | grant amplo | matriz/testes |
| múltiplos papéis | união + deny | conflito | SoD/policy |
| custom roles | futuro tenant-scoped | explosão | limites/ADR |
| cache | versionado | janela de revogação | concorrência/SLO |
| multi-tab | contexto por request | UX complexa | protótipo/testes |
| migração | shadow evaluation | divergência | relatório/owner |

Antes de implementar: aprovar hierarchy, catálogo, templates, delegação, SoD,
assurance, cache e migrations. Exceção exige owner, permission/scope, motivo,
aprovação e expiração. Esta Discovery não concede ou remove permissões reais.
