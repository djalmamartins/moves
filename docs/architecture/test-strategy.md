# MovesOS — estratégia de testes

Status: proposta de Discovery para revisão. Este documento define cobertura,
isolamento e gates para futuras implementações; não adiciona ferramentas, testes
ou alterações funcionais.

## Objetivo

A suíte deve fornecer confiança proporcional ao risco, detectar regressões perto
da origem e provar as propriedades transversais do MovesOS: autorização,
multi-tenancy, idempotência, auditoria e ausência de efeitos parciais.

Esta estratégia complementa os [padrões de código](code-standards.md), as
[convenções de domínio](domain-conventions.md) e a
[estratégia da API](api-strategy.md).

## Baseline e limites

O baseline usa PHP 8.2, PHPUnit 11.5 e duas suítes em `phpunit.xml`:

- `tests/Unit`: regras puras, políticas e contratos sem I/O real;
- `tests/Integration`: banco, autenticação, controllers, filas e adaptadores;
- `tests/run.php`: força `MOVESOS_ENV=testing` e um banco com sufixo `_test`;
- `tests/prepare-environment.php`: cria/prepara schema e sessão isolados;
- CI: valida Composer, executa lint PHP e `composer test` em MariaDB efêmero;
- smoke autenticado: jornada implantada, executada separadamente quando o
  ambiente estiver disponível.

O banco principal e o usuário ID 1 nunca são fixtures. Testes locais destrutivos
só podem atuar em banco identificado como teste; o uso eventual de ID 2 em smoke
manual não substitui o isolamento automatizado.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| Autor | traduz critérios de aceite em testes e executa a suíte aplicável |
| Revisor | procura cenários ausentes, falso positivo e acoplamento ao detalhe |
| Dono do domínio | confirma invariantes, estados e exemplos de negócio |
| Segurança | define abusos, autorização negativa e dados sensíveis |
| CI | reproduz gates em ambiente limpo e publica resultado rastreável |

## Camadas e proporção

```text
          E2E — poucas jornadas críticas
       Contrato — APIs, eventos e integrações
    Integração — banco, HTTP, filas e adapters
 Unitário — regras, estados, valores e políticas
```

- a menor camada capaz de provar o comportamento é preferida;
- regra de domínio não depende de E2E para cobertura básica;
- integração prova wiring e persistência, não repete cada combinação unitária;
- E2E valida jornadas de alto valor e acessibilidade essencial, sem virar a
  principal fonte de diagnóstico;
- teste de contrato protege consumidores sem exigir rede externa real.

Não se fixa percentual global nesta etapa. Cobertura será usada como sinal e
orçamento por risco, nunca como substituto dos cenários obrigatórios.

## Matriz mínima por tipo de mudança

| Mudança | Unitário | Integração | Contrato/E2E | Verificação obrigatória |
| --- | --- | --- | --- | --- |
| regra/estado | sim | se persistir | não usual | transições e invariantes |
| controller/API | política | sim | contrato | status, schema, auth e tenant |
| migration | parser/regra | banco real | não | aplicar, repetir, rollback/verificação |
| job/fila | decisão | sim | contrato do evento | retry, deduplicação e poison message |
| UI/componente | lógica | render quando útil | jornada crítica | teclado, foco, estados e erros |
| segurança | política | sim | abuso crítico | negação, enumeração e vazamento |
| bug | teste de regressão | conforme origem | somente se crítico | falha antes, sucesso depois |

## Estados do ciclo de teste

```text
specified → implemented → passing → reviewed → accepted
                 ↘ failing ↗       ↘ flaky/quarantined
```

- `specified`: cenário, dados e resultado esperado são claros;
- `implemented`: teste falha pelo motivo previsto ou cobre comportamento já
  existente documentado;
- `passing`: resultado é determinístico no ambiente suportado;
- `reviewed`: outro responsável validou intenção e força das asserções;
- `accepted`: integra o gate oficial;
- `flaky/quarantined`: possui Issue, responsável, evidência e prazo; não pode
  conferir aprovação silenciosa ao PR.

Falha de infraestrutura é distinguida de regressão. Retry automático não mascara
teste instável; no máximo confirma diagnóstico e registra ambas as tentativas.

## Dados, relógio e isolamento

- cada teste cria apenas os dados que usa e não depende de ordem;
- fixtures possuem factories/builders com defaults válidos e overrides explícitos;
- valores aleatórios só evitam colisão; a seed é registrada quando influencia a
  regra testada;
- relógio, UUID/ULID, filas, e-mail e serviços externos são substituíveis;
- timezone e locale são declarados; comparação de dinheiro é exata;
- sessões, arquivos e caches usam diretórios temporários exclusivos;
- limpeza ocorre mesmo após falha e nunca desativa permanentemente FKs;
- teste tenant-scoped cria ao menos dois tenants para provar isolamento.

Dados pessoais reais, tokens e dumps de produção são proibidos. Massa derivada
de produção só pode existir após anonimização comprovada e aprovação específica.

## Cenários obrigatórios de domínio e API

Para cada agregado ou endpoint aplicável:

