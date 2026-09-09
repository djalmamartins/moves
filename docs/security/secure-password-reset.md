# MovesOS — redefinição segura de senha

Status: proposta de Discovery para revisão. Especifica a etapa autenticada por
token que confirma uma nova senha; não altera código, banco, usuários, sessões,
MFA, filas ou configuração de produção.

## Limite do domínio

[Recuperação de senha](password-recovery.md) cobre solicitação, descoberta segura
da identidade e entrega do link. Esta especificação começa quando o cliente
apresenta um token e termina quando a credencial foi trocada, os efeitos de
segurança foram reconciliados e a confirmação foi registrada.

Princípios:

1. abrir ou validar o formulário não consome o token;
2. somente o commit válido de uma senha aceita consome o token;
3. senha, consumo e versão da credencial mudam atomicamente;
4. replay e concorrência produzem no máximo uma alteração;
5. efeitos posteriores são idempotentes e nunca restauram a senha anterior;
6. o fluxo não remove MFA nem reduz controles de papéis sensíveis;
7. tenant, usuário, papel e redirect não são escolhidos pelo cliente.

Depende da [política de senha](password-policy.md), da
[recuperação](password-recovery.md), da [revogação de sessões](session-revocation.md),
do [rate limiting](authentication-rate-limiting.md), da
[auditoria](audit-trail-strategy.md) e das [notificações](../architecture/notification-strategy.md).

## Baseline observado e lacunas

`Auth::reset()` valida tamanho e confirmação e delega a `PasswordReset::consume()`.
O serviço exige token hexadecimal de 64 caracteres, busca o digest com `FOR UPDATE`,
rejeita vencido/usado/revogado, atualiza `users.password` e marca `used_at` na mesma
transação. A migration atual registra token, validade, IP e timestamps.

Lacunas para o contrato futuro: o lookup ainda combina e-mail fornecido com token;
não há purpose/credential version, revogação de sessões, MFA/step-up, evento/outbox,
confirmação de segurança, catálogo de reason codes, proteção CSRF explícita nesta
camada, política distribuída de tentativas, retenção, reconciliação nem garantia
documentada entre todas as superfícies. Mensagens públicas distinguem alguns erros.

## Atores, entradas e saídas

| Ator | Responsabilidade |
| --- | --- |
| portador do token | apresentar token e senha nova sem escolher o alvo |
| serviço de identidade | validar política, estado, purpose e versão |
| repositório | serializar consumo e atualizar credencial atomicamente |
| serviço de sessões | revogar sessões e refresh tokens afetados |
| MFA/risco | exigir step-up para papel ou contexto sensível |
| notificação | confirmar alteração por canal verificado |
| auditoria | registrar resultado opaco e efeitos pendentes |

Entrada aceita: token opaco, nova senha, confirmação, CSRF/nonce de formulário e
contexto de requisição derivado pelo servidor. E-mail, user ID, tenant, role,
`return_url`, algoritmo, validade e flags de revogação fornecidos pelo cliente são
ignorados ou rejeitados.

Saída pública é sucesso genérico ou falha genérica com código de campo apenas para
política/confirmacão quando isso não revela o token. Saída interna inclui reset ID,
credential version, reason code, correlation ID e efeitos pós-commit.

## Estados e transições

```text
reset: presented → validated → committing → password_committed → reconciling → complete
          ↘ rejected   ↘ policy_rejected       ↘ reconciliation_required

token: active → locked_for_update → consumed
          ↘ expired/revoked/superseded

effects: pending → sessions_revoked → lockout_reconciled → notified
                    ↘ retry_wait/reconciliation_required
```

Estados de token são monotônicos. `policy_rejected` não consome token, mas conta
para proteção contra abuso. `password_committed` nunca retorna a estado anterior.
Um efeito assíncrono falho permanece rastreável; retry reutiliza a mesma chave.

## Validação e política da nova senha

- validar formato e tamanho do request antes de hashing custoso;
- aplicar a mesma política server-side de cadastro e troca autenticada;
- comparar confirmação após a normalização oficialmente aprovada;
- bloquear senha comprometida e derivada da identidade conforme política;
- não revelar identidade, histórico de senhas ou estado do token na mensagem;
- aceitar gerenciadores de senha, colagem e `autocomplete="new-password"`;
- limitar tentativas antes do hash para proteger CPU e storage.

