# Estado atual de contratos, documentos e aprovações

Inventário inicial da ERP-003, executado sem alterar o banco principal.

## Conclusão

A geração oficial `Erp/Connect` não possui rotas, controllers, serviços, models
ou views para contratos e aprovações. O fluxo `Erp/V1/Condominium::documents`
é legado congelado e não pode ser reativado. Também não existe migration
rastreada em `main` para `erp_contracts`, `erp_documents` e
`erp_approval_steps`; a implementação precisa incluir schema idempotente antes
de conectar a interface.

## Matriz atual

| Fluxo | Persistência rastreada | Backend Connect | Interface oficial | Estado |
|---|---|---|---|---|
| Contratos | ausente | ausente | ausente | não implementado |
| Documentos vinculados | ausente | ausente | ausente | não implementado |
| Etapas de aprovação | ausente | ausente | ausente | não implementado |
| Documentos legados do condomínio | tabelas legadas | somente V1 congelado | sem rota oficial | referência apenas |

## Ciclo alvo

1. Criar contrato em rascunho, sempre limitado ao condomínio ativo.
2. Anexar metadados de documentos ao contrato sem expor caminhos privados.
3. Enviar o contrato para aprovação e criar etapas ordenadas.
4. Permitir decisão apenas sobre a primeira etapa pendente aplicável.
5. Ativar o contrato somente quando todas as etapas forem aprovadas; qualquer
   rejeição mantém histórico, autor, data e justificativa.
6. Exibir vencimentos e estado do ciclo na interface oficial do ERP.

## Regras mínimas

- Toda consulta e mutação exige `condominium_id` explícito.
- Aprovação e rejeição são transacionais e não podem ser repetidas.
- A sequência de aprovação não pode ser ignorada.
- Documentos pertencem ao mesmo condomínio e entidade do vínculo.
- Nenhuma migration ou dado será aplicado diretamente em `moves_db`.
- Testes usarão exclusivamente o banco descartável e IDs diferentes do usuário
  principal.

## Sequência de implementação

1. Adicionar migration idempotente e schema equivalente no bootstrap de testes.
2. Criar serviço transacional para contratos, documentos e decisões.
3. Cobrir isolamento, ordem, aprovação integral e rejeição.
4. Registrar rotas e telas em `Erp/Connect`.
5. Validar a interface autenticada e preparar a PR para revisão.

## Primeiro corte implementado

- Migration idempotente rastreia contratos, documentos e etapas ordenadas.
- Serviço cria contratos e documentos sempre no escopo do condomínio.
- Envio e decisão são transacionais; a ordem dos aprovadores é obrigatória.
- Aprovação integral ativa o contrato e rejeição preserva nota e data da decisão.
- Rotas `/erp/contracts` e `/erp/contracts/{id}` usam exclusivamente
  `Erp/Connect`, com criação, documentos, submissão e decisão protegidos por CSRF.
- A submissão aceita somente usuários ativos existentes; a própria atribuição da
  etapa concede o escopo de decisão, sempre combinado ao condomínio da sessão.
- A superfície exige `erp.contracts.manage`; decisões exigem adicionalmente
  `erp.contracts.approve`. Perfis administrativos recebem ambas e gestores
  recebem apenas gerenciamento por padrão.
