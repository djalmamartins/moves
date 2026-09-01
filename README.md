# Moves

Moves reúne Studio, Web, ERP e aplicações para residentes em uma mesma
fundação PHP. O Organic V2 é o design system oficial.

## Instalação local

Requer PHP 8.2+, PDO MySQL, Intl, MariaDB/MySQL e Composer 2.

```bash
cp .env.example .env
composer install
composer test
```

Configure o banco local no `.env`. Nunca versione esse arquivo, certificados,
uploads, logs, backups ou outros dados gerados em `storage/`.

## Publicação no servidor

O servidor deve apontar o DocumentRoot para a raiz deste projeto e permitir a
reescrita de URLs pelo `.htaccess`. Antes de liberar o acesso público:

```bash
composer install --no-dev --optimize-autoloader
composer build:studio
composer db:status
composer db:migrate
composer deploy:check
```

Use `APP_ENV=production`, `APP_DEBUG=false`, uma `APP_URL` HTTPS e credenciais
exclusivas do banco de produção. Os diretórios operacionais dentro de `storage/`
precisam ter permissão de escrita para o usuário do PHP, mas `.env`, certificados,
SQL e logs não devem ser servidos publicamente.

O comando `db:migrate` registra cada SQL em `movesos_schema_migrations` e nunca
reexecuta uma migration aplicada. Para um banco vazio, carregue o baseline
estrutural versionado (sem dados de aplicação):

```bash
php service/commands/database-migrate.php install --confirm-install
composer db:verify
composer db:status
```

Em instalação antiga, primeiro faça backup e valide um clone. O baseline só é
registrado quando o fingerprint do schema corresponde ao manifesto versionado:

```bash
php service/commands/database-migrate.php verify
php service/commands/database-migrate.php baseline --confirm-baseline
```

Em produção, `install` e `baseline` também exigem `--confirm-production`. O
exportador `database-schema-export.php --confirm-export` é destinado apenas à
criação revisada de uma nova versão de baseline; nunca deve ser usado como etapa
automática de deploy.

Faça backup do banco e de `storage/images`, `storage/files` e `storage/uploads`
antes de cada atualização. Depois da publicação, valide `/`, `/studio`,
`/helpdesk` e `/erp`, além do envio de arquivos e da fila de e-mails.

## Estrutura

```text
api/          ponto reservado para interfaces públicas
container/    apps, Web, Mail e recursos visuais compartilhados
organic/      distribuição oficial do Organic V2 usada pelo Moves
service/      commands, jobs e workers operacionais
source/       aplicação PHP
storage/      dados gerados e suporte de banco em storage/database
tests/        testes unitários e de integração
```

Os themes usam `layouts/`, `pages/`, `components/` e `assets/`. Bibliotecas de
terceiros compartilhadas ficam em `container/shared/assets/vendor/`.

## Organic V2

```bash
cd ../organic-v2
npm ci && npm test && npm run build
cd ../erp
php service/commands/sync-organic-v2.php ../organic-v2
```

Consulte [arquitetura](docs/architecture.md),
[desenvolvimento](docs/development.md) e [testes](docs/testing.md).
