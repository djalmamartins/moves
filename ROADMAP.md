# ROADMAP MOVESOS

O roadmap é organizado por dependência técnica. Novas funcionalidades permanecem congeladas até concluir a Fase 0.

## FASE 0 — Estabilização crítica

- EST-001: baseline e migrations — integrada pelo PR #69.
- SEC-001: settings e ACL — em revisão na branch `codex/SEC-001-settings-acl`.
- Garantir backup/clone e preservar usuário ID 1.
- Saída: deploy repetível, `db:status` limpo e acesso do ID 2 previsível.

## FASE 1 — Segurança e testes de fumaça

- SEC-002: recuperação segura.
- TST-001: rotas autenticadas — em revisão na branch `codex/TST-001-auth-smoke`.
- LEG-001: models inválidos que podem causar fatal — integrada pelo PR #70.
- Saída: autenticação/ACL cobertas e inventário executável.

## FASE 2 — Limites arquiteturais

- ARC-001: separar Operation do Studio.
- ARC-002: serviço comum de Help Desk.
- ERP-001: escolher geração ERP oficial.
- DOC-001: catálogo do banco.
- Saída: módulos com ownership, rotas e dependências claros.

## FASE 3 — Base visual e assets

- AST-001: pipeline único.
- EDT-001: Organic Editor único.
- UX-001: catálogo de componentes.
- PERF-001: orçamento de bundles.
- Saída: um padrão de frontend reutilizável.

## FASE 4 — Operation

- OPR-003: dashboard isolado.
- OPR-001: E2E de visitas.
- OPR-002: checklists completos.
- TST-002: CRUDs críticos.
- Saída: fluxo operacional demonstrável com dados reais de homologação.

## FASE 5 — ERP administrativo

- ERP-002: financeiro.
- ERP-003: contratos/documentos/aprovações.
- Integração de condomínios, usuários e fornecedores.
- Saída: núcleo administrativo reconciliado e auditável.

## FASE 6 — Moradores

- APP-001: escopo/API.
- Implementar por dependência: identidade → condomínio/unidade → documentos/comunicados → financeiro → solicitações/reservas.
- Saída: portal com permissões e APIs testadas.

## FASE 7 — Studio, conteúdo e comunicação

- Consolidar páginas, posts, mídia, notificações, agenda, propostas e Help Desk.
- WEB-001 e OBS-001.
- Saída: CMS sem duplicação no Operation e conteúdo público governado.

## FASE 8 — UX, acessibilidade e integrações

- UX-002, UX-003 e EXT-001.
- Testes de teclado, contraste, tablet/celular, câmera, localização e offline.
- Saída: matriz visual aprovada e dependências externas controladas.

## FASE 9 — Limpeza e distribuição

- CLEAN-001 após rastreamento e aprovação.
- Remoção gradual de legado coberto por testes.
- CI: lint, PHPUnit, migrations em banco vazio e clone, build e smoke tests.
- Saída: pacote distribuível e documentação atualizada.

## Marcos de decisão

1. Nenhuma feature antes da Fase 0.
2. Nenhuma remoção de legado antes das Fases 1–2.
3. Nenhuma expansão de ERP antes de escolher a geração oficial.
4. Nenhuma duplicação visual nova após a Fase 3.
5. ID 1 nunca é usado em testes destrutivos; ID 2 é o operador de homologação.
