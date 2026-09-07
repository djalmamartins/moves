<?php $this->layout('layouts/erp'); ?>
<?php if (!$condo->select): ?>
    <?php $this->insert('/pages/welcome-condo'); ?>
<?php else: ?>
<div class="container">
    <?php $this->insert('components/finance/sidebar'); ?>
    <main class="page_main">
        <header><h1>Financeiro</h1><p>Visão canônica de lançamentos e pagamentos do condomínio.</p></header>
        <section class="finance-summary">
            <article><small>A receber</small><strong>R$ <?= number_format((float)$totals['receivable'], 2, ',', '.') ?></strong></article>
            <article><small>Recebido</small><strong>R$ <?= number_format((float)$totals['received'], 2, ',', '.') ?></strong></article>
            <article><small>A pagar</small><strong>R$ <?= number_format((float)$totals['payable'], 2, ',', '.') ?></strong></article>
            <article><small>Pago</small><strong>R$ <?= number_format((float)$totals['paid'], 2, ',', '.') ?></strong></article>
        </section>
        <p><a class="btn btn_blue" href="<?= url('/erp/finance/entries') ?>">Gerenciar lançamentos</a></p>
        <section class="finance-columns">
            <?php foreach ([['Contas a pagar', $expense], ['Contas a receber', $income]] as [$title, $rows]): ?>
                <article><h2><?= $title ?></h2>
                    <?php if (!$rows): ?><p>Nenhum lançamento pendente.</p><?php endif; ?>
                    <?php foreach (array_slice($rows, 0, 8) as $item): ?>
                        <div class="finance-row"><span><strong><?= htmlspecialchars($item->description) ?></strong><small><?= date_fmt($item->due_at, 'd/m/Y') ?></small></span><b>R$ <?= number_format((float)$item->amount - (float)$item->paid_amount, 2, ',', '.') ?></b></div>
                    <?php endforeach; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</div>
<style>.finance-summary,.finance-columns{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:18px 0}.finance-summary article,.finance-columns article{padding:16px;border:1px solid #e5e7eb;border-radius:10px;background:#fff}.finance-summary small,.finance-summary strong,.finance-row small{display:block}.finance-summary strong{margin-top:8px;font-size:1.25rem}.finance-columns{grid-template-columns:1fr 1fr}.finance-row{display:flex;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #eee}.finance-row small{color:#777;margin-top:4px}@media(max-width:800px){.finance-summary{grid-template-columns:repeat(2,1fr)}.finance-columns{grid-template-columns:1fr}}</style>
<?php endif; ?>
