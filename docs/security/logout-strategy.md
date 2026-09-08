# MovesOS — logout

Status: proposta de Discovery para revisão. Define contrato e testes; não encerra
sessões reais nem altera rotas, cookies, banco ou usuários.

## Objetivo e princípios

Logout encerra a sessão selecionada no servidor, limpa credenciais locais e leva
o usuário a destino seguro. Deve ser idempotente, resistente a CSRF e coerente em
todas as audiences.

1. remover estado local não substitui revogação server-side;
2. logout atual não encerra outras sessões sem escolha explícita;
3. sessão já ausente/revogada retorna resultado seguro;
4. ação não depende de resposta do browser para ser efetiva;
5. redirect é local e allowlisted;
6. revogação e falhas relevantes são auditadas sem token/PII;
7. logout de tenant é diferente de logout da identidade.

Depende de [sessões revogáveis](revocable-sessions.md), do
[login](login-strategy.md) e da [trilha de auditoria](audit-trail-strategy.md).

## Baseline e lacunas

`Auth::logout()` remove `authUser` e `authCondo` da sessão PHP;
`logoutCondo()` remove apenas o condomínio. Studio/Operation/Helpdesk redirecionam
ao login da própria base; ERP e App têm handlers próprios e mensagens. Não foi
comprovada revogação de registro server-side, destruição do ID PHP, expiração do
cookie, POST/CSRF uniforme, audience explícita ou invalidação distribuída.

Alguns caminhos também chamam logout quando autorização falha. Esses encerramentos
de segurança precisam de reason code distinto do logout voluntário.

## Atores e comandos

| Ator | Comando permitido |
| --- | --- |
| usuário autenticado | encerrar sessão atual |
| usuário com step-up | encerrar outras/todas as próprias sessões |
| segurança/suporte autorizado | revogar por incidente e escopo |
| sistema | expirar/revogar por conta, política ou risco |
| service account | revogar sua credencial/sessão não interativa própria |

Comandos canônicos: `logout_current`, `logout_other`, `logout_all_others`,
`logout_all` e `leave_tenant_context`. Cada um possui audience, target público,
reason code e correlation/idempotency ID. “Sair do condomínio” só limpa contexto
e reautoriza a próxima seleção; não representa logout.

## Estados e fluxo

```text
active → revocation_requested → revoked → local_cleanup → anonymous
             ↘ already_terminal ────────────────↗
             ↘ failed → retry/reconcile
```

Fluxo atual:

1. aceitar somente método/CSRF/origem definidos para browser;
2. identificar sessão e audience pelo contexto confiável;
3. autorizar alvo/escopo e step-up quando necessário;
4. revogar server-side atomicamente e publicar invalidação;
5. registrar auditoria/evento após commit;
6. expirar cookie e limpar estado local allowlisted;
7. responder sem cache e redirecionar para destino local.

Se a resposta se perder após o commit, retry encontra estado terminal e não cria
erro. Falha do cookie cleanup não reativa a sessão revogada.

## Métodos, CSRF e redirects

Logout iniciado pela UI usa POST com CSRF; GET pode renderizar confirmação, mas
não muda estado. API usa comando autenticado e não depende de CSRF quando utiliza
Authorization fora de cookie. CORS/origin seguem audience.

Redirect pós-logout é derivado de audience e allowlist server-side: login do
Studio, Operation, Helpdesk, ERP ou portal. `return_to` externo, protocol-relative,
host fornecido e javascript URL são rejeitados. Resposta define `Cache-Control:
no-store` e não inclui session ID em URL, body ou Referer.

## Cookies e limpeza local

Expiração repete exatamente name, path, domain, Secure e SameSite do cookie
emitido. Cookies de sessão, CSRF e remember-token são tratados por catálogo; o
cookie que apenas lembra e-mail pode ser preservado se essa foi a escolha do
usuário, mas nunca autentica. Storage local/browser cache não guarda token.

Rotação/destruição do ID PHP ocorre após revogação. Flash de “saída concluída” é
criado em contexto anônimo seguro, sem copiar dados privados da sessão anterior.
Tabs concorrentes recebem 401/redirect na próxima interação.

## Logout individual, global e incidentes

