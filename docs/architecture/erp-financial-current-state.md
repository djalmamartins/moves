# Estado atual do financeiro ERP

Inventário inicial da ERP-002, executado sem alterar o banco principal.

## Conclusão

A interface atual lê apenas o legado `app_invoices` e `app_wallets`. O schema
canônico já contém `erp_financial_entries`, `erp_payments` e
`erp_bank_transactions`, mas essas tabelas não possuem models, serviços, rotas
ou telas conectadas. A tela de listagem de cobranças ainda exibe linhas estáticas.

## Fluxos

| Fluxo | Persistência | Backend | Interface | Estado |
|---|---|---|---|---|
| Resumo financeiro | `app_invoices` | `Connect\Finance::home` | `finance/home` | legado parcial |
| Contas a receber/pagar | `erp_financial_entries` | ausente | mock estático | desconectado |
| Pagamentos | `erp_payments` | ausente | ausente | desconectado |
| Extrato bancário | `erp_bank_transactions` | ausente | ausente | desconectado |
| Conciliação | transação + `matched_entry_id` | ausente | ausente | desconectado |
| Carteiras | `app_wallets` | `AppWallet` | seleção indireta | parcial |

## Sequência de implementação

1. Criar serviço transacional para lançamentos, pagamentos e totais por
   condomínio, sem depender de views.
2. Cobrir criação, edição, cancelamento, pagamento parcial/integral e
   reconciliação em banco isolado.
3. Conectar rotas e telas Connect ao serviço.
4. Substituir os totais baseados em `app_invoices` e retirar os mocks.
5. Migrar dados legados apenas em tarefa explícita, com backup e reconciliação;
   nenhum DDL/DML deve ser aplicado diretamente em `moves_db`.

## Regras mínimas de consistência

- Escopo obrigatório por `condominium_id` em toda consulta e mutação.
- Valores monetários persistidos como decimal; cálculos sem `float`.
- `paid_amount` deve equivaler à soma de pagamentos válidos.
- Status deriva dos valores e vencimento, não de entrada livre da interface.
- Uma transação bancária pode conciliar no máximo um lançamento e a operação
  precisa ser atômica e auditável.
