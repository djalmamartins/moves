# MovesOS — configuração de ambientes

Status: proposta de Discovery para revisão. Este documento define contratos e
gates; não modifica `.env`, infraestrutura, banco, domínio ou deploy.

## Objetivo e princípios

Cada processo do MovesOS deve saber inequivocamente onde está, quais recursos
pode acessar e quais operações são permitidas. A configuração é validada antes
do boot e nunca deriva segurança apenas do hostname.

1. código e artefato são os mesmos entre ambientes;
2. configuração muda por variável/secret store, não por edição do código;
3. variáveis do processo têm precedência sobre `.env` local;
4. produção falha fechada quando configuração obrigatória está ausente;
5. teste nunca possui fallback para banco ou serviço real;
6. configuração não secreta e estado de negócio permanecem conceitos distintos.

Esta estratégia complementa a [fundação técnica](foundation.md), a
[estratégia de migrations](migration-strategy.md) e o
[threat model](../security/threat-model.md).

## Ambientes suportados

| Ambiente | Finalidade | Dados | Serviços externos | Debug |
| --- | --- | --- | --- | --- |
| `local` | desenvolvimento individual | banco local descartável/controlado | fake/sandbox explícito | permitido sem segredo |
| `testing` | suíte automatizada/CI | banco efêmero com sufixo `_test` | fake local | desativado na saída |
| `preview` | validar PR/jornada | dados sintéticos isolados | sandbox | desativado |
| `staging` | ensaio de release/operação | massa sintética ou anonimizada | sandbox/homologação | desativado |
| `production` | uso real | dados reais | endpoints reais aprovados | sempre desativado |

`preview` e `staging` são contratos-alvo; não são declarados provisionados por
este documento. `development` não vira alias silencioso: aliases futuros devem
ser normalizados e registrados antes da validação.

## Baseline observado

Hoje `source/Boot/Config.php` carrega uma allowlist do `.env`, preserva variáveis
já definidas pelo processo e normaliza aliases `APP_*`/`DB_*` para `MOVESOS_*`.
`testing` exige banco terminado em `_test`; `local` possui defaults para XAMPP;
produção exige host, usuário, senha e nome do banco. A CI usa PHP 8.2 e MariaDB
efêmero. O Composer declara `deploy:check`, porém o script alvo não está versionado
nesta cadeia; portanto o gate de configuração de deploy ainda é uma lacuna, não
um controle disponível.

Aliases legados continuam compatíveis durante a migração, mas uma variável
canônica deve ter uma única semântica e precedência documentada.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| Desenvolvedor | `.env` local, dados sintéticos e serviços fake/sandbox |
| CI | ambiente efêmero, secrets mínimos e gates reproduzíveis |
| Plataforma/DevOps | provisionamento, secret store, deploy, rotação e rollback |
| Segurança | classificação, acesso e auditoria de configuração sensível |
| Dono do serviço | contrato, limites e endpoints por ambiente |
| Aplicação | validar configuração e falhar com diagnóstico seguro |

Nenhuma pessoa compartilha credencial nominal. Service accounts têm owner,
escopo, ambiente, expiração/rotação e trilha de uso.

## Fontes e precedência

Ordem proposta, da maior para a menor prioridade:

1. variável injetada pelo runtime/orquestrador;
2. secret store montado/injetado pelo ambiente;
3. `.env` local, apenas em workstation explicitamente local;
4. default seguro e não secreto permitido pelo schema;
5. ausência: erro de configuração antes de aceitar tráfego/trabalho.

Banco, token, certificado, chave, senha, webhook secret e credencial de e-mail
nunca recebem default funcional em produção. `settings` guarda preferências
administráveis do produto depois da conexão; não guarda infraestrutura ou segredo.

## Schema de configuração

Toda variável canônica deve declarar:

- nome, tipo, formato e descrição;
- ambientes onde é obrigatória/permitida;
- valor secreto ou não secreto;
- default seguro, se existir;
- owner e consumidor;
- possibilidade de reload ou exigência de restart;
- estratégia de rotação/depreciação;
- validação semântica e mensagem segura.

