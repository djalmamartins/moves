# MovesOS — sessões revogáveis

Status: proposta de Discovery para revisão. Define contrato e testes; não altera
cookies, sessões, banco, usuários ou acessos em produção.

## Objetivo e princípios

Cada login cria uma sessão identificável e revogável no servidor. A aplicação
deve encerrar uma sessão individual ou todas as sessões elegíveis de um ator sem
depender apenas da expiração do cookie.

1. cookie contém identificador opaco, nunca identidade ou autorização;
2. estado e revogação são verificados no servidor;
3. session ID gira no login, elevação e eventos de risco;
4. autorização e tenant são reavaliados, não congelados indefinidamente;
5. revogação é atômica, auditável, idempotente e rapidamente efetiva;
6. falha do session store não amplia acesso;
7. contas privilegiadas exigem MFA/step-up e controles mais estritos.

Depende do [login](login-strategy.md), do [RBAC](rbac-strategy.md), da
[trilha de auditoria](audit-trail-strategy.md) e do [threat model](threat-model.md).

## Baseline e lacunas

`Source\Core\Session` inicia sessão PHP com cookie de sessão, HttpOnly, SameSite
Lax e Secure conforme HTTPS/proxy; login regenera o ID e grava `authUser`.
`Auth::logout()` apenas remove `authUser`/`authCondo`. `AppSession` consulta a
tabela `app_session`, usada por partes do ERP, mas não há vínculo comprovado entre
o ID PHP e um registro revogável verificado em toda requisição.

Não há contrato canônico para status, expiração absoluta/inatividade, device,
audience, MFA assurance, rotação, revogação global, cache ou invalidação. Apagar
variáveis locais não invalida necessariamente cópias roubadas em outro cliente.

## Modelo e atores

| Conceito | Regra |
| --- | --- |
| session | autorização contínua emitida após autenticação |
| session ID | segredo aleatório opaco no cookie, armazenado como digest |
| actor | usuário público/opaco autenticado |
| audience | Studio, Operation, Helpdesk, ERP, portal ou API |
| assurance | fatores e recência da autenticação |
| device label | descrição opcional minimizada, nunca fingerprint invasivo |
| policy version | regras de duração e risco aplicadas |

Usuário consulta/revoga próprias sessões; suporte só atua com capacidade e motivo;
segurança revoga por incidente; sistema expira/rotaciona de forma auditável.

## Estados e transições

```text
pending → active → idle → active
             ↘ rotating → active
             ↘ step_up_required → active
             ↘ revoked
             ↘ expired
             ↘ compromised → revoked
```

`pending` nunca autoriza negócio. `active` exige cookie válido e registro ativo.
`idle` é estado lógico anterior ao timeout. `rotating` troca segredo sem manter o
anterior válido além da janela técnica mínima. Estados terminais não reativam;
novo login cria nova sessão.

Registro mínimo: session public ID, actor ID, audience, digest do segredo, status,
issued/last-seen/absolute-expiry/revoked timestamps, assurance, policy version,
created/revoked reason, correlation ID e versão de concorrência. Tenant atual é
contexto reautorizado, não propriedade que permita acesso permanente.

## Criação, uso e rotação

Login verificado e autorizado cria registro e cookie em unidade consistente. A
resposta só é autenticada após persistência. ID usa CSPRNG com entropia adequada;
somente digest é persistido. Cookie define Secure fora de local, HttpOnly,
SameSite, path/domain, prefixo e duração por audience.

Cada requisição valida status, prazos, actor/account, audience e policy version;
RBAC valida a ação/recurso. `last_seen` é atualizado com write coalescing para não
gravar a cada request. Rotacionar no login, step-up, mudança de privilégio e sinal
de risco; concorrência aceita no máximo janela curta e controlada do ID anterior.

## Expiração e limites

Sessões possuem timeout de inatividade e expiração absoluta. Valores ficam `TBD`
por audience/risco após baseline e UX; “lembrar-me” não remove o limite absoluto.
Contas administrativas/financeiras têm duração menor e assurance maior. Mudança
de horário do cliente não afeta cálculo, que usa relógio do servidor.

Limite de sessões simultâneas é política explícita. Ao exceder, a UX permite
revisar/revogar ou aplica regra documentada; nunca remove silenciosamente uma
sessão sem evento. Expiração é verificada no acesso e limpa por job idempotente.

## Revogação

Comandos: revogar atual, uma sessão própria, todas as outras, todas do ator,
audience específica ou conjunto por incidente/policy version. Entrada usa session
public ID e versão, nunca digest/cookie. Comando repetido retorna sucesso seguro.

Revogação marca estado antes de emitir eventos. Cache recebe invalidação após
commit; request seguinte deve negar dentro de objetivo mensurável. Para ação
crítica, falha de invalidação força consulta consistente ou deny. Logout é uma
revogação da sessão atual, não apenas remoção local.

