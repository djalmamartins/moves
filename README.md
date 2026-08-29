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
api/          integrações e interfaces
container/    layouts, páginas, componentes e assets
organic/      distribuição oficial do Organic V2 usada pelo Moves
service/      workers, manutenção e automações CLI
source/       aplicação PHP
storage/      dados e arquivos gerados no ambiente
database/     schema e migrations versionáveis
tests/        testes unitários e de integração
```

Os themes usam `layouts/`, `pages/`, `components/` e `assets/`. Bibliotecas de
terceiros compartilhadas ficam em `container/shared/assets/vendor/`.

## Organic V2

```bash
cd ../organic-v2
npm ci && npm test && npm run build
cd ../erp
php service/sync-organic-v2.php ../organic-v2
```

Consulte [arquitetura](docs/architecture.md),
[desenvolvimento](docs/development.md) e [testes](docs/testing.md).
