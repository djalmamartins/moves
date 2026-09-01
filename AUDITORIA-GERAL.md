# AUDITORIA GERAL DO MOVESOS

Data: 31/08/2026  
Branch: `main`  
Commit de referência: `91d9a1f`  
Escopo: leitura de código, banco `moves_db`, rotas, assets, testes automatizados e navegação pública/sem autenticação.  
Regra da etapa: nenhuma funcionalidade, migration ou dado foi alterado por esta auditoria.

## 1. Resumo executivo

O projeto é um monólito PHP com sete superfícies: Site, Suporte público, Studio, Operation, Help Desk, ERP e Moradores. Há 371 declarações GET/POST, 93 tabelas, 43 foreign keys, 48 models e aproximadamente 194 arquivos de rota/view/layout. A aplicação inicializa e as páginas públicas testadas respondem; Composer é válido, todos os arquivos PHP passam no lint e a suíte atual passa com 47 testes e 167 asserções.

O estado não é, porém, de produto concluído. A estimativa global é **58%**. Site e núcleo do Studio possuem maior maturidade. Operation tem backend real, mas pouco dado e cobertura insuficiente. ERP/Moradores conservam arquitetura e componentes legados. O banco tem schema híbrido (`app_*`, `erp_*`, `studio_*`, `operation_*`) e a cadeia oficial de migrations não representa o schema real.

Os maiores riscos são:

1. **P0 — migrations inconsistentes:** 21 migrations são reportadas como pendentes em banco que já contém parte dessas alterações. `db:migrate` já falhou anteriormente em coluna duplicada. Publicar assim pode interromper o deploy.
2. **P0 — ativação/ACL sem configuração:** `settings` tem zero registros; `Access::moduleEnabled()` interpreta ausência como módulo desabilitado. Usuários não desenvolvedores podem perder acesso mesmo com role correta.
3. **P1 — Operation acoplado ao Studio:** `Operation extends Studio`, replica 165 rotas e expõe CMS, usuários, mídia, configurações e suporte sob `/operation`.
4. **P1 — teste funcional incompleto:** a suíte cobre serviços centrais, mas não navega CRUDs autenticados nem testa JavaScript, responsividade, recorrência completa, PDF e uploads ponta a ponta.
5. **P1 — legado/modelos inválidos:** há models para tabelas inexistentes e `Banking` contém `parent::__construct("app_banking", [id], ...)`, que usa constante indefinida.

Próxima tarefa recomendada: **EST-001 — estabilizar migrations e bootstrap de `settings`/ACL em clone do banco, antes de desenvolver qualquer tela.**

## 2. Arquitetura atual

Fluxo principal:

`index.php` → bootstrap/autoload → escolha de temas por `moves_container_path()` → inclusão de arquivos `default.php` → Router → Controller → Model/PDO → View PHP.

| Camada | Situação real |
|---|---|
| `api/` | Existe, mas não é a principal superfície dos módulos auditados. |
| `container/` | Contém temas, rotas, views e assets de Site, Studio, Operation, Help Desk, ERP e Moradores. |
| `organic/` | Biblioteca de componentes e editor próprios, com fontes e builds minificados. |
| `service/` | Comandos de deploy/migration/build e jobs operacionais. |
| `source/` | Controllers, Models, Core, Services e Support. |
| `storage/` | Banco/migrations, uploads, cache, logs, sessões e temporários. |
| `vendor/` | Dependências Composer versionadas no workspace. |
| Banco | MySQL/MariaDB, 93 tabelas, 43 FKs, sem views. |

### Arquitetura esperada versus encontrada

A raiz esperada existe, mas há itens adicionais: `.idea/`, `.vscode/`, `.phpunit.cache/`, `docs/`, `tests/`, `DEPLOY-SERVIDOR.txt` e `Arquivo.zip`. `docs/` e `tests/` são justificáveis; IDE, cache e ZIP são artefatos que não deveriam integrar distribuição. Há ainda ZIPs dentro de `container/apps` observados no worktree.

## 3. Estrutura e temas

