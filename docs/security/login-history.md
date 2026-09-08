# MovesOS — histórico de login

Status: proposta de Discovery para revisão. Define eventos, projeções e testes;
não cria tabela, coleta novos dados ou altera login, auditoria ou telas.

## Objetivo e distinções

O histórico permite ao usuário reconhecer acessos recentes e à segurança investigar
autenticações, sem expor detalhes que facilitem enumeração, fingerprinting ou
acesso cross-tenant.

- auditoria é a evidência append-only da ação;
- histórico é projeção segura e orientada ao usuário/operação;
- log técnico explica falhas e pode ter retenção/acesso diferentes;
- sessão ativa representa autorização atual, não evento histórico.

Depende do [login](login-strategy.md), da [trilha de auditoria](audit-trail-strategy.md),
das [sessões revogáveis](revocable-sessions.md) e da
[observabilidade](../architecture/observability-strategy.md).

## Baseline e lacunas

Login bem-sucedido chama `AppLog::register()`, hoje encaminhado ao `AppLogger` como
`user_activity`; falhas geram `authentication_failed` com hash simples da
identidade. Studio agrega `app_session` para último acesso/quantidade e possui
painel técnico de `app_log`. `report_access` mede visitas de página, não login.

Não há catálogo canônico de eventos, projeção por usuário, audience, assurance,
device seguro, resultado, origem aproximada, retenção ou confirmação de
integridade. O hash simples de identidade não é pseudônimo resistente a dicionário.

## Atores e visões

| Ator | Pode consultar |
| --- | --- |
| usuário | próprio histórico seguro e sessões relacionadas |
| administrador de tenant | eventos permitidos no vínculo/escopo do tenant |
| suporte | resumo mínimo associado a caso autorizado |
| segurança | eventos detalhados conforme capacidade e finalidade |
| sistema | projeção, retenção, detecção e notificação |
| auditor | evidência original e verificações de integridade |

Visão de usuário nunca vira acesso ao log técnico. Visão administrativa exige
filtro de escopo e sua própria auditoria.

## Catálogo mínimo de eventos

| Evento | Resultado/uso |
| --- | --- |
| `identity.login.succeeded` | sessão criada e audience autorizada |
| `identity.login.failed` | tentativa rejeitada, agregável |
| `identity.login.limited` | rate limiter impediu verificação |
| `identity.login.locked` | política de lockout aplicada |
| `identity.login.challenge_required` | MFA/step-up solicitado |
| `identity.login.challenge_failed` | fator adicional rejeitado |
| `identity.login.denied_surface` | identidade válida sem audience |
| `identity.session.revoked` | acesso posterior encerrado |
| `identity.logout.succeeded` | sessão atual revogada voluntariamente |

Nomes são fatos estáveis. Falha técnica é evento separado de senha inválida; não
é exibida com detalhes ao usuário. Evento duplicado usa event/idempotency ID.

## Envelope e minimização

Campos: event public ID, occurred/recorded at UTC, actor public ID quando conhecido,
identity pseudonym versionado quando anônimo, audience, outcome/reason code,
session public ID opcional, assurance level, policy/release version,
correlation/causation IDs e source summary classificado.

Source summary pode conter categoria de dispositivo/browser e localização
aproximada derivada com finalidade; IP bruto, user-agent integral e geolocalização
precisa ficam fora da projeção comum. Senha, hash, token, cookie, MFA response,
e-mail e documento são proibidos. Identidade anônima usa HMAC server-side
versionado, não SHA simples reutilizável.

## Estados de ingestão e projeção

```text
captured → committed → projected → visible → expired → disposed
              ↘ projection_failed → retry → projected
              ↘ security_hold → released
```

Evento de sucesso é confirmado somente após sessão criada. Auditoria/evento entra
na unidade de consistência/outbox do login; falha de projeção não desfaz login,
mas gera backlog/alerta. Projeção é reconstruível e não edita evidência.

Ordenação usa `occurred_at + event_id`; paginação por cursor. Eventos atrasados são
inseridos na posição temporal sem duplicar notificação. Correção cria novo evento,
nunca altera o original silenciosamente.

## Experiência do usuário

Lista mostra data/hora no fuso preferido, audience, resultado público, device
aproximado, localização ampla quando aprovada e indicador de sessão atual. Oferece
“não reconheço este acesso”, levando a fluxo de segurança: revogar sessões,
alterar senha e revisar MFA, sem prometer comprometimento.

