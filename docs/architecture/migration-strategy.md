# MovesOS — estratégia de migrations

Status: proposta de Discovery para revisão. Este documento formaliza a evolução
do schema e dos dados; não cria, aplica ou modifica migrations.

## Objetivo e princípios

Migrations devem levar qualquer banco suportado de um estado conhecido ao
próximo estado de forma rastreável, verificável e operacionalmente segura.

1. todo schema nasce de baseline versionado mais migrations imutáveis;
2. cada alteração é pequena, ordenada, compatível e possui plano de recuperação;
3. deploy de aplicação e mudança destrutiva de dados nunca dependem do mesmo
   instante;
4. migration aplicada não é editada; correção é uma nova migration;
5. backup não substitui rollback, e rollback não substitui verificação;
6. nenhum comando escolhe ou altera silenciosamente o banco principal.

Esta estratégia complementa as [convenções de domínio](domain-conventions.md), a
[estratégia de IDs](identifier-strategy.md) e a
[estratégia de testes](test-strategy.md).

## Baseline atual

O repositório já possui:

- baseline estrutural `storage/database/baseline/20260831_schema.sql`, sem dados;
- manifesto com hashes das tabelas e fingerprint do schema;
- migrations SQL ordenadas em `storage/database/migrations/`;
- registro `movesos_schema_migrations` com nome, checksum, batch e instante;
- comandos Composer `db:status`, `db:migrate`, `db:baseline`, `db:install` e
  `db:verify`;
- lock nomeado para impedir dois aplicadores concorrentes;
- confirmações explícitas para baseline/install e proteção adicional em produção;
- teste automatizado de integridade do baseline e manifesto.

O baseline é ponto de instalação e verificação, não histórico apagável. Bancos
existentes só recebem baseline após comparação integral do schema em clone
verificado e backup recuperável.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| Autor | compatibilidade, SQL, testes, custo, rollback e runbook |
| Revisor de domínio | invariantes, estados e retenção dos dados |
| Revisor de banco | locks, índices, plano de execução e capacidade |
| Segurança/LGPD | acesso, PII, criptografia, retenção e auditoria |
| Operador de deploy | backup testado, janela, execução, métricas e decisão de parar |
| Aplicador automatizado | lock, checksum, registro e saída determinística |

O usuário da aplicação não executa DDL. A credencial de migration é separada e
temporária, com o menor privilégio compatível com a mudança aprovada.

## Nomes, ordem e ownership

Formato proposto:

```text
YYYYMMDDHHMM_<modulo>_<acao>.sql
```

O timestamp UTC evita colisão; módulo e ação usam `snake_case`. Nome, conteúdo e
checksum tornam-se imutáveis após aplicação em qualquer ambiente compartilhado.
Cada arquivo declara em comentários: Issue/owner, pré-condições, compatibilidade,
estimativa, verificação e procedimento de recuperação.

Tabelas seguem o domínio proprietário. Mudança que cruza módulos exige owner de
cada contrato e ordem explícita; FK para estado privado de outro módulo requer
ADR. Objetos tenant-scoped recebem `tenant_id` e índices compatíveis antes de se
tornarem acessíveis à aplicação.

## Estados e transições

```text
authored → validated → approved → scheduled → applying → applied → verified
              ↘ rejected                 ↘ failed      ↘ degraded/recovered
```

- `validated`: aplica do zero e a partir do baseline suportado em banco isolado;
- `approved`: revisão técnica e operacional concluída;
- `scheduled`: backup, janela e observabilidade confirmados;
- `applying`: lock exclusivo adquirido e alvo identificado;
- `applied`: checksum registrado somente após o SQL correspondente concluir;
- `verified`: schema, invariantes e métricas pós-deploy aprovados;
- `failed`: execução para, preserva evidência e não registra falso sucesso;
- `recovered`: restauração ou correção forward concluída e verificada.

Uma migration pendente não pode ser pulada fora de um baseline formal. Divergência
de checksum bloqueia execução até investigação; nunca se atualiza o checksum para
“aceitar” uma alteração retroativa.

## Fluxo expand/contract

Mudanças incompatíveis usam etapas independentes:

1. **expand:** adicionar estrutura compatível e nullable/default seguro;
2. **dual-compatible:** publicar código que lê/escreve os dois contratos quando
   necessário;
3. **backfill:** migrar dados em lotes reiniciáveis, com checkpoint e limite;
4. **enforce:** validar total/invariantes e só então adicionar restrição;
5. **switch:** mover leitura para a nova estrutura com telemetria;
6. **contract:** remover legado em release posterior, após ausência de uso;
7. **verify:** confirmar schema, dados, latência, erros e rollback encerrado.

Adicionar coluna obrigatória, renomear/remover coluna, alterar tipo ou reconstruir
índice em tabela grande não ocorre em uma única etapa bloqueante. Views/adapters
temporários têm owner e condição de remoção.

## Schema e dados

DDL e transformação de dados volumosa são separadas. Migrations estruturais não
fazem chamadas de rede nem dependem de horário, locale ou estado da aplicação.
Backfills são jobs idempotentes com chave/checkpoint, tamanho de lote, throttle,
retry e progresso observável.

- defaults são determinísticos e não inventam dado de negócio;
- novas unicidades passam por relatório de duplicatas e reconciliação;
- FKs são criadas após órfãos serem medidos e tratados;
- índices têm consulta-alvo, cardinalidade e impacto de escrita avaliados;
- conversão destrutiva preserva cópia/coluna antiga até o gate de contract;
- PII respeita finalidade, retenção e minimização durante cópia e logs.

