# Organic UI

Base visual interna do Moves Studio. Ela é incremental: classes `organic-*` convivem com o CSS legado até a migração de cada tela.

## Carregamento

O layout do Studio carrega `organic/css/organic.css` antes dos estilos legados. O arquivo usa `@import` nativo porque o projeto não possui bundler de front-end; não há dependência adicional.

## Tokens

Os tokens estão em `css/organic-tokens.css`. Use propriedades `--organic-*` para cores, espaçamento, tipografia, raios, sombras, transições e camadas. A cor principal é `--organic-primary` (`#6e00b3`).

## Layout e grid

```html
<main class="organic-container organic-stack organic-stack-lg">
  <section class="organic-grid organic-grid-3">
    <article>...</article>
  </section>
</main>
```

Use `organic-grid-auto` quando a quantidade de colunas puder ser fluida. Grades explícitas de 1 a 5 colunas se adaptam em tablet e mobile.

## Tipografia e utilitários

```html
<p class="organic-eyebrow">Sistema</p>
<h1 class="organic-page-title">Configurações</h1>
<p class="organic-text-muted">Controle a identidade do produto.</p>
```

Os helpers disponíveis são intencionalmente poucos: `organic-hidden`, `organic-flex`, `organic-grid`, `organic-w-full`, alinhamentos e gaps.

## Componentes

Use `organic-btn` com variantes `organic-btn-primary`, `organic-btn-secondary`, `organic-btn-outline`, `organic-btn-danger`, `organic-btn-ghost` e `organic-btn-link`; os tamanhos são `organic-btn-sm` e `organic-btn-lg`.

```html
<article class="organic-card organic-card-hover">
  <header class="organic-card-header"><h2 class="organic-card-title">Título</h2></header>
  <div class="organic-card-body">Conteúdo</div>
</article>
```

Formulários usam `organic-form`, `organic-form-group`, `organic-label`, `organic-input`, `organic-select`, `organic-textarea`, `organic-help` e `organic-error`. Tabelas usam `organic-table-wrapper` e `organic-table`; paginação usa `organic-pagination` e `organic-pagination-item`.

Há badges `organic-badge-*` (`primary`, `success`, `warning`, `danger`, `info`) e estados de feedback (`organic-alert`, `organic-empty-state`, `organic-spinner`, `organic-skeleton` e `Organic.Toast.show()`). Ionicons continuam sendo a biblioteca de ícones; `organic-icon` apenas normaliza o seu alinhamento.

## Interações

Os scripts em `organic/js/` expõem `Organic.Modal`, `Organic.Dropdown`, `Organic.Tabs`, `Organic.Tooltip`, `Organic.Toast`, `Organic.Carousel` e `Organic.Editor`. As APIs são acionadas com atributos `data-organic-*`, não exigem jQuery e convivem com os comportamentos legados até a tela correspondente ser migrada.

O carousel usa `data-organic-carousel`, `.organic-carousel-track` e controles `data-organic-carousel-prev`/`next`. Owl Carousel permanece intacto porque ainda é usado por outros produtos do repositório.

## Breakpoints

Os pontos oficiais são 480px, 640px, 768px, 1024px, 1280px e 1440px. Novas regras responsivas devem usar esses limites, salvo necessidade técnica justificada.
