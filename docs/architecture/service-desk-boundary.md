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
- Chamados: extração do workflow pendente; o adaptador Operation ainda é
  transitório e não deve receber novas funções.