Logout atual requer apenas sessão válida/CSRF. Revogar outras ou todas exige
autenticação recente/step-up conforme risco. `logout_all` inclui ou exclui a atual
de modo explícito; não afeta service accounts sem escopo.

Mudança/reset de senha, remoção de MFA, conta desativada, papel crítico revogado e
incidente usam a matriz de revogação das sessões. Encerramento automático informa
reason code internamente e mensagem pública neutra. Usuário ID 1 nunca é alvo de
testes ou revogação automatizada sem processo break-glass aprovado.

## Falhas, concorrência e idempotência

Dois logouts concorrentes terminam no mesmo estado. Revogação usa versão/compare-
and-swap e invalidation após commit. Request que já iniciou antes da revogação
revalida antes de efeito sensível; jobs revalidam actor/RBAC no consumo.

Session store indisponível falha fechado para novas operações autenticadas. A UI
pode limpar cookie para proteger o dispositivo, mas mostra que a revogação global
não foi confirmada e fornece próxima ação segura. Reprocessamento por outbox não
duplica auditoria/notificação.

## Segurança, auditoria e multi-tenancy

Auditar logout voluntário agregado quando apropriado, revogação de outras/todas,
ação administrativa, incidente, falha/reconciliação e mudança de política. Evento
usa actor real/efetivo, session public ID, audience, tenant-context quando
aplicável, outcome/reason e correlação; nunca cookie, digest, CSRF, senha ou PII.

CSRF de logout é risco de disponibilidade/engenharia social. XSS, fixation,
replay, cache, redirects abertos e service worker são avaliados. Operador de um
tenant não encerra sessão global de outro ator; sair de tenant não remove vínculos
nem troca grants. Contagens e telas respeitam escopo.

## Métricas e alertas

- logout atual/outras/global por audience e outcome;
- latência de revogação e atraso de invalidação;
- retries, estado já terminal e falha de cleanup;
- uso de sessão após revogação e request concorrente negado;
- falha do store/outbox e reconciliação;
- redirects rejeitados e falhas CSRF agregadas.

Labels não incluem sessão, actor ou tenant irrestritos. Alertas têm owner/runbook;
logout voluntário individual não gera incidente.

## Estratégia de testes

1. POST+CSRF válido revoga servidor, expira cookie e redireciona localmente;
2. GET/CSRF ausente/origin inválida não muda estado;
3. repetição e concorrência são idempotentes;
4. logout atual preserva outras sessões e global aplica seleção exata;
5. sessão revogada não funciona em outra aba/cópia do cookie;
6. redirect externo e headers não confiáveis são rejeitados;
7. cookies são expirados com atributos correspondentes;
8. falha após commit não reativa sessão e retry reconcilia;
9. store indisponível não permite operação autenticada silenciosa;
10. leave-tenant não revoga identidade nem cruza escopo;
11. revogar outras/todas exige autorização/step-up;
12. logs, auditoria e respostas não vazam token/PII;
13. audiences e tenants não encerram sessões indevidas;
14. integração usa ID 2 e nunca revoga/altera ID 1.

## Migração incremental

1. inventariar handlers, métodos, cookies, messages e redirects;
2. publicar comando/resultado central e adapters por audience;
3. integrar revogação server-side à sessão atual;
4. migrar logout voluntário de uma superfície com POST+CSRF;
5. adicionar lista e revogação de outras/todas;
6. integrar senha, MFA, RBAC e incidentes;
7. migrar demais superfícies e remover handlers duplicados;
8. retirar remoção local permissiva após equivalência observada.

Feature flag por audience e rollback preservam sessão revogada como terminal.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| método | POST+CSRF | compatibilidade legado | inventário/adapters |
| revogação | server-side primeiro | store indisponível | fault tests |
| audience | redirect explícito | fragmentação | mapa de superfícies |
| global | step-up | recuperação difícil | UX/MFA |
| cleanup | catálogo de cookies | resíduo local | teste por browser |
| tenant | comando separado | confusão de UX | nomenclatura clara |

Antes da implementação, aprovar audiences, catálogo de cookies, matriz de
revogação, step-up, comportamento degradado e Issues executáveis. Exceção exige
owner, motivo, escopo e expiração. Esta Discovery não encerra nenhuma sessão.
