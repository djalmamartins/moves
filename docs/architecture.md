# Arquitetura do Moves

`source/` concentra regras de aplicação, modelos, suporte e controladores.
`api/` é reservado a interfaces e integrações. Processos CLI, filas e rotinas
de manutenção pertencem a `service/`.

A apresentação vive em `container/`: `studio/` reúne aplicações autenticadas,
`themes/` atende Web e suporte público, `send/` contém e-mails sem JavaScript e
`shared/` contém dependências visuais comuns.

Cada theme usa `layouts`, `pages`, `components` e `assets`. A distribuição em
`organic/editor/` é a distribuição canônica e compartilhada do Organic
Editor. Temas não mantêm cópias do editor: o pipeline incorpora o CSS canônico
no bundle visual e o bootstrap do Studio importa o módulo ESM por URL pública.
Páginas, artigos e templates usam namespaces independentes de persistência
(`page`, `post` e `template`, seguidos do ID); após o envio válido do formulário,
o autosave local deixa o registro salvo no servidor como fonte da verdade.
Arquivos gerados pertencem a `storage/`. Migrations SQL permanecem em
`database/` porque são código reproduzível e versionável.
