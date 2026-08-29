# API

Este diretório é o ponto público reservado para contratos e documentação da
API do Moves. A versão atual não registra endpoints HTTP de API; Web, Studio,
ERP e Residents são carregados pelo `index.php`.

Novos endpoints devem manter os controladores em `source/Controllers/Api` e
registrar suas rotas explicitamente, sem duplicar regras de negócio aqui.
