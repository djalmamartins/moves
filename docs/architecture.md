# Arquitetura do Moves

`source/` concentra regras de aplicação, modelos, suporte e controladores.
`api/` é reservado a interfaces e integrações. Processos CLI, filas e rotinas
de manutenção pertencem a `service/`.

A apresentação vive em `container/`: `studio/` reúne aplicações autenticadas,
`themes/` atende Web e suporte público, `send/` contém e-mails sem JavaScript e
`shared/` contém dependências visuais comuns.

Cada theme usa `layouts`, `pages`, `components` e `assets`. A distribuição em
`organic/` é gerada pelo Organic V2; os bundles não são editados manualmente.
Arquivos gerados pertencem a `storage/`. Migrations SQL permanecem em
`database/` porque são código reproduzível e versionável.
