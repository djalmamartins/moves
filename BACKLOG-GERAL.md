# BACKLOG GERAL

Fonte: `AUDITORIA-GERAL.md`. Status inicial: `ABERTO`. Nenhuma tarefa abaixo foi implementada durante a auditoria.

## P0 — crítico

### EST-001 — Baseline e cadeia de migrations

- Módulo: Plataforma/Banco
- Problema: 21 migrations pendentes em schema parcialmente atualizado; execução pode falhar por DDL duplicado.
- Arquivos: `storage/database/migrations/*`, `service/commands/database-migrate.php`.
- Dependências: clone e backup verificado de `moves_db`.
- Critério de aceite: banco vazio e clone atual chegam ao mesmo schema; `db:status` retorna zero pendências; execução repetida é segura.
- Status: CONCLUÍDO — integrado pelo PR #69; validação em banco vazio e clone descartável concluída.

### SEC-001 — Bootstrap de settings e ACL

- Módulo: Segurança
- Problema: `settings` vazia bloqueia módulos para não developers.
- Arquivos: `source/Support/Access.php`, model Settings, migration de baseline.
- Dependências: EST-001.
- Critério de aceite: IDs 1 e 2 acessam apenas ambientes autorizados; ausência de configuração falha de modo documentado; testes cobrem o caso.
- Status: CONCLUÍDO — integrado pelo PR #71 após testes e CI verde.

## P1 — essencial

### SEC-002 — Recuperação de senha segura
- Módulo: Autenticação
- Descrição: substituir MD5/uniqid por token aleatório hashado, expirável e de uso único.
- Arquivos: `source/Models/Auth.php`, banco, templates de e-mail.
- Dependências: EST-001.
- Critério de aceite: expiração, revogação, rate limit e testes completos.
- Status: EM REVISÃO — tokens hashados, expiração, revogação, uso único e rate limit implementados na branch `codex/SEC-002-password-recovery`.

### TST-001 — Smoke tests autenticados
- Módulo: Qualidade
- Descrição: testar login ID 2, menus e GETs de cada ambiente em banco isolado.
- Arquivos: `tests/Integration`, configuração de teste.
- Dependências: SEC-001.
- Critério de aceite: todas as rotas de menu retornam 2xx/redirect esperado sem usar ID 1.
- Status: CONCLUÍDO — integrado pelo PR #72 após 24 rotas autenticadas e CI verde.

### TST-002 — E2E de CRUDs críticos
- Módulo: Qualidade
- Descrição: criar/editar/filtrar/paginar/excluir com ID 2 em Usuários, Agenda, Chamados, Demandas, Visitas e Condomínios.
- Dependências: TST-001.
- Critério de aceite: banco e interface verificados, incluindo CSRF e mensagens.
- Status: ABERTO

### ARC-001 — Separar Operation do Studio
- Módulo: Arquitetura
- Descrição: remover herança de controller total e republicação de CMS.
- Arquivos: controllers e rotas Studio/Operation.
- Dependências: TST-001.
- Critério de aceite: Operation expõe apenas rotas operacionais; serviços compartilhados não dependem de views.
- Status: ABERTO

### ARC-002 — Delimitar Help Desk
- Módulo: Arquitetura
- Descrição: extrair serviço de chamados/agenda do controller Studio.
- Dependências: ARC-001.
- Critério de aceite: Studio, Operation e Help Desk consomem serviço comum com ACL própria.
- Status: ABERTO

### ERP-001 — Escolher geração ERP oficial
- Módulo: ERP
- Descrição: decidir entre `Erp/V1` e `Erp/Connect`, mapear equivalências e congelar a outra.
- Dependências: TST-001.
- Critério de aceite: uma árvore oficial de rotas/controllers documentada.
- Status: ABERTO

### ERP-002 — Concluir financeiro
- Módulo: ERP
- Descrição: fechar lançamentos, pagamentos, transações, conciliação e relatórios.
- Dependências: ERP-001, EST-001.
- Critério de aceite: fluxos CRUD e totais reconciliados em testes.
- Status: ABERTO

### ERP-003 — Contratos, documentos e aprovações
- Módulo: ERP
- Descrição: conectar tabelas `erp_*` a telas e regras.
- Dependências: ERP-001.
- Critério de aceite: ciclo de contrato e aprovação rastreável.
- Status: ABERTO

### OPR-001 — Validar fluxo completo de visitas
- Módulo: Operation
- Descrição: E2E de agenda inteligente, check-in, checklist, evidência, ocorrência, assinatura, checkout e PDF.
- Dependências: TST-002.
- Critério de aceite: cenário real com ID 2 e dados descartáveis em DB de teste.
- Status: ABERTO

