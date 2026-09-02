# Fronteira de Agenda e Help Desk

ARC-002 extrai persistência e consulta de Agenda/Chamados dos controllers e das
views. Serviços em `Source\Services\ServiceDesk` recebem apenas dados, usuário e
conexão; ACL, caminhos, mensagens HTTP e renderização permanecem no ambiente
consumidor.

Estado da extração:

- `AgendaService`: criação, edição, exclusão, participantes e filtros mensais.
- Operation: já consome `AgendaService` com `operation.agenda.manage`.
- Studio e Operation: consomem o mesmo `AgendaService`, mantendo respectivamente
  `support.manage` e `operation.agenda.manage` nos controllers.
- `TicketService`: criação, edição, respostas, ações em lote, respostas rápidas
  e filtros extraídos sem dependência de views.
- Chamados: Studio e Operation consomem `TicketService`; o Operation possui
  controller, ACL, rotas e views próprios, sem herdar ações HTTP do Studio.
- Upload e entrega de notificações continuam nas bordas HTTP de cada ambiente;
  persistência, transições, respostas e consultas pertencem ao serviço.
