# Publicação do MovesOS

## Requisitos obrigatórios

- PHP 8.2 ou superior com PDO MySQL, mbstring, intl, curl, fileinfo, GD, OpenSSL e ZIP.
- HTTPS ativo antes do primeiro login.
- Banco e usuário exclusivos para produção.
- Pastas `storage/images`, `storage/files`, `storage/logs` e `storage/cache` graváveis pelo usuário do PHP, sem permissão global 777.
- Document root com `mod_rewrite` e `mod_headers` ativos.

## Variáveis de ambiente

Configure fora do repositório:

```text
MOVESOS_DB_HOST=localhost
MOVESOS_DB_USER=usuario_do_banco
MOVESOS_DB_PASS=senha_forte
MOVESOS_DB_NAME=banco_movesos
MOVESOS_ENV=production
```

As credenciais antigas precisam ser revogadas, pois já estiveram gravadas no código.

## Tarefas agendadas

Execute a cada minuto, ajustando o caminho absoluto do projeto:

```text
* * * * * MOVESOS_ENV=production /usr/bin/php /caminho/do/projeto/bin/process-mail-queue.php >> /caminho/privado/movesos-mail.log 2>&1
```

## Antes de liberar tráfego

1. Importar o banco e executar as migrations dos módulos.
2. Testar SMTP e o envio de uma proposta com PDF.
3. Confirmar backup automático do banco e de `storage/` fora do servidor.
4. Conferir `/`, `/solicite-sua-proposta`, `/suporte`, `/studio/login`, erros 403/404/500 e recuperação de senha.
5. Trocar todas as senhas temporárias e revogar credenciais antigas.
6. Manter `display_errors=Off` e registrar erros somente no Log do MovesOS.
