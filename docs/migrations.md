# Migrations e baseline estrutural

## Regras de segurança

- Nunca execute `install` ou `baseline` antes de criar e validar backup/clone.
- `install` aceita somente banco sem tabelas de aplicação e exige `--confirm-install`.
- `baseline` não executa DDL: verifica o fingerprint e exige `--confirm-baseline`.
- Em produção, ambos exigem também `--confirm-production`.
- `apply` recusa banco vazio e usa lock para impedir duas execuções concorrentes.
- O baseline contém somente estrutura; dados iniciais e ACL pertencem a migrations/tarefas próprias.

## Banco vazio

```bash
php service/commands/database-migrate.php install --confirm-install
php service/commands/database-migrate.php verify
php service/commands/database-migrate.php status
```

## Banco existente

No clone restaurado do banco atual:

```bash
php service/commands/database-migrate.php verify
php service/commands/database-migrate.php baseline --confirm-baseline
php service/commands/database-migrate.php status
```

Se `verify` listar tabelas ausentes, extras ou alteradas, não registre o baseline.
Reconcilie a diferença em uma nova migration revisável.

## Novo baseline

O exportador é uma ferramenta deliberadamente manual e somente leitura no banco:

```bash
php service/commands/database-schema-export.php --confirm-export
git diff -- storage/database/baseline
```

Revise o SQL e o manifesto antes de commitá-los. Nunca exporte dados de aplicação.

## Evidência EST-001

Validado em 31/08/2026 com bancos descartáveis:

- instalação vazia: 92 tabelas, fingerprint compatível e zero pendências;
- segunda execução de `apply`: nenhuma migration reexecutada;
- clone estrutural: baseline recusado sem confirmação e aceito com confirmação;
- divergência proposital: tabela extra detectada e comando encerrado com erro;
- `moves_db`: apenas `verify`, sem DDL ou registro de baseline.

O PHPUnit global possui falhas preexistentes fora deste escopo no bootstrap de
Auth/User/Help Desk. Elas se repetem em banco de teste novo e devem ser tratadas
nas Issues de qualidade correspondentes.
