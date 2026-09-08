# MovesOS — convenções de domínio

Status: proposta de Discovery para revisão arquitetural. Este documento define
vocabulário e limites lógicos; não escolhe framework, protocolo ou persistência.

## Princípios

1. O nome no código deve representar a linguagem usada pelo negócio em
   português, mesmo que classes e APIs permaneçam em inglês.
2. Cada entidade tem uma única fonte de verdade e um responsável pelo estado.
3. Estado é explícito; valores nulos não representam estados implícitos.
4. Transições são comandos auditáveis, autorizados e idempotentes quando
   repetição for possível.
5. Todo dado de negócio pertence a um tenant. Identificadores externos nunca
   substituem o identificador interno.
6. Integrações publicam fatos do próprio domínio e não escrevem diretamente no
   estado privado de outro domínio.

## Contextos delimitados

| Contexto | Responsabilidade | Entidades principais | Não deve possuir |
| --- | --- | --- | --- |
| Identity & Access | identidade, credenciais, sessão e autorização | User, Role, Session, Device | cadastro operacional de moradores |
| Tenancy | administradoras, condomínios, unidades e vínculo organizacional | Tenant, Condominium, Unit, Membership | senha, sessão ou cobrança |
| Operation | visitas, checklists, demandas e execução diária | Visit, Checklist, Task, Demand, Evidence | conteúdo editorial |
| Finance | carteiras, lançamentos, faturas e aprovações | Wallet, Entry, Invoice, Approval | autenticação ou documento operacional |
| Documents | documentos, versões, validade e assinaturas | Document, DocumentVersion, Requirement | arquivos sem metadados de negócio |
| Service Desk | chamados, comentários, SLA e notificações do atendimento | Ticket, Comment, SLA | agenda operacional genérica |
| Content | páginas, artigos, mídia e publicação | Page, Post, MediaAsset | operação condominial |
| Communication | mensagens, templates, entregas e preferências | Message, Delivery, Template | decisão do estado de origem |
| Audit | fatos imutáveis de segurança e negócio | AuditEvent | edição retroativa de eventos |

`ERP`, `Operation`, `Studio`, `Residents` e `Web` são superfícies de aplicação,
não domínios. Uma superfície pode orquestrar mais de um contexto sem transferir
a propriedade dos dados.

## Atores e escopo

- **Platform operator:** configura a plataforma, sem acesso implícito aos dados
  de todos os tenants.
- **Tenant administrator:** administra usuários e políticas do próprio tenant.
- **Manager/operator:** executa rotinas autorizadas para sua carteira.
- **Condominium representative:** síndico ou conselho com poderes explícitos.
- **Resident:** acessa apenas vínculos e unidades autorizados.
- **Service account:** integração identificável, com escopo mínimo e rotação.

Toda autorização combina `actor_id`, `tenant_id`, capacidade e, quando
necessário, `resource_id`. Nível numérico de usuário não é regra de domínio.

## Entidades, valores e identificadores

- **Entidade:** possui identidade e ciclo de vida (`Visit`, `Unit`, `User`).
- **Objeto-valor:** imutável e comparado pelo valor (`DocumentNumber`,
  `Money`, `EmailAddress`, `DateRange`).
- **Agregado:** fronteira de consistência; apenas sua raiz aceita comandos.
- **Serviço de domínio:** regra que não pertence naturalmente a uma entidade.
- **Evento de domínio:** fato no passado, imutável e com versão de esquema.

Convenções de nomes:

- classes e tipos no singular (`Condominium`, não `Condominiums`);
- comandos no imperativo (`ScheduleVisit`, `ApproveInvoice`);
- eventos no passado (`VisitScheduled`, `InvoiceApproved`);
- consultas descrevem intenção (`FindOpenDemandsByCondominium`);
- status usam `snake_case` persistido e enum/tipo no código;
- IDs internos são opacos; CPF, CNPJ, e-mail e número da unidade são atributos;
- tabelas usam plural `snake_case`; chaves usam `<entity>_id`;
- datas persistidas em UTC e apresentadas no fuso do tenant.

Nomes legados `App*`, `Connect` e versões de controllers continuam válidos
apenas como compatibilidade. Código novo não amplia essa convenção.

## Estados e transições

Todo agregado ativo expõe `status`, `created_at`, `updated_at` e, quando
aplicável, `closed_at`/`archived_at`. Exclusão lógica usa estado explícito e não
se confunde com encerramento do fluxo.

