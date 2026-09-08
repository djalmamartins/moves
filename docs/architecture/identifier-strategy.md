# MovesOS — estratégia de identificadores

Status: proposta de Discovery para revisão. Esta decisão complementa as
[convenções de domínio](domain-conventions.md) e não altera banco ou código.

## Decisão resumida

O MovesOS separará três conceitos:

1. **ID interno:** chave técnica local, numérica e sem significado de negócio.
2. **ID público:** ULID canônico de 26 caracteres, opaco e não enumerável,
   usado em URLs, APIs, eventos e integrações novas.
3. **Identificador natural:** CPF, CNPJ, e-mail, número de unidade, protocolo ou
   código de fornecedor; é atributo validado, nunca chave primária.

O legado continua usando seus `INT/BIGINT UNSIGNED AUTO_INCREMENT` internamente.
A adoção do ID público será incremental, por tabela e com compatibilidade de
leitura; não haverá reescrita global de chaves primárias.

## Escopo e atores

- domínios criam e validam IDs de suas próprias entidades;
- clientes Web, App, ERP, Operation e Studio recebem apenas IDs públicos quando
  um recurso for exposto fora da fronteira confiável;
- integrações podem manter seus IDs externos em uma tabela de correspondência;
- operadores não escolhem nem editam IDs técnicos;
- suporte pode pesquisar por ID público ou identificador natural autorizado,
  sem obter acesso adicional ao recurso.

## Formatos

| Uso | Formato | Exemplo | Regras |
| --- | --- | --- | --- |
| PK interna existente | `INT/BIGINT UNSIGNED` | `1842` | somente persistência e joins locais |
| ID público novo | ULID maiúsculo | `01J8M6Z7Y5K2F1R4D9QW3A8BCE` | 26 chars, Crockford Base32, gerado pela aplicação |
| ID de evento | ULID | `01J8M70...` | global, imutável e ordenável por tempo |
| Correlation ID | UUID v4 | `936da01f-...` | aceito ou criado na borda; não identifica entidade |
| Chave idempotente | string aleatória | hash persistido | mínimo 128 bits; escopo por tenant, ator e operação |
| ID externo | string + sistema | `superlogica:1234` | nunca promovido a PK local |

ULID foi escolhido por ser compacto, seguro para URL, ordenável e gerável sem
coordenação. A ordenação temporal não é autorização nem relógio de negócio.
UUID v7 é alternativa futura caso o ecossistema adotado ofereça suporte nativo
maduro; a API trata o valor como opaco para permitir essa revisão.

## Propriedades obrigatórias

- geração local, sem consulta ao banco;
- pelo menos 80 bits aleatórios por ID público;
- representação canônica única, sem distinção de maiúsculas/minúsculas;
- validação de formato antes de consulta;
- índice único global para entidades globais e `UNIQUE(tenant_id, public_id)`
  para entidades tenant-scoped;
- nenhuma reutilização após exclusão, restauração ou importação;
- comparação em tempo constante apenas quando o valor também for segredo;
- logs podem registrar IDs públicos, mas não tokens, documentos ou chaves de
  idempotência em texto puro.

## Multi-tenancy e autorização

Um ID nunca carrega permissão. Toda resolução tenant-scoped usa, na mesma
consulta, `tenant_id` e `public_id`. Buscar globalmente e filtrar depois é
proibido.

```text
resolve(tenant_id, public_id, actor_capability) → entity | not_found
```

Recursos de outro tenant retornam o mesmo `not_found` de um ID inexistente,
salvo operação administrativa explicitamente autorizada e auditada. IDs
naturais únicos são normalizados e limitados ao escopo correto; e-mail global
no domínio de identidade não implica vínculo com qualquer tenant.

## Ciclo de vida e estados

```text
generated → persisted → active → archived
                    ↘ rejected
```

- `generated`: existe apenas na memória da operação;
- `persisted`: foi gravado sob restrição única;
- `active`: pode ser exposto conforme autorização;
- `archived`: continua reservado e resolvível apenas nos fluxos permitidos;
- `rejected`: colisão ou formato inválido; gera um novo valor, sem reaproveitar.

Uma colisão de ULID deve ser tratada como evento excepcional auditável. A
aplicação tenta novamente com limite pequeno; persistindo a falha, encerra a
operação sem gravação parcial.

## Entradas, saídas e exceções

- APIs aceitam somente o ID público documentado; IDs numéricos legados não são
  aceitos silenciosamente em endpoints novos.
- Rotas administrativas legadas podem aceitar o ID interno enquanto estiverem
  cobertas por adaptador e teste de autorização.
