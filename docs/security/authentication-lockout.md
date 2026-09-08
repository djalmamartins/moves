# MovesOS — bloqueio após tentativas de autenticação

Status: proposta de Discovery para revisão. Define regras, estados e testes; não
bloqueia contas, altera banco, sessão, controllers ou políticas em produção.

## Objetivo e distinções

Conter ataques persistentes contra uma identidade com recuperação segura, sem
permitir que um atacante cause bloqueio permanente de uma vítima. O controle
complementa o [rate limiting](authentication-rate-limiting.md): rate limiting
protege capacidade e frequência; lockout representa risco acumulado da conta.

Depende também da [trilha de auditoria](audit-trail-strategy.md), do
[threat model](threat-model.md) e da
[observabilidade](../architecture/observability-strategy.md).

## Baseline e lacunas

`Auth::attempt()` valida identidade, senha e nível, mas não mantém estado canônico
de falhas ou bloqueio. `request_limit()` usa apenas sessão PHP e não acompanha a
conta entre navegadores/instâncias. Falhas geram log pseudonimizado; logins bem
sucedidos entram em `AppLog`. Mensagens diferentes ainda podem revelar se a conta
existe. Não foram comprovados unlock, cooldown progressivo, notificação segura,
revisão de suporte ou revogação de sessões ligada ao risco.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| usuário | autenticar e recuperar acesso por canal seguro |
| identidade | avaliar risco e transicionar estado atomicamente |
| rate limiter | conter volume antes da verificação custosa |
| suporte autorizado | investigar/liberar sem conhecer a senha |
| segurança | regras, alertas e resposta a ataque coordenado |
| notificações | avisar sem revelar segredo ou oferecer link inseguro |
| auditoria | preservar tentativas e decisões relevantes |

## Estados da conta para autenticação

```text
active → elevated_risk → temporarily_locked → cooling_down → active
   ↑           ↘ challenged → active              ↘ temporarily_locked
   └──────────── manually_recovered ← security_review
administratively_disabled (estado externo, não desbloqueado por tempo)
```

- `active`: autenticação normal sujeita ao rate limiter;
- `elevated_risk`: falhas/padrões elevaram risco, sem revelar isso ao cliente;
- `challenged`: exige fator ou recuperação já aprovado;
- `temporarily_locked`: credencial primária não pode criar sessão;
- `cooling_down`: acesso pode exigir passo adicional e monitoramento;
- `security_review`: evidência exige intervenção autorizada;
- `administratively_disabled`: suspensão de negócio/segurança separada.

Expiração de lockout não reativa conta desativada, excluída ou sem permissão. O
estado possui versão para concorrência, `reason_code`, início, expiração, política
aplicada e próxima ação; texto livre não decide autorização.

## Regras de decisão

Falhas elegíveis são credencial inválida e desafios de autenticação inválidos.
Erros técnicos, timeout, CSRF, validação malformada e negação de autorização não
incrementam lockout como senha incorreta. Sinais podem considerar origem,
dispositivo confiável, velocidade, distribuição e histórico, sem armazenar senha.

Limiares, janelas e progressão ficam `TBD` até baseline/threat review. O modelo
preferido é atraso/lockout temporário progressivo com teto e recuperação, não
bloqueio permanente automático. Sucesso reduz risco conforme política, mas não
apaga auditoria nem incidentes. Ataque distribuído aciona proteção sem tornar a
identidade um alvo fácil de DoS.

Avaliação e transição são atômicas e idempotentes por tentativa. Eventos repetidos
com o mesmo ID não contam duas vezes. Relógio vem do servidor; cliente não informa
expiração, estado ou quantidade de falhas.

## Respostas não enumeráveis

Conta inexistente, senha inválida, conta temporariamente bloqueada e conta
desativada retornam resposta pública equivalente, com timing aproximado e ação
segura. O cliente não recebe contador, motivo, existência, papel ou instante exato
quando isso ampliar ataque. API usa código externo genérico; UI oferece
recuperação sem confirmar cadastro.

Quando permitido, `Retry-After` representa controle de frequência, não prova de
lockout da identidade. Notificação de segurança usa canal previamente verificado,
texto neutro, correlation ID e instrução para recuperação; nunca envia senha,
token longo ou dados de origem desnecessários.

## Desbloqueio e recuperação

Formas possíveis, escolhidas por risco e papel:

1. expiração do lockout temporário;
2. recuperação de senha concluída e sessões revogadas conforme política;
3. MFA/step-up válido para identidade previamente inscrita;
4. liberação por suporte com capacidade, verificação, motivo e auditoria;
5. revisão de segurança para conta privilegiada ou ataque persistente.

Administrador, financeiro e autorizador de pagamentos exigirão MFA conforme a
Issue de MFA; até essa especificação ser aprovada, este documento não cria bypass.
Suporte nunca define senha, remove MFA sozinho ou desbloqueia ID 1 em teste.

## Sessões e efeitos relacionados