Exemplo de visita:

```text
draft → scheduled → in_progress → completed
              ↘ cancelled      ↘ cancelled
```

- somente `scheduled` pode iniciar;
- conclusão exige checklist obrigatório resolvido ou exceção justificada;
- cancelamento registra ator, instante e motivo;
- repetir o mesmo comando com a mesma chave idempotente retorna o resultado já
  produzido, sem duplicar evidências, tarefas ou notificações;
- transição inválida falha sem alterar parcialmente o agregado.

Cada contexto deve publicar sua própria tabela de transições antes da futura
implementação. Novos estados exigem migration compatível e plano de rollback.

## Entradas, saídas e erros

Entradas são normalizadas na borda, mas validadas pelo domínio. Saídas não
expõem modelos de persistência diretamente. Erros usam categorias estáveis:

- `validation_error`: entrada inválida, com campos seguros;
- `unauthenticated`: identidade ausente ou inválida;
- `forbidden`: identidade válida sem capacidade;
- `not_found`: recurso inexistente ou invisível ao ator;
- `conflict`: versão, unicidade ou transição incompatível;
- `rate_limited`: limite excedido, com instante seguro para nova tentativa;
- `dependency_unavailable`: integração indisponível, sem perder o comando.

Mensagens internas não devem vazar segredos, SQL, caminhos ou existência de
recursos de outro tenant.

## Segurança, auditoria e multi-tenancy

As ameaças transversais e prioridades iniciais estão registradas no
[`threat model`](../security/threat-model.md), sujeito à revisão da Issue #7.

- `tenant_id` é obrigatório em entidades tenant-scoped e filtros são aplicados
  no repositório, não apenas no controller.
- Operações privilegiadas falham fechadas quando a política está ausente.
- Eventos de auditoria registram ator, tenant, ação, alvo, resultado, instante,
  correlation ID e metadados não sensíveis.
- Credenciais, tokens e documentos pessoais não entram em logs ou eventos.
- Automações registram regra, versão, origem, responsável e próxima ação; toda
  ação automática relevante possui compensação ou procedimento de reversão.
- Concorrência usa versão otimista ou outra garantia declarada por agregado.

## Critérios testáveis para futuras implementações

Para cada agregado ou fluxo, a Issue executável deve conter:

1. cenário feliz cobrindo todas as transições permitidas;
2. tentativa de transição inválida sem efeitos parciais;
3. autorização positiva e negativa por papel/capacidade;
4. isolamento entre dois tenants com IDs distintos;
5. repetição da mesma chave idempotente sem duplicação;
6. concorrência entre duas versões do mesmo agregado;
7. evento de auditoria para sucesso e falha relevante;
8. indisponibilidade de dependência com retry/compensação definidos.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão necessária |
| --- | --- | --- | --- |
| Idioma do código | tipos em inglês, glossário em português | tradução inconsistente | validar termos com produto |
| IDs | opacos, sem formato físico definido | migração futura | Issue de estratégia de IDs |
| Consistência | agregados pequenos | transações entre contextos | Issue de eventos internos |
| Legado | adaptadores preservam nomes `App*` | vazamento para código novo | regra automatizada futura |
| Tenant | escopo obrigatório | tabelas globais excepcionais | especificação de tenancy |

## Pontos abertos

- confirmar o glossário e os limites com a auditoria funcional da Superlógica;
- decidir formato e geração de IDs na Issue #2;
- aplicar a proposta detalhada em
  [`identifier-strategy.md`](identifier-strategy.md) após revisão da Issue #2;
- revisar o contrato proposto em [`api-strategy.md`](api-strategy.md) na
  Issue #3;
- revisar a proposta de eventos em
  [`internal-events-strategy.md`](internal-events-strategy.md) na Issue #13 e
  detalhar consistência entre contextos na Issue #40;
- detalhar autenticação/autorização nas Issues #23–#39;
- catalogar tabelas legadas que ainda misturam contextos.

## Gate para sair do Discovery

- revisão por produto e engenharia;
- glossário aprovado sem termos ambíguos críticos;
- limites de contexto associados ao backlog;
- riscos e pontos abertos convertidos em Issues rastreáveis;
- nenhuma decisão física tratada como definitiva sem ADR específica.
