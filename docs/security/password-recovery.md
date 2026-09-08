# MovesOS — recuperação de senha

Status: proposta de Discovery para revisão. Consolida o baseline existente e
define o contrato futuro; não altera usuários, tokens, banco, sessões, e-mail,
MFA ou configuração de produção.

## Objetivo e princípios

Permitir que uma pessoa recupere o acesso sem revelar se a identidade existe,
sem transferir controle a outro tenant e sem transformar e-mail, suporte ou o
próprio fluxo em bypass de autenticação.

1. toda resposta pública é neutra em conteúdo, status e timing observável;
2. token é aleatório, curto, purpose-bound, de uso único e persistido como digest;
3. emissão, validação e consumo são limitados e idempotentes;
4. redefinir a senha e consumir o token formam uma operação atômica;
5. o sucesso revoga sessões e reconcilia bloqueios segundo política explícita;
6. administrador, financeiro e autorizador de pagamentos exigem MFA/step-up;
7. notificações nunca contêm senha nem confirmam existência da conta;
8. suporte pode iniciar, mas nunca escolher a nova senha ou usar o token.

Depende das estratégias de [notificações](../architecture/notification-strategy.md),
[rate limiting](authentication-rate-limiting.md), [política de senha](password-policy.md),
[revogação de sessões](session-revocation.md), [RBAC](rbac-strategy.md),
[auditoria](audit-trail-strategy.md) e [isolamento multi-tenant](multi-tenant-data-isolation.md).

## Baseline observado e lacunas

O código atual gera token aleatório de 256 bits, persiste somente SHA-256 em
`password_reset_tokens`, usa validade de 30 minutos, invalida tokens anteriores,
limita três emissões por usuário ou IP por hora e consome token junto à troca de
senha. A migration `20260901_secure_password_resets.sql` é aditiva; testes cobrem
emissão, expiração, replay e confirmação divergente.

Esse baseline é uma implementação legada endurecida, não o contrato completo.
Ainda faltam equivalência comprovada de timing, rate limiter compartilhado,
idempotência de requisição, vínculo ao tenant/superfície, ciclo de notificação,
revogação transacional de sessões, MFA para papéis de alto risco, auditoria
canônica, concorrência multi-instância, retenção/limpeza e operação assistida.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| usuário | solicitar e concluir a recuperação em dispositivo confiável |
| identidade | normalizar alvo, emitir/consumir token e aplicar políticas |
| notificação | entregar instrução sem expor conta, tenant ou segredo |
| MFA/step-up | confirmar papéis e operações de alto risco |
| suporte | iniciar fluxo autorizado e orientar sem acessar segredo |
| segurança | definir risco, retenção, alertas e resposta a abuso |
| auditoria | registrar outcome e correlação sem PII/token |
| plataforma | garantir relógio, storage, filas e atomicidade |

## Escopo e identificadores

Entradas públicas: identidade normalizada, superfície, locale e contexto de rede
derivado de proxies confiáveis. O cliente não escolhe tenant, user ID, papel,
canal, redirect, validade ou destinatário. Um pedido recebe `request_id` opaco e
uma chave idempotente server-side; a mesma tentativa repetida dentro da janela não
gera tempestade de tokens ou mensagens.

O registro futuro contém public ID, user ID interno, tenant/scope confiável,
purpose, digest e versão do token, timestamps de criação/expiração/consumo,
request/correlation ID, policy version e reason code terminal. IP e identidade
são pseudonimizados conforme retenção aprovada; token bruto nunca é persistido.

## Estados e transições

```text
request: received → evaluated → accepted_generic
                       ↘ suppressed_rate_limit
                       ↘ suppressed_policy

token: issued → delivery_pending → active → validating → consumed
            ↘ delivery_failed      ↘ expired
            ↘ superseded           ↘ revoked

recovery: pending → password_committed → sessions_revoked → notified → complete
                              ↘ reconciliation_required
```

`accepted_generic` é resposta pública, não confirmação de token ou usuário. Um
novo token supersede os anteriores ativos para a mesma credencial/purpose. Estado
terminal é monotônico; correção administrativa cria novo evento, não reabre token.
Falha de e-mail pode deixar token inútil até expirar, mas não o torna consumido.

