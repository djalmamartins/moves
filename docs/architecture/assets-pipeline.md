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
executado na CI depois da instalação das dependências. O comando carrega apenas
o compressor e o builder, sem inicializar configurações ou conexão de banco.

Qualquer alteração em CSS ou JavaScript fonte deve ser acompanhada por
`composer assets:build`. Um segundo `composer assets:build` precisa informar
todos os bundles como inalterados e `composer assets:check` precisa terminar com
código zero e `git diff` limpo.

## Orçamento de carregamento

`config/asset-budgets.php` limita o tamanho não comprimido de cada bundle inicial.
O build e a CI falham quando o limite é ultrapassado. Cada superfície carrega dois
bundles locais (um CSS e um JavaScript); fontes e ícones externos pertencem ao
trabalho de CSP e não entram neste orçamento.

| Superfície | CSS máximo | JS máximo | Requisições locais | Dependências pesadas |
|---|---:|---:|---:|---|
| Web | 60 KB | 210 KB | 2 | nenhuma |
| ERP | 170 KB | 480 KB | 2 | carrossel e gráficos |
| Moradores | 150 KB | 270 KB | 2 | carrossel |
| Studio | 300 KB | 180 KB | 2 | editor carregado separadamente quando necessário |

Highcharts foi removido dos bundles Web e Moradores por não haver consumidores.
O carrossel também foi removido do Web. Dependências específicas de página devem
ser adicionadas ao componente consumidor, nunca ao bundle global.