| Tema/superfície | Diretório | Controller principal | Assets/build | Estado |
|---|---|---|---|---|
| Site | `container/web/default` | `Web\\Connect\\Web` | `style.css`, `scripts.js` gerados localmente | 🟡 |
| Suporte público | `container/web/support` | `Web\\Support\\Support` | assets próprios + widget | 🟡 |
| Studio | `container/apps/studio/default` | `Studio\\Studio` | `studio.min.css/js` por build explícito | 🟡 |
| Operation | `container/apps/operation/default` | `Operation\\Operation` herdando Studio | cópia de assets/vendor e build Studio | 🟡 |
| Help Desk | `container/apps/helpdesk/default` | métodos herdados do Studio | cópia quase integral de assets Studio | 🟡 |
| ERP | `container/apps/erp/default` | vários controllers `Erp\\Connect` | minifica durante acesso local | 🟡/🔴 |
| Moradores | `container/apps/residents/default` | `App\\Connect` | minifica durante acesso local | 🟡/🔴 |
| E-mail | `container/mail/default` | render por serviços Auth/Email | templates sem pipeline próprio | 🟡 |
| Erros | layouts por superfície | controllers correspondentes | herdados do tema | 🟡 |

### Minify

- Studio/Operation usam build explícito e previsível, mas o nome final é `studio.min.css/js`, divergindo da regra desejada `style.css/script.js`.
- ERP, Moradores e Web podem escrever builds automaticamente em localhost durante o bootstrap Composer.
- Operation, Studio e Help Desk carregam/copiavam bibliotecas Organic e assets similares; os minificados medem cerca de 346–382 KB de CSS e 164 KB de JS.
- Há 18 views byte a byte idênticas entre Studio e Operation, além de duplicação de vendor/editor, fontes e ícones.
- O layout ainda carrega TinyMCE e Organic Editor simultaneamente.

## 4. Mapa real de rotas

Total declarado: **371** operações HTTP.

| Superfície | GET/POST declarados | Prefixo | Arquivo |
|---|---:|---|---|
| Operation | 165 | `/operation` | `container/apps/operation/default/default.php` |
| ERP | 72 | `/erp` e aliases | `container/apps/erp/default/default.php` |
| Studio | 71 | `/studio` | `container/apps/studio/default/default.php` |
| Help Desk | 32 | `/helpdesk` | `container/apps/helpdesk/default/default.php` |
| Site | 13 | `/` | `container/web/default/default.php` |
| Moradores | 10 | `/app`/login | `container/apps/residents/default/default.php` |
| Suporte público | 8 | `/suporte` | `container/web/support/default.php` |

### Grupos de páginas/rotas

- Site: `/`, `/artigos`, `/artigos/{uri}`, `/solicite-sua-proposta`, `/politica-de-privacidade`, `/termos-de-uso`, login/recuperação.
- Studio: login, dashboard, busca, blog/categorias, páginas, mídia, usuários, FAQ, depoimentos, slides, propostas, notificações, agenda, chamados, suporte, relatórios, versões, logs e configurações.
- Operation nativo: dashboard, Meu Dia, realtime, agenda, condomínios, demandas, visitas/relatório, checklists, pendências, planos, equipamentos, desejos, orçamentos, fornecedores, pessoas, documentos e relatórios.
- Operation herdado: todo o conjunto CMS/Studio acima também foi republicado em `/operation`.
- Help Desk: dashboard, tickets, agenda, suporte, usuários, configurações, versões e logs.
- ERP: autenticação, dashboard, condomínios, usuários, cadastro, perfil, unidades, carteiras/faturas e financeiro.
- Moradores: autenticação e dashboard; o conjunto de rotas é pequeno frente ao layout esperado.

Aliases em inglês/português (`visits/visitas`, `demands/demandas`, `quotes/orcamentos`) aumentam compatibilidade, mas também duplicam superfície de manutenção.

## 5. Árvore real do menu

### Operation

- Operacional: Dashboard, Meu Dia, Agenda, Demandas, Visitas, Pendências, Checklists, Desejos dos moradores, Planos de ação, Equipamentos, Chamados.
- Gestão: Orçamentos, Documentos, Fornecedores, Moradores e Síndicos, Relatórios, Carteira de Condomínios.
- Sistema: Notificações, Configurações.
- Seletor de ambientes: Studio, Operacional, ERP, Help Desk, Moradores e Site, filtrado por ACL.

### Studio

Dashboard, Conteúdo/Blog, Páginas, Mídia, Usuários, FAQs/Suporte, Depoimentos, Slides, Propostas, Agenda, Chamados, Notificações, Relatórios, Versões, Logs e Configurações. Parte é ocultada por permissão.

