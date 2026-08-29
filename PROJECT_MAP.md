# Mapa do projeto MovesOS

> Inventário revisado em 15/08/2026. A marcação descreve o estado desta rodada de trabalho; como esta pasta não possui Git, ela não substitui um histórico de versões.

## Legenda

- 🟢 criado nesta implementação
- 🟡 alterado nesta implementação
- ⚪ existente e necessário
- 🔴 candidato à remoção, sujeito a backup e validação final

## Raiz

```text
erp/
├── ⚪ index.php                 entrada e registro das rotas
├── ⚪ .htaccess                 reescrita de URLs e servidor
├── ⚪ composer.json/lock        dependências PHP
├── ⚪ README.md                 documentação original
├── 🟢 PROJECT_MAP.md            este inventário
├── 🟢 database/                 migrações reproduzíveis
│   └── migrations/
│       ├── 20260815_notifications_audit.sql
│       └── 20260815_movesos_access_control.sql
├── ⚪ api/                      integração/API pública
├── ⚪ organic/                  biblioteca visual e scripts compartilhados
├── ⚪ storage/                  uploads, cache, logs e certificados
├── ⚪ vendor/                   dependências do Composer
├── ⚪ container/                telas, temas e layouts
└── ⚪ source/                   regras de negócio, modelos e controladores
```

## Interface (`container`)

```text
container/
├── themes/
│   ├── ⚪ connect_by_moves/     site institucional ativo
│   └── ✅ web_by_moves/         removido e enviado para a Lixeira
├── studio/
│   ├── ⚪ moves_studio/         painel administrativo /studio
│   │   ├── 🟡 default.php       rotas, incluindo notificações
│   │   ├── 🟡 _studio.php       menu e central no cabeçalho
│   │   ├── 🟡 assets/css/admin.css
│   │   └── 🟢 widgets/notifications/home.php
│   ├── ⚪ app_connect/          aplicativo do usuário ativo
│   ├── ⚪ connect/              ERP ativo no banco
│   ├── ✅ erp_connect/          variante antiga removida
│   ├── ✅ erp_by_moves/         variante antiga removida
│   └── ✅ studio_connect/       pasta vazia removida
└── send/                        templates/rotinas de envio
```

## Aplicação (`source`)

```text
source/
├── Boot/                       configuração, constantes e helpers
├── Core/
│   └── 🟡 Model.php            auditoria automática em criar/alterar/excluir
├── Models/
│   ├── Banking/                financeiro
│   ├── Brief/                  depoimentos/briefings
│   ├── Corporation/            condomínios e organizações
│   ├── Erp/                    entidades do ERP
│   ├── Faq/                    perguntas frequentes
│   ├── 🟡 Notification/
│   │   ├── Notification.php    entrega por usuário
│   │   ├── 🟢 NotificationCategory.php
│   │   ├── 🟢 NotificationMessage.php
│   │   └── 🟢 AuditLog.php
│   ├── Post/                   páginas, artigos e categorias
│   ├── Report/                 acessos e relatórios
│   ├── Session/                sessões e logs
│   ├── Settings/               configurações gerais
│   ├── Slide/                  destaques
│   └── Talk/                   contatos e conversas
├── Public/
│   ├── Api/                    controladores de API
│   ├── App/                    aplicativo do usuário
│   ├── Erp/                    ERP administrativo/condominial
│   ├── Pay/                    pagamentos
│   ├── 🟡 Studio/Studio.php    gestão de conteúdo e notificações
│   └── Web/                    site institucional
├── Support/
│   ├── 🟢 Audit.php            trilha central, máscara de dados e aviso ao master
│   └── 🟢 Access.php           perfis, permissões e exceções individuais
└── Minify/                     minificação de recursos
```

## Limpeza realizada

Removidos após conferência de referências:

- arquivos `.DS_Store` — metadados do macOS, sem função na aplicação;
- `default.php`, `default.php.bak` e `default.php.old.php` — páginas antigas da hospedagem;
- `composer.phar` — cópia local desnecessária, pois o Composer do ambiente já é utilizado;
- backups `.before-codelab` em `source/Boot/` — cópias sem referências no autoload ou runtime.

Itens legados removidos após conferência de referências:

- ✅ `container/themes/web_by_moves/`;
- ✅ `container/studio/erp_connect/`;
- ✅ `container/studio/erp_by_moves/`;
- ✅ `container/studio/studio_connect/`;
- ✅ código JavaScript legado de notificações em `moves_studio/assets/js/scripts.js`.

Os arquivos foram enviados para `/Users/djalmamartins/.Trash/connect-cleanup-20260815-notifications` e podem ser recuperados enquanto a Lixeira não for esvaziada.

Não foram removidos `vendor`, `storage`, `organic`, `source`, o tema ativo
`connect_by_moves`, o app ativo `app_connect`, o ERP ativo `connect`, os dados
de `database/`, os testes, PDFs gerados ou backups de banco.

## Nome da plataforma

Nome definido: **MovesOS** — plataforma que reúne Site, Studio, App e ERP.
