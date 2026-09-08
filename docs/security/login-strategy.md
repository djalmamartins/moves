# MovesOS — login

Status: proposta de Discovery para revisão. Define contrato funcional e de
segurança; não altera rotas, sessões, usuários, banco ou telas.

## Objetivo e escopo

Login autentica uma identidade, aplica controles de risco e cria uma sessão para
a superfície correta, sem revelar existência da conta ou conceder acesso além do
RBAC. Cobre navegador e contrato futuro de API; cadastro, recuperação, logout,
MFA e sessão revogável têm especificações próprias.

Depende da [política de senha](password-policy.md), do
[rate limiting](authentication-rate-limiting.md), do
[lockout](authentication-lockout.md) e do [RBAC](rbac-strategy.md).

## Baseline e lacunas

`Auth::attempt()` aceita e-mail ou documento, valida senha e nível, consulta o
usuário e retorna mensagens diferentes para identidade inexistente, senha errada
e nível insuficiente. `Auth::login()` registra falha pseudonimizada, persiste o
e-mail opcional em cookie, registra sucesso e regenera a sessão com `authUser`.

Studio/Operation/Helpdesk compartilham controller e checam capacidade depois do
login; portais Web têm implementações duplicadas e mensagens divergentes. O rate
limit atual é por sessão. CSRF existe nas bordas observadas. Não há contrato
comprovado de MFA/step-up, sessão revogável, device/risk ou resposta uniforme.

## Atores e entradas

| Ator | Responsabilidade |
| --- | --- |
| usuário | informar identificador e senha por canal protegido |
| cliente web/API | preservar CSRF, origem, cookies e redirects seguros |
| identidade | normalizar, verificar fatores e emitir resultado tipado |
| risk controls | rate limit, lockout e challenge sem enumerar conta |
| RBAC | autorizar a superfície após autenticação |
| sessão | criar vínculo rotacionado, limitado e revogável |
| auditoria/suporte | investigar por correlação sem acessar segredo |

Entrada canônica: identificador, senha, superfície/audience, opção de lembrar
identificador (não autenticação), CSRF para browser e contexto técnico confiável.
Tenant não é selecionado pelo atacante para influenciar verificação.

## Estados e transições

```text
anonymous → credentials_received → verified → challenge_required → authenticated
                  ↘ rejected          ↘ rejected             ↘ denied_surface
                  ↘ limited/locked    ↘ recovery_required
                  ↘ technical_failure
```

- `verified` confirma o fator primário, mas ainda não cria sessão plena;
- `challenge_required` aplica MFA/step-up conforme papel, risco e superfície;
- `authenticated` só ocorre após sessão persistida/rotacionada com sucesso;
- `denied_surface` não mantém sessão utilizável naquela audience;
- falha técnica não é convertida em credencial inválida para auditoria interna.

Uma tentativa recebe ID/correlation ID e é idempotente apenas para telemetria;
repetir credenciais não reutiliza resultado ou sessão anterior.

## Ordem canônica do fluxo

1. validar método, content type, tamanho, origem/CSRF e configuração;
2. normalizar identificador sem alterar a senha;
3. avaliar rate limiting e proteção global antes de hash custoso;
4. carregar estado de identidade de forma não enumerável;
5. verificar senha com caminho/timing equivalente;
6. avaliar lockout, status da conta, risco e fator adicional;
7. autorizar a audience/superfície por RBAC;
8. regenerar identificador e persistir sessão com metadados mínimos;
9. registrar auditoria/log, atualizar risco e responder/redirect seguro.

Falha em qualquer etapa anterior à sessão não deixa autenticação parcial. Efeito
externo/notificação usa evento/outbox após commit quando necessário.

## Respostas e não enumeração

Identidade inexistente, senha incorreta, conta bloqueada/desativada e ausência de
capacidade retornam mensagem pública genérica quando distingui-las ajudar ataque.
Internamente usam reason codes separados e acesso restrito. Timing, status,
tamanho e headers devem ser equivalentes dentro de tolerância testada.

API futura usa Problem Details; browser usa JSON/HTML consistente. Redirect aceita
apenas destino local allowlisted e nunca query externa. Erro inesperado retorna
incident/correlation ID sem stack, SQL, papel, tenant ou existência da conta.

## Sessão, cookies e superfícies

Após sucesso, session ID é regenerado e o estado anterior não é copiado sem
allowlist. Cookie de autenticação usa Secure fora de local, HttpOnly, SameSite,
path/domain e duração por audience. “Lembrar” guarda no máximo identificador
minimizado; não senha, token de sessão ou autorização persistente.

Studio, Operation, Helpdesk, ERP e portal são audiences explícitas. Autenticar em
uma não concede automaticamente outra; SSO futuro exige contrato próprio. Sessão
carrega actor ID público, assurance level, issued/last-used, policy version e
escopos resolvidos no servidor. Troca de tenant reautoriza, não troca identidade.