### Help Desk

Fila de chamados, Base de conhecimento, Agenda da equipe, Usuários, Configurações, Versões e Log.

### ERP e Moradores

Usam sidebars/layouts legados fragmentados por controller. A navegação não compartilha o sistema visual Studio/Operation.

## 6. Matriz do sistema

Legenda: ✅ funcionando; 🟡 parcial; 🔴 não funcionando; ⚪ somente layout; ⚫ não implementado.

| Módulo | Tela/rota principal | Backend | Banco/dados | CRUD/ações | UX | Status | Prioridade |
|---|---|---|---|---|---|---|---|
| Site | `/` | OK | Real, mas métricas zeradas | leitura | consistente | ✅ | P2 |
| Site | `/artigos` | OK | tabela vazia | leitura | estado vazio | 🟡 | P2 |
| Site | proposta | OK | proposals com dados | criar | formulário real | ✅ | P2 |
| Site | privacidade/termos | OK | fallback no controller | leitura | OK | ✅ | P3 |
| Suporte público | `/suporte` | OK | tabelas vazias | busca/leitura | OK | 🟡 | P2 |
| Suporte público | widget/ticket | OK | tabelas vazias | criar/anexar | não testado autenticado | 🟡 | P1 |
| Autenticação | login | OK | users/sessions | login/logout | login abre | 🟡 | P0 |
| Autenticação | recuperação | OK | token em `users.forget` | reset | sem E2E de e-mail | 🟡 | P1 |
| ACL | roles/permissões | OK | 6 roles/22 permissões | gestão no Studio | `settings` vazio bloqueia módulos | 🔴 | P0 |
| Studio | dashboard | controller real | tabelas reais | leitura | dados podem zerar | 🟡 | P1 |
| Studio | usuários | real | users/ACL | CRUD, filtros, paginação | ampla | 🟡 | P1 |
| Studio | páginas | real | pages vazia | CRUD | Organic + fallback TinyMCE | 🟡 | P1 |
| Studio | posts/categorias | real | posts/categories vazias | CRUD/busca | editor integrado | 🟡 | P1 |
| Studio | mídia | real | filesystem | upload/filtro/exclusão | modal real | 🟡 | P1 |
| Studio | Organic Editor | JS real | conteúdo HTML em DB | editar/salvar | claro/escuro parcial | 🟡 | P1 |
| Studio | agenda | real | calendar vazia | CRUD | não validado autenticado | 🟡 | P1 |
| Studio | chamados | real | tabelas vazias | CRUD/mensagens/anexos | complexo | 🟡 | P1 |
| Studio | notificações | real | tabelas vazias | compor/fila/auditar | polling | 🟡 | P2 |
| Studio | propostas | real | 5 propostas/4 respostas | fluxo/PDF | real | ✅/🟡 | P2 |
| Studio | FAQs/suporte | real | tabelas vazias | CRUD | real | 🟡 | P2 |
| Studio | depoimentos | real | brief com 2 | CRUD | real | ✅/🟡 | P2 |
| Studio | slides | real | slides com 3 | CRUD | model alternativo inconsistente | 🟡 | P1 |
| Studio | relatórios | real | logs/relatórios | filtros | cores/componentes mistos | 🟡 | P2 |
| Studio | logs | real | app_log com dados | resolver/ignorar/lote | alert nativo restante | 🟡 | P1 |
| Studio | configurações | real | `settings` vazia | salvar | crítico sem registro base | 🔴 | P0 |
| Operation | `/operation/dash` | herdado do Studio | múltiplas tabelas | leitura | não é dashboard operacional puro | 🟡 | P1 |
| Operation | Meu Dia | real | agenda/visitas/tarefas | leitura/atalhos | layout específico | 🟡 | P1 |
| Operation | agenda | real | events/visits | CRUD/filtros/modos | sem E2E autenticado | 🟡 | P1 |
| Operation | demandas | real genérico | tabela vazia | CRUD/transições/tarefas | painel lateral | 🟡 | P1 |
| Operation | visitas | real | tabelas vazias | agendar/executar/finalizar | campo/offline | 🟡 | P1 |
| Operation | checklists | genérico | tabelas vazias | checklist sem editor de itens completo na mesma tela | simplificado | 🟡 | P1 |
| Operation | pendências | genérico | tabela vazia | CRUD | simplificado | 🟡 | P2 |
| Operation | planos/equipamentos/desejos | genérico | tabelas vazias | CRUD básico | mesma tela genérica | 🟡 | P2 |
| Operation | chamados | herdado | suporte vazio | CRUD | mistura Help Desk/Operation | 🟡 | P1 |
| Operation | orçamentos | real | tabelas vazias | CRUD/ofertas | parcial | 🟡 | P1 |
| Operation | documentos | real | tabela vazia | upload/exclusão | parcial | 🟡 | P1 |
| Operation | fornecedores/pessoas | genérico | tabelas vazias | CRUD | parcial | 🟡 | P1 |
| Operation | condomínios | genérico | 5 registros | CRUD | dados reais | 🟡 | P1 |
| Operation | relatórios | real | agregações | leitura | sem exportação/filtros completos | 🟡 | P2 |
| Help Desk | tickets | herdado Studio | tabelas vazias | CRUD | assets duplicados | 🟡 | P1 |
| Help Desk | agenda/suporte/logs | herdado | tabelas reais/vazias | parcial | tema próprio copiado | 🟡 | P2 |
| ERP | dashboard | legado real | `app_*` e `erp_*` | leitura | visual legado | 🟡 | P1 |
| ERP | condomínios/unidades | controllers reais | dados reais | CRUD | fragmentado | 🟡 | P1 |
| ERP | usuários | controllers reais | users/app_owner | CRUD | risco de caminhos duplicados | 🟡 | P1 |
| ERP | financeiro | controller parcial | muitas tabelas vazias | incompleto | legado | 🔴 | P1 |
| ERP | contratos/documentos/aprovações | schema existe | tabelas vazias | telas ausentes/incompletas | — | ⚫ | P1 |
| Moradores | login/dashboard | real | app_* | leitura | legado | 🟡 | P1 |
| Moradores | reservas, ocorrências, documentos | sem rotas suficientes | tabelas não dedicadas | ausente | — | ⚫ | P1 |

