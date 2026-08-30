<?php $this->layout('layouts/studio'); ?>
<?php
$totalViews = 0;
foreach ($accessDays as $day) { $totalViews += (int)$day->pages; }
?>
<section class="studio-page-head studio-dashboard-head">
    <div><p class="studio-eyebrow">Visão geral</p><h1 class="studio-page-title">Olá, <?= htmlspecialchars($user->first_name) ?>!</h1><p class="studio-page-description">Aqui está o resumo do seu site hoje.</p></div>
    <div class="actions"><a class="studio-btn outline" href="<?= url('/') ?>" target="_blank"><ion-icon name="globe-outline"></ion-icon>Visualizar site</a></div>
</section>
<section class="studio-dashboard-kpis">
    <a class="studio-panel" href="<?= url('/studio/pages') ?>"><i><ion-icon name="documents-outline"></ion-icon></i><div><span>Páginas</span><strong><?= $pagesCount ?></strong><small>conteúdos cadastrados</small></div></a>
    <a class="studio-panel" href="<?= url('/studio/blog/home') ?>"><i><ion-icon name="newspaper-outline"></ion-icon></i><div><span>Artigos</span><strong><?= $postsCount ?></strong><small><?= $publishedCount ?> publicados</small></div></a>
    <a class="studio-panel" href="<?= url('/studio/users') ?>"><i><ion-icon name="people-outline"></ion-icon></i><div><span>Usuários</span><strong><?= $usersCount ?></strong><small><?= $onlineCount ?> online agora</small></div></a>
    <a class="studio-panel" href="<?= url('/studio/reports') ?>"><i><ion-icon name="eye-outline"></ion-icon></i><div><span>Visualizações</span><strong><?= number_format($totalViews, 0, ',', '.') ?></strong><small>nos últimos registros</small></div></a>
    <?php if($user->can('proposals.manage')):?><a class="studio-panel" href="<?= url('/studio/proposals') ?>"><i><ion-icon name="document-text-outline"></ion-icon></i><div><span>Propostas</span><strong>Ver lista</strong><small>acompanhe negociações</small></div></a><?php endif;?>
</section>
<section class="studio-dashboard-actions studio-panel-body">
    <p class="studio-eyebrow">Ações rápidas</p>
    <div class="studio-actions"><a class="studio-btn primary" href="<?= url('/studio/page') ?>">Nova página</a>
    <a class="studio-btn" href="<?= url('/studio/blog/post') ?>">Novo artigo</a>
    <a class="studio-btn" href="<?= url('/studio/slide') ?>">Novo destaque</a>
    <a class="studio-btn" href="<?= url('/studio/faqs') ?>">Perguntas frequentes</a>
    <?php if($user->can('proposals.manage')):?><a class="studio-btn" href="<?= url('/studio/proposals') ?>">Propostas recebidas</a><?php endif;?>
    <a class="studio-btn" href="<?= url('/studio/settings') ?>">Configurações</a></div>
</section>
