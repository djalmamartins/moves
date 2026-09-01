# Recuperação segura de senha

O fluxo usa tokens aleatórios de 256 bits. Somente o SHA-256 do token é persistido em
`password_reset_tokens`; o valor original aparece apenas no link enviado ao usuário.

Regras aplicadas:

- validade de 30 minutos;
- um novo pedido revoga tokens anteriores ainda ativos;
- cada token pode ser consumido somente uma vez;
- três emissões por usuário ou IP a cada hora;
- atualização da senha e consumo do token na mesma transação;
- resposta neutra para identidades inexistentes, evitando enumeração de contas;
- códigos legados de ativação permanecem isolados e não são aceitos na recuperação.

A migration `20260901_secure_password_resets.sql` é aditiva. Antes de aplicá-la fora de testes, execute o
fluxo oficial de backup e migrations. Nenhum dado do usuário ID 1 é necessário para validar esta função.