Resultado conservador por grupo de tela: **10 funcionando, 28 parciais, 4 quebradas, 1 somente layout/ausente e vários recursos de ERP/Moradores não implementados**. “Parcial” inclui telas com código real que não puderam ser validadas autenticadas ponta a ponta.

## 7. Banco de dados

### Inventário por domínio

| Domínio | Tabelas principais | Models | Observação |
|---|---|---|---|
| ACL/segurança | access_roles, access_permissions, vínculos, overrides, protected_users | Access/User | `system_protected_users` vazio; proteção ID 1 existe no Model/User. |
| Legado condominial | app_condominium, app_owner, app_units, app_wallets, app_invoices etc. | vários `Corporation/Erp` | maior parte do ERP/Moradores. |
| ERP novo | erp_contracts, suppliers, documents, entries, payments, meetings, approvals | sem models dedicados | tabelas vazias e telas incompletas. |
| Conteúdo | pages, posts, categories, slides, brief, FAQs | models correspondentes | pages/posts/categorias vazios; conteúdo público possui fallbacks. |
| Comunicação | notifications, messages, mail_queue, tickets | models parciais + PDO direto | muitas queries no controller Studio. |
| Operation | 31 tabelas `operation_*` | apenas 8 models simples | a maioria usa PDO direto no controller. |
| Observabilidade | app_log, audit_logs, report_access/online | models | `app_log` agrega logs antigos e novos. |
| Configuração | settings, modules, versions, migrations | Settings | `settings` e `modules` vazias. |

### Inconsistências

- Models apontam para tabelas inexistentes: `app_banking`, `slide`, `slide_categories` e `talk` não estão no banco atual.
- Existem dezenas de tabelas sem model; nem sempre é erro, mas Operation e ERP concentram regras e SQL em controllers.
- `AppCondominium` exige `condo_name`, enquanto migrations recentes usam nomes como `condominium_name`/`fantasy_name` em pontos distintos.
- 93 tabelas para apenas 2 usuários e muitos domínios vazios indicam schema adiantado em relação às telas.
- `movesos_schema_migrations` registra somente 9 migrations; 21 arquivos permanecem pendentes.
- Não há views de banco; relatórios repetem agregações SQL em controllers.

