# MovesOS — configuração segura

Status: proposta de Discovery para revisão. Este documento classifica
configurações e define seu ciclo seguro; não move credenciais, altera `settings`,
rotaciona segredos ou modifica infraestrutura.

## Objetivo e princípios

Configuração segura garante que cada valor tenha fonte, owner, ambiente, acesso,
validação e ciclo de vida explícitos. O valor menos privilegiado e a falha fechada
são os defaults.

1. segredo nunca é código, conteúdo editorial ou preferência administrativa;
2. produção recebe segredo apenas do runtime/secret store;
3. nenhuma resposta, log, auditoria, teste ou backup expõe valor secreto;
4. acesso e rotação são individuais, rastreáveis e separados por ambiente;
5. configuração é validada antes do uso e não escolhe fallback inseguro;
6. browser recebe somente configuração pública destinada ao cliente.

Esta especificação depende do [contrato de ambientes](../architecture/environment-strategy.md)
e trata os riscos do [threat model](threat-model.md).

## Baseline e lacuna observada

O repositório ignora `.env`, certificados e diretórios de runtime; `.htaccess`
bloqueia `.env`, código interno e áreas sensíveis de `storage`. O boot usa uma
allowlist de variáveis, produção exige credenciais de banco e o logger possui
redaction por nomes sensíveis.

Porém `source/Boot/Settings.php` ainda lê `mail_pass`, `pay_live` e `pay_test` da
tabela `settings` e os promove a constantes globais. Esses campos devem ser
tratados como dívida de segurança: a migração para secret store requer inventário,
rotação e compatibilidade; não deve copiar valores para logs, PRs ou documentação.

## Classificação

| Classe | Exemplos | Fonte permitida | Exposição |
| --- | --- | --- | --- |
| Pública | nome, logo, idioma, URL pública | código/config admin | HTML/API pública quando previsto |
| Interna | feature flag, limite, nome de fila | env/config service | apenas operadores autorizados |
| Sensível | e-mail, endpoint privado, tenant mapping | config protegida | mínima e mascarada |
| Secreta | senha, token, API key, webhook secret | secret store/runtime | somente consumidor autorizado |
| Criptográfica | chave privada, certificado+chave, pepper | KMS/HSM/secret store | nunca exportável quando possível |

Classificação acompanha o valor em catálogo e tooling. Nome aparentemente neutro
não reduz a classe se o conteúdo autentica, autoriza ou identifica pessoa.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| Owner do segredo | finalidade, consumidores, escopo e rotação |
| Plataforma | injeção, versionamento, acesso, backup e revogação |
| Segurança | política, auditoria, detecção e resposta a vazamento |
| Aplicação | validar presença/formato e evitar exposição |
| Operador | usar workflow aprovado sem visualizar além do necessário |
| Revisor | impedir segredo em código, teste, log, artifact e PR |

Service account não é compartilhada entre pessoas, tenants, ambientes ou serviços
sem justificativa explícita. Todo acesso humano privilegiado é individual.

## Catálogo obrigatório

Cada item de configuração declara:

- nome canônico e aliases em depreciação;
- classe e fundamento;
- owner, consumidores e ambientes;
- tipo, formato e validação semântica;
- fonte autorizada e método de injeção;
- permissões/scopes mínimos;
- criação, validade, rotação e revogação;
- efeito de ausência/expiração;
- possibilidade de reload ou restart;
- regra de auditoria, backup e retenção.

O catálogo registra metadados, nunca o valor. Descoberta automática deve reportar
apenas nome/local de consumo e hash seguro quando estritamente necessário.

## Estados e ciclo de vida de segredos

```text
requested → approved → generated → distributed → active
                         ↘ failed       ↘ rotating → revoked → destroyed
                                              ↘ compromised
```

- `generated`: entropia suficiente no sistema autorizado, sem transmissão manual;
- `distributed`: disponível apenas às identidades/runtime permitidos;
- `active`: uso monitorado, validade e próxima rotação conhecidas;
- `rotating`: versão nova e anterior coexistem pelo menor período compatível;
- `revoked`: autenticação com a versão antiga falha e consumidores foram verificados;
- `compromised`: revogação urgente, contenção, auditoria e incidente;
- `destroyed`: cópias e backups obedecem retenção/crypto-shredding aplicável.