Senha e confirmação nunca aparecem em URL, sessão persistida, flash, log, trace,
analytics, auditoria ou evento. O formulário usa TLS, CSRF, `no-store`, política de
referrer e não carrega recurso externo que observe o token.

## Comando e atomicidade

O comando canônico recebe `token`, `new_password`, `request_id`, `correlation_id`
e contexto confiável. Dentro de uma única transação:

1. localizar o digest e bloquear a linha ou executar compare-and-swap;
2. validar purpose, validade, estado e credential version;
3. validar que o usuário/credencial continua elegível;
4. criar o novo hash com parâmetros atuais e salt da biblioteca;
5. atualizar hash e incrementar credential version;
6. marcar token como consumido com timestamp e reset ID;
7. inserir evento/outbox e registro de auditoria sem segredo;
8. confirmar somente se todas as escritas obrigatórias tiverem sucesso.

Dois workers concorrentes obtêm um único vencedor. Retry após resposta perdida
consulta o reset ID/idempotency key e retorna resultado seguro sem re-hash ou novo
evento. Deadlock/timeout faz rollback integral e pode ser repetido dentro da
validade, com limite. Banco sem transação/lock adequado não é suportado.

## Efeitos pós-commit

O commit agenda efeitos idempotentes:

- revogar todas as sessões e refresh tokens anteriores à nova credential version;
- encerrar links mágicos, resets e desafios de autenticação ativos;
- reconciliar lockout/cooldown sem excluir evidência de ataque;
- invalidar caches de autorização/credencial relevantes;
- emitir confirmação de segurança em canal verificado;
- registrar métricas e conclusão, ou abrir reconciliação com owner/prazo.

A sessão usada no reset não vira sessão autenticada automaticamente. A política
pode exigir novo login e MFA. Falha de notificação não desfaz a senha; falha de
revogação deixa alerta crítico e mecanismo de reconciliação até o encerramento.

## MFA e ações críticas

Redefinição de senha não desabilita, reconfigura nem substitui MFA. Administrador,
financeiro e usuário autorizador de pagamentos permanecem sujeitos a MFA
obrigatório e step-up. Se o fator também foi perdido, o fluxo para recuperação de
MFA é separado, não usa o mesmo token e requer controles adicionais.

Após reset motivado por risco, ações críticas podem ficar temporariamente
restritas até novo login, MFA e cooldown aprovados. Alterar e-mail, destinatário,
papel ou limites de pagamento dentro deste fluxo é proibido.

## Autorização negativa e multi-tenancy

O token resolve a credencial no servidor; não se combina um token válido com
e-mail/tenant arbitrário. Uma credencial global pode afetar memberships de vários
tenants somente conforme modelo de identidade aprovado. Nenhum operador de tenant
consulta token, hash, reset ou membership de outro tenant.

Devem falhar: trocar user/tenant no request, reutilizar token em outra superfície,
usar token de ativação/convite, resetar conta removida/suspensa fora da policy,
forçar redirect externo, rebaixar MFA, preservar sessão anterior ou executar por
suporte sem capacidade e processo próprios.

## Ameaças e respostas

| Ameaça | Controle exigido |
| --- | --- |
| guessing/credential stuffing | entropia, limite por origem/token/global |
| replay | uso único, credential version e CAS |
| corrida | lock transacional, um vencedor e idempotência |
| enumeração | resposta/timing equivalentes e alvo server-side |
| vazamento no navegador | no-store, referrer policy, sem terceiros/analytics |
| CSRF/fixation | nonce, origem e nova sessão após login |
| host/open redirect | origem e rota allowlisted |
| mailbox comprometida | MFA/step-up e confirmação pós-evento |
| insider/support abuse | RBAC, segregação, aprovação e auditoria |
| cross-tenant | contexto confiável e testes negativos |
| exaustão de hashing | rate limit antes de trabalho caro |

## Auditoria, privacidade e observabilidade