- Respostas serializam IDs como strings para evitar perda de precisão.
- Formato inválido produz `validation_error`; ID bem formado inexistente ou
  invisível produz `not_found`; colisão de criação produz `conflict` interno.
- Importação com ID externo duplicado exige regra explícita de reconciliação e
  nunca substitui entidade automaticamente.

## Chaves estrangeiras e referências entre contextos

Dentro do mesmo banco/contexto, FKs continuam apontando para a PK interna.
Entre contextos, mensagens e contratos usam IDs públicos. Um contexto não cria
FK para uma tabela privada de outro contexto sem ADR específica.

Referências polimórficas atuais (`entity_type` + `entity_id`) devem ser
catalogadas. Código novo prefere referência tipada (`entity_type` estável +
`entity_public_id`) ou tabela associativa explícita, com lista fechada de tipos.

## Idempotência

- cliente fornece `Idempotency-Key` ou a borda gera uma para jobs internos;
- escopo lógico: `tenant_id + actor_id + operation + key_hash`;
- o valor bruto nunca é armazenado, apenas hash resistente a pré-imagem;
- mesma chave e mesmo payload retornam o resultado anterior;
- mesma chave e payload diferente retornam `conflict`;
- retenção é definida por operação e nunca menor que o maior retry esperado;
- efeito e registro idempotente são persistidos na mesma transação.

## Estratégia incremental de migração

1. inventariar tabelas, tipos de PK/FK e rotas que expõem IDs internos;
2. adicionar `public_id CHAR(26) NULL` sem trocar a PK;
3. preencher em lotes reiniciáveis, registrando progresso e colisões;
4. validar total, formato, unicidade e isolamento por tenant;
5. tornar `public_id` obrigatório e criar índice único apropriado;
6. expor leitura dupla somente nos adaptadores legados;
7. migrar links, APIs, eventos e integrações para o ID público;
8. desativar aceitação externa do ID numérico após telemetria sem uso;
9. manter PK/FK interna, salvo ADR posterior com benefício comprovado.

Cada etapa possui rollback aditivo: parar o backfill, remover o índice ainda
não consumido ou reativar o adaptador. A coluna não será apagada enquanto
existirem referências emitidas.

## Segurança e privacidade

- ULID reduz enumeração, mas não substitui autorização e rate limiting;
- o timestamp embutido pode revelar aproximadamente a criação; entidades cuja
  data de existência seja sensível devem avaliar UUID aleatório em ADR própria;
- tokens de confirmação, reset, sessão e convite são segredos, não IDs públicos;
- identificadores naturais são dados pessoais e recebem mascaramento, retenção
  e auditoria compatíveis com sua finalidade;
- respostas e logs não informam se um ID existe fora do escopo do ator.

## Critérios testáveis para a futura implementação

1. gerar grande amostra sem formato inválido nem colisão;
2. garantir serialização como string e round-trip canônico;
3. resolver recurso somente com o `tenant_id` correto;
4. retornar resultado indistinguível para ID ausente e de outro tenant;
5. rejeitar IDs numéricos em endpoint novo;
6. repetir criação com a mesma chave/payload sem duplicar efeitos;
7. rejeitar a mesma chave com payload diferente;
8. executar backfill duas vezes com o mesmo resultado;
9. simular colisão e falha durante lote sem gravação parcial;
10. verificar que logs não contêm chave idempotente, token ou ID natural bruto.

## Riscos e decisões reversíveis

| Risco | Mitigação | Decisão reversível |
| --- | --- | --- |
| mistura entre IDs internos e públicos | tipos distintos e adaptadores | formato público pode mudar antes do primeiro contrato estável |
| índices maiores | manter PK/FK numérica local | estratégia de índice por entidade |
| timestamp do ULID | avaliação de sensibilidade | UUID aleatório para domínio específico |
| colisão no backfill | restrição única e job reiniciável | tamanho e algoritmo do lote |
| links legados quebrados | leitura compatível e telemetria | prazo de desativação |

## Pontos abertos

- escolher biblioteca ULID após avaliação de manutenção e testes;
- confirmar collation binária/ASCII compatível no MySQL suportado;
- definir entidades globais versus tenant-scoped na especificação de tenancy;
- inventariar todas as rotas que hoje expõem IDs numéricos;
- definir retenção por classe de operação idempotente;
- validar impacto do timestamp do ULID com privacidade e segurança.

## Gate para sair do Discovery

- convenções de domínio da Issue #1 aprovadas;
- formato e semântica revisados por arquitetura e segurança;
- prova de índice/collation no MySQL alvo;
- plano de backfill e rollback validado em cópia isolada;
- pontos abertos convertidos em Issues executáveis, sem migration neste PR.