## 8. Segurança

### Pontos positivos

- ID 1 está protegido em `Model::delete()`, `Model::destroy()` e `User::destroy()`; há teste automatizado.
- Queries novas usam prepared statements na maior parte.
- CSRF aparece nas mutações principais e possui teste unitário.
- Upload valida tamanho e MIME real por `finfo`; imagens usam `getimagesize()`.
- Sessão é regenerada no login; cookies de e-mail são HttpOnly, SameSite=Lax e Secure em HTTPS.
- `.env` está com permissão `600` e não teve valores expostos nesta auditoria.
- Logs removem/redigem chaves sensíveis.

### Riscos

- Token de recuperação usa `md5(uniqid(rand(), true))`, inferior a `random_bytes()` e sem expiração evidente.
- `Access::legacy()` libera genericamente permissões para `level >= 5` quando ACL/tabela falha, um fail-open amplo.
- `moduleEnabled()` retorna falso quando `settings` não possui linha, bloqueando usuários legítimos.
- Operation usa permissões genéricas `studio.access` para alguns recursos e permissões Studio para chamados/configurações.
- Algumas views ainda usam HTML não escapado deliberadamente para conteúdo editorial; precisam de política de sanitização de HTML, não apenas confiança no editor.
- Recursos externos Ionicons, ViaCEP e Meta Pixel criam dependência de rede e política de privacidade/CSP.

## 9. Organic Editor

| Função | Estado | Evidência |
|---|---|---|
| Instalação/build | ✅ | fonte em `organic/editor`, cópias vendor por tema e minificados. |
| Carregamento Studio | ✅/🟡 | `assets/js/editor.js` importa bundle; layout também carrega TinyMCE. |
| Páginas/posts/slides | 🟡 | `data-organic-editor` presente; fallback remove atributo e inicializa TinyMCE. |
| Preview | 🟡 | plugin/código existe; não validado autenticado. |
| Claro/escuro/paper branco | 🟡 | CSS existe; validação visual interna bloqueada por login. |
| Ícones/templates/blocos | ✅ | plugins de ícone, workspace, tabela, mídia e especiais presentes. |
| Persistência no banco | 🟡 | conteúdo POST chega aos models; não houve CRUD autenticado nesta auditoria. |
| Geração HTML | ✅ | editor serializa HTML. |
| Integração única | 🔴 | três cópias do editor + TinyMCE coexistem. |

## 10. UX/UI

- Site público abriu sem erro de console próprio; houve apenas aviso externo do Meta Pixel.
- Título atual do site contém “Título alterado no teste”, sinal de dado de teste persistido em configuração/fallback.
- Métricas públicas mostram `+0`, `0%`, prejudicando credibilidade.
- Operation e Studio usam sistema visual moderno comum, porém com cópias divergentes de CSS.
- ERP e Moradores seguem layouts legados, com tabelas, botões e sidebars diferentes.
- Modais e drawers novos coexistem com `alert()`/`confirm()` nativos e views antigas de formulário em página.
- Há links `href="#"` em views legadas de eventos, resultados, usuários e FAQ.
- Estados vazios existem nas views recentes; módulos antigos frequentemente não os padronizam.
- Dark mode é tratado no Studio/Operation, mas não há evidência equivalente consistente em ERP/Web/Moradores.

## 11. Código legado, morto e duplicado

- `Arquivo.zip` na raiz e ZIPs do Studio no worktree.
- `.DS_Store` em `organic/`.
- Controllers duplicados `Erp/V1` e `Erp/Connect` com responsabilidades semelhantes.
- Views antigas `associations`, `events`, `results` existem em Studio/Operation sem rotas atuais correspondentes no menu.
- 18 views idênticas Studio/Operation; várias outras diferem apenas no prefixo de URL.
- Organic Editor copiado em Studio, Operation e Help Desk além da fonte em `/organic`.
- `Studio.php` possui 2.171 linhas e mistura autenticação, CMS, mídia, usuários, agenda, Help Desk, logs e configurações.
- `Operation.php` possui cerca de 500 linhas, parte em linhas extremamente longas, com SQL, upload, PDF, recorrência e apresentação.
- Assets antigos concatenados chegam a 475 KB de JS em ERP e 473 KB no Web.

## 12. Instruções aparentemente não implementadas

