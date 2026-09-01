# STATUS GERAL

Versão atual: `VERSION_STUDIO` do ambiente / base `7857bac`
Branch ativa: `codex/ARC-001-operation-boundary`
Última auditoria: 01/09/2026

## Progresso geral

Arquitetura: **52%**  
Backend: **64%**  
Frontend: **66%**  
Banco: **61%**  
UX: **57%**  
Testes: **45%**  
Conclusão estimada ponderada: **58%**

## Indicadores

- Módulos/superfícies principais: 7
- Grupos funcionais inventariados: 35
- Rotas GET/POST declaradas: 371
- Tabelas: 93
- Testes: 47, com 167 asserções
- Telas/grupos funcionando: 10
- Telas/grupos parciais: 28
- Telas/grupos quebrados: 4
- Bugs: 2 P0, 7 P1, 6 P2, 2 P3
- Backlog oficial: 26 tarefas

## Em desenvolvimento

- EST-001 e LEG-001 integradas pelos PRs #69 e #70.
- SEC-001 integrada pelo PR #71.
- TST-001 integrada pelo PR #72.
- SEC-002 integrada pelo PR #73.
- BASE-001 integrada pela PR #75; ARC-001 em desenvolvimento para remover rotas e views CMS do Operation.
- GitHub Project: `MOVES — Desenvolvimento`; backlog sincronizado nas Issues #43–#68.
- Desenvolvimento de funcionalidades permanece congelado até concluir a estabilização.
- Operation possui workflow de visitas validado em banco automatizado isolado; os demais CRUDs aguardam TST-002.

## Validação atual

- EST-001: integrado e aprovado em banco vazio e clone descartável; repetição idempotente.
- SEC-001: integrada após CI verde; migration validada com IDs descartáveis 1 e 2.
- TST-001: integrada após 24 rotas autenticadas retornarem HTTP 200.
- SEC-002: integrada após CI verde no PR #73.
- BASE-001: 4 testes focados/13 asserções do workflow de visitas aprovados; sintaxe PHP de controllers, views, models e jobs aprovada.
- Schema atual: fingerprint compatível em verificação somente leitura.
- PHPUnit global: a execução agregada ainda é interrompida por testes legados de controllers que encerram o processo; validações focadas permanecem obrigatórias até a correção do runner.

## Bloqueado

- Aplicação de migrations no banco principal continua condicionada a backup; nenhum DDL desta tarefa foi executado em `moves_db`.
- Auditoria visual interna completa requer sessão autenticada e dados de homologação.

## Próximas 10 tarefas

1. Concluir ARC-001 — separar Operation do Studio.
2. TST-002 — E2E dos CRUDs críticos.
3. ARC-002 — extrair Help Desk.
4. ARC-002 — extrair Help Desk.
5. ERP-001 — escolher geração ERP.
6. APP-001 — definir o portal de moradores.
7. AST-001 — pipeline de assets.
8. EDT-001 — Organic Editor único.
9. DOC-001 — catálogo do banco.
10. UX-001 — catálogo de componentes.

## Bugs críticos

- BUG-001: resolvido por EST-001/PR #69.
- BUG-002: resolvido por SEC-001/PR #71.

## Próxima tarefa recomendada

**Concluir ARC-001 removendo a herança total do controller Studio; depois executar TST-002.**

## Fontes oficiais

- `AUDITORIA-GERAL.md`
- `BACKLOG-GERAL.md`
- `ROADMAP.md`
- Este arquivo