## MFA, risco e contas privilegiadas

Administrador, financeiro e autorizador de pagamentos exigem MFA inicialmente.
Quando inscrição estiver pendente, o estado e caminho seguro são definidos pela
Issue de MFA; não há bypass silencioso. Dispositivo/rede novos, recuperação recente
e ação crítica podem exigir challenge adicional.

Risk score é explicável por códigos e não usa atributo protegido ou dado sem
finalidade. CAPTCHA não substitui fator, rate limit ou lockout. Conta técnica não
faz login interativo; service accounts usam credencial/escopo próprios.

## Segurança, auditoria e multi-tenancy

TLS é obrigatório fora de local. Senha não passa por sanitização que a modifique,
nem aparece em log, trace, analytics, URL ou auditoria. Headers de proxy/origem só
são aceitos de infraestrutura confiável. CSRF e fixation são testados.

Auditar sucesso, falha relevante/agregada, challenge, denied surface, degradação
e decisão privilegiada com actor pseudônimo quando não autenticado, audience,
outcome/reason code, policy version e correlação. Um tenant não observa tentativas
ou sessões de outro. A identidade/tenant vêm do servidor após verificação.

## Concorrência, falhas e abuso

Criação de sessão e atualização de risco são consistentes; resposta perdida não
cria cadeia ilimitada de sessões. Limiter/lockout/store indisponível seguem suas
matrizes de fallback e nunca ampliam privilégio silenciosamente. Banco ou sessão
indisponível retorna falha técnica segura, não “senha inválida”.

Hashing tem limite de concorrência. Ataque distribuído, NAT e troca de cookies
são cobertos por buckets combinados. Repetição de submit no browser desabilita UI
sem depender disso como controle.

## Métricas e alertas

- tentativas e resultados seguros por audience/ambiente/policy version;
- latência total e de verificação, sucesso e erro técnico;
- limited, locked, challenge e denied surface;
- sessões criadas, falhas de persistência e regeneração;
- rehash, fallback e capacidade de hashing;
- padrões agregados de enumeração/credential stuffing.

Labels nunca incluem e-mail, documento, senha, actor/tenant irrestrito ou reason
que revele conta. Alertas possuem owner/runbook e distinguem abuso de regressão.

## Estratégia de testes

1. fluxo válido cria uma única sessão regenerada e redirect permitido;
2. mensagens/status/timing não enumeram identidade ou estado;
3. senha Unicode não é alterada por sanitização/normalização indevida;
4. CSRF, fixation, redirect externo e headers não confiáveis são rejeitados;
5. limiter e lockout precedem trabalho caro e resistem à troca de sessão;
6. audience sem RBAC não mantém acesso parcial;
7. MFA obrigatório não possui bypass por rota/superfície alternativa;
8. concorrência/reenvio não cria sessões ilimitadas ou estado divergente;
9. falhas de banco, sessão, audit e risk store seguem contrato seguro;
10. cookies cumprem atributos por ambiente/audience;
11. logs/respostas/traces não contêm senha, PII ou existência;
12. dois tenants/audiences não compartilham autorização indevida;
13. acessibilidade, autofill e gerenciador de senha funcionam;
14. integração usa usuário sintético/ID 2 e nunca modifica ID 1.

## Migração incremental

1. inventariar rotas, formulários, audiences, mensagens e redirects;
2. publicar comando/resultado tipado e adapter para `Auth` atual;
3. uniformizar resposta e telemetria sem mudar sessão;
4. integrar limiter/lockout central em uma superfície;
5. introduzir sessão revogável e assurance level;
6. integrar MFA/step-up para papéis obrigatórios;
7. migrar portais/superfícies mantendo adapters;
8. remover duplicações, níveis e mensagens enumeráveis após cobertura.

Rollout é por audience/feature flag com shadow metrics e rollback. Nenhuma etapa
migra ou invalida usuários em massa sem plano próprio.

## Riscos, exceções e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| identificador | e-mail/documento normalizado | enumeração/PII | testes/redaction |
| resposta | genérica externamente | suporte difícil | códigos internos |
| audiences | autorização explícita | UX fragmentada | mapa de superfícies |
| MFA | obrigatório por risco | enrollment incompleto | spec de MFA |
| sessão | contrato futuro revogável | legado | migração gradual |
| timing | equivalência medida | custo/side-channel | benchmark |

Exceção requer owner, audience, motivo, compensação, aprovação e expiração;
bypass de MFA, segredo em telemetria e sessão antes de autorização não são aceitos.
Antes de implementar, aprovar mapa de audiences, respostas, sessão, MFA, risk
fallback e Issues executáveis. Esta Discovery não concede nem revoga acesso.
