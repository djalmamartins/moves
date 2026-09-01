# Bootstrap de configurações e ACL

O MOVES trata configuração e autorização ausentes de forma segura:

- uma tabela `settings` vazia recebe uma única linha de defaults mínimos;
- Studio, ERP e App permanecem desabilitados até ativação explícita;
- Site e Suporte ficam disponíveis para permitir recuperação administrativa;
- permissões sem papel, vínculo ou cadastro correspondente são negadas;
- falhas de leitura da ACL são registradas e resultam em negação, sem fallback por `users.level`;
- a migration atribui papéis apenas a usuários ativos ainda sem vínculo, preservando atribuições existentes;
- o usuário ID 1 mantém o papel técnico `developer`; o ID 2 recebe somente o papel compatível com seu nível cadastrado.

O bootstrap é idempotente e não altera configurações ou papéis já existentes. Em instalações novas,
execute `php service/commands/database-migrate.php apply` antes de liberar rotas autenticadas.
