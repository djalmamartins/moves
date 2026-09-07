# MOVES Design System — protótipo

Este diretório é o ambiente isolado de validação visual da primeira entrega. Ele não depende do backend nem altera estilos dos ambientes ERP, Studio ou Operação.

## Foundations oficiais

- Cor primária CONNECT: `#C5A131` (`rgb(197, 161, 49)`).
- Fonte de verdade: `default/assets/tokens.css`.
- Catálogo navegável: `default/foundations.html`.
- Componentes consomem aliases semânticos (`--ms-color-primary`, `--ms-color-surface`, `--ms-color-text`) em vez da paleta bruta.
- Escala espacial baseada em 4 px.
- Tema escuro é aplicado por `html[data-theme="dark"]`.
- Movimento reduzido respeita `prefers-reduced-motion`.

Abra `default/foundations.html` diretamente no navegador para revisar os tokens em temas claro e escuro.

## Shell oficial do ERP

Abra `default/shell.html` para revisar o shell isolado: navegação lateral, identificação do ambiente, seletor “Ambientes MOVES”, busca rápida por `⌘/Ctrl + K`, tema claro/escuro e menu móvel. O conteúdo é demonstrativo e não chama o backend.

## Regra de adoção

Esta entrega permanece restrita a `/prototype`. A integração com layouts de produção só pode ocorrer depois do gate de revisão visual e funcional da Epic #78.