Mudança/reset de senha, remoção de MFA, rebaixamento de papel, desativação e
incidente definem matriz de sessões afetadas. Falha de notificação não desfaz a
revogação. Jobs em andamento revalidam autorização antes de efeitos sensíveis.

## Lista e experiência do usuário

A tela mostra sessões próprias com device label seguro, audience, criação, última
atividade aproximada e indicação “esta sessão”. IP/localização exatos não são
exibidos sem finalidade/consentimento. Sessões terminais podem aparecer por curto
período de segurança, separadas da auditoria.

Revogar outra sessão exige autenticação recente conforme risco; revogar a atual
termina com redirect local ao login. Respostas não confirmam sessão de outro ator
ou tenant. Ações em lote confirmam escopo e resultado parcial/atômico documentado.

## Segurança, auditoria e multi-tenancy

Cookie/segredo/digest, CSRF e tokens não entram em log, trace, analytics ou
auditoria. Proteções incluem fixation, replay, roubo, session puzzling, XSS/CSRF,
cache compartilhado e host/proxy não confiável. IDs em URL são proibidos.

Auditar criação, rotação relevante, revogação, expiração agregada, consulta
privilegiada, falha do store e mudança de policy com actor real/efetivo, target
opaco, audience, outcome/reason, policy version e correlação.

Sessão pode acessar múltiplos tenants somente conforme vínculos atuais. Troca de
tenant reautoriza e registra contexto; não duplica grant no cookie. Operador de um
tenant não lista/revoga sessões globais de outro usuário sem capacidade platform.

## Concorrência e indisponibilidade

Criação, rotação e revogação usam transação/versionamento. Request concorrente à
revogação não inicia nova operação sensível após o commit. Cache é chaveado por
session ID + version; TTL não é o único mecanismo de revogação.

Store indisponível falha fechado para sessão autenticada, com página/resposta
segura e alerta. Não há fallback para `authUser` local que amplie acesso. Cleanup,
last-seen e telemetria podem degradar sem impedir request apenas se status e
autorização forem confirmados por fonte confiável.

## Métricas e alertas

- sessões criadas, ativas, expiradas e revogadas por audience/policy;
- latência/erro do store e atraso de invalidação;
- rotações, replays e uso de ID terminal;
- sessões simultâneas e idade sem actor/tenant como label irrestrita;
- revogações por incidente, senha, MFA e papel;
- falhas de cookie/CSRF e jobs de cleanup.

Alertas têm owner/runbook e distinguem abuso, indisponibilidade e regressão.

## Estratégia de testes

1. login cria registro/digest e cookie seguro, sem segredo persistido;
2. fixation é impedida e ID gira no login/step-up;
3. inactivity/absolute expiry usam relógio controlado;
4. revogar atual/uma/outras/todas/audience é idempotente;
5. request concorrente após revogação não executa ação sensível;
6. cache invalida por versão e não aceita estado terminal;
7. store indisponível não cai em sessão local permissiva;
8. troca de senha/MFA/papel aplica matriz correta;
9. audience e tenant são reautorizados a cada uso relevante;
10. sessão de outro ator/tenant não é listada nem revogada;
11. logs/auditoria/UI não vazam cookie, digest, PII ou localização indevida;
12. rotação tolera concorrência controlada sem replay permanente;
13. cleanup é idempotente e preserva auditoria/retenção;
14. integração usa usuário ID 2 e nunca altera ID 1.

## Migração incremental

1. inventariar cookies, sessões PHP, `app_session`, audiences e logout;
2. definir schema/policy e adapter que espelha sessões em observe-only;
3. criar store central e validar sem bloquear requests;
4. exigir registro ativo em uma audience não privilegiada;
5. implementar revogação atual/listagem e testar invalidação;
6. integrar senha, MFA, RBAC, risco e revogação global;
7. migrar demais superfícies e jobs;
8. remover `authUser` permissivo/legado após equivalência observada.

Feature flag por audience e policy version permite rollback; rollback nunca
reativa sessão revogada. Migrations seguem expand/backfill/contract.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| storage | server-side abstrato | dependência crítica | PoC/fault test |
| duração | TBD por audience | UX/risco | baseline + security |
| cookie | por audience | SSO fragmentado | mapa de superfícies |
| cache | versão + invalidação | janela de acesso | teste concorrente |
| device | label minimizada | fingerprinting | privacy review |
| simultâneas | policy explícita | logout inesperado | UX/recovery |

Antes de implementar: aprovar schema, audiences, durações, matriz de revogação,
consistência/cache, MFA e migração do legado. Exceção exige owner, escopo, motivo,
compensação e expiração. Esta Discovery não cria, revoga ou lista sessões reais.