## Solicitação e resposta neutra

- normalizar a identidade uma única vez pela regra canônica;
- avaliar buckets de origem, identidade pseudônima, par e limite global;
- executar lookup e trabalho falso equivalente para identidades inexistentes;
- devolver sempre texto e status genéricos, sem contagem ou prazo interno;
- não refletir e-mail, tenant, papel ou motivo de supressão;
- manter latência em faixa equivalente sem `sleep` previsível como única defesa;
- permitir nova solicitação legítima sem prolongar cooldown indefinidamente.

Enumeração por body, status, redirect, tamanho, tempo, rate-limit header, e-mail
secundário, log acessível ou atendimento é cenário negativo obrigatório.

## Token, link e validação

Token usa CSPRNG com pelo menos 256 bits, encoding URL-safe e comparação constante
do digest. É vinculado a purpose, versão da credencial e, quando aplicável,
superfície/tenant confiável. Validade inicial observada é 30 minutos, mas permanece
configuração versionada sujeita a threat review.

O link usa origem allowlisted e rota fixa; redirect livre é proibido. Token não vai
para analytics, referer externo, trace, screenshot, log ou query de recurso de
terceiro. A página define política de referrer/caching e não carrega terceiros que
possam observar a URL. Abrir o link não consome o token; somente o commit válido.

Validação rejeita token malformado, desconhecido, vencido, consumido, revogado,
superseded, de purpose divergente ou emitido antes de mudança relevante da
credencial. Todas as rejeições públicas são indistinguíveis.

## Nova senha, concorrência e efeitos

A senha segue a política canônica em servidor e cliente sem divergência. Token,
versão da credencial e estado são verificados novamente dentro da transação. Um
compare-and-swap garante que duas submissões concorrentes produzam no máximo uma
senha ativa e um consumo; replay não aplica nova alteração.

Após o commit:

1. incrementar versão da credencial e registrar evento seguro;
2. revogar sessões, refresh tokens e links de acesso anteriores;
3. reconciliar lockout sem apagar evidência de ataque;
4. cancelar outros tokens de recuperação ativos;
5. emitir confirmação de segurança sem senha ou link reutilizável;
6. sinalizar reconciliação se sessão/notificação assíncrona falhar.

Falha antes do commit preserva senha e token de forma consistente. Falha posterior
não desfaz a nova senha; cria retry idempotente e alerta operacional.

## MFA, papéis sensíveis e suporte

Administrador, financeiro e usuário capaz de autorizar pagamentos devem possuir
MFA obrigatório. Recuperação de senha não remove, troca nem contorna MFA. Para
esses papéis, conclusão exige step-up por fator independente ou processo de
recuperação de MFA separado, com aprovação, cooldown e auditoria reforçada.

Perda simultânea de senha e MFA não é resolvida por pergunta secreta, documento
enviado em canal comum ou ação unilateral do suporte. Break-glass exige Issue e
runbook próprios, capacidade restrita, dupla aprovação quando aplicável, prazo,
notificação e revisão posterior. Suporte nunca vê token, hash ou nova senha.

## Notificações

Pedido válido cria intent idempotente do catálogo de segurança. O recipient é
resolvido pelo servidor a partir de endereço verificado; o cliente não fornece
destino. Mensagem inicial informa instruções e expiração sem confirmar dados além
do próprio canal. Confirmação pós-sucesso alerta sobre a mudança e fornece rota
segura para resposta a incidente, nunca link que reverta sem autenticação.

Retry mantém a mesma delivery e não cria token novo. Bounce, suppression ou fila
indisponível têm reason code e alerta, mas a resposta pública permanece neutra.
In-app não deve revelar recuperação a uma sessão já suspeita como único canal.

## Segurança, auditoria e multi-tenancy

Threats mínimos: enumeração, token guessing, credential stuffing, replay, race,
DoS dirigido, mailbox compromise, host-header/open redirect, referer leak, CSRF,
session fixation, confused deputy, insider/support abuse e cruzamento de tenant.

