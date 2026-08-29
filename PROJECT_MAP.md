# Mapa do projeto Moves

> Arquitetura canônica revisada em 29/08/2026.

```text
erp/
├── api/                         ponto reservado para a API pública
├── container/
│   ├── apps/
│   │   ├── studio/default/      painel administrativo
│   │   ├── erp/default/         aplicação ERP interna
│   │   └── residents/default/   aplicação de moradores
│   ├── web/
│   │   ├── default/             site institucional
│   │   └── support/             central de suporte
│   ├── mail/default/            templates de e-mail
│   └── shared/                  dependências visuais compartilhadas
├── organic/                     distribuição oficial Organic V2
├── service/
│   ├── commands/                manutenção explícita
│   ├── jobs/                    tarefas operacionais pontuais
│   └── workers/                 processos contínuos e filas
├── source/
│   ├── Boot/                    ambiente, constantes e helpers
│   ├── Core/                    infraestrutura da aplicação
│   ├── Models/                  domínio e persistência
│   ├── Controllers/                  controladores HTTP
│   ├── Services/Minify/         geração de bundles por contexto
│   └── Support/                 serviços de apoio
├── storage/
│   ├── backups/                 cópias locais, ignoradas pelo Git
│   ├── cache/                   cache de runtime
│   ├── database/                migrations e suporte de schema
│   ├── logs/                    logs de runtime
│   ├── output/                  arquivos gerados
│   ├── sessions/                sessões isoladas
│   ├── temp/                    temporários
│   └── uploads/                 uploads privados
├── tests/                       testes unitários e de integração
└── vendor/                      dependências instaladas pelo Composer
```

O entrypoint HTTP é `index.php`. Os identificadores antigos de temas são
aceitos somente como aliases de configuração; não correspondem mais a árvores
físicas paralelas. Arquivos gerados e credenciais ficam fora do versionamento.
