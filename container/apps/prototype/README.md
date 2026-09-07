# MOVES Design System — protótipo

Este diretório é o ambiente isolado de validação visual da primeira entrega. Ele não depende do backend nem altera estilos dos ambientes ERP, Studio ou Operação.

## Foundations oficiais

- Cor primária CONNECT: `#C5A131` (`rgb(197, 161, 49)`).
- Fonte de verdade: `default/assets/tokens.css`.
- Catálogo de foundations: `default/foundations.html`.
- Catálogo navegável de componentes e estados: `default/components.html`.
- Cockpit contextual do condomínio: `default/cockpit.html`.
- Componentes consomem aliases semânticos (`--ms-color-primary`, `--ms-color-surface`, `--ms-color-text`) em vez da paleta bruta.
- Escala espacial baseada em 4 px.
- Tema escuro é aplicado por `html[data-theme="dark"]`.
- Movimento reduzido respeita `prefers-reduced-motion`.

Abra `default/foundations.html` para revisar os tokens, `default/components.html` para testar componentes ou `default/cockpit.html` para validar contexto, indicadores, prioridades e ações do condomínio em desktop e mobile.

## Regra de adoção

Esta entrega permanece restrita a `/prototype`. A integração com layouts de produção só pode ocorrer depois do gate de revisão visual e funcional da Epic #78.