Auditar solicitação agregada/suprimida, emissão, consumo, rejeição relevante,
revogação, mudança de política, ação de suporte e reconciliação. Eventos usam ator
ou alvo opaco, tenant confiável, outcome/reason, correlation e policy version.
Senha, token/digest, e-mail direto, IP bruto e body são proibidos em logs/traces.

Uma identidade compartilhada entre tenants não permite escolher ou inferir
membership. A recuperação atua sobre a credencial global somente se esse for o
modelo aprovado; efeitos tenant-scoped precisam de comando separado e capacidade.
Operador de tenant não redefine credencial de outro tenant nem consulta pedidos.

## Limites, falhas e observabilidade

Buckets seguem a estratégia de rate limiting e protegem emissão, validação e
hashing. Valores atuais (`3/h` e `30 min`) são baseline, não SLA definitivo.
Storage do limiter degradado usa postura conservadora para operação cara; fila de
e-mail falha sem criar loop. Clock skew, banco indisponível e worker duplicado
geram estado observável e reconciliação.

Métricas: solicitações/decisões seguras, tokens emitidos/consumidos/expirados,
latência, taxa de sucesso, replay, concorrência perdida, fila/bounce, sessões
revogadas e reconciliações pendentes. Não usar e-mail, user ID, tenant irrestrito,
token ou IP como label. Alertas possuem owner, threshold e runbook.

## Estratégia de testes futura

1. existente/inexistente/suprimido têm status, body, headers e timing equivalentes;
2. token possui entropia, digest apenas, purpose e validade corretos;
3. malformado, vencido, usado, revogado e superseded falham igualmente;
4. duas emissões/retries idempotentes não disparam mensagens duplicadas;
5. duas submissões concorrentes alteram senha e consomem token uma vez;
6. falha transacional não deixa senha/token em estado parcial;
7. política de senha é aplicada e segredo não aparece em nenhum artefato;
8. sucesso revoga sessões/tokens e reconcilia lockout sem apagar auditoria;
9. admin, financeiro e autorizador não contornam MFA por recuperação;
10. tenant, surface, host, redirect e recipient fornecidos pelo cliente são rejeitados;
11. CSRF, referer, analytics, cache e logs não vazam token ou identidade;
12. rate limit distribuído resiste a troca de sessão, IP e concorrência;
13. notification retry/bounce não cria token nem confirma existência;
14. ação de suporte sem capacidade/aprovação é negada e auditada;
15. falhas de banco, limiter, relógio, fila e revogação acionam comportamento seguro;
16. testes integrados usam usuário sintético/ID 2 e nunca alteram ID 1.

## Migração incremental

1. mapear todas as rotas, templates e chamadas de reset existentes;
2. medir equivalência de respostas/timing e instrumentar reason codes seguros;
3. adaptar emissão ao limiter e catálogo de notificações em shadow mode;
4. adicionar request idempotency, purpose e versão de credencial;
5. tornar consumo, senha e invalidação atomicamente concorrentes;
6. integrar revogação de sessões e reconciliação observável;
7. ativar step-up/MFA para papéis sensíveis após fluxo de recuperação próprio;
8. migrar superfícies por feature flag e remover caminho legado só após equivalência;
9. executar limpeza/retention e revisar incidentes/falsos positivos.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| validade | 30 min como baseline | janela excessiva/UX | threat + UX review |
| limite | 3/h como baseline | DoS ou abuso | observe-only/calibração |
| identidade | resposta neutra | timing lateral | teste estatístico |
| token | 256 bits + SHA-256 | vazamento no cliente | browser/security review |
| sessão | revogar após sucesso | falha parcial | contrato/reconciliação |
| MFA | obrigatório em papéis sensíveis | recuperação complexa | fluxo MFA aprovado |
| tenant | contexto server-side | ambiguidade de membership | modelo de identidade |
| suporte | inicia, não conclui | abuso interno | RBAC + runbook |

Antes de implementar: aprovar ownership da credencial entre tenants, matriz de
revogação, thresholds, equivalência de timing, catálogo de mensagens, retenção,
MFA recovery e comportamento de falha. Abrir Issues pequenas por adapter, storage,
notificação, sessão e superfície. Esta Discovery não autoriza aplicar migration,
alterar senha/usuário, enviar e-mail ou habilitar MFA em produção.