### OPR-002 — Editor completo de checklist
- Módulo: Operation
- Descrição: revisar criação/ordenação/frequência/evidência de itens por condomínio e tipo de visita.
- Dependências: OPR-001.
- Critério de aceite: checklist configurável sem SQL manual.
- Status: ABERTO

### OPR-003 — Dashboard operacional isolado
- Módulo: Operation
- Descrição: substituir `parent::dash()` por dados e view estritamente operacionais.
- Dependências: ARC-001.
- Critério de aceite: dashboard sem cards/rotas CMS.
- Status: ABERTO

### APP-001 — Definir escopo do portal de moradores
- Módulo: Moradores
- Descrição: especificar e conectar documentos, boletos, comunicados, reservas e ocorrências.
- Dependências: ERP-001.
- Critério de aceite: mapa de API, telas e permissões aprovado.
- Status: ABERTO

### LEG-001 — Corrigir models com tabelas inexistentes
- Módulo: Legado
- Descrição: Banking, SlideCategory, AppSlide e Talk.
- Dependências: inventário de uso.
- Critério de aceite: cada model aponta para tabela real ou é marcado para remoção sem chamadas ativas.
- Status: ABERTO

## P2 — importante

### AST-001 — Pipeline único de assets
- Módulo: Frontend
- Descrição: build explícito por tema, sem escrita durante request.
- Dependências: ARC-001.
- Critério de aceite: comando CI produz assets determinísticos e `git diff` limpo.
- Status: ABERTO

### EDT-001 — Consolidar Organic Editor
- Módulo: Studio
- Descrição: remover cópias por tema e fallback duplo não planejado.
- Dependências: AST-001.
- Critério de aceite: bundle único em páginas/posts/templates com testes de persistência.
- Status: ABERTO

### UX-001 — Catálogo de componentes
- Módulo: UX
- Descrição: padronizar botões, tabelas, cards, modal, drawer, formulário e feedback.
- Dependências: AST-001.
- Critério de aceite: documentação e exemplos usados pelos temas modernos.
- Status: ABERTO

### UX-002 — Remover links vazios e alertas nativos
- Módulo: UX
- Descrição: resolver `href="#"`, `alert()` e confirmações inconsistentes.
- Dependências: UX-001.
- Critério de aceite: nenhuma ação visível sem comportamento; feedback acessível.
- Status: ABERTO

### WEB-001 — Corrigir conteúdo público de teste
- Módulo: Site
- Descrição: título alterado em teste e métricas zeradas.
- Dependências: aprovação de conteúdo.
- Critério de aceite: título oficial e estados sem números artificiais.
- Status: ABERTO

### OBS-001 — Unificar logs e auditoria
- Módulo: Observabilidade
- Descrição: esclarecer papéis de `app_log`, audit e report tables.
- Dependências: EST-001.
- Critério de aceite: retenção, busca e incidentes documentados/testados.
- Status: ABERTO

### DOC-001 — Catálogo de tabelas e relações
- Módulo: Documentação
- Descrição: registrar owner, model/service, telas e retenção para as 93 tabelas.
- Dependências: EST-001.
- Critério de aceite: catálogo versionado e conferido por schema.
- Status: ABERTO

### PERF-001 — Reduzir bundles e dependências externas
- Módulo: Performance
- Descrição: medir e remover CSS/JS/vendor não utilizados por página.
- Dependências: AST-001.
- Critério de aceite: orçamento de tamanho e carregamento por superfície.
- Status: ABERTO

## P3 — melhoria

### CLEAN-001 — Higiene do repositório
- Módulo: Repositório
- Descrição: tratar ZIPs, caches, `.DS_Store` e arquivos IDE.
- Dependências: confirmação de que não são fontes de recuperação.
- Critério de aceite: distribuição sem artefatos locais; nada apagado sem revisão.
- Status: ABERTO

### UX-003 — Dark mode e responsividade globais
- Módulo: UX
- Descrição: matriz desktop/tablet/mobile e claro/escuro para todas as superfícies.
- Dependências: UX-001.
- Critério de aceite: screenshots/regressão nos breakpoints definidos.
- Status: ABERTO

### EXT-001 — Dependências externas e CSP
- Módulo: Segurança/Web
- Descrição: revisar Ionicons CDN, ViaCEP, Maps e Meta Pixel.
- Dependências: política de privacidade.
- Critério de aceite: CSP documentada, falhas externas tratadas e tracking por ambiente.
- Status: ABERTO

Total inicial: **26 tarefas** — 2 P0, 13 P1, 8 P2 e 3 P3.
