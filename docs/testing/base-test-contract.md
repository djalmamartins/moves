# MovesOS — contrato dos testes base

Status: proposta de Discovery para revisão. Transforma a estratégia geral em um
baseline executável e mensurável para futuras Issues; não muda suíte, dependências,
banco, CI ou código de produção.

## Objetivo e fronteira

Os testes base são o piso comum que toda mudança usa para provar comportamento,
isolamento e ausência de efeito proibido. Eles não substituem os testes específicos
do domínio nem prometem cobertura que o repositório ainda não mede.

Este contrato concretiza a [estratégia de testes](../architecture/test-strategy.md)
e se integra aos [padrões de código](../architecture/code-standards.md), à
[estratégia de migrations](../architecture/migration-strategy.md) e à futura
especificação de integração contínua.

## Baseline comprovado

O repositório possui PHPUnit 11.5, suites `Unit` e `Integration`, runner
`tests/run.php`, preparação isolada em `tests/prepare-environment.php`, bootstrap
de schema em `tests/bootstrap.php` e base compartilhada `tests/TestCase.php`.
Comandos Composer separam suíte completa, unitária e integração.

O preparador exige banco terminado em `_test`. A base de integração limpa tabelas
conhecidas, recria sessão e oferece factory mínima de usuário. A CI atual valida
Composer, sintaxe e `composer test` sobre MariaDB efêmero. Há testes para acesso,
autenticação, conteúdo, operações críticas, notificações, visitas, reset de senha,
service desk, configurações, migrations e helpers.

Lacunas: schema de teste é mantido manualmente e pode divergir das migrations;
`TestCase` usa truncation global, não transações por teste; fixtures ainda são
locais; não há baseline publicado de duração, flakiness, cobertura ou ordem
aleatória; E2E e acessibilidade não pertencem ao gate PHP; serviços externos e
relógio não têm contrato uniforme; skips/warnings não possuem política automática.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| autor | selecionar camadas, provar aceite e executar gates aplicáveis |
| revisor | verificar força, cenários negativos e ausência de falsos positivos |
| owner do domínio | confirmar estados, invariantes e exemplos |
| segurança | exigir autorização negativa, segredo e tenant isolation |
| mantenedor da suíte | fixtures, bootstrap, tempo, flakiness e diagnóstico |
| CI | executar baseline limpo e publicar evidência rastreável |

## Suites e classificação

```text
tests/Unit         regra pura, helper, policy, parser, valor e contrato sem I/O
tests/Integration  banco real de teste, sessão, controllers, adapters e wiring
smoke              processo/HTTP implantado e jornadas críticas selecionadas
contract           schema/evento/provider em fake local ou sandbox separado
```

Um teste fica na menor suíte que prova seu risco. A tag/nome futuro deve declarar
módulo, tipo (`unit`, `integration`, `contract`, `smoke`), risco (`normal`,
`security`, `migration`, `destructive`) e dependências. Classificação não pode
servir para tirar um teste obrigatório do gate.

## Estados do caso e do gate

```text
case: specified → implemented → passing → reviewed → protected
                       ↘ failing      ↘ flaky/quarantined

run: queued → preparing → running → passed
              ↘ infra_failed  ↘ failed/cancelled/timed_out
```

`passed` exige zero failure, error, risky, warning ou skip não aprovado. Falha de
infra não é sucesso nem regressão: possui código, evidência e owner. Quarentena
exige Issue, motivo, owner, expiração e execução visível; não conta como proteção.

## Ambiente e guardas destrutivas

- `MOVESOS_ENV=testing` é obrigatório antes de conexão ou bootstrap;
- database name deve terminar em `_test`, inclusive em subprocesso;
- host/schema/usuário de produção e banco principal são denylist adicional;
- usuário ID 1 nunca é fixture, alvo de update, login destrutivo ou cleanup;
- smoke manual usa ID 2 somente quando explicitamente necessário;
- arquivos, sessão, cache, filas e uploads usam namespace/diretório temporário;
- teardown roda após falha e confirma restauração de FK/configurações;
- segredo ou credencial real não entra em fixture, snapshot, log ou artifact.

