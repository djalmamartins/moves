# STATUS GERAL

Versão atual: `VERSION_STUDIO` do ambiente / commit `91d9a1f`  
Branch ativa: `codex/EST-001-migrations`  
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

- EST-001 em revisão: baseline estrutural, manifesto por fingerprint e runner protegido implementados.
- GitHub Project: `MOVES — Desenvolvimento`; backlog sincronizado nas Issues #43–#68.
- Desenvolvimento de funcionalidades permanece congelado até concluir a estabilização.
- Operation possui implementações recentes ainda sem validação E2E autenticada.

## Validação atual

- EST-001: aprovado em banco vazio e clone descartável; repetição idempotente.
- Schema atual: fingerprint compatível em verificação somente leitura.
- PHPUnit global: 47 testes executados; 2 erros, 3 falhas e 1 teste arriscado em banco novo, todos fora do escopo EST-001 (Auth/User/Help Desk).

## Bloqueado

- Aplicação do baseline no banco principal aguarda revisão/backup; nenhum DDL foi executado em `moves_db`.
- `settings` sem registro torna ativação de módulos inconsistente.
- Auditoria visual interna completa requer sessão autenticada e dados de homologação.

## Próximas 10 tarefas

1. Revisar e integrar EST-001 — baseline/migrations.
2. SEC-001 — settings e ACL.
3. TST-001 — smoke tests autenticados.
4. SEC-002 — recuperação de senha.
5. LEG-001 — models/tabelas inválidos.
6. ARC-001 — separar Operation do Studio.
7. ARC-002 — extrair Help Desk.
8. ERP-001 — escolher geração ERP.
9. AST-001 — pipeline de assets.
10. EDT-001 — Organic Editor único.

## Bugs críticos

- BUG-001: migrations podem falhar/reaplicar alterações.
- BUG-002: usuários legítimos podem perder acesso quando `settings` está vazia.

## Próxima tarefa recomendada

**Concluir revisão de EST-001 e iniciar SEC-001 — bootstrap seguro de settings e ACL.**

## Fontes oficiais

- `AUDITORIA-GERAL.md`
- `BACKLOG-GERAL.md`
- `ROADMAP.md`
- Este arquivo
