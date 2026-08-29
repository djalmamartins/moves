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
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.example
DB_HOST=localhost
DB_PORT=3306
DB_NAME=banco_movesos
DB_USER=usuario_do_banco
DB_PASS=senha_forte
```

Os nomes `MOVESOS_*` continuam aceitos somente por compatibilidade. Novas instalações
devem usar as variáveis canônicas acima. Nunca envie o arquivo `.env` ao Git.

Para habilitar o Banco Inter, gere um novo aplicativo/segredo no Internet Banking,
revogue a credencial que anteriormente esteve no código e configure também
`INTER_CLIENT_ID`, `INTER_CLIENT_SECRET`, `INTER_CERT_PATH` e `INTER_KEY_PATH`.
Certificado e chave devem ficar fora do diretório público e legíveis apenas pelo
usuário do PHP.

## Tarefas agendadas

Execute a cada minuto, ajustando o caminho absoluto do projeto:

```text
* * * * * APP_ENV=production /usr/bin/php /caminho/do/projeto/service/workers/process-mail-queue.php >> /caminho/privado/movesos-mail.log 2>&1
```

## Procedimento de implantação

1. Salvar backup do banco, de `storage/` e do `.env` atual.
2. Publicar o commit aprovado pelo CI em um novo diretório de release.
3. Executar `composer install --no-dev --prefer-dist --optimize-autoloader`.
4. Restaurar o `.env` externo e apontar `storage/` persistente para a release.
5. Executar as migrations aplicáveis e conferir suas cópias de segurança.
6. Trocar o link/diretório ativo de forma atômica e reiniciar o PHP quando necessário.
7. Executar os testes rápidos abaixo; em caso de falha, voltar à release anterior.

## Antes de liberar tráfego

1. Importar o banco e executar as migrations dos módulos.
2. Testar SMTP e o envio de uma proposta com PDF.
3. Confirmar backup automático do banco e de `storage/` fora do servidor.
4. Conferir `/`, `/solicite-sua-proposta`, `/suporte`, `/studio/login`, erros 403/404/500 e recuperação de senha.
5. Trocar todas as senhas temporárias e revogar credenciais antigas.
6. Manter `display_errors=Off` e registrar erros somente no Log do MovesOS.