Toda operação destrutiva valida ambiente e alvo imediatamente antes da execução.
Fallback para banco principal é proibido. Erro de preparação encerra a suíte antes
do primeiro teste, com mensagem que identifica apenas configuração não sensível.

## Dados, factories e isolamento

Cada teste cria o mínimo de dados e não depende de ordem ou resíduo. Factory tem
defaults válidos, overrides explícitos e IDs retornados, sem depender de ID fixo.
Dados únicos podem usar seed reproduzível; a seed deve aparecer no diagnóstico.

Integração tenant-scoped cria tenant A e B, usuários equivalentes e prova tanto o
efeito permitido quanto a ausência no tenant vizinho. Clock, public ID, fila,
mailer e provider precisam de adapters controláveis. Dinheiro usa decimal exato;
datas declaram timezone; teste concorrente usa barreira, nunca `sleep` arbitrário.

## Matriz mínima de casos

| Tipo de mudança | Casos obrigatórios |
| --- | --- |
| regra/estado | feliz, limites, transição inválida, invariantes |
| persistência | create/read/update, conflito, rollback, constraints |
| controller/API | schema, validação, não autenticado, sem capability, tenant B |
| migration | banco limpo, baseline upgrade, repetição segura, dados preservados |
| job/fila | idempotência, retry, timeout, poison/terminal, observabilidade |
| segurança | abuso, enumeração, segredo ausente, horizontal/vertical deny |
| UI crítica | teclado, foco, loading/empty/error/success e jornada essencial |
| bug | reproduz falha anterior e passa somente com a correção |

Toda negação afirma também que nenhum write, evento, e-mail, arquivo, log sensível
ou mudança de sessão ocorreu. Um `assert status` isolado não prova autorização.

## Contrato do bootstrap

O bootstrap futuro deve:

1. validar ambiente e database antes de abrir operação destrutiva;
2. preparar schema a partir de fonte versionada única ou verificar equivalência;
3. carregar somente configuração de teste explicitamente allowlisted;
4. criar namespaces de sessão/cache/files exclusivos da execução;
5. fornecer clock e geradores substituíveis;
6. registrar versão do schema e commit sem coletar segredo;
7. falhar cedo em pré-condição ausente;
8. limpar recursos em ordem segura e verificar o resultado.

Alterar manualmente `tests/bootstrap.php` sem atualizar a fonte de schema e o teste
de equivalência deve falhar no gate futuro. Migração pendente nunca é ignorada.

## Contrato da TestCase e helpers

A classe base fornece apenas infraestrutura transversal. Helpers de negócio ficam
em builders/factories do módulo para não transformar a base em API global acoplada.
Conexão, clock e tenant precisam ser explícitos. Estado global é restaurado no
`tearDown`, mesmo quando o teste lança exceção.

Factories não executam a ação em teste, não escondem permissões e não criam
privilégio máximo por padrão. Usuário base nasce com capacidade mínima. Helpers
de autenticação diferenciam sessão, token e capability; não contornam middleware.

## Determinismo, concorrência e flakiness

- locale, timezone, clock e seed são definidos por execução;
- ordem aleatória roda periodicamente e publica a seed;
- rede externa é bloqueada no gate comum;
- concorrência usa conexões/processos reais quando a propriedade depender disso;
- retry automático não converte falha em verde;
- teste que falha uma vez entra em diagnóstico, não em lista silenciosa;
- budgets de duração são definidos só depois do baseline medido.

Flaky confirmado recebe Issue e prioridade por risco. A quarentena mantém execução
e relatório; expiração sem correção faz o gate falhar ou exige decisão registrada.

## Segurança e privacidade

Casos base cobrem CSRF, XSS contextual, SQL injection, mass assignment, path
traversal, upload disfarçado, redirect aberto, brute force, enumeração e escalada
de privilégio onde aplicável. Logs/artifacts são examinados para senha, token,
cookie, chave, e-mail pessoal e documento.

Mocks não podem afirmar a mesma autorização que configuraram. Pelo menos um teste
integrado atravessa a policy e consulta real tenant-scoped. Testes de segurança
usam payload sintético inofensivo e nunca chamam pessoa ou serviço real.