| Regra esperada | Situação atual | Arquivos | Impacto | Recomendação |
|---|---|---|---|---|
| Studio e Operation desvinculados | Operation herda Studio e replica CMS/rotas/assets | controllers e dois temas | alto acoplamento e mistura de telas | extrair serviços compartilhados e reduzir rotas Operation. |
| Um build previsível por tema | pipelines e nomes diferentes; alguns escrevem em localhost | `Services/Minify/*` | deploy inconsistente | comando único de build sem escrita em request. |
| Forms preferencialmente em modal | mistura de modal, drawer, lateral e páginas antigas | views diversas | UX inconsistente | definir padrão por tipo de fluxo. |
| Banco/migrations como fonte da verdade | 21 pendências com schema parcialmente aplicado | migrations/runner | risco de deploy | baseline verificado e migrations idempotentes. |
| Operation totalmente em tempo real | polling apenas em pontos; maioria recarrega/AJAX | layouts/controllers | expectativa não atendida | definir realtime real (SSE/WebSocket) ou renomear promessa. |
| Dados completos de operação | quase todas tabelas Operation vazias | banco | telas sem validação real | fixtures não destrutivas em ambiente de teste. |
| Organic único | cópias e TinyMCE coexistem | organic + assets dos temas | bugs divergentes | publicar um pacote/bundle compartilhado. |
| Proteção ID 1 persistente no banco | código protege exclusão, tabela de proteção vazia | Model/User/DB | defesa incompleta | popular e impor trigger/constraint onde viável. |

## 13. Bugs encontrados

| ID | Pri. | Descrição/rota | Causa provável | Impacto | Sugestão |
|---|---|---|---|---|---|
| BUG-001 | P0 | `composer db:migrate` não é confiável | 21 migrations não registradas e schema já alterado | deploy pode parar ou reaplicar DDL | reconstruir baseline em clone e testar ida/volta. |
| BUG-002 | P0 | usuários não developer podem perder acesso | tabela `settings` vazia + `moduleEnabled()` false | indisponibilidade de Studio/ERP/App | bootstrap obrigatório e default seguro explícito. |
| BUG-003 | P1 | Operation mistura CMS/Studio | herança total e 165 rotas | permissões e navegação incorretas | separar controllers por domínio. |
| BUG-004 | P1 | `Banking` pode gerar fatal | `[id]` usa constante indefinida e tabela ausente | módulo bancário quebra ao instanciar | corrigir model após decidir destino legado. |
| BUG-005 | P1 | models Slide/Talk apontam para tabelas ausentes | legado de nomes | rotas antigas quebram | mapear uso e migrar/remover futuramente. |
| BUG-006 | P1 | recuperação sem token forte/expiração evidente | md5/uniqid em `Auth` | risco de segurança | token aleatório hashado + validade + uso único. |
| BUG-007 | P1 | Dashboard Operation não é isolado | chama `parent::dash()` | informações Studio no operacional | dashboard/controller próprio. |
| BUG-008 | P1 | financeiro/contratos ERP incompletos | schema sem telas/serviços completos | função central ausente | implementar somente após estabilização. |
| BUG-009 | P1 | CRUDs internos não têm E2E | suíte testa classes/DB, não browser autenticado | regressões de botões/modais | testes HTTP/browser em banco isolado. |
| BUG-010 | P2 | título público contém texto de teste | configuração/fallback persistido | aparência não profissional | corrigir dado após aprovação. |
| BUG-011 | P2 | métricas públicas zeradas | ausência de fonte/configuração | baixa confiança | tratar vazio sem números falsos. |
| BUG-012 | P2 | vários `href="#"` e confirm/alert nativos | views legadas | ações confusas | inventariar e substituir por componente aprovado. |
| BUG-013 | P2 | editor duplicado e fallback duplo | Organic + TinyMCE + cópias | comportamento divergente | consolidar integração. |
| BUG-014 | P2 | aliases e rotas sem item `{id}` em tickets | rotas amplas herdadas | navegação/links ambíguos | contrato de rotas por módulo. |
| BUG-015 | P2 | pipeline de assets diverge por ambiente | minify automático em alguns temas | arquivos mudam ao navegar localmente | build CI explícito. |
| BUG-016 | P3 | aviso Meta Pixel no console | permissão de tráfego Meta | ruído e tracking inoperante | configurar/remover em desenvolvimento. |
| BUG-017 | P3 | cores/componentes antigos coexistem | CSS legado e moderno concatenados | inconsistência visual | catálogo de componentes e remoção futura. |

