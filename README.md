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
