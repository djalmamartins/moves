# CRUDs críticos

A cobertura integrada de `CriticalOperationCrudTest` usa exclusivamente o banco
automatizado definido por `MOVESOS_TEST_DB`. O cenário preserva um proprietário
descartável no ID 1 e executa as ações com um operador não developer no ID 2.

## Superfícies cobertas

- Usuários: criar, editar, filtrar/paginar e excluir.
- Agenda: criar, editar e excluir compromisso.
- Chamados: criar, editar, filtrar e encerrar.
- Condomínios: criar, editar e excluir.
- Demandas: criar, editar, filtrar/paginar e excluir.
- Visitas: criar e excluir, complementado pelo teste do workflow completo.
- Segurança: rejeição de escrita com CSRF inválido e mensagens JSON verificadas.

Execute:

```bash
composer test -- --filter CriticalOperationCrudTest
```

As migrations operacionais e de Help Desk são carregadas pelo bootstrap de
testes. Nenhuma migration ou limpeza é executada no banco principal.
