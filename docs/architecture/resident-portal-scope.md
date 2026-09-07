# Escopo oficial do portal de moradores

Decisão da APP-001: `/app` é o portal de autosserviço de moradores, síndicos e
conselheiros. Ele consome dados publicados pelo ERP e pelo módulo Operacional,
mas não replica telas administrativas nem concede acesso direto às tabelas de
gestão.

## Estado encontrado

- O contrato público possui apenas entrada, dashboard, logout, aceite de cookie
  e uma rota de permissões.
- O dashboard atual contém valores estáticos e links para `/`.
- A única permissão específica é `app.access`.
- Não há serviços dedicados nem vínculo de unidade aplicado às consultas.
- Documentos, boletos, comunicados, reservas e ocorrências ainda não possuem
  rotas oficiais no portal.

## Perfis e limites

| Perfil | Escopo | Pode fazer | Não pode fazer |
|---|---|---|---|
| Morador | suas unidades e conteúdo publicado | consultar, baixar, reservar e abrir ocorrência | administrar condomínio ou dados de terceiros |
| Síndico | condomínio selecionado | ações do morador e visão consolidada publicada | operar financeiro, contratos ou visitas pelo portal |
| Conselheiro | condomínio selecionado, leitura ampliada | consultar prestações e documentos autorizados | editar lançamentos ou publicar conteúdo |
| Funcionário/terceiro | somente recursos explicitamente delegados | consultar tarefas ou documentos liberados | navegar dados gerais de moradores |

O vínculo usuário–condomínio–unidade deve ser a origem do escopo. `app.access`
autoriza a entrada, nunca substitui o filtro de tenant e unidade.

## Mapa de telas, rotas e API

As rotas de página são HTML autenticado. As APIs usam JSON, CSRF em mutações e
o mesmo serviço de domínio da página correspondente.

| Capacidade | Tela | Rota de página | API planejada | Dados de origem |
|---|---|---|---|---|
| Início | resumo pessoal | `GET /app/dash/home` | `GET /app/api/v1/summary` | agregados publicados |
| Documentos | lista e detalhe/download | `GET /app/documents[/{id}]` | `GET /app/api/v1/documents[/{id}]` | documentos com visibilidade residents/public |
| Boletos | lista, detalhe e segunda via | `GET /app/billing[/{id}]` | `GET /app/api/v1/billing[/{id}]` | cobranças da unidade do usuário |
| Comunicados | caixa e leitura | `GET /app/communications[/{id}]` | `GET /app/api/v1/communications[/{id}]` | comunicados publicados e audiência |
| Reservas | agenda e solicitações | `GET/POST /app/reservations` | `GET/POST /app/api/v1/reservations` | áreas, disponibilidade e reservas |
| Ocorrências | lista, abertura e conversa | `GET/POST /app/occurrences[/{id}]` | `GET/POST /app/api/v1/occurrences[/{id}]` | solicitações vinculadas ao morador/unidade |
| Perfil | dados e preferências | `GET/POST /app/profile` | `GET/PATCH /app/api/v1/profile` | usuário autenticado e vínculos |

Downloads devem usar um endpoint autorizado com identificador opaco; caminhos
de armazenamento nunca aparecem na resposta ou no HTML.

## Permissões

| Permissão | Morador | Síndico | Conselheiro | Funcionário |
|---|:---:|:---:|:---:|:---:|
| `app.access` | ✓ | ✓ | ✓ | delegado |
| `app.documents.view` | próprias/publicadas | condomínio | condomínio | delegado |
| `app.billing.view` | próprias | condomínio | leitura | — |
| `app.communications.view` | audiência | condomínio | condomínio | delegado |
| `app.reservations.create` | próprias | próprias | próprias | delegado |
| `app.occurrences.create` | próprias | condomínio | próprias | delegado |
| `app.condominium.summary` | — | ✓ | ✓ | — |

Permissões ampliam capacidades dentro de um vínculo existente; jamais ampliam
o tenant. Overrides `deny` continuam tendo precedência.

## Contratos de segurança

1. Toda consulta recebe `user_id`, `condominium_id` e, quando aplicável,
   `unit_id` resolvidos no servidor; nenhum deles é aceito do formulário.
2. Identificadores fora do escopo retornam 404 para não revelar existência.
3. Mutações exigem CSRF, validação de estado e idempotência quando repetíveis.
4. Arquivos passam por endpoint de download autorizado e trilha de auditoria.
5. Dados administrativos, notas internas e documentos `internal/managers` não
   entram no portal do morador.
6. O dashboard não calcula valores a partir de mocks nem consulta tabelas sem
   filtro de vínculo.

## Estados e experiência

Cada tela deve prever carregamento, vazio, erro recuperável, acesso negado e
sucesso. A navegação móvel é prioritária; ações críticas exibem consequência e
confirmação no próprio componente, sem `alert()` ou links vazios.

## Sequência de entrega

1. Criar vínculo canônico usuário–condomínio–unidade e testes de isolamento.
2. Criar `ResidentPortalService` somente leitura para resumo, documentos,
   boletos e comunicados.
3. Substituir o dashboard estático e registrar navegação real.
4. Implementar reservas com concorrência e idempotência.
5. Implementar ocorrências com histórico e anexos seguros.
6. Publicar APIs versionadas reutilizando os mesmos serviços.

## Critério de aceite técnico

O mapa acima é o contrato de produto da primeira versão. Uma capacidade só pode
ser marcada pronta quando página, API, permissão, isolamento e estados de tela
estiverem cobertos. Recursos fora deste mapa exigem nova Issue e decisão de
escopo.