Segredo sem owner, validade ou consumidor conhecido não pode permanecer ativo.
Rotação falha mantém estado observável e não apaga a versão necessária antes de
confirmar todos os consumidores.

## Fontes, injeção e precedência

- runtime/secret store é a fonte de produção;
- `.env` é permitido apenas localmente, com permissão mínima e valores não reais;
- arquivos de certificado/chave ficam fora do webroot e do artefato, montados
  read-only com owner do processo;
- variáveis canônicas vencem aliases; conflito é erro explícito;
- linha `settings` não guarda banco, SMTP password, payment secret, token ou chave;
- parâmetros de CI vêm de secrets/environment protegido, nunca de output de job;
- comando, worker e HTTP consomem o mesmo resolvedor e schema.

Não se aceita segredo em query string, argumento visível na lista de processos,
nome de arquivo previsível, cookie não protegido ou JavaScript entregue ao cliente.

## Configuração persistida e painel administrativo

Preferências públicas/internas podem permanecer em `settings` quando possuem
schema, autorização e auditoria. Campos sensíveis são exibidos mascarados e uma
edição nunca devolve o valor atual ao browser. “Manter segredo existente” é uma
ação distinta de “substituir”; campo vazio não apaga silenciosamente.

Mudança registra ator, tenant/escopo, chave, versão anterior/nova como referência
não secreta, instante e resultado. A UI não permite alterar infraestrutura global
com uma permissão editorial. Valores com efeito de segurança exigem capacidade
específica e, quando crítico, reautenticação/aprovação.

## Validação e falha fechada

Antes de iniciar:

- ambiente e fonte são permitidos;
- todas as chaves obrigatórias estão presentes e não são placeholders;
- tipo, URL, porta, path, escopo e validade são coerentes;
- arquivo sensível existe, não é symlink inesperado e tem permissão adequada;
- endpoint/certificado pertence ao ambiente correto;
- pares dependentes estão completos (certificado/chave, client ID/secret);
- produção não usa default local, debug ou credencial de teste.

Ausência de segredo de banco/sessão/identidade impede readiness. Integração
opcional ausente desabilita apenas a capacidade declarada, com estado degradado;
jamais troca por endpoint ou credencial real de outro ambiente.

## Criptografia e armazenamento

Hash irreversível é usado para senha, token verificável e chave idempotente quando
o valor original não precisa ser recuperado. Criptografia autenticada é usada
somente quando o segredo precisa ser lido, com chave mestra separada dos dados.

- algoritmo e versão acompanham o ciphertext;
- nonce nunca é reutilizado;
- rotação suporta reencrypt gradual e rollback controlado;
- chave mestra não fica no mesmo banco/backup do ciphertext;
- comparação de tokens usa primitiva apropriada;
- “base64” e hash sem salt não são criptografia.

## Logs, erros, métricas e suporte

O envelope, correlação, níveis, sinks e retenção são detalhados na proposta de
[`logging estruturado`](../architecture/logging-strategy.md), sujeita à revisão
da Issue #10.

Redaction combina catálogo, nomes e tipos; não depende apenas de regex. Headers,
cookies, bodies e URLs são minimizados antes do logger. Erros públicos nunca
incluem configuração, path interno, stack, SQL ou existência de segredo.

Métricas mostram presença, idade, versão segura e sucesso de rotação, não valor.
Suporte recebe procedimento de diagnóstico por referência/correlation ID. Captura
de tela, dump, profiler e exportação respeitam a mesma política.

## CI/CD e supply chain

- secrets não são disponibilizados a PRs não confiáveis ou forks;
- permissões de workflow e token são mínimas e temporárias;
- scripts não usam trace que imprima ambiente;
- artefato, cache, relatório e imagem não incorporam `.env`/certificados;
- scanner busca padrões e entropia antes do merge e no histórico relevante;
- dependências e actions são versionadas e revisadas;
- deploy usa identidade federada/curta quando disponível, não chave permanente.

Um achado de segredo bloqueia publicação, aciona revogação e trata o histórico
como comprometido; apenas remover a linha do último commit não é suficiente.

## Multi-tenancy