Contagem: **2 P0, 7 P1, 6 P2 e 2 P3**.

## 14. Dívida técnica

- Controllers gigantes e acoplados; lógica de negócio e SQL direto em controllers.
- Ausência de services/repositories para Agenda, Visitas, Demandas, ACL e Help Desk.
- Models Operation de uma linha, sem encapsular regras.
- Views com lógica, queries indiretas e JavaScript inline.
- CSS/JS duplicados e bundles grandes.
- Duas gerações ERP (`V1` e `Connect`) convivendo.
- Migrations sem baseline confiável.
- Testes não cobrem rotas autenticadas, HTML, JS, acessibilidade e responsividade.
- Documentação de arquitetura existe parcialmente, mas ainda não governa as implementações.

## 15. Testes realizados

| Teste | Resultado |
|---|---|
| `composer validate --no-check-publish` | OK |
| PHP lint em `source`, `container`, `api`, `service` | OK, nenhum erro sintático |
| PHPUnit completo | OK: 47 testes, 167 asserções |
| `deploy-check.php` | 0 erros, 2 avisos |
| Status das migrations | 21 pendentes |
| Banco | 93 tabelas, 43 FKs, 0 views |
| Usuário ID 1 | existente, confirmado, role developer, proteção em código |
| Usuário ID 2 | existente, confirmado, role client_admin |
| Rotas públicas principais | HTTP 200 |
| Rotas internas sem sessão | redirecionam para login |
| Navegador: site | abriu; sem erros JS próprios; aviso Meta externo |
| Navegador: Operation | redirecionou para login; login renderizou |

Não foram executados POSTs destrutivos nem login com credenciais. CRUDs autenticados, câmera, geolocalização, offline real, PDF e uploads não foram validados ponta a ponta nesta auditoria.

## 16. Riscos e pendências

- Não publicar migrations antes de criar clone/backup e baseline.
- Não confiar em percentuais visuais ou presença de view como prova de funcionalidade.
- Não remover legado antes de rastrear rotas, imports e dados.
- Não continuar expansão de Operation antes de separar ACL/configuração e testes.
- Criar conjunto pequeno de dados de homologação sempre com usuário ID 2.

## 17. Recomendações e ordem correta

1. Congelar features e corrigir baseline/migration/settings/ACL em ambiente clonado.
2. Criar smoke tests autenticados para todas as superfícies.
3. Definir limites entre Studio, Operation, Help Desk, ERP e Moradores.
4. Consolidar build e Organic Editor compartilhado.
5. Estabilizar autenticação, recuperação e usuários.
6. Concluir fluxos Operation com testes reais.
7. Escolher e migrar a geração ERP oficial; só então completar financeiro/contratos.
8. Completar Moradores com APIs/serviços definidos.
9. Padronizar UX e remover legado apenas após cobertura.

## 18. Respostas objetivas

1. **Onde estamos?** Monólito funcional em desenvolvimento, com núcleo maduro e módulos híbridos.
2. **O que funciona?** Site, autenticação básica, propostas e partes do Studio/Operation com backend real.
3. **O que está apenas desenhado?** Partes do ERP novo e funcionalidades esperadas de Moradores.
4. **O que está quebrado?** Pipeline de migrations, habilitação de módulos sem settings e models legados específicos.
5. **O que falta?** E2E autenticado, financeiro/contratos, portal completo e consolidação arquitetural.
6. **O que foi implementado errado?** Herança total Operation→Studio, DDL sem baseline e assets duplicados.
7. **O que foi duplicado?** Rotas, 18+ views, editor/vendor, fontes, CSS/JS e controllers ERP.
8. **O que remover futuramente?** ZIPs/caches, views sem rota, models/tabelas legados e cópias após rastreamento.
9. **Maiores riscos?** Deploy/migrations, ACL/configuração e regressões sem E2E.
10. **Próxima tarefa?** EST-001.
11. **Ordem correta?** Estabilização → segurança/testes → arquitetura compartilhada → módulos.
12. **Quanto concluído?** Estimativa conservadora: **58%**.

