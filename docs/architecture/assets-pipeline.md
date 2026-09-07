# Pipeline único de assets

A AST-001 oficializa `service/commands/build-assets.php` como único ponto de
compilação dos temas Web, ERP, Moradores e Studio. Os builders antigos não são
mais carregados pelo autoload e nenhuma requisição HTTP escreve bundles.

## Comandos

- `composer assets:build`: gera todos os bundles e publica somente arquivos cujo
  conteúdo mudou.
- `composer assets:check`: gera em arquivos temporários e falha se algum bundle
  versionado estiver desatualizado; não altera os alvos.
- `composer build:studio`: alias compatível que gera apenas o Studio.
- `php service/commands/build-assets.php erp`: gera somente o escopo informado;
  valores válidos são `web`, `erp`, `residents`, `studio` e `all`.

## Determinismo e segurança

As fontes descobertas por padrão glob são ordenadas lexicalmente. Dependências
compartilhadas possuem ordem explícita. Cada bundle é produzido ao lado do alvo
e publicado por troca atômica, evitando arquivos parciais. O modo `--check` é
executado na CI depois da instalação das dependências.

Qualquer alteração em CSS ou JavaScript fonte deve ser acompanhada por
`composer assets:build`. Um segundo `composer assets:build` precisa informar
todos os bundles como inalterados e `composer assets:check` precisa terminar com
código zero e `git diff` limpo.