## Concorrência e disponibilidade

O aplicador mantém um lock global com timeout e identificação do processo. Como
DDL de MySQL/MariaDB pode executar commit implícito, não se presume rollback
transacional. Cada arquivo define atomicidade real e ponto seguro de interrupção.

Antes da produção devem ser estimados duração, lock de metadata, crescimento de
disco, replicação e impacto em leitura/escrita. Alteração potencialmente longa usa
técnica online suportada, janela ou cópia gradual aprovada; timeout encerra com
diagnóstico, não com retry cego.

## Entradas, saídas e erros

O comando recebe modo explícito e configuração do ambiente. Antes de escrever,
exibe/valida ambiente, host lógico, nome do banco, pendências, checksums e lock,
sem mostrar senha. Saídas são estáveis para automação e distinguem:

- configuração ausente ou alvo recusado;
- baseline/schema divergente;
- checksum alterado;
- lock indisponível;
- pré-condição ou SQL inválido;
- aplicação concluída mas verificação falhou;
- banco atualizado sem pendências.

Em qualquer falha, novas migrations param. O lock é liberado em bloco garantido;
o operador recebe migration, fase e correlation ID. SQL, credencial, PII e stack
sensível não entram em saída pública.

## Segurança, auditoria e multi-tenancy

- produção exige confirmação não interativa própria do pipeline e aprovação;
- nome do banco/ambiente usa allowlist e proteção contra banco de teste/produção
  trocados;
- arquivos SQL vêm somente do artefato versionado e revisado;
- execução registra commit, migration, checksum, batch, ator de deploy, ambiente,
  início, fim e resultado em trilha protegida;
- backfill tenant-scoped processa tenant explícito e prova que não cruza escopo;
- seeds de demonstração/teste são separados e proibidos em produção;
- dump, backup e evidência seguem criptografia, acesso e expiração definidos.

O usuário ID 1 e dados reais nunca participam de testes destrutivos. Smoke manual
pode usar apenas a identidade técnica autorizada (ID 2), sem editar credenciais ou
ownership do usuário protegido.

## Rollback e recuperação

Preferência: correção forward compatível. Down migrations automáticas não são
obrigatórias porque perda de coluna/dado pode ser irreversível. Cada mudança,
porém, deve escolher e ensaiar uma estratégia:

- desativar código novo por configuração;
- voltar aplicação enquanto o schema expandido permanece compatível;
- restaurar coluna/tabela preservada;
- executar migration corretiva forward;
- restaurar backup em incidente grave, com RPO/RTO e perda avaliados.

Backup só é válido após teste de restauração e verificação. Contract destrutivo
exige confirmação de que rollback de aplicação não depende mais do contrato antigo.

## Estratégia de testes

1. lint/parse e proibição de padrões perigosos não justificados;
2. instalar baseline em banco vazio e verificar fingerprint;
3. aplicar todas as migrations pendentes na ordem;
4. executar novamente sem alteração (idempotência do aplicador);
5. partir de snapshot suportado e chegar ao mesmo schema final;
6. alterar checksum aplicado e confirmar bloqueio;
7. disputar lock com dois processos e garantir um único aplicador;
8. interromper backfill e retomar do checkpoint sem duplicação;
9. provar constraints, índices e isolamento entre tenants;
10. ensaiar rollback/restore e validar invariantes pós-recuperação;
11. medir duração e locks com volume representativo;
12. garantir que logs e fixtures não exponham segredo ou PII.

Testes usam exclusivamente banco descartável com sufixo `_test` ou instância
efêmera. Nenhum fallback aponta para o banco configurado da aplicação.

## Checklist operacional

Antes: PR e checksum aprovados, compatibilidade declarada, backup restaurável,
espaço/replicação avaliados, owner online e critério de abort definido.

Durante: um aplicador, logs/correlation ID, métricas de locks/latência/erros e
checkpoint dos lotes acompanhados.

Depois: `db:status`, `db:verify` quando aplicável, invariantes e smoke aprovados,
auditoria registrada e adapter/backfill com próxima ação definida.

## Exceções e incidentes

Migration manual de emergência ainda exige arquivo versionado posterior que
reconcilie o estado, evidência do comando, aprovador e relatório do incidente.
Hotfix nunca edita migration já aplicada. Divergência desconhecida bloqueia
baseline/apply até comparação em clone; não se força produção para “alinhar”.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| fonte de verdade | baseline + SQL incremental | drift manual | verificação e checksum |
| rollback | forward/compatibilidade | recuperação mais longa | runbook por mudança |
| backfill | job separado | dupla escrita temporária | flag e telemetria |
| locking | lock global | deploy serial | granularidade só com evidência |
| ferramenta | comando PHP atual | parser/DDL limitado | avaliar após medir lacunas |
| baseline | snapshot estrutural | envelhecimento | nova baseline por ADR |

## Pontos abertos

- definir tamanho/idade que exige teste de volume e técnica online;
- adicionar metadados de commit, duração e resultado ao registro futuro;
- decidir frequência e política de renovação do baseline;
- catalogar bancos/versões MySQL e MariaDB suportados;
- formalizar credencial temporária e aprovação do pipeline;
- especificar schema/data diff seguro para verificação contínua;
- medir todas as migrations existentes e classificar risco/rollback.

## Gate para sair do Discovery

- estratégia revisada por engenharia, banco, segurança e operação;
- expand/contract e recuperação ensaiados em uma mudança representativa;
- matriz de compatibilidade e ambientes suportados confirmada;
- pontos abertos convertidos em Issues executáveis;
- nenhuma alteração de banco executada por este documento.