Segredos globais da plataforma e segredos de tenant são namespaces distintos.
Resolução de segredo tenant-scoped usa tenant obtido da identidade confiável, não
um path/nome fornecido livremente pelo cliente. Cache inclui ambiente, tenant,
chave e versão; falha de lookup não cai para segredo de outro tenant ou global.

Exportação, desligamento ou deleção de tenant inclui inventário, revogação e
retenção dos seus secrets, sem afetar outros tenants.

## Rotação e incidentes

Rotação normal:

1. gerar nova versão e registrar owner/validade;
2. disponibilizar às aplicações sem ativar uso quando necessário;
3. alternar consumidores, observar autenticação e erros;
4. confirmar ausência de uso da versão anterior;
5. revogar e destruir conforme retenção;
6. registrar evidência sem valor secreto.

Vazamento: classificar alcance, revogar primeiro quando seguro, rotacionar todos
os derivados, invalidar sessões/tokens afetados, procurar uso indevido, notificar
conforme política e corrigir a origem. Break-glass expira automaticamente e gera
alerta em tempo real.

## Migração dos segredos em `settings`

1. inventariar campos e consumidores (`mail_pass`, `pay_live`, `pay_test` e outros);
2. classificar e definir nomes canônicos no secret store;
3. criar leitura preferencial nova com fallback legado monitorado e redigido;
4. rotacionar o valor — não copiar o segredo antigo quando puder ser regenerado;
5. migrar cada consumidor e verificar telemetria;
6. remover fallback em release posterior;
7. limpar colunas por migration contract após backup/retention aprovados;
8. revisar backups e logs que possam conter valores anteriores.

Cada etapa é reversível antes do contract. Nenhuma migração imprime ou exporta os
valores existentes.

## Estratégia de testes

1. catálogo/schema aceita e rejeita valores por classe/ambiente;
2. precedência e conflito entre fonte canônica e alias;
3. produção falha com segredo ausente, placeholder ou de teste;
4. aplicação inicia com secret store fake sem gravar valor;
5. painel mantém/substitui segredo sem devolvê-lo ao cliente;
6. logs, exceções, métricas e auditoria não contêm canary secrets;
7. arquivo de chave com permissão/path/symlink inválido é recusado;
8. rotação atual/anterior, revogação e recovery sob falha;
9. tenant A nunca resolve segredo do tenant B;
10. artefato/cache/build não contém `.env`, certificado ou alta entropia conhecida;
11. worker, HTTP, migration e CLI aplicam as mesmas regras;
12. fallback legado emite métrica e desaparece após o gate de migração.

Testes usam canaries sintéticos e secret stores fake; nunca credenciais reais.

## Exceções

Exceção registra segredo/classe (sem valor), ameaça, escopo, owner, aprovador,
expiração, detecção compensatória e plano de remoção. Não existe exceção para
commitar credencial real, logar segredo ou compartilhar produção com teste/local.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| fonte | secret store abstrato | fornecedor não escolhido | ADR da plataforma |
| persistência | remover secrets de `settings` | legado depende de constantes | adapter gradual |
| cache | memória curta/versionada | segredo após revogação | TTL/invalidação por classe |
| rotação | duas versões temporárias | janela maior de exposição | menor período mensurado |
| detecção | catálogo + scanner | falso positivo/negativo | baseline e tuning |
| criptografia | KMS/AEAD quando reversível | complexidade | piloto por caso real |

## Pontos abertos

- selecionar secret store/KMS e modelo de identidade do runtime;
- inventariar todos os campos sensíveis de `settings` e consumidores globais;
- definir TTL/reload e rotação por classe de segredo;
- revisar o segredo derivado de banco usado pelo widget de suporte;
- catalogar chaves de sessão/cookies e tokens de reset;
- verificar exposição histórica em Git, backups, logs e artefatos;
- transformar a migração de SMTP/pagamento em Issues executáveis separadas.

## Gate para sair do Discovery

- catálogo inicial revisado por engenharia, segurança e operação;
- owners e ciclos de rotação definidos para segredos críticos;
- plano de migração de `settings` aprovado sem copiar valores em claro;
- testes de redaction, ambiente e tenant especificados como critérios executáveis;
- fornecedor físico permanece decisão reversível até ADR e prova controlada.
