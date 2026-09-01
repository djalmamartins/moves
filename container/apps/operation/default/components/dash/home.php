<?php $this->layout('layouts/studio'); ?>
<?php
$weekdays = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
$months = [1=>'janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
$todayLabel = $weekdays[(int)date('w')] . ', ' . date('d') . ' de ' . $months[(int)date('n')] . ' de ' . date('Y');
$firstName = html_entity_decode((string)$user->first_name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$nextEvent = $pendingTasks[0] ?? null;
$totalToday = (int)($appointmentsCount ?? 0);
$totalTasks = (int)($scheduledTasksCount ?? 0);
$totalWaiting = (int)($waitingThirdPartiesCount ?? 0);
$totalVisits = (int)($weeklyVisitsCount ?? 0);
$modules = [
 ['Visitas','Agenda e vistorias','/operation/visits','calendar-outline','blue'],
 ['Pendências','Problemas e não conformidades','/operation/issues','warning-outline','red'],
 ['Checklists','Rotinas de verificação','/operation/checklists','checkbox-outline','purple'],
 ['Planos de ação','Ações e responsáveis','/operation/action-plans','clipboard-outline','green'],
 ['Equipamentos','Inventário e manutenção','/operation/assets','construct-outline','cyan'],
 ['Condomínios','Carteira operacional','/operation/condominiums','business-outline','violet'],
];
$dashboardKpis = [['Demandas abertas',(int)($demandsOpen??0),'warning-outline','/operation/demandas'],['Chamados abertos',(int)($ticketsOpen??0),'headset-outline','/operation/chamados'],['Visitas hoje',$totalToday,'calendar-outline','/operation/visitas'],['Tarefas atrasadas',(int)($scheduledTasksCount??0),'time-outline','/operation/meu-dia'],['Orçamentos pendentes',(int)($quotesPending??0),'calculator-outline','/operation/orcamentos'],['Pendências críticas',(int)($criticalIssues??0),'alert-circle-outline','/operation/issues']];
?>
<section class="operation-dashboard-head">
  <div><h1>Controle de Operações</h1><p>Visão geral da operação dos condomínios · <?= htmlspecialchars(ucfirst($todayLabel)) ?></p></div>
  <div class="operation-head-actions"><a class="operation-icon-btn" href="<?= url('/operation/notifications') ?>" aria-label="Notificações"><ion-icon name="notifications-outline"></ion-icon></a><a class="operation-btn primary" href="<?= url('/operation/agenda') ?>"><ion-icon name="add-outline"></ion-icon>Nova visita</a></div>
</section>

<section class="operation-control-kpis"><?php foreach($dashboardKpis as [$label,$value,$icon,$href]): ?><a href="<?= url($href) ?>"><i><ion-icon name="<?= $icon ?>"></ion-icon></i><span><strong><?= $value ?></strong><small><?= $label ?></small></span></a><?php endforeach; ?></section>

<section class="operation-dashboard-summary">
  <article class="operation-next-card">
    <div class="operation-building-visual"><ion-icon name="business-outline"></ion-icon></div>
    <div><small>Próximo compromisso</small><h2><?= htmlspecialchars($nextEvent->title ?? 'Agenda livre') ?></h2><p><ion-icon name="calendar-outline"></ion-icon><?= htmlspecialchars($nextEvent->due ?? 'Nenhum compromisso futuro') ?></p><span><?= $nextEvent?'Programação confirmada':'Use a agenda para planejar o dia' ?></span></div>
    <div class="operation-next-actions"><a class="operation-btn primary" href="<?= url('/operation/agenda') ?>"><ion-icon name="play-outline"></ion-icon><?= $nextEvent?'Abrir compromisso':'Agendar horário' ?></a><a class="operation-btn" href="<?= url('/operation/agenda') ?>">Ver agenda</a></div>
  </article>
  <?php foreach ([['Visitas hoje',$totalToday,'Compromissos','calendar-outline','green','visits_today'],['Itens a verificar',$totalTasks,'Tarefas','checkmark-circle-outline','blue','checklist_pending'],['Pendências',$totalWaiting,'Abertas','warning-outline','orange','issues_open'],['Visitas na semana',$totalVisits,'Programadas','time-outline','red','weekly_visits']] as [$label,$value,$note,$icon,$tone,$realtimeKey]): ?>
  <article class="operation-summary-card <?= $tone ?>"><i><ion-icon name="<?= $icon ?>"></ion-icon></i><strong data-operation-count="<?= $realtimeKey ?>"><?= $value ?></strong><span><?= $label ?></span><small><?= $note ?></small></article>
  <?php endforeach; ?>
</section>

<section class="operation-dashboard-grid operation-control-grid">
 <article class="operation-dashboard-panel"><header><div><h2>Demandas recentes</h2><p>Necessidades operacionais cadastradas.</p></div><a href="<?= url('/operation/demandas') ?>">Ver todas</a></header><div class="studio-table-scroll"><table class="studio-table"><thead><tr><th>Protocolo</th><th>Condomínio</th><th>Demanda</th><th>Prioridade</th><th>Status</th></tr></thead><tbody><?php if(empty($recentDemands)): ?><tr><td colspan="5"><div class="studio-empty">Nenhuma demanda cadastrada.</div></td></tr><?php endif; ?><?php foreach(($recentDemands??[]) as $d): ?><tr><td><a href="<?= url('/operation/demandas/'.$d->id) ?>"><?= htmlspecialchars($d->protocol) ?></a></td><td><?= htmlspecialchars($d->condominium_name) ?></td><td><strong><?= htmlspecialchars($d->title) ?></strong><small><?= htmlspecialchars($d->assigned_name??'Sem responsável') ?></small></td><td><?= htmlspecialchars($d->priority) ?></td><td><?= htmlspecialchars(str_replace('_',' ',$d->status)) ?></td></tr><?php endforeach; ?></tbody></table></div></article>
 <article class="operation-dashboard-panel"><header><div><h2>Visão por condomínio</h2><p>Carteira que necessita atenção.</p></div><a href="<?= url('/operation/condominios') ?>">Abrir carteira</a></header><div class="operation-condo-attention"><?php if(empty($condominiumsAttention)): ?><div class="operation-empty"><strong>Nenhum condomínio requer atenção.</strong></div><?php endif; ?><?php foreach(($condominiumsAttention??[]) as $c): ?><a href="<?= url('/operation/condominios/'.$c->id) ?>"><i><ion-icon name="business-outline"></ion-icon></i><span><strong><?= htmlspecialchars($c->name) ?></strong><small><?= (int)$c->demands_open ?> demanda(s) · <?= (int)$c->issues_open ?> pendência(s) · <?= (int)$c->quotes_pending ?> orçamento(s)</small></span><ion-icon name="chevron-forward-outline"></ion-icon></a><?php endforeach; ?></div></article>
 <article class="operation-dashboard-panel"><header><div><h2>Atividade recente</h2><p>Últimas alterações da operação.</p></div></header><div class="operation-activity-list"><?php if(empty($recentActivity)): ?><div class="operation-empty"><strong>Nenhuma atividade registrada.</strong></div><?php endif; ?><?php foreach(($recentActivity??[]) as $a): ?><article><i></i><span><strong><?= htmlspecialchars($a->summary) ?></strong><small><?= htmlspecialchars($a->user_name??'Automação') ?> · <?= date('d/m H:i',strtotime($a->created_at)) ?></small></span></article><?php endforeach; ?></div></article>
</section>

<section class="operation-dashboard-grid">
  <article class="operation-dashboard-panel operation-modules"><header><div><h2>Módulos principais</h2><p>Acesse rapidamente as rotinas da operação.</p></div></header><div><?php foreach($modules as [$label,$description,$href,$icon,$tone]): ?><a href="<?= url($href) ?>"><i class="<?= $tone ?>"><ion-icon name="<?= $icon ?>"></ion-icon></i><span><strong><?= $label ?></strong><small><?= $description ?></small></span><ion-icon name="chevron-forward-outline"></ion-icon></a><?php endforeach; ?></div></article>
  <article class="operation-dashboard-panel operation-day-list"><header><div><h2>Meu dia</h2><p>Compromissos e atividades programadas.</p></div><a href="<?= url('/operation/agenda') ?>">Ver todos</a></header><div><?php if($dayAgenda): foreach($dayAgenda as $event): ?><a href="<?= url('/operation/agenda?event='.(int)$event->id) ?>"><time><?= htmlspecialchars($event->time) ?></time><i><ion-icon name="<?= $event->type==='meeting'?'business-outline':($event->type==='support'?'heart-outline':'checkbox-outline') ?>"></ion-icon></i><span><strong><?= htmlspecialchars($event->title) ?></strong><small><?= htmlspecialchars($event->description ?: ucfirst($event->type)) ?></small></span><ion-icon name="chevron-forward-outline"></ion-icon></a><?php endforeach; else: ?><div class="operation-empty"><ion-icon name="calendar-clear-outline"></ion-icon><strong>Nenhum compromisso hoje</strong><small>Seu dia está livre neste momento.</small></div><?php endif; ?></div><footer><a class="operation-btn full" href="<?= url('/operation/agenda') ?>"><ion-icon name="add-outline"></ion-icon>Novo compromisso</a></footer></article>
  <article class="operation-dashboard-panel operation-pending-list"><header><div><h2>Próximas atividades</h2><p>Itens que precisam de acompanhamento.</p></div><a href="<?= url('/operation/issues') ?>">Ver todas</a></header><div><?php if($pendingTasks): foreach($pendingTasks as $task): ?><a href="<?= url('/operation/issues') ?>"><i><ion-icon name="<?= htmlspecialchars($task->icon) ?>"></ion-icon></i><span><strong><?= htmlspecialchars($task->title) ?></strong><small><?= htmlspecialchars($task->subtitle ?: 'Atividade operacional') ?></small></span><time><?= htmlspecialchars($task->due??'Sem prazo') ?></time></a><?php endforeach; else: ?><div class="operation-empty"><ion-icon name="checkmark-done-outline"></ion-icon><strong>Nenhuma atividade pendente</strong><small>Novas tarefas aparecerão aqui.</small></div><?php endif; ?></div><footer><a class="operation-btn full" href="<?= url('/operation/issues') ?>"><ion-icon name="add-outline"></ion-icon>Nova pendência</a></footer></article>
</section>

<nav class="operation-quick-access" aria-label="Acesso rápido"><strong>Acesso rápido</strong><?php foreach([['Visitas','/operation/visits','calendar-outline'],['Pendências','/operation/issues','warning-outline'],['Equipamentos','/operation/assets','construct-outline'],['Condomínios','/operation/condominiums','business-outline'],['Solicitações','/operation/requests','heart-outline']] as [$label,$href,$icon]): ?><a href="<?= url($href) ?>"><ion-icon name="<?= $icon ?>"></ion-icon><?= $label ?></a><?php endforeach; ?></nav>
<?php $this->start('scripts') ?><script>window.addEventListener('operation:updated',event=>{const counts=event.detail?.counts||{};document.querySelectorAll('[data-operation-count]').forEach(node=>{const key=node.dataset.operationCount;if(counts[key]!==undefined)node.textContent=counts[key]})});</script><?php $this->stop() ?>
