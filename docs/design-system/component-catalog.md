# Catálogo de componentes MOVES

Os componentes modernos compartilhados vivem em `container/shared/assets/css/moves-components.css`. Studio e Operation carregam essa folha diretamente; classes legadas continuam válidas durante a migração gradual.

## Convenções

- Prefixo público: `moves-`.
- Cor primária oficial: `#C5A131`.
- Todo controle interativo possui foco visível, altura mínima de 42 px e estado desabilitado.
- Feedback não depende apenas de cor: combine badge/alerta com texto e, quando útil, ícone.
- Modal usa `.moves-overlay > .moves-modal`; drawer usa `.moves-drawer` e precisa de botão de fechar, Escape e gerenciamento de foco no JavaScript consumidor.

## Exemplos

```html
<article class="moves-card">
  <header class="moves-card__header"><h2>Visita semanal</h2><span class="moves-badge moves-badge--success">Concluída</span></header>
  <div class="moves-card__body"><button class="moves-button moves-button--primary">Abrir visita</button></div>
</article>
```

```html
<form class="moves-form">
  <div class="moves-field-group">
    <label for="assunto">Assunto</label>
    <input class="moves-field" id="assunto" aria-describedby="assunto-ajuda">
    <small class="moves-field-help" id="assunto-ajuda">Descreva o resultado esperado.</small>
  </div>
</form>
```

```html
<div class="moves-table-wrap"><table class="moves-table"><thead><tr><th>Condomínio</th><th>Status</th></tr></thead><tbody><tr><td>Solar</td><td><span class="moves-badge moves-badge--warning">Atenção</span></td></tr></tbody></table></div>
```

## Inventário

| Grupo | Classes principais |
|---|---|
| Ações | `moves-button`, `moves-button--primary`, `moves-button--danger`, `moves-icon-button` |
| Dados | `moves-card`, `moves-table`, `moves-table-wrap`, `moves-badge` |
| Formulários | `moves-form`, `moves-field-group`, `moves-field`, `moves-field-help` |
| Feedback | `moves-alert`, `moves-empty`, `moves-skeleton`, `moves-toast` |
| Overlay | `moves-overlay`, `moves-modal`, `moves-drawer` |

Novos componentes entram neste catálogo antes de serem copiados para uma tela. Variações específicas de domínio devem compor essas classes, sem redefinir tokens globais.
