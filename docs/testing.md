# Testes

A suíte usa um banco isolado cujo nome obrigatoriamente termina em `_test`.

```bash
composer test
composer test:unit
composer test:integration
```

O preparador cria o banco e o schema de teste; nunca aponte essas variáveis para
produção. Além da suíte PHP, faça smoke test das rotas públicas, autenticação,
assets do Organic V2 e áreas Studio/Web afetadas pela mudança.

O contrato detalhado de isolamento, fixtures, estados, critérios negativos e
evidências está em [`testing/base-test-contract.md`](testing/base-test-contract.md).