Auditar token apresentado de forma agregada, rejeição relevante, commit, corrida
perdida, revogação, reconciliação, mudança de policy e ação administrativa. Usar
reset/public ID, alvo opaco, actor type, outcome/reason, credential version,
correlation e tenant confiável quando aplicável.

Proibido registrar senha, confirmação, token/digest, e-mail, IP bruto, cookie,
session ID ou payload. Métricas têm cardinalidade limitada: resultados, latência,
policy rejection, replay, conflict, rollback, revocation lag e reconciliation age.
Alertas possuem owner/runbook; reset ID permite investigação autorizada.

## Falhas e exceções

| Falha | Resultado |
| --- | --- |
| token store indisponível | falha segura; nenhuma senha alterada |
| hashing falha/limite excedido | rollback; token permanece conforme policy |
| conflito/deadlock | retry limitado e idempotente |
| evento/outbox obrigatório falha | rollback do commit |
| sessão/worker falha pós-commit | reconciliação crítica; não desfazer senha |
| notificação falha | retry/alerta; não expor no response |
| relógio inconsistente | decisão conservadora e métrica |

Bypass manual de token, senha escolhida pelo suporte, alteração direta em banco e
reativação de token terminal são proibidos. Break-glass requer fluxo separado,
dupla aprovação quando aplicável, expiração, notificação e revisão posterior.

## Estratégia de testes futura

1. token ativo + senha aceita produz um commit e consumo;
2. token malformado, desconhecido, vencido, usado, revogado ou superseded falha;
3. token de outra finalidade/superfície/credential version falha;
4. confirmação/política inválida não consome token nem vaza o alvo;
5. duas submissões concorrentes resultam em um único vencedor;
6. retry após timeout não re-hasheia, duplica evento ou reaplica efeitos;
7. falha em cada passo obrigatório causa rollback integral;
8. senha/token não aparecem em log, trace, URL, sessão ou analytics;
9. CSRF, host injection e redirect externo são rejeitados;
10. user/tenant/e-mail fornecidos pelo cliente não trocam o alvo;
11. sessões e refresh tokens anteriores são revogados após sucesso;
12. falha pós-commit abre reconciliação e retry sem restaurar senha;
13. lockout é reconciliado sem apagar evidência de segurança;
14. papéis sensíveis continuam exigindo MFA e step-up;
15. respostas e timing não distinguem estado de token ou identidade;
16. carga limita hashing e mantém atomicidade multi-instância;
17. testes integrados usam usuário sintético/ID 2 e nunca alteram ID 1.

## Migração e rollout

1. inventariar todas as rotas `reset()` e divergências de mensagem/política;
2. publicar comando tipado e reason codes internos em shadow mode;
3. adicionar purpose, credential version, reset ID e idempotency key;
4. persistir outbox/auditoria dentro da transação;
5. integrar revogação/reconciliação antes de migrar papéis sensíveis;
6. migrar uma superfície por feature flag e comparar resultados;
7. exigir MFA/step-up e restrição de ações críticas conforme policies aprovadas;
8. remover e-mail do lookup e caminhos legados somente após equivalência;
9. aplicar retenção e exercitar rollback/fault injection.

## Decisões, riscos e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| alvo | resolvido só pelo token | migração do link legado | compatibilidade testada |
| atomicidade | senha + token + outbox | suporte do storage | teste concorrente |
| sessão | revogar credenciais anteriores | falha pós-commit | reconciliação pronta |
| MFA | não é alterado pelo reset | usuário sem fator | MFA recovery separado |
| resposta | genérica | UX/suporte | teste de enumeração |
| política | serviço único | bordas divergentes | contract tests |
| efeitos | idempotentes | atraso operacional | SLO/runbook |

Antes da implementação, aprovar modelo de credencial entre tenants, parâmetros de
senha/hash, credential version, matriz de revogação, MFA recovery, restrição de
ações críticas, reason codes, retenção e comportamento de falhas. Abrir Issues
executáveis pequenas por comando, persistence, sessões, notificações e superfície.
Esta Discovery não autoriza alterar senha, sessão, token, MFA ou migration.
