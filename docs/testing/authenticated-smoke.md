# Smoke autenticado

Execute `composer test:smoke` para validar as rotas principais de menu de Studio, ERP e App.

O comando cria um banco descartável cujo nome termina em `_test`, carrega o baseline, cadastra somente
o usuário ID 2, prepara ACL e sessão isoladas, inicia um servidor PHP local e realiza requisições GET
autenticadas. São aceitos HTTP 200 e redirects legítimos; redirects para 403/404/500 e loops para a
própria rota falham o teste. O banco é removido no bloco `finally`, inclusive quando há falha.

A matriz cobre 24 rotas:

- Studio: dashboard, páginas, blog, mídia, usuários, FAQs, suporte, agenda, chamados, relatórios, versões e configurações;
- ERP: dashboard, cadastros, usuários, financeiro, condomínio e unidades;
- App: dashboard do morador.

O comando recusa bancos sem o sufixo `_test` e nunca autentica ou altera o usuário ID 1.
