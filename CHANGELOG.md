# Changelog

## 2026-08-31 — EST-001

- Adicionado baseline estrutural versionado e sem dados de aplicação.
- Adicionado manifesto determinístico com fingerprint por tabela.
- Adicionados os modos seguros `install` e `verify` ao runner de migrations.
- `baseline` agora exige confirmação explícita e recusa schemas divergentes.
- Instalação em banco não vazio e `apply` em banco vazio são recusados.
- Adicionado lock de execução para impedir concorrência entre migrations.
- Documentada a operação em banco vazio, clone existente e produção.

## Unreleased

- Migração física das interfaces para `container/apps`, `container/web` e
  `container/mail`.
- Centralização de schema e migrations em `storage/database`.
- Organização de commands, jobs e workers em `service`.
- Migração dos controladores para `source/Controllers` e do Minify para
  `source/Services/Minify`.
- Resolução canônica de temas com compatibilidade para identificadores antigos.
- Remoção de uploads e imagens de runtime do versionamento.