Grupos iniciais:

| Grupo | Exemplos atuais | Regras |
| --- | --- | --- |
| aplicação | `MOVESOS_ENV`, `APP_URL`, `APP_DEBUG` | URL absoluta; debug falso fora de local |
| banco | `MOVESOS_DB_*`, `MOVESOS_TEST_DB_*` | conjunto completo; alvo coerente com ambiente |
| integrações | `INTER_*` | endpoint allowlisted; segredo/certificado fora do repo |
| build | `MOVESOS_BUILD_ASSETS` | somente comando de build, não requisição |
| runtime | timezone, sessões, storage, logs | caminhos e permissões validados |

Nomes `APP_ENV` e `DB_*` permanecem aliases temporários. A telemetria deve medir
uso antes da remoção; conflito entre alias e canônico é erro ou alerta explícito,
nunca escolha invisível.

## Estados do ambiente

```text
declared → provisioned → configured → validated → ready → serving
                         ↘ invalid       ↘ degraded → recovered
                                      ↘ draining → retired
```

- `provisioned`: recursos existem, sem garantir contrato correto;
- `configured`: valores foram injetados;
- `validated`: schema, conexões e permissões mínimas passaram;
- `ready`: migrations compatíveis, storage e dependências críticas disponíveis;
- `serving`: recebe tráfego/jobs;
- `degraded`: dependência opcional falhou, com capacidade reduzida explícita;
- `draining`: para novos trabalhos antes de deploy/retirada;
- `retired`: secrets revogados, dados expirados e recursos removidos.

Falha em banco, identidade, chave de sessão ou migration incompatível impede
`ready`. Integração opcional indisponível não autoriza resposta falsa; a função
afetada informa estado degradado.

## Isolamento de dados e multi-tenancy

- ambientes usam bancos, buckets, caches, filas, domínios e credentials distintos;
- produção nunca é origem direta de testes automatizados;
- cópia para staging exige minimização/anonimização verificável e prazo de deleção;
- tenant IDs de preview/staging não coincidem com autorização de produção;
- e-mail, SMS e webhook não alcançam destinatários reais fora de produção;
- backups são segregados por ambiente e não restaurados em destino menos confiável
  sem aprovação e sanitização.

Dentro de um ambiente, o isolamento entre tenants continua obrigatório; ambiente
não substitui `tenant_id` nem capacidade.

## Domínios, URLs e rede

`APP_URL` é canônica, absoluta e HTTPS fora de local. Host recebido é validado
contra allowlist para evitar host-header injection. Cookies usam domínio, path,
Secure, HttpOnly e SameSite próprios do ambiente. CORS, callbacks OAuth/webhook,
CSP e links de e-mail são listas explícitas por ambiente.

Serviços internos e bancos não ficam publicamente expostos. Egress para APIs/CDNs
é inventariado; staging e preview não herdam automaticamente allowlists de
produção.

## Segredos e certificados

Classificação, ciclo de vida, rotação e migração dos campos legados são detalhados
em [`secure-configuration.md`](../security/secure-configuration.md), sujeitos à
revisão da Issue #9.

- segredos são injetados no runtime, não commitados, exibidos ou persistidos em
  `settings`;
- acesso segue menor privilégio e separação por ambiente;
- rotação aceita período controlado de chave atual/anterior quando necessário;
- certificado/chave têm permissões mínimas, validade monitorada e owner;
- logs e health checks mostram apenas presença/versão segura, nunca valor;
- comprometimento dispara revogação, rotação, auditoria e revisão de dependências.

`.env.example` contém somente nomes e valores inertes. Um scanner futuro deve
bloquear segredo em commit e artefato.

## Build, release e deploy

O build ocorre na CI uma vez, gera artefato identificável pelo commit e não
depende do banco. Assets não são recompilados durante requisição. O mesmo artefato
é promovido entre ambientes; configuração é injetada no deploy.

Sequência mínima:

1. validar configuração sem exibir segredos;
2. verificar dependências e compatibilidade de migrations;
3. aplicar expand migrations aprovadas com lock;
4. iniciar instâncias sem tráfego e executar health/readiness;
5. liberar tráfego gradualmente e rodar smoke;
6. observar erros, latência, filas e auditoria;
7. concluir ou reverter aplicação mantendo schema compatível.

Workers e cron usam o mesmo contrato de ambiente do HTTP. Nenhum job lê `.env`
por lógica paralela divergente; a convergência desse legado será Issue executável.

## Observabilidade e debug

Logs incluem ambiente, versão/commit, serviço e correlation ID, nunca segredo.
Métricas e alertas são separados por ambiente. Produção não exibe stack trace,
SQL, paths ou configuração ao usuário. Debug local não pode desativar escaping,
autorização, tenancy ou validação.

Health prova processo vivo; readiness prova dependências críticas e compatibilidade.
Endpoints são mínimos, autenticados/restritos quando detalhados e não retornam
credenciais ou inventário explorável.

## Entradas, saídas e falhas

O validador recebe ambiente declarado e mapa de configuração. Produz lista de
erros/avisos com chave, regra e ação, redigindo valores. Categorias:

- ambiente desconhecido ou inferido ambiguamente;
- chave obrigatória ausente, tipo/formato inválido ou alias conflitante;
- debug/URL/política insegura;
- banco, storage ou serviço pertencente a outro ambiente;
- secret/certificado ausente, expirado ou com permissão imprópria;
- migration, build ou versão incompatível.

Erro crítico termina antes de tráfego, job ou migration. Não há fallback de
produção para local, nem de teste para produção.

## Estratégia de testes

1. matriz de obrigatoriedade e defaults para cada ambiente;
2. precedência runtime > secret > `.env` local > default seguro;
3. conflito entre alias e canônico;
4. produção sem cada segredo/configuração crítica falha fechada;
5. `testing` recusa banco sem `_test` e qualquer endpoint real;
6. local funciona com valores documentados e sem segredo real;
7. host, `APP_URL`, cookies, CORS e callbacks respeitam allowlist;
8. logs/erros/deploy-check não revelam valores sensíveis;
9. HTTP, worker e migration resolvem o mesmo ambiente/alvo;
10. smoke por superfície em preview/staging com dados sintéticos;
11. rotação de segredo sem indisponibilidade indevida;
12. rollback do artefato preserva compatibilidade de schema/configuração.

## Exceções e break-glass

Acesso emergencial exige incidente, aprovador, identidade individual, escopo,
expiração curta e auditoria. Não permite copiar segredo para arquivo ou usar
credencial de produção em local. Toda exceção registra owner, risco, compensação
e condição de remoção.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| ambientes | cinco contratos | preview/staging ainda ausentes | provisionar gradualmente |
| configuração | env + secret store | aliases duplicados | telemetria e depreciação |
| local | `.env` permitido | drift | schema e deploy-check compartilhados |
| artefato | build único | pipeline ainda parcial | validar antes de obrigar promoção |
| dados | sintéticos fora de produção | cobertura de casos raros | geradores/anonimização aprovada |
| dependências | sandbox/fake | diferença do real | contratos + smoke controlado |

## Pontos abertos

- inventariar todas as variáveis consumidas e remover leitura paralela em workers;
- implementar e testar o alvo atualmente ausente de `composer deploy:check`;
- definir plataforma de secret store e rotação;
- provisionar/confirmar contratos de preview e staging;
- separar credenciais de aplicação, migration e leitura operacional;
- definir readiness/health e dependências opcionais por superfície;
- catalogar egress, callbacks, domínios e políticas de cookies/CORS;
- escolher retenção e anonimização dos dados não produtivos.

## Gate para sair do Discovery

- schema de configuração e owners revisados por engenharia, segurança e operação;
- matriz de ambientes e isolamento aprovada;
- boot HTTP, worker, CI e migration validados contra o mesmo contrato;
- preview/staging tratados como alvo, não como capacidade existente;
- pontos abertos convertidos em Issues executáveis sem alterar ambientes neste PR.