Falha de login não revoga sessão válida automaticamente, evitando DoS remoto.
Comprometimento provável, recuperação concluída ou decisão de segurança pode
revogar sessões/tokens por comando separado, auditável e idempotente. Lockout não
cancela jobs de negócio nem troca tenant corrente de sessões existentes sem regra.

Notificações e eventos são publicados por outbox após a transição; falha de envio
não desfaz lockout. Reenvio não duplica alertas ao usuário. Mudança manual exige
reason e não edita eventos anteriores.

## Segurança, auditoria e multi-tenancy

Auditar: entrada/saída de estado, challenge, recuperação, liberação manual,
mudança de política, tentativa privilegiada e falha de persistência. Tentativas
comuns podem ser agregadas; transições e ações administrativas não são amostradas.

Eventos usam actor técnico/usuário, alvo pseudonimizado, outcome, policy version,
correlation/causation IDs e tenant apenas de contexto confiável. Senha, token,
body, identidade direta, IP bruto sem finalidade e resposta de MFA são proibidos.

Estado pertence à identidade global ou vínculo tenant conforme o modelo aprovado;
uma tentativa em tenant fornecido pelo atacante não pode bloquear outro vínculo.
Operador de tenant não consulta histórico global nem desbloqueia identidade fora
de sua capacidade. Contas privilegiadas têm escalada e segregação de funções.

## Concorrência, falhas e disponibilidade

Persistência futura requer compare-and-swap/lock curto, TTL para estados temporários
e índice por pseudônimo opaco. Tentativas simultâneas não perdem incremento nem
produzem desbloqueio antecipado. Expiração é avaliada de forma idempotente, sem
depender exclusivamente de cron.

Se o storage de lockout falhar, a matriz de risco decide fallback por papel/ação:
limite local conservador, challenge ou falha fechada para acesso crítico. Nunca
há fail-open silencioso. Degradação gera métrica/alerta por canal independente e
não cria escrita ilimitada.

## Métricas e alertas

- transições por estado, superfície e policy version;
- duração de lockout, recuperação e re-lock;
- falhas elegíveis/não elegíveis e decisões do rate limiter;
- liberação manual, falsos positivos e abandono legítimo;
- contas privilegiadas afetadas e ataques distribuídos agregados;
- conflito/timeout do storage e atraso de eventos/notificações.

Labels evitam identidade/tenant de alta cardinalidade. Alerta possui owner,
runbook, severidade, deduplicação e recuperação; um lockout individual comum não
gera automaticamente incidente crítico.

## Estratégia de testes

1. falhas elegíveis transitam progressivamente; erros técnicos não contam;
2. conta inexistente e estados reais mantêm resposta/timing equivalentes;
3. concorrência, retry e mesmo attempt ID não duplicam contagem;
4. sucesso reduz risco sem apagar auditoria;
5. expiração/cooling usam relógio controlado e não reativam conta desabilitada;
6. ataque distribuído não contorna controle nem bloqueia vítimas indefinidamente;
7. lockout remoto não revoga sessão saudável sem decisão explícita;
8. recuperação/MFA/liberação obedecem autorização e segregação;
9. storage indisponível segue matriz e produz alerta sem fail-open silencioso;
10. evento/notificação falha e reprocessa sem duplicar transição;
11. segredo, PII, contador e existência não vazam em resposta/log/métrica;
12. dois tenants não consultam nem alteram estados entre si;
13. mudança de política preserva versão e estados em andamento;
14. teste integrado usa identidade sintética/ID 2 e nunca modifica ID 1.

## Migração incremental

1. inventariar todas as superfícies e mensagens de autenticação;
2. medir falhas em modo observe-only com pseudonimização aprovada;
3. definir thresholds, vínculo tenant e matriz de fallback em ADR;
4. criar repositório/serviço central com transição atômica;
5. habilitar uma superfície para usuários não privilegiados;
6. validar falso positivo, suporte, notificação e recuperação;
7. integrar MFA, sessão revogável e contas privilegiadas;
8. remover controles locais apenas após equivalência observada.

Feature flag por superfície/papel, rollout gradual e kill switch auditado tornam
a implantação reversível. Rollback não apaga estado ou evidência já persistidos.

## Riscos, exceções e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| limiares | TBD por baseline | falso positivo | observe-only |
| progressão | temporária com teto | ataque prolongado | simulação/threat review |
| identidade | pseudônimo versionado | correlação/rotação | ADR de chaves |
| desbloqueio | múltiplos caminhos seguros | engenharia social | segregação/testes |
| sessão | não revogar por falha isolada | sessão comprometida | sinal de risco explícito |
| storage | atômico e abstrato | indisponibilidade | fault injection |

Exceção requer owner, motivo, escopo, aprovação, expiração e auditoria; allowlist
permanente e desbloqueio invisível são proibidos. Antes de implementar, devem ser
aprovados baseline, limiares, matriz de fallback, vínculo multi-tenant, políticas
de recuperação/MFA/sessão e Issues executáveis pequenas. Esta Discovery não
autoriza alterar banco, bloquear usuário ou mudar mensagens públicas.