## Serviços externos e mensagens

Mailer, webhook, storage remoto, mapas e demais providers usam fake local
versionado. O teste de contrato prova request, assinatura, timeout, retry e
tradução de erros. Sandbox remoto é job separado, usa segredo de teste com menor
privilégio e não transforma indisponibilidade de terceiro em aprovação.

Nenhum teste envia e-mail, SMS, push, WhatsApp ou webhook para destino real. O
fake registra envelope e permite provar deduplicação, redaction e ordem relevante.

## Evidência e diagnóstico

Cada execução publica de forma legível: commit, ambiente, suites, total, duração,
seed, falhas e versão do schema/runtime. Artifacts não contêm `.env`, dumps,
credenciais ou bodies sensíveis. Falha aponta comando reproduzível e camada.

Métricas futuras: duração p50/p95, flakiness, falhas por categoria, casos
quarentenados, tempo de preparação e distribuição por módulo/risco. Cobertura é
sinal por área alterada, não meta global ou substituto de assert forte.

## Critérios negativos do próprio framework

O baseline deve falhar quando:

1. database não termina em `_test` ou aponta para alvo proibido;
2. `MOVESOS_ENV` não é `testing`;
3. usuário ID 1 seria criado, alterado, autenticado destrutivamente ou removido;
4. schema/bootstrap diverge da fonte versionada;
5. teste depende de ordem, rede real, relógio real ou resíduo;
6. skip, warning, risky ou quarantine não possui decisão válida;
7. segredo/PII aparece em output ou artifact;
8. suíte sem testes é reportada como sucesso;
9. cleanup desativa FK ou deixa estado global após término;
10. subprocesso perde os mesmos guardas do runner principal.

## Estratégia de validação futura

1. executar Unit sem banco/rede e provar isolamento;
2. executar Integration duas vezes, inclusive em ordem diferente;
3. apontar propositalmente para banco não `_test` e confirmar fail-fast;
4. simular preparação incompleta e obter diagnóstico acionável;
5. provar tenant A/B e capability negativa em fixture mínima;
6. provocar rollback e verificar ausência de efeito parcial;
7. rodar concorrência/idempotência em duas conexões;
8. simular clock, queue, mailer e provider indisponíveis;
9. procurar segredo/PII nos artifacts gerados;
10. medir baseline de tempo/flakiness sem impor threshold prematuro;
11. repetir em PHP/MariaDB suportados e comparar schema;
12. usar ID 2 apenas em smoke isolado; nunca tocar ID 1.

## Migração incremental

1. inventariar testes, skips, duração, globals e dependências externas;
2. publicar taxonomia e guardas de ambiente como contract tests;
3. extrair schema/factories comuns sem mudar comportamento;
4. introduzir clock, IDs, fila e mailer controláveis por adapter;
5. adicionar tenant A/B e autorização negativa às áreas críticas;
6. medir ordem aleatória, flakiness e duração por suite;
7. integrar evidência limpa à CI e jobs periódicos;
8. criar smoke/E2E mínimos fora do gate unitário;
9. remover duplicação de bootstrap após equivalência comprovada.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| framework | manter PHPUnit 11.5 | upgrade/drift | matriz de compatibilidade |
| banco | MariaDB real `_test` | lentidão/estado | guardas + baseline |
| schema | fonte versionada única futura | migração complexa | parity test |
| limpeza | isolamento antes de paralelizar | duração | repetição/ordem |
| cobertura | por risco, sem percentual global | lacuna invisível | inventário por módulo |
| flaky | Issue e quarentena com prazo | normalização | relatório/gate |
| E2E | poucas jornadas críticas separadas | falsa confiança visual | catálogo revisado |

Antes de implementar: aprovar taxonomia, fonte de schema, política de warning/skip,
guardas destrutivas, estratégia de fixtures, artifacts e baseline de duração. As
lacunas viram Issues pequenas com owner e aceite. Esta Discovery não autoriza
alterar runner, banco, dependência, suíte ou CI.
