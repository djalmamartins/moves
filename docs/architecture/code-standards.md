# MovesOS — padrões de código

Status: proposta de Discovery para revisão. Este documento define critérios
verificáveis para código novo e alterações no legado; não instala ferramentas,
reorganiza módulos nem altera comportamento.

## Objetivo e princípios

O padrão existe para tornar mudanças previsíveis, revisáveis, seguras e fáceis
de reverter. Ele se apoia na [fundação técnica](foundation.md), nas
[convenções de domínio](domain-conventions.md) e no
[contrato da API](api-strategy.md).

1. clareza e correção prevalecem sobre concisão;
2. dependências apontam para contratos do domínio, não para detalhes de UI;
3. entrada externa é não confiável até validação e autorização;
4. uma mudança deve ser pequena, testável e observável;
5. compatibilidade legada é explícita e não vira padrão para código novo;
6. comentários explicam decisões e riscos, não repetem o código.

## Escopo, atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| Autor | escopo, implementação, testes, documentação e rollback |
| Revisor | corretude, simplicidade, segurança e aderência ao domínio |
| Dono do domínio | vocabulário, invariantes, estados e compatibilidade |
| Segurança | ameaças, autorização, dados sensíveis e dependências críticas |
| CI | executar gates reproduzíveis, sem substituir revisão humana |

Aplicam-se a PHP, JavaScript, CSS, SQL, templates, comandos, jobs e testes.
Arquivos gerados ou de terceiros não são formatados manualmente: sua origem e
processo de geração devem ser identificáveis.

## Organização e dependências

- `source/` contém código PHP autoloadado por `Source\\`; `tests/` usa
  `MovesOSTests\\` e espelha a responsabilidade testada.
- entrypoints (`index.php`, comandos e jobs) montam dependências e delegam;
  não concentram regra de negócio.
- controllers traduzem HTTP, autenticam, autorizam, validam a forma da entrada
  e chamam um caso de uso; não executam SQL nem decidem regra de domínio.
- modelos legados de persistência permanecem atrás de serviços/repositórios em
  código novo. Templates apenas apresentam dados preparados.
- módulos não acessam estado privado de outro módulo. Integração ocorre por
  contrato público, comando, consulta ou evento documentado.
- dependência circular, helper global novo ou singleton mutável exige decisão
  arquitetural explícita; o padrão é injeção de dependência.

`App*`, controllers extensos e helpers globais existentes são compatibilidade.
Uma alteração local pode preservá-los, mas não deve ampliar seu alcance.

## PHP

- PHP 8.2 é a versão mínima; arquivos novos declaram `strict_types=1`.
- estilo segue PSR-12, PSR-4 e UTF-8/LF; uma classe principal por arquivo.
- classes, propriedades, parâmetros e retornos recebem tipos sempre que o
  contrato permitir. `mixed` e arrays sem forma exigem justificativa.
- nomes de tipos e métodos ficam em inglês; conceitos seguem o glossário de
  domínio em português. Classes são substantivos; comandos usam imperativo;
  eventos descrevem fato passado.
- `final` é preferido quando extensão não faz parte do contrato; interfaces só
  existem onde há fronteira ou substituição real.
- exceções representam falha excepcional e pertencem à camada apropriada;
  retorno booleano ambíguo, `die`, `exit` e supressão com `@` são proibidos em
  código de domínio/aplicação.
- datas usam objetos imutáveis e fuso explícito; dinheiro não usa `float`.
- SQL usa parâmetros vinculados e transações na fronteira do caso de uso.

## JavaScript, CSS e templates

- JavaScript novo evita estado global, handlers inline e dependência implícita
  da ordem dos scripts; módulos expõem uma API mínima.
- eventos, seletores e payloads recebem nomes estáveis; ação assíncrona trata
  espera, sucesso, vazio e erro sem `alert()` como fluxo de produto.
- CSS usa tokens oficiais para cor, espaço, tipografia, raio e elevação. Não se
  duplica um design system por superfície.
- componentes preservam teclado, foco, semântica e contraste; estados visuais
  também possuem texto/atributo acessível.
- templates escapam saída conforme o contexto. HTML confiável exige sanitização
  na borda e contrato explícito; dados não fazem concatenação em scripts ou URLs.

## Banco, migrations e dados

- alteração de schema ocorre somente por migration versionada, nunca por efeito
  colateral de requisição; a estratégia detalhada pertence à Issue #6.
- nomes seguem `snake_case`, tabelas no plural e FKs `<entity>_id`.
- queries tenant-scoped incluem `tenant_id` na própria resolução.
- migration destrutiva exige backfill, verificação, rollback operacional e etapa
  separada de remoção. Seeds de teste não usam nem alteram dados reais.
- segredos e PII não entram em fixtures, commits, logs ou mensagens de erro.

## Entradas, saídas e tratamento de falhas

