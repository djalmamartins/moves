# MOVES Design System — protótipo

Este diretório é o ambiente isolado de validação visual da primeira entrega. Ele não depende do backend nem altera estilos dos ambientes ERP, Studio ou Operação.

## Foundations oficiais

- Cor primária CONNECT: `#C5A131` (`rgb(197, 161, 49)`).
- Fonte de verdade: `default/assets/tokens.css`.
- Catálogo de foundations: `default/foundations.html`.
- Catálogo navegável de componentes e estados: `default/components.html`.
- Prontuário da unidade: `default/prontuario-unidade.html`, com resumo cadastral, vínculos, documentos, financeiro e histórico filtrável.
- Componentes consomem aliases semânticos (`--ms-color-primary`, `--ms-color-surface`, `--ms-color-text`) em vez da paleta bruta.
- Escala espacial baseada em 4 px.
- Tema escuro é aplicado por `html[data-theme="dark"]`.
- Movimento reduzido respeita `prefers-reduced-motion`.

Abra `default/foundations.html` para revisar os tokens ou `default/components.html` para testar componentes, estados, navegação por teclado, modal e feedback em temas claro e escuro.

## Regra de adoção

Esta entrega permanece restrita a `/prototype`. A integração com layouts de produção só pode ocorrer depois do gate de revisão visual e funcional da Epic #78.