Falhas podem ser agrupadas por janela para não revelar contagem/estratégia exata.
Conta inexistente não recebe histórico. Notificações de novo acesso são
configuráveis conforme risco, deduplicadas e nunca contêm link autenticador.

## Consulta operacional e investigação

Filtros autorizados: período limitado, actor/identity pseudonym, audience,
outcome, assurance, correlation, session public ID e incident/case ID. Busca livre
não consulta segredo/PII redigida. Exportação exige capacidade, motivo, limite,
expiração e auditoria.

Investigação liga evento a sessão, revogação, release e incidentes sem copiar stack
ou payload. Anotações do analista são registros separados. Contagem agregada
aplica limiar para impedir inferência de outro tenant.

## Segurança, auditoria e multi-tenancy

Actor/tenant vêm do contexto confiável. Antes da autenticação, o evento é
platform-scoped e pseudonimizado; tenant informado pelo cliente não decide
visibilidade. Após login, membership/audience determinam projeções autorizadas.

Consultar, exportar, marcar “não reconheço”, reter e descartar histórico são ações
auditáveis. Usuário não apaga evidência; pode exercer direitos de privacidade via
processo que respeita obrigação legal/hold. Admin de condomínio não vê atividade
global nem login em outro tenant/audience.

## Retenção, integridade e falhas

Retenção varia entre projeção do usuário, auditoria e log técnico e fica `TBD`
após privacy/legal review. O usuário vê janela declarada; ausência antiga não
significa que nunca houve acesso. Legal hold atua na evidência, não prolonga
exposição na UI automaticamente.

Outbox/projeção usam retry, backoff e dead-letter observáveis. Perda, atraso,
duplicidade, gap de sequência ou quebra de integridade geram reconciliação. Falha
da tela não revela log bruto como fallback.

## Métricas e alertas

- eventos por tipo/outcome/audience/policy version;
- atraso, backlog, retry, duplicata e dead-letter da projeção;
- histórico consultado, exportado e “não reconhecido”;
- falhas/lockouts agregados e novo device aproximado;
- integridade, retenção e descarte;
- actor, identity, session e tenant não são labels irrestritas.

Alertas possuem owner/runbook e distinguem ataque, pipeline quebrado e regressão.

## Estratégia de testes

1. sucesso só é registrado após sessão confirmada;
2. falha, limit, lockout, challenge e denied surface usam eventos distintos;
3. retry/event ID não duplica projeção ou notificação;
4. ordem/cursor permanecem estáveis com evento atrasado;
5. usuário vê somente eventos próprios e campos minimizados;
6. admin/suporte respeitam tenant, capacidade e case;
7. conta inexistente e cross-tenant não são inferíveis;
8. senha, hash, token, IP/UA bruto e MFA response não aparecem;
9. HMAC/version rotation preserva política sem pseudônimo reversível;
10. “não reconheço” dispara fluxo/revogação idempotente e auditado;
11. falha/outbox/dead-letter reconcilia sem expor log bruto;
12. retenção/hold descartam apenas projeção/evidência elegível;
13. acessibilidade, fuso e localização aproximada são corretos;
14. integração usa ID 2 e nunca altera histórico/sessão do ID 1.

## Migração incremental

1. inventariar `AppLog`, `app_log`, `app_session` e produtores de autenticação;
2. aprovar catálogo/envelope, privacy e retenção;
3. emitir eventos canônicos em shadow mode junto ao legado;
4. construir projeção do próprio usuário com dados sintéticos;
5. reconciliar contagens e gaps antes de liberar UI;
6. integrar sessões/revogação e “não reconheço”;
7. adicionar visão operacional/exportação com auditoria;
8. remover eventos/mensagens legados após equivalência observada.

Rollout por audience/feature flag é reversível; rollback preserva evidência e não
reintroduz PII já removida.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| fonte | evento/auditoria, projeção separada | duplicidade | reconciliação |
| origem | aproximada/minimizada | pouca precisão | privacy review |
| falhas | agrupáveis | ocultar ataque | UX/security review |
| retenção | TBD por finalidade | excesso ou lacuna | legal/privacy |
| pseudônimo | HMAC versionado | rotação | ADR de chaves |
| notificação | por risco | fadiga | baseline/preferências |

Antes de implementar: aprovar catálogo, envelope, origem/device, retenção,
visibilidade, notificações e Issues pequenas. Exceção exige owner, finalidade,
campos, prazo e auditoria. Esta Discovery não coleta nem expõe novos dados.
