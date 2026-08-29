# Changelog

## Unreleased

- Migração física das interfaces para `container/apps`, `container/web` e
  `container/mail`.
- Centralização de schema e migrations em `storage/database`.
- Organização de commands, jobs e workers em `service`.
- Migração dos controladores para `source/Controllers` e do Minify para
  `source/Services/Minify`.
- Resolução canônica de temas com compatibilidade para identificadores antigos.
- Remoção de uploads e imagens de runtime do versionamento.
