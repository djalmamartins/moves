<?php $this->layout('layouts/studio'); ?>
<?php
$totalViews = 0;
foreach ($accessDays as $day) { $totalViews += (int)$day->pages; }
?>
<section class="studio-page-head organic-page-header studio-dashboard-head">
    <div><p class="organic-eyebrow">Visão geral</p><h1 class="organic-page-title">Olá, <?= htmlspecialchars($user->first_name) ?>!</h1><p class="organic-page-description">Aqui está o resumo do seu site hoje.</p></div>
    <div class="organic-page-actions"><a class="organic-btn organic-btn-outline" href="<?= url('/') ?>" target="_blank"><ion-icon name="globe-outline"></ion-icon>Visualizar site</a></div>
</section>
<section class="studio-dashboard-kpis">
    <a class="organic-card" href="<?= url('/studio/pages') ?>"><i><ion-icon name="documents-outline"></ion-icon></i><div><span>Páginas</span><strong><?= $pagesCount ?></strong><small>conteúdos cadastrados</small></div></a>
    <a class="organic-card" href="<?= url('/studio/blog/home') ?>"><i><ion-icon name="newspaper-outline"></ion-icon></i><div><span>Artigos</span><strong><?= $postsCount ?></strong><small><?= $publishedCount ?> publicados</small></div></a>
    <a class="organic-card" href="<?= url('/studio/users') ?>"><i><ion-icon name="people-outline"></ion-icon></i><div><span>Usuários</span><strong><?= $usersCount ?></strong><small><?= $onlineCount ?> online agora</small></div></a>
    <a class="organic-card" href="<?= url('/studio/reports') ?>"><i><ion-icon name="eye-outline"></ion-icon></i><div><span>Visualizações</span><strong><?= number_format($totalViews, 0, ',', '.') ?></strong><small>nos últimos registros</small></div></a>
    <?php if($user->can('proposals.manage')):?><a class="organic-card" href="<?= url('/studio/proposals') ?>"><i><ion-icon name="document-text-outline"></ion-icon></i><div><span>Propostas</span><strong>Ver lista</strong><small>acompanhe negociações</small></div></a><?php endif;?>
</section>
<section class="studio-dashboard-actions organic-card organic-card-body">
    <p class="organic-eyebrow">Ações rápidas</p>
    <div class="organic-cluster"><a class="organic-btn organic-btn-primary" href="<?= url('/studio/page') ?>">Nova página</a>
    <a class="organic-btn" href="<?= url('/studio/blog/post') ?>">Novo artigo</a>
    <a class="organic-btn" href="<?= url('/studio/slide') ?>">Novo destaque</a>
    <a class="organic-btn" href="<?= url('/studio/faqs') ?>">Perguntas frequentes</a>
    <?php if($user->can('proposals.manage')):?><a class="organic-btn" href="<?= url('/studio/proposals') ?>">Propostas recebidas</a><?php endif;?>
    <a class="organic-btn" href="<?= url('/studio/settings') ?>">Configurações</a></div>
</section>
