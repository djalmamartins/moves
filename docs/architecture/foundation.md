# MovesOS — Fundação técnica

## Identidade e autoload

O projeto é `djalmamartins/moves-os` (MovesOS). O namespace de aplicação é
`Source\\` para `source/`; testes usam `MovesOSTests\\` para `tests/`.

## Boot efetivo

```text
index.php / worker CLI
  → vendor/autoload.php
  → Boot/Config.php
  → Boot/Helpers.php
  → Boot/Connection.php (compatibilidade)
  → Boot/Settings.php
  → Boot/Minify/{Web,App,Studio}.php
  → Router e contextos Web, App e Studio
```

`Config.php` carrega o `.env` local quando presente e define ambiente e conexão.
Variáveis já definidas pelo processo têm precedência. `Settings.php` carrega apenas
configurações persistentes e administráveis depois que a conexão já existe.

## Ambientes

- `MOVESOS_ENV=testing` usa exclusivamente `MOVESOS_TEST_DB` (o nome precisa
  terminar em `_test`).
- Ambiente local usa o banco local por TCP, evitando diferença entre o socket
  do PHP CLI e o MySQL do XAMPP.
- Produção exige as quatro variáveis `MOVESOS_DB_*`; segredos não pertencem à
  tabela `settings`.

## Contextos

O entrypoint HTTP é `index.php`; os contextos registrados são Web, App e
Studio. O Router atual é preservado.

## Minify

Os pontos oficiais são `source/Boot/Minify/Web.php`, `App.php` e `Studio.php`.
Web e App mantêm os adaptadores em `source/Minify/`, que usam os componentes
MovesCode e referenciam os assets Organic. Esses assets permanecem separados
do núcleo da aplicação.

## Testes

`tests/prepare-environment.php` prepara o banco isolado antes da suíte. A
suíte inclui testes unitários e de integração, inclusive chamados, agenda,
notificações e fila de e-mails.

## Dependência legada removida

`organic/router` foi removido do lock de forma controlada. Não havia uso no
código, dependência transitiva registrada, nem requisito no `composer.json`.
O Router utilizado pelo projeto é `movescode/router`.
- Organic não foi alterado nesta etapa.
