# STATUS GERAL

Versão atual: `VERSION_STUDIO` do ambiente / commit `daa7922`
Branch ativa: `codex/SEC-001-settings-acl`
Última auditoria: 31/08/2026

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
- SEC-001 em revisão: bootstrap idempotente de settings e ACL fail-closed implementados.
- GitHub Project: `MOVES — Desenvolvimento`; backlog sincronizado nas Issues #43–#68.
- Desenvolvimento de funcionalidades permanece congelado até concluir a estabilização.
- Operation possui implementações recentes ainda sem validação E2E autenticada.

## Validação atual

- EST-001: integrado e aprovado em banco vazio e clone descartável; repetição idempotente.
- SEC-001: 12 testes focados/231 asserções e suíte completa aprovados; migration validada com IDs descartáveis 1 e 2.
- Schema atual: fingerprint compatível em verificação somente leitura.
- PHPUnit global: suíte completa aprovada no banco automatizado isolado.

## Bloqueado

- Aplicação de migrations no banco principal continua condicionada a backup; nenhum DDL desta tarefa foi executado em `moves_db`.
- Auditoria visual interna completa requer sessão autenticada e dados de homologação.

## Próximas 10 tarefas

1. Revisar e integrar SEC-001 — settings e ACL.
2. TST-001 — smoke tests autenticados.
3. SEC-002 — recuperação de senha.
4. ARC-001 — separar Operation do Studio.
5. ARC-002 — extrair Help Desk.
6. ERP-001 — escolher geração ERP.
7. APP-001 — definir o portal de moradores.
8. AST-001 — pipeline de assets.
9. EDT-001 — Organic Editor único.
10. DOC-001 — catálogo do banco.

## Bugs críticos

- BUG-001: resolvido por EST-001/PR #69.
- BUG-002: corrigido por SEC-001; aguarda revisão.

## Próxima tarefa recomendada

**Revisar e integrar SEC-001; depois iniciar TST-001 com o usuário ID 2 em banco isolado.**

## Fontes oficiais

- `AUDITORIA-GERAL.md`
- `BACKLOG-GERAL.md`
- `ROADMAP.md`
- Este arquivo