1. cenário feliz e estado/resultados emitidos;
2. cada transição inválida sem alteração parcial;
3. entrada ausente, malformada, limite e campo desconhecido;
4. usuário não autenticado e ator sem capacidade;
5. recurso inexistente e recurso de outro tenant indistinguíveis;
6. mesma idempotency key com payload igual e diferente;
7. duas versões concorrentes com conflito previsível;
8. dependência indisponível, timeout, retry e compensação;
9. auditoria de sucesso e falha relevante sem PII/segredo;
10. compatibilidade do adaptador legado durante migração.

## Segurança e abuse cases

O conjunto negativo inclui CSRF em sessão, XSS contextual, SQL injection,
mass assignment, path traversal, upload disfarçado, redirect aberto, brute force,
enumeração de IDs e escalada horizontal/vertical. Testes verificam também que
logs, erros e snapshots não contêm senha, token, cookie, chave idempotente ou
documento pessoal.

O teste afirma tanto o status/resultado quanto a ausência do efeito proibido.
Mocks de autorização não substituem pelo menos um teste integrado da política e
da consulta tenant-scoped.

## Concorrência, jobs e automações

- concorrência usa duas conexões/processos e barreira controlada quando necessário;
- jobs provam transições `pending`, `running`, `succeeded`, `failed` e retryable;
- redelivery do mesmo evento não duplica efeitos;
- limite de tentativas e dead letter/estado terminal são verificáveis;
- ação automática registra regra, versão, ator de serviço e correlação;
- testes não dependem de `sleep`; relógio e scheduler são controláveis.

## Integrações externas

Testes comuns usam fake local com respostas versionadas. Contratos verificam
payload, assinatura, timeout e mapeamento de erros. Sandbox remoto roda em job
separado, não bloqueia todo PR por instabilidade de terceiro e nunca usa segredo
de produção. Nenhum teste envia e-mail, mensagem ou webhook a pessoa real.

## Pipeline e gates

| Momento | Gate | Resultado esperado |
| --- | --- | --- |
| edição/commit | testes focados + lint | feedback em segundos |
| PR | Composer, sintaxe, unitários e integração | obrigatório e determinístico |
| mudança de contrato | consumer/schema tests | compatibilidade provada |
| mudança de migration | banco limpo + upgrade de baseline | repetível e verificado |
| pré-release | smoke e jornadas críticas | ambiente semelhante à produção |
| periódico | segurança, dependências e suíte ampliada | tendência e Issue rastreável |

Paralelização só ocorre após independência comprovada. O gate falha com warning,
risky test, erro ou teste pulado sem justificativa. Snapshot é pequeno, revisável
e não aceita segredo nem dado volátil.

## Qualidade dos testes

Um teste deve falhar se a regra protegida for removida. Nome descreve condição e
resultado; Arrange/Act/Assert fica legível sem abstração excessiva. Asserções
observam contrato público e efeitos relevantes, não sequência interna de chamadas.

Flakiness, duração e falhas recorrentes são métricas por suíte. Testes lentos têm
orçamento explícito e owner. Remoção ou enfraquecimento de teste exige explicar
qual proteção deixou de ser necessária.

## Exceções e falhas operacionais

- dependência de teste indisponível: falhar com diagnóstico acionável, sem usar o
  banco principal como fallback;
- schema ausente: preparar o banco isolado ou falhar antes da suíte;
- teste flaky: abrir Issue e corrigir; quarentena temporária não conta como gate;
- incidente urgente: adicionar regressão assim que o comportamento estiver
  estabilizado, com prazo registrado no PR/Issue;
- recurso não testável: refatorar a fronteira ou registrar dívida com owner; não
  declarar cobertura inexistente.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| framework | PHPUnit 11.5 | acoplamento aceitável | avaliar só por necessidade |
| banco | MariaDB real em integração | duração | otimizar fixtures antes de trocar |
| cobertura | por risco, sem meta global | áreas esquecidas | inventário e baseline futuro |
| E2E | poucas jornadas críticas | lacunas visuais | catálogo por superfície |
| externos | fakes + contratos | drift do fornecedor | sandbox periódico |
| flaky | quarentena rastreável | normalização da dívida | prazo e owner obrigatórios |

## Pontos abertos

- medir duração, flakiness e cobertura do baseline sem alterar o gate;
- definir orçamento por módulo e lista inicial de jornadas E2E;
- formalizar factories compartilhadas e estratégia transacional;
- escolher ferramenta de contrato e automação de acessibilidade;
- mapear integrações que exigem sandbox periódico;
- estabelecer retenção dos relatórios e evidências da CI;
- alinhar testes de migrations à proposta em
  [`migration-strategy.md`](migration-strategy.md), sujeita à revisão da Issue #6.

## Gate para sair do Discovery

- matriz revisada por engenharia, produto, segurança e donos dos módulos;
- ao menos um fluxo real classificado em todas as camadas aplicáveis;
- baseline de duração/flakiness coletado antes de impor orçamento;
- lacunas convertidas em Issues com prioridade, responsável e aceite;
- nenhuma ferramenta ou meta declarada obrigatória antes de prova no repositório.