Toda entrada declara formato, obrigatoriedade, limites e normalização. Validação
estrutural ocorre na borda; invariantes continuam no domínio. Saídas usam DTO ou
estrutura documentada e não expõem linha do banco diretamente.

Estados mínimos de um caso de uso:

```text
received → validated → authorized → executing → succeeded
    ↘ rejected   ↘ rejected     ↘ failed/retryable
```

- falha antes da execução não produz efeito;
- falha transacional reverte o conjunto atômico;
- retry só ocorre quando idempotência e limite estão definidos;
- exceções externas são convertidas em categorias estáveis na borda;
- `not_found` também protege recurso de outro tenant contra enumeração;
- mensagens ao usuário são acionáveis; detalhes internos ficam em log seguro,
  ligados por correlation ID.

## Segurança, auditoria e multi-tenancy

- autenticação identifica; autorização verifica capacidade, tenant e recurso em
  cada ação. Esconder botão nunca é autorização.
- consultas e comandos falham fechados quando o contexto de tenant está ausente.
- saída é escapada, SQL parametrizado, uploads inspecionados e URLs redirecionadas
  validadas por lista de destinos permitidos.
- alterações relevantes registram ator, tenant, alvo, ação, resultado e
  correlação, sem segredo ou conteúdo pessoal desnecessário.
- credenciais vêm do ambiente/secret store; valores de exemplo não funcionam em
  produção e nenhum segredo recebe fallback silencioso.
- dependência nova exige avaliação de licença, manutenção, vulnerabilidades,
  tamanho, necessidade e alternativa nativa.

## Testes e critérios verificáveis

Cada mudança funcional deve cobrir, conforme o risco:

1. cenário feliz e resultado persistido/apresentado;
2. validação negativa e ausência de efeito parcial;
3. autorização permitida e negada;
4. isolamento entre dois tenants;
5. estados/transições inválidos;
6. concorrência e idempotência quando repetição for possível;
7. indisponibilidade de dependência e recuperação;
8. regressão do comportamento legado preservado.

Unitários cobrem regra isolada; integração cobre banco, HTTP e adaptadores;
end-to-end cobre apenas jornadas críticas. Testes são determinísticos, isolados,
independentes de ordem, relógio real, rede externa e banco principal. A proposta
detalhada está em [`test-strategy.md`](test-strategy.md), sujeita à revisão da
Issue #5.

## Gates de contribuição

Uma alteração chega a revisão somente com:

- Issue e escopo rastreáveis; branch `codex/<codigo>-<slug>`;
- diff sem arquivos acidentais, segredos, binários ou artefatos gerados fora do
  processo oficial;
- `composer validate --strict`, sintaxe PHP e suíte aplicável aprovados;
- documentação e exemplos atualizados quando o contrato muda;
- riscos, compatibilidade, migration e rollback descritos no PR;
- nenhum warning/risky test novo aceito silenciosamente.

Commits são atômicos, no imperativo e explicam uma intenção. PRs pequenos são o
padrão; refatoração independente não acompanha correção funcional sem necessidade.
Falha de gate move a mudança para `changes_requested`; aprovação técnica move
para `ready_to_merge`; merge e `Done` dependem da revisão humana definida no
workflow oficial.

## Exceções ao padrão

Uma exceção é válida apenas quando registra regra afetada, motivo, alternativas,
risco, responsável e prazo/condição de remoção. Deve ter o menor escopo possível
e teste que preserve o comportamento necessário. Urgência não autoriza remover
isolamento tenant, validação, auditoria ou proteção de segredo.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| estilo PHP | PSR-12, sem formatador escolhido | drift manual | selecionar ferramenta em PR próprio |
| tipos | rigor em código novo | atrito no legado | adaptadores graduais |
| módulos | contratos públicos | fronteiras atuais misturadas | catálogo e ADR por extração |
| qualidade | gates mínimos existentes | ausência de análise estática | piloto antes de tornar obrigatório |
| frontend | JS modular e tokens | assets legados duplicados | migração por superfície |

## Pontos abertos

- confirmar estratégia de monólito modular e catálogo de fronteiras;
- escolher formatador, linter e análise estática após medir o baseline;
- definir orçamento de cobertura e testes na Issue #5;
- detalhar migrations na Issue #6 e tratamento de erros na Issue #11;
- catalogar código gerado, vendorizado e exceções legadas;
- definir ownership por módulo e política de aprovação;
- automatizar os gates somente após execução limpa no baseline.

## Gate para sair do Discovery

- padrões revisados por engenharia, segurança e donos de domínio;
- exemplos e exceções validados contra ao menos um fluxo real;
- ferramentas candidatas avaliadas sem mudança funcional neste PR;
- pontos abertos transformados em Issues executáveis;
- nenhuma regra declarada como automatizada antes de existir na CI.
