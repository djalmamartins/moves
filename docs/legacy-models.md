# Models legados sem tabela

Inventário da tarefa LEG-001, realizado em 31/08/2026.

| Model | Situação | Decisão |
| --- | --- | --- |
| `Banking\Banking` | `app_banking` nunca existiu; nenhuma chamada ativa | Deprecado e bloqueado com mensagem que direciona para `Erp\AppWallet` ou `Banking\AppBankInter` |
| `Talk\Talk` | `talk` não existe; nenhuma chamada ativa | Deprecado e bloqueado explicitamente |
| `Slide\AppSlide` | Duplicava `AppSlides`, mas apontava para `slide` inexistente | Alias temporário de compatibilidade apontando para `slides` |
| `Slide\SlideCategory` | Duplicava `CategorySlide`; o banco atual usa `categories_slide` | Alias temporário de compatibilidade apontando para `categories_slide` |

As rotas ativas do Studio usam `AppSlides` e `CategorySlide`. Os aliases podem
ser removidos numa limpeza posterior após confirmar que não existem consumidores
externos do autoload público.
